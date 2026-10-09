<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reseller\RegisterPaymentRequest;
use App\Http\Requests\Admin\Reseller\StoreResellerRequest;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionActivator;
use App\Support\ClientCredentials;
use App\Support\Money;
use App\Support\Offers;
use App\Support\ResellerSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Revendedores: alta con usuario y contraseña, y registro a mano de los pagos de su suscripción
 * (los cobrados en línea se registran solos). El administrador ve quién vence y le escribe.
 */
class ResellerController extends Controller
{
    public function index(): View
    {
        // Primero los que vencen antes (los que nunca pagaron, al final): así se ve a quién escribirle
        $resellers = User::query()
            ->where('is_reseller', true)
            ->with(['subscriptionPayments' => fn ($query) => $query->latest('paid_at')->latest('id')->limit(1)])
            // El cupo usado del mes lo cuenta la base, no una consulta por fila
            ->withCount(['resellerInvitations as month_invitations_count' => fn ($query) => $query->where('created_at', '>=', now()->timezone(config('app.timezone'))->startOfMonth())])
            ->orderByRaw('subscription_renews_at is null')
            ->orderBy('subscription_renews_at')
            ->orderBy('name')
            ->get();

        $rows = $resellers->map(fn (User $reseller) => $this->row($reseller));

        return view('admin.resellers.index', [
            'rows' => $rows,
            // Vencen en los próximos días o ya vencieron: hay que escribirles
            'dueSoon' => $rows->filter(fn (array $row) => $row['dueSoon'])->values(),
            'plans' => config('bida.reseller_plans', []),
        ]);
    }

    public function store(StoreResellerRequest $request, ClientCredentials $credentials): RedirectResponse
    {
        $name = trim($request->validated('name'));
        $password = $credentials->password();

        // Mismo alta que un cliente: usuario a partir del nombre y contraseña dictable, que no se guarda legible
        $reseller = User::create([
            'name' => $name,
            'username' => $credentials->username($name),
            'password' => Hash::make($password),
            'is_admin' => false,
            'is_reseller' => true,
            'reseller_plan' => $request->validated('plan'),
            'business_name' => $request->validated('business_name') ?: null,
        ]);

        return redirect()
            ->route('admin.resellers.index')
            ->with('success', "{$reseller->name} ya es revendedor. Registra su primer pago para activarlo.")
            // Se muestra una sola vez: si se pierde, se genera otra desde el editor de cualquier invitación suya
            ->with('reseller_credentials', [
                'name' => $reseller->name,
                'username' => $reseller->username,
                'password' => $password,
            ]);
    }

    public function registerPayment(RegisterPaymentRequest $request, User $reseller, SubscriptionActivator $activator): RedirectResponse
    {
        abort_unless($reseller->isReseller(), 404);

        // El código del formulario hace que un doble clic o una recarga no sume otro mes
        $payment = $activator->recordPayment($reseller, $request->validated('plan'), [
            'amount' => $request->validated('amount'),
            'currency' => Money::code(),
            'registered_by' => $request->user()->id,
            'note' => $request->validated('note'),
            'request_token' => $request->validated('request_token'),
        ]);

        if ($payment === null) {
            return back()->with('success', 'Ese pago ya estaba registrado: no se sumó otro mes.');
        }

        return back()->with(
            'success',
            "Pago registrado: {$reseller->name} queda al día hasta el ".$payment->renews_until->locale('es')->translatedFormat('j \d\e F \d\e Y').'.'
        );
    }

    /**
     * Anula el último pago de un revendedor registrado a mano (uno cargado por error): la fecha
     * vuelve a la del pago anterior. Los cobrados con tarjeta o QR se devuelven en la pasarela; aquí
     * no se tocan, para que el historial no diga algo distinto de lo que cobró la pasarela.
     */
    public function destroyPayment(User $reseller, SubscriptionPayment $payment, SubscriptionActivator $activator): RedirectResponse
    {
        abort_unless($reseller->isReseller() && (int) $payment->user_id === (int) $reseller->id, 404);
        abort_unless($payment->isManual(), 422, 'Los pagos con tarjeta o QR se devuelven desde la pasarela.');
        abort_unless($activator->annulLatest($payment), 422, 'Solo se puede anular el último pago.');

        return back()->with('success', "Pago anulado. {$reseller->name} vuelve a la fecha que tenía antes de ese pago.");
    }

    /** Un revendedor en la tabla: plan, estado, cupo del mes y el enlace para escribirle. */
    private function row(User $reseller): array
    {
        $plan = $reseller->planConfig();
        $daysLeft = ResellerSubscription::daysLeft($reseller);
        $limit = ResellerSubscription::quotaLimit($reseller);
        $used = (int) $reseller->month_invitations_count;

        [$statusLabel, $statusClass] = match (true) {
            $reseller->subscription_renews_at === null => ['Sin pagos', 'is-pending'],
            // La tarjeta no pasó; la pasarela la reintenta mientras corren los días de gracia
            $reseller->subscription_status === 'past_due' && $reseller->hasActiveSubscription() => ['Cobro fallido', 'is-danger'],
            ResellerSubscription::inGrace($reseller) => ['En días de gracia', 'is-pending'],
            $reseller->hasActiveSubscription() && $daysLeft > ResellerSubscription::WARNING_DAYS => ['Al día', 'is-success'],
            $reseller->hasActiveSubscription() => ['Vence pronto', 'is-pending'],
            $reseller->subscription_status === 'canceled' => ['Cancelada', 'is-primary'],
            default => ['Vencida', 'is-danger'],
        };

        return [
            'reseller' => $reseller,
            'planKey' => $reseller->reseller_plan,
            'planName' => $plan['name'] ?? 'Sin plan',
            // Lo que se cobra hoy: con el descuento del plan, si tiene
            'planPrice' => Offers::planPrice($plan),
            'statusLabel' => $statusLabel,
            'statusClass' => $statusClass,
            'daysLeft' => $daysLeft,
            // A quién escribirle: vence pronto y no se cobra solo (o el cobro con tarjeta falló)
            'dueSoon' => $reseller->subscription_renews_at !== null && ResellerSubscription::expiresSoon($reseller)
                && ! ($reseller->renewsAutomatically() && $reseller->subscription_status === 'active'),
            'renewsLabel' => $reseller->subscription_renews_at?->locale('es')->translatedFormat('j \d\e F \d\e Y') ?? '—',
            // Con tarjeta se cobra solo: no hace falta escribirle para renovar
            'renewal' => match (true) {
                $reseller->renewsAutomatically() => 'Con tarjeta: se renueva sola',
                $reseller->hasRecurringSubscription() => 'Con tarjeta: canceló la renovación',
                default => null,
            },
            'quotaLabel' => $limit === null ? "{$used} (sin tope)" : "{$used} de {$limit}",
            'lastPayment' => $reseller->subscriptionPayments->first(),
            // WhatsApp sin número: el administrador elige el chat y envía él mismo el mensaje
            'reminderUrl' => 'https://wa.me/?text='.rawurlencode($this->reminder($reseller, $daysLeft, $plan)),
        ];
    }

    private function reminder(User $reseller, ?int $daysLeft, ?array $plan): string
    {
        $when = match (true) {
            $daysLeft === null => 'todavía no tiene un pago registrado',
            $daysLeft < 0 => 'venció hace '.abs($daysLeft).' '.(abs($daysLeft) === 1 ? 'día' : 'días'),
            $daysLeft === 0 => 'vence hoy',
            default => "vence en {$daysLeft} ".($daysLeft === 1 ? 'día' : 'días'),
        };

        return "Hola {$reseller->name}, te escribimos de ".config('bida.brand').": tu suscripción {$when}. "
            .'Para seguir creando invitaciones, el plan '.($plan['name'] ?? '').' cuesta '.Money::format(Offers::planPrice($plan)).' al mes.';
    }
}
