<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StartCheckoutRequest;
use App\Models\PaymentOrder;
use App\Payments\PaymentGateways;
use App\Support\Money;
use App\Support\Offers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * Pagar un plan de «Hazlo tú» en línea: elegir plan y forma de pago, ir a la pasarela y esperar su
 * aviso. La suscripción se activa recién cuando la pasarela avisa que cobró (WebhookProcessor), no
 * al volver de su página: volver no prueba que se haya pagado.
 *
 * Con la tarjeta la suscripción se renueva sola; para cambiar de plan, de tarjeta o cancelar, el
 * cliente va al portal de la pasarela (así nunca quedan dos suscripciones cobrándose a la vez).
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

        return view('client.subscription.show', [
            'user' => $user,
            'plans' => $plans,
            'selected' => old('plan', $selected),
            'methods' => $this->gateways->availableMethods(),
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

        // Ya tiene una tarjeta que se renueva sola: otra compra la cobraría dos veces
        if ($method === 'card' && $user->renewsAutomatically()) {
            return back()->withInput()->withErrors(['plan' => 'Ya pagas con tarjeta y se renueva sola. Para cambiar de plan, entra a «Administrar mi suscripción».']);
        }

        // Una sola compra abierta por pasarela: la anterior sin pagar queda vencida
        $user->paymentOrders()->pending()->where('provider', $provider)->get()
            ->each(fn (PaymentOrder $order) => $order->close(PaymentOrder::EXPIRED, 'Reemplazada por una compra nueva'));

        $order = PaymentOrder::forSubscription(
            $user,
            $plan,
            1,
            number_format(Offers::planPrice(config("bida.reseller_plans.{$plan}")), 2, '.', ''),
            Money::code(),
            $provider,
        );

        try {
            $checkout = $this->gateways->get($provider)->createCheckout($order);
        } catch (Throwable $e) {
            report($e);
            $order->close(PaymentOrder::FAILED, 'No se pudo abrir el cobro en la pasarela');

            return back()->withInput()->withErrors(['method' => 'No pudimos abrir el pago. Inténtalo de nuevo en un momento.']);
        }

        $order->forceFill(['provider_ref' => $checkout->providerRef, 'expires_at' => $checkout->expiresAt])->save();

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

        if (! $url) {
            return back()->withErrors(['portal' => 'No pudimos abrir tu suscripción en este momento. Inténtalo de nuevo en unos minutos.']);
        }

        return redirect()->away($url);
    }
}
