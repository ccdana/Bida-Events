<?php

namespace App\Services;

use App\Events\InvitationUpdated;
use App\Models\Invitation;
use App\Models\InvitationPayment;
use App\Models\PaymentOrder;
use App\Support\DiyInvitations;
use App\Support\Packages;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * El único lugar donde un cobro cambia una invitación de «Hazlo tú»: la publica con el nivel pagado
 * (o la sube de nivel) y registra el pago; si se devuelve, la vuelve al nivel que tenía.
 *
 * Como SubscriptionActivator: cada cobro se aplica una sola vez aunque el aviso llegue repetido (por
 * la orden y por el id del cobro en la pasarela), con la orden y la invitación bloqueadas.
 */
class InvitationActivator
{
    /**
     * La pasarela cobró la orden: queda pagada, la invitación toma su nivel (nunca baja: si mientras
     * tanto subió más, se queda con el mayor) y se publica. Devuelve null si ya estaba aplicada.
     *
     * @param  array{amount?: string|null, currency?: string|null}  $charged  Lo que cobró de verdad la pasarela
     */
    public function completeOrder(PaymentOrder $order, ?string $externalId = null, array $charged = []): ?InvitationPayment
    {
        $payment = DB::transaction(function () use ($order, $externalId, $charged) {
            $order = PaymentOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (in_array($order->status, [PaymentOrder::PAID, PaymentOrder::REFUNDED], true)) {
                return null;
            }

            if (! $order->isForInvitation() || ! Packages::isTier($order->plan)) {
                throw new DomainException("La orden {$order->reference_code} no es de una invitación.");
            }

            $invitation = Invitation::whereKey($order->invitation_id)->lockForUpdate()->first()
                ?? throw new DomainException("La invitación de la orden {$order->reference_code} ya no existe.");

            if ($externalId && InvitationPayment::where('provider', $order->provider)->where('external_id', $externalId)->exists()) {
                return null;
            }

            $order->forceFill(['status' => PaymentOrder::PAID, 'paid_at' => now()])->save();

            $from = $invitation->package;
            $tier = Packages::isTier($from) ? Packages::higherTier($from, $order->plan) : $order->plan;

            $invitation->forceFill([
                'package' => $tier,
                'status' => 'active',
                'expires_at' => DiyInvitations::expiresAt($invitation->event_date, $tier),
            ])->save();

            return InvitationPayment::create([
                'user_id' => $order->user_id,
                'invitation_id' => $invitation->id,
                'payment_order_id' => $order->id,
                'tier' => $tier,
                'from_tier' => $from,
                'amount' => $charged['amount'] ?? $order->amount,
                'currency' => $charged['currency'] ?? $order->currency,
                'provider' => $order->provider,
                'external_id' => $externalId,
                'paid_at' => now(),
            ]);
        });

        // Fuera de la transacción: la caché de la página pública se limpia con la invitación ya guardada
        if ($payment?->invitation) {
            InvitationUpdated::dispatch($payment->invitation);
        }

        return $payment;
    }

    /**
     * Devolución completa: la invitación vuelve al nivel que tenía antes de ese pago (un borrador vuelve
     * a ser borrador y se oculta). Solo si es el último pago de la invitación; si no, devuelve false para
     * revisarlo a mano.
     */
    public function refund(InvitationPayment $payment): bool
    {
        $done = DB::transaction(function () use ($payment) {
            $invitation = $payment->invitation_id ? Invitation::whereKey($payment->invitation_id)->lockForUpdate()->first() : null;
            $latest = InvitationPayment::where('invitation_id', $payment->invitation_id)->latest('paid_at')->latest('id')->first();

            if (! $latest?->is($payment)) {
                return false;
            }

            $payment->order?->forceFill(['status' => PaymentOrder::REFUNDED])->save();
            $payment->delete();

            if ($invitation) {
                $from = Packages::isTier($payment->from_tier) ? $payment->from_tier : null;

                $invitation->forceFill([
                    'package' => $from,
                    'status' => $from === null ? 'inactive' : $invitation->status,
                    'expires_at' => DiyInvitations::expiresAt($invitation->event_date, $from),
                ])->save();
            }

            return true;
        });

        if ($done && $payment->invitation) {
            InvitationUpdated::dispatch($payment->invitation->fresh());
        }

        return $done;
    }

    /**
     * Subir de nivel sin cobrar (la diferencia es 0, por ejemplo con un descuento nuevo): igual que un
     * cobro, sin pasarela ni pago registrado.
     */
    public function grant(Invitation $invitation, string $tier): void
    {
        $tier = Packages::isTier($invitation->package) ? Packages::higherTier($invitation->package, $tier) : $tier;

        $invitation->forceFill([
            'package' => $tier,
            'status' => 'active',
            'expires_at' => DiyInvitations::expiresAt($invitation->event_date, $tier),
        ])->save();

        InvitationUpdated::dispatch($invitation);
    }
}
