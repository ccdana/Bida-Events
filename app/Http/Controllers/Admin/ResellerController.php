<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Reseller\RegisterPaymentRequest;
use App\Http\Requests\Admin\Reseller\StoreResellerRequest;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\ClientCredentials;
use App\Support\ResellerSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Revendedores: alta con usuario y contraseña, y registro de los pagos de su suscripción.
 * No hay cobro automático ni mensajes automáticos: el administrador ve quién vence y le escribe.
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

    public function registerPayment(RegisterPaymentRequest $request, User $reseller): RedirectResponse
    {
        abort_unless($reseller->isReseller(), 404);

        $plan = $request->validated('plan');

        $renewsUntil = DB::transaction(function () use ($request, $reseller, $plan) {
            // El revendedor queda bloqueado mientras se registra: dos envíos del mismo formulario no se pisan
            $reseller = User::whereKey($reseller->id)->lockForUpdate()->firstOrFail();

            // Mismo formulario enviado otra vez (doble clic, recarga): el pago ya está, no se suma otro mes
            if (SubscriptionPayment::where('request_token', $request->validated('request_token'))->exists()) {
                return null;
            }

            $renewsUntil = ResellerSubscription::renewalDate($reseller, $plan);

            SubscriptionPayment::create([
                'user_id' => $reseller->id,
                'plan' => $plan,
                'amount' => $request->validated('amount'),
                'paid_at' => now()->toDateString(),
                'renews_until' => $renewsUntil->toDateString(),
                'registered_by' => $request->user()->id,
                'note' => $request->validated('note'),
                'request_token' => $request->validated('request_token'),
            ]);

            $reseller->update([
                'reseller_plan' => $plan,
                'subscription_status' => 'active',
                'subscription_renews_at' => $renewsUntil->toDateString(),
            ]);

            return $renewsUntil;
        });

        if ($renewsUntil === null) {
            return back()->with('success', 'Ese pago ya estaba registrado: no se sumó otro mes.');
        }

        return back()->with(
            'success',
            "Pago registrado: {$reseller->name} queda al día hasta el ".$renewsUntil->locale('es')->translatedFormat('j \d\e F \d\e Y').'.'
        );
    }

    /**
     * Anula el último pago de un revendedor (uno cargado por error). Solo el último, para que la
     * cadena de renovaciones no quede con huecos: la fecha vuelve a la del pago anterior o, si no
     * había otro, la suscripción queda sin activar.
     */
    public function destroyPayment(User $reseller, SubscriptionPayment $payment): RedirectResponse
    {
        abort_unless($reseller->isReseller() && (int) $payment->user_id === (int) $reseller->id, 404);

        $latest = $reseller->subscriptionPayments()->latest('paid_at')->latest('id')->first();
        abort_unless($latest?->is($payment), 422, 'Solo se puede anular el último pago.');

        DB::transaction(function () use ($reseller, $payment) {
            $payment->delete();

            $previous = $reseller->subscriptionPayments()->latest('paid_at')->latest('id')->first();

            $reseller->update([
                'reseller_plan' => $previous?->plan ?? $reseller->reseller_plan,
                'subscription_status' => $previous ? 'active' : null,
                'subscription_renews_at' => $previous?->renews_until?->toDateString(),
            ]);
        });

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
            $reseller->hasActiveSubscription() && $daysLeft > ResellerSubscription::WARNING_DAYS => ['Al día', 'is-success'],
            $reseller->hasActiveSubscription() => ['Vence pronto', 'is-pending'],
            $reseller->subscription_status === 'canceled' => ['Cancelada', 'is-primary'],
            default => ['Vencida', 'is-danger'],
        };

        return [
            'reseller' => $reseller,
            'planKey' => $reseller->reseller_plan,
            'planName' => $plan['name'] ?? 'Sin plan',
            'planPrice' => (int) ($plan['price'] ?? 0),
            'statusLabel' => $statusLabel,
            'statusClass' => $statusClass,
            'daysLeft' => $daysLeft,
            'dueSoon' => $reseller->subscription_renews_at !== null && ResellerSubscription::expiresSoon($reseller),
            'renewsLabel' => $reseller->subscription_renews_at?->locale('es')->translatedFormat('j \d\e F \d\e Y') ?? '—',
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
            .'Para seguir creando invitaciones, el plan '.($plan['name'] ?? '').' cuesta '.($plan['price'] ?? '').' Bs al mes.';
    }
}
