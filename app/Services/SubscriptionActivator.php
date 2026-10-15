<?php

namespace App\Services;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Support\ResellerSubscription;
use DomainException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * El único lugar donde un pago extiende una suscripción, venga de donde venga: el administrador que
 * lo registra a mano, la tarjeta o el QR (cuando la pasarela avisa que cobró una orden).
 *
 * Cada pago se registra una sola vez aunque llegue repetido (doble clic en el formulario, una
 * pasarela que reintenta su aviso): se reconoce por el código del formulario, por el id del cobro en
 * la pasarela o por la orden. Todo corre con la cuenta bloqueada, así dos pagos simultáneos no se pisan.
 */
class SubscriptionActivator
{
    /**
     * Registra el pago y deja la suscripción al día con ese plan. Quien paga queda como revendedor
     * (una cuenta abierta desde el sitio se vuelve revendedor con su primer pago).
     * Devuelve null si ese pago ya estaba registrado.
     *
     * @param  array{amount: string|int|float, currency: string, provider?: string, payment_order_id?: int|null, external_id?: string|null, registered_by?: int|null, note?: string|null, request_token?: string|null}  $payment
     * @param  int|null  $months  Meses que paga; null = el ciclo del plan
     */
    public function recordPayment(User $user, string $plan, array $payment, ?int $months = null): ?SubscriptionPayment
    {
        $payment['provider'] ??= SubscriptionPayment::MANUAL;

        return DB::transaction(function () use ($user, $plan, $payment, $months) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            if ($this->alreadyRecorded($payment)) {
                return null;
            }

            $renewsUntil = ResellerSubscription::renewalDate($user, $plan, months: $months);

            $record = SubscriptionPayment::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'amount' => $payment['amount'],
                'currency' => $payment['currency'],
                'provider' => $payment['provider'],
                'payment_order_id' => $payment['payment_order_id'] ?? null,
                'external_id' => $payment['external_id'] ?? null,
                'paid_at' => now()->toDateString(),
                'renews_until' => $renewsUntil->toDateString(),
                'registered_by' => $payment['registered_by'] ?? null,
                'note' => $payment['note'] ?? null,
                'request_token' => $payment['request_token'] ?? null,
            ]);

            $user->forceFill([
                'is_reseller' => true,
                'reseller_plan' => $plan,
                'subscription_status' => 'active',
                'subscription_renews_at' => $renewsUntil->toDateString(),
            ])->save();

            return $record;
        });
    }

    /**
     * La pasarela cobró la orden: queda pagada y se registra su pago. También si se había dado por
     * vencida o fallida (un QR pagado justo después de vencer igual se cobró). Una orden ya pagada o
     * devuelta no se vuelve a aplicar: devuelve null.
     *
     * $charged: lo que cobró de verdad la pasarela (con impuestos, en su moneda), si lo informa; si
     * no, se registra el monto de la orden.
     *
     * @param  array{amount?: string|null, currency?: string|null}  $charged
     */
    public function completeOrder(PaymentOrder $order, ?string $externalId = null, array $charged = []): ?SubscriptionPayment
    {
        return DB::transaction(function () use ($order, $externalId, $charged) {
            $order = PaymentOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($order->status, [PaymentOrder::PAID, PaymentOrder::REFUNDED], true)) {
                return null;
            }

            if ($order->purpose !== PaymentOrder::PURPOSE_SUBSCRIPTION || ! $order->plan) {
                throw new DomainException("La orden {$order->reference_code} no es de un plan de revendedor.");
            }

            $user = $order->user ?? throw new DomainException("La cuenta de la orden {$order->reference_code} ya no existe.");

            $order->forceFill(['status' => PaymentOrder::PAID, 'paid_at' => now()])->save();

            return $this->recordPayment($user, $order->plan, [
                'amount' => $charged['amount'] ?? $order->amount,
                'currency' => $charged['currency'] ?? $order->currency,
                'provider' => $order->provider,
                'payment_order_id' => $order->id,
                'external_id' => $externalId,
            ], $order->months);
        });
    }

    /**
     * Anota qué suscripción de la pasarela es de esta persona (la que se renueva sola): con ese id
     * se la reconoce en cada renovación.
     */
    public function linkGatewaySubscription(User $user, string $provider, ?string $subscriptionId, ?string $customerId = null, ?int $cycleMonths = null): void
    {
        $changes = array_filter([
            'payment_provider' => $provider,
            'provider_subscription_id' => $subscriptionId,
            'provider_customer_id' => $customerId,
            // Cada cuántos meses renueva (1, 3 o 12): lo que suma cada renovación cobrada
            'subscription_cycle_months' => $cycleMonths !== null ? max(1, $cycleMonths) : null,
        ]);

        // Una suscripción nueva empieza renovándose sola (la cancelación era de la anterior)
        if ($subscriptionId && $subscriptionId !== $user->provider_subscription_id) {
            $changes['cancel_at_period_end'] = false;
        }

        $user->forceFill($changes)->save();
    }

    /**
     * Lo que la pasarela dice del estado de la suscripción. No extiende la fecha (eso lo hace solo un
     * pago): marca el pago atrasado, la cancelación (sigue al día hasta el vencimiento), el fin de la
     * suscripción y el cambio de plan hecho desde el portal.
     *
     * Con $renewsAt (la próxima fecha de cobro de la pasarela), una suscripción al día toma esa fecha:
     * la pasarela es la que decide cuándo cobra, así la fecha del sitio no se corre después de un
     * cobro reintentado.
     *
     * Con $months (el ciclo de la variante: 1, 3 o 12), cambiar de ciclo desde el portal se anota igual
     * que el cambio de plan.
     *
     * @param  string  $status  active | past_due | cancelled | expired
     */
    public function applyGatewayStatus(User $user, string $status, bool $cancelled = false, ?string $plan = null, ?string $renewsAt = null, ?int $months = null): void
    {
        DB::transaction(function () use ($user, $status, $cancelled, $plan, $renewsAt, $months) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $hasSubscription = $user->subscription_status !== null;

            $changes = match ($status) {
                // Sigue normal o reanudó la renovación. Una atrasada vuelve a estar al día solo con
                // el aviso del cobro (recordPayment), nunca con el del estado, que puede llegar antes
                'active' => ['cancel_at_period_end' => $cancelled],
                // Canceló la renovación: puede usar lo pagado hasta el vencimiento
                'cancelled' => ['cancel_at_period_end' => true],
                'past_due' => $hasSubscription ? ['subscription_status' => 'past_due'] : [],
                // Terminó: no se renueva más
                'expired' => ['subscription_status' => $hasSubscription ? 'canceled' : null, 'cancel_at_period_end' => false],
                default => [],
            };

            // Cambió de plan desde el portal: rige desde ya (la pasarela cobra la diferencia)
            if ($plan && $plan !== $user->reseller_plan && in_array($status, ['active', 'cancelled'], true) && $hasSubscription) {
                $changes['reseller_plan'] = $plan;
            }

            if ($months && (int) $months !== (int) $user->subscription_cycle_months && in_array($status, ['active', 'cancelled'], true) && $hasSubscription) {
                $changes['subscription_cycle_months'] = max(1, (int) $months);
            }

            // Solo si ya está al día (con su pago registrado) y la fecha es futura: el aviso del
            // estado nunca extiende por sí solo una suscripción atrasada o sin pagar
            $nextCharge = $this->gatewayDate($renewsAt);

            if ($status === 'active' && $user->subscription_status === 'active' && $nextCharge?->isFuture()) {
                $changes['subscription_renews_at'] = $nextCharge->toDateString();
            }

            $user->forceFill($changes)->save();
        });
    }

    /**
     * Anula un pago (uno cargado por error). Solo el último, para que la cadena de renovaciones no
     * quede con huecos: la fecha vuelve a la del pago anterior o, si no había otro, la suscripción
     * queda sin activar. Devuelve false si no era el último.
     */
    public function annulLatest(SubscriptionPayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $user = User::whereKey($payment->user_id)->lockForUpdate()->firstOrFail();
            $latest = $user->subscriptionPayments()->latest('paid_at')->latest('id')->first();

            if (! $latest?->is($payment)) {
                return false;
            }

            $payment->delete();

            $previous = $user->subscriptionPayments()->latest('paid_at')->latest('id')->first();

            $user->forceFill([
                'reseller_plan' => $previous?->plan ?? $user->reseller_plan,
                'subscription_status' => $previous ? 'active' : null,
                'subscription_renews_at' => $previous?->renews_until?->toDateString(),
            ])->save();

            return true;
        });
    }

    /** Fecha ISO 8601 de la pasarela en la hora de Bolivia; null si no viene o no se entiende. */
    private function gatewayDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->timezone(config('app.timezone'))->startOfDay();
        } catch (Throwable) {
            return null;
        }
    }

    /** Ese pago ya está: mismo formulario, mismo cobro de la pasarela o misma orden. */
    private function alreadyRecorded(array $payment): bool
    {
        $query = SubscriptionPayment::query();
        $conditions = 0;

        if (! empty($payment['request_token'])) {
            $query->orWhere('request_token', $payment['request_token']);
            $conditions++;
        }

        if (! empty($payment['external_id'])) {
            $query->orWhere(fn ($q) => $q->where('provider', $payment['provider'])->where('external_id', $payment['external_id']));
            $conditions++;
        }

        if (! empty($payment['payment_order_id'])) {
            $query->orWhere('payment_order_id', $payment['payment_order_id']);
            $conditions++;
        }

        return $conditions > 0 && $query->exists();
    }
}
