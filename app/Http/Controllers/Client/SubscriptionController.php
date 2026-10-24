<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StartCheckoutRequest;
use App\Models\PaymentOrder;
use App\Payments\Contracts\ManagesSubscriptions;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\PaymentGateways;
use App\Payments\SubscriptionPrice;
use App\Support\Money;
use App\Support\Offers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

/**
 * Pagar un plan de «Únete» en línea: elegir plan y forma de pago, ir a la pasarela y esperar su
 * aviso. La suscripción se activa recién cuando la pasarela avisa que cobró (WebhookProcessor), no
 * al volver de su página: volver no prueba que se haya pagado.
 *
 * Con la tarjeta la suscripción se renueva sola, cada mes o (si la pasarela tiene ese ciclo) cada 3 o
 * 12 meses con su descuento; para cambiar de plan, de tarjeta o cancelar, el cliente va al portal de
 * la pasarela (así nunca quedan dos suscripciones cobrándose a la vez). Con el QR de Bolivia cada
 * pago cubre los meses que elige (1, 3 o 12), en bolivianos, con el mismo descuento.
 */
class SubscriptionController extends Controller
{
    public function __construct(private readonly PaymentGateways $gateways) {}

    public function show(Request $request): View
    {
        abort_unless(config('payments.enabled'), 404);

        $user = $request->user();
        $plans = Offers::resellerPlans();
        $selected = array_key_exists((string) $request->query('plan'), $plans)
            ? (string) $request->query('plan')
            : ($user->reseller_plan && array_key_exists($user->reseller_plan, $plans) ? $user->reseller_plan : array_key_first($plans));

        $methods = $this->gateways->availableMethods();

        return view('client.subscription.show', [
            'user' => $user,
            'plans' => $plans,
            'selected' => old('plan', $selected),
            // Desde la página de planes llega el ciclo elegido (?meses=12)
            'selectedMonths' => in_array((int) $request->query('meses'), array_keys(SubscriptionPrice::prepayOptions()), true) ? (int) $request->query('meses') : 1,
            'methods' => $methods,
            // Por método: su moneda, si se renueva sola y lo que cuesta cada plan por cada cantidad de meses
            'pricing' => collect($methods)->map(function (string $provider) use ($plans) {
                $gateway = $this->gateways->get($provider);

                return [
                    'currency' => $gateway->currency(),
                    'symbol' => Money::symbol($gateway->currency()),
                    'recurring' => $gateway->isRecurring(),
                    'prices' => SubscriptionPrice::table($gateway->currency()),
                    // Por plan, cuántos meses se pueden pagar (o cada cuántos se renueva) con esta forma de pago
                    'cycles' => collect($plans)->keys()->mapWithKeys(fn (string $plan) => [$plan => $this->cycles($gateway, $plan)])->all(),
                ];
            })->all(),
            'prepay' => SubscriptionPrice::prepayOptions(),
            'needsEmail' => blank($user->email) || ! $user->hasVerifiedEmail(),
            'recurring' => $user->hasRecurringSubscription(),
            'canOpenPortal' => $user->hasRecurringSubscription() && $this->gateways->subscriptionManager($user->payment_provider) !== null,
        ]);
    }

    public function store(StartCheckoutRequest $request): RedirectResponse
    {
        abort_unless(config('payments.enabled'), 404);

        $user = $request->user();
        $plan = $request->validated('plan');
        $method = $request->validated('method');

        // La pasarela necesita un correo real para el recibo y los avisos de la renovación
        if (blank($user->email) || ! $user->hasVerifiedEmail()) {
            return redirect()->route('client.account')
                ->withErrors(['email' => 'Para pagar en línea, agrega tu correo y confírmalo con el enlace que te enviamos.']);
        }

        $provider = $this->gateways->providerFor($method, $plan);

        if (! $provider) {
            return back()->withInput()->withErrors(['method' => 'Esa forma de pago no está disponible para este plan. Elige otra o escríbenos.']);
        }

        $gateway = $this->gateways->get($provider);

        // Ya tiene una tarjeta que se renueva sola: otra compra la cobraría dos veces. Con QR sí puede
        // pagar por adelantado (cuando la tarjeta no se renueva sola, por ejemplo)
        if ($gateway->isRecurring() && $user->renewsAutomatically()) {
            return back()->withInput()->withErrors(['plan' => 'Ya pagas con tarjeta y se renueva sola. Para cambiar de plan, entra a «Administrar mi suscripción».']);
        }

        // Con QR se pagan varios meses de una vez; con tarjeta, el ciclo con el que se renueva sola
        $months = (int) ($request->validated('months') ?? 1);

        if (! in_array($months, $this->cycles($gateway, $plan), true)) {
            return back()->withInput()->withErrors(['months' => 'Con esa forma de pago este plan no se paga por '.($months === 1 ? '1 mes' : "{$months} meses").'. Elige otra cantidad de meses.']);
        }

        // Una sola compra abierta por pasarela: la anterior sin pagar queda vencida
        $user->paymentOrders()->pending()->where('provider', $provider)->get()
            ->each(fn (PaymentOrder $order) => $order->close(PaymentOrder::EXPIRED, 'Reemplazada por una compra nueva'));

        $order = PaymentOrder::forSubscription(
            $user,
            $plan,
            $months,
            SubscriptionPrice::amount($plan, $months, $gateway->currency()),
            $gateway->currency(),
            $provider,
        );

        try {
            $checkout = $gateway->createCheckout($order);
        } catch (Throwable $e) {
            report($e);
            $order->close(PaymentOrder::FAILED, 'No se pudo abrir el cobro en la pasarela');

            return back()->withInput()->withErrors(['method' => 'No pudimos abrir el pago. Inténtalo de nuevo en un momento.']);
        }

        $order->forceFill([
            'provider_ref' => $checkout->providerRef,
            'expires_at' => $checkout->expiresAt,
            // El QR y la página de la pasarela se muestran en la página de la orden
            'meta' => array_filter(['qr_image' => $checkout->qrImage, 'payment_page_url' => $checkout->paymentPageUrl]) ?: null,
        ])->save();

        // Tarjeta: a la página de pago de la pasarela. QR: lo muestra la página de la orden
        return $checkout->redirectUrl
            ? redirect()->away($checkout->redirectUrl)
            : redirect()->route('client.subscription.order', $order->public_id);
    }

    /** A dónde vuelve el comprador: espera (consultando cada pocos segundos) el aviso del cobro. */
    public function order(Request $request, PaymentOrder $order): View|JsonResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        if ($request->wantsJson()) {
            return response()->json(['status' => $order->status]);
        }

        return view('client.subscription.order', [
            'order' => $order,
            'user' => $request->user(),
            'planName' => config("bida.reseller_plans.{$order->plan}.name", $order->plan),
        ]);
    }

    /**
     * Cuántos meses se pueden pagar de una vez (sin renovación automática: los de «payments.prepay») o
     * cada cuántos se renueva sola (tarjeta: los ciclos que tiene la pasarela para ese plan).
     *
     * @return list<int>
     */
    private function cycles(PaymentGateway $gateway, string $plan): array
    {
        $prepay = array_keys(SubscriptionPrice::prepayOptions());

        if ($gateway instanceof ManagesSubscriptions) {
            return array_values(array_intersect($gateway->billingCycles($plan), $prepay));
        }

        return $gateway->isRecurring() ? [1] : $prepay;
    }

    /** El portal de la pasarela (cambiar de plan o de tarjeta, cancelar, facturas). Su enlace vence: se pide en el momento. */
    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();
        $manager = $user->hasRecurringSubscription() ? $this->gateways->subscriptionManager($user->payment_provider) : null;

        try {
            $url = $manager?->portalUrl($user);
        } catch (Throwable $e) {
            report($e);
            $url = null;
        }

        // Un enlace firmado entra al portal sin pedir iniciar sesión; uno sin firma manda al ingreso por correo de Lemon Squeezy.
        // No se anota la firma: solo de dónde vino y si traía una (php artisan bida:portal-de-pagos usuario lo muestra)
        if ($url && ! str_contains($url, 'signature=')) {
            Log::warning('El portal de pagos llegó sin firma: pedirá iniciar sesión', ['user' => $user->id, 'host' => parse_url($url, PHP_URL_HOST), 'path' => parse_url($url, PHP_URL_PATH)]);
        }

        if (! $url) {
            return back()->withErrors(['portal' => 'No pudimos abrir tu suscripción en este momento. Inténtalo de nuevo en unos minutos.']);
        }

        return redirect()->away($url);
    }
}
