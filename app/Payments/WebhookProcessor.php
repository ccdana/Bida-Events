<?php

namespace App\Payments;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\SubscriptionActivator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Aplica un aviso guardado de una pasarela:
 * - una orden cobrada activa la suscripción; una rechazada o vencida se cierra;
 * - una renovación cobrada suma un mes; una que falla deja el pago atrasado;
 * - un cambio de la suscripción (cancelada, terminada, otro plan) se refleja en la cuenta;
 * - una devolución completa anula el pago y la fecha vuelve a la anterior.
 *
 * Un cobro que no se puede aplicar y no se arreglaría reintentando (una orden que no existe, un
 * monto que no coincide, una renovación de una suscripción que no conocemos) queda procesado con su
 * nota en «error», en el log de operación y en «bida:salud», para revisarlo a mano: ahí hay dinero
 * cobrado sin activar.
 */
class WebhookProcessor
{
    public function __construct(private readonly SubscriptionActivator $activator) {}

    public function process(WebhookEvent $webhook): void
    {
        if ($webhook->isProcessed()) {
            return;
        }

        $webhook->increment('attempts');
        $provider = $webhook->provider;
        $event = $webhook->gatewayEvent();

        $note = match ($event->type) {
            GatewayEvent::ORDER_PAID => $this->orderPaid($provider, $event),
            GatewayEvent::ORDER_FAILED => $this->closeOrder($provider, $event, PaymentOrder::FAILED),
            GatewayEvent::ORDER_EXPIRED => $this->closeOrder($provider, $event, PaymentOrder::EXPIRED),
            GatewayEvent::SUBSCRIPTION_UPDATED => $this->subscriptionUpdated($provider, $event),
            GatewayEvent::SUBSCRIPTION_RENEWED => $this->subscriptionRenewed($provider, $event),
            GatewayEvent::SUBSCRIPTION_PAYMENT_FAILED => $this->renewalFailed($provider, $event),
            GatewayEvent::PAYMENT_REFUNDED => $this->refunded($provider, $event),
            default => null,
        };

        $webhook->markProcessed($note);
    }

    private function orderPaid(string $provider, GatewayEvent $event): ?string
    {
        $order = $this->findOrder($provider, $event);

        if (! $order) {
            return $this->needsReview('Cobro de una orden que no existe', $provider, $event);
        }

        if (! $this->amountMatches($order, $event)) {
            return $this->needsReview(
                "El monto cobrado ({$event->amount} {$event->currency}) no coincide con la orden {$order->reference_code} ({$order->amount} {$order->currency})",
                $provider,
                $event
            );
        }

        // La pasarela dice qué plan cobró (Lemon Squeezy, por la variante): tiene que ser el de la orden
        $chargedPlan = $event->extra['plan'] ?? null;

        if ($chargedPlan !== null && $chargedPlan !== $order->plan) {
            return $this->needsReview("Se cobró el plan «{$chargedPlan}» y la orden {$order->reference_code} es del plan «{$order->plan}»", $provider, $event);
        }

        if (! $order->user) {
            return $this->needsReview("La cuenta de la orden {$order->reference_code} ya no existe", $provider, $event);
        }

        $this->activator->completeOrder($order, $event->externalId, [
            'amount' => $event->extra['charged_amount'] ?? null,
            'currency' => $event->extra['charged_currency'] ?? null,
        ]);

        if (! empty($event->extra['customer_id'])) {
            $this->activator->linkGatewaySubscription($order->user, $provider, null, $event->extra['customer_id']);
        }

        $this->warnIfPriceDiffers($order, $event);

        return null;
    }

    /** Un rechazo o un vencimiento no mueve dinero: si la orden no existe, no hay nada que revisar. */
    private function closeOrder(string $provider, GatewayEvent $event, string $status): ?string
    {
        $this->findOrder($provider, $event)?->close($status);

        return null;
    }

    /**
     * La suscripción cambió. La primera vez se la reconoce por nuestra orden (viaja en los datos de la
     * compra) y desde ahí por su id. Un estado de una suscripción que no conocemos no mueve dinero: se
     * ignora (si después llega una renovación de ella, esa sí queda para revisar).
     */
    private function subscriptionUpdated(string $provider, GatewayEvent $event): ?string
    {
        $user = $this->subscriber($provider, $event->externalId) ?? $this->findOrder($provider, $event)?->user;

        if (! $user) {
            return null;
        }

        $this->activator->linkGatewaySubscription($user, $provider, $event->externalId, $event->extra['customer_id'] ?? null);
        $this->activator->applyGatewayStatus(
            $user,
            (string) ($event->extra['status'] ?? 'unknown'),
            (bool) ($event->extra['cancelled'] ?? false),
            $event->extra['plan'] ?? null,
            $event->extra['renews_at'] ?? null,
        );

        return null;
    }

    private function subscriptionRenewed(string $provider, GatewayEvent $event): ?string
    {
        $subscriptionId = $event->extra['subscription_id'] ?? null;
        $user = $this->subscriber($provider, $subscriptionId) ?? $this->findOrder($provider, $event)?->user;

        if (! $user) {
            return $this->needsReview("Renovación cobrada de una suscripción que no conocemos ({$subscriptionId})", $provider, $event);
        }

        $plan = $event->extra['plan'] ?? $user->reseller_plan;

        if (! $plan) {
            return $this->needsReview("Renovación cobrada a {$user->username}, que no tiene plan", $provider, $event);
        }

        $this->activator->recordPayment($user, $plan, [
            'amount' => $event->extra['charged_amount'] ?? $event->amount ?? 0,
            'currency' => $event->extra['charged_currency'] ?? $event->currency ?? 'USD',
            'provider' => $provider,
            'external_id' => $event->externalId,
        ], months: 1);

        return null;
    }

    /** Falló el cobro de la renovación: el pago queda atrasado (con los días de gracia) mientras la pasarela reintenta. */
    private function renewalFailed(string $provider, GatewayEvent $event): ?string
    {
        $user = $this->subscriber($provider, $event->extra['subscription_id'] ?? null);

        if ($user) {
            $this->activator->applyGatewayStatus($user, 'past_due');
        }

        return null;
    }

    /**
     * Devolución completa: se anula ese pago y la fecha vuelve a la anterior (si era el último). Una
     * parcial no cambia la suscripción. Si es un cobro que no registramos (el aviso de factura que
     * acompaña al de la orden), no hay nada que anular.
     */
    private function refunded(string $provider, GatewayEvent $event): ?string
    {
        $payment = $event->externalId
            ? SubscriptionPayment::where('provider', $provider)->where('external_id', $event->externalId)->first()
            : null;

        if (! $payment || ! ($event->extra['full'] ?? false)) {
            Log::channel('operations')->info('Devolución sin cambios en la suscripción', [
                'provider' => $provider,
                'external_id' => $event->externalId,
                'full' => $event->extra['full'] ?? false,
                'found' => (bool) $payment,
            ]);

            return null;
        }

        $order = $payment->order;

        if (! $this->activator->annulLatest($payment)) {
            return $this->needsReview("Devolución de un pago que no es el último ({$event->externalId}): revisar la fecha de renovación a mano", $provider, $event);
        }

        $order?->forceFill(['status' => PaymentOrder::REFUNDED])->save();

        return null;
    }

    private function findOrder(string $provider, GatewayEvent $event): ?PaymentOrder
    {
        // public_id es uuid: otro texto ni se consulta (PostgreSQL lo rechazaría)
        if (! $event->orderId || ! Str::isUuid($event->orderId)) {
            return null;
        }

        return PaymentOrder::where('public_id', $event->orderId)->where('provider', $provider)->first();
    }

    private function subscriber(string $provider, ?string $subscriptionId): ?User
    {
        if (! $subscriptionId) {
            return null;
        }

        return User::where('payment_provider', $provider)->where('provider_subscription_id', $subscriptionId)->first();
    }

    /** Sin monto en el aviso no hay nada que comparar; con monto, tiene que ser el de la orden (al centavo). */
    private function amountMatches(PaymentOrder $order, GatewayEvent $event): bool
    {
        if ($event->amount === null) {
            return true;
        }

        $sameAmount = (int) round((float) $event->amount * 100) === (int) round((float) $order->amount * 100);
        $sameCurrency = $event->currency === null || strcasecmp($event->currency, $order->currency) === 0;

        return $sameAmount && $sameCurrency;
    }

    /**
     * Lemon Squeezy cobra el precio de su variante (más impuestos), no el de la orden. Si el precio
     * sin impuestos que mostró el sitio no coincide, se avisa: hay que igualar Ajustes y la variante.
     */
    private function warnIfPriceDiffers(PaymentOrder $order, GatewayEvent $event): void
    {
        $charged = $event->extra['charged_amount'] ?? null;

        if ($charged !== null && (float) $charged < (float) $order->amount) {
            Log::channel('operations')->warning('El cobro de la pasarela fue menor que el precio del sitio', [
                'order' => $order->reference_code,
                'plan' => $order->plan,
                'site' => $order->amount.' '.$order->currency,
                'charged' => $charged.' '.($event->extra['charged_currency'] ?? ''),
            ]);
        }
    }

    private function needsReview(string $problem, string $provider, GatewayEvent $event): string
    {
        Log::channel('operations')->error('Pago para revisar: '.$problem, [
            'provider' => $provider,
            'event' => $event->id,
            'order' => $event->orderId,
            'external_id' => $event->externalId,
        ]);

        return $problem.'.';
    }
}
