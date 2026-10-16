<?php

namespace Tests\Concerns;

use Illuminate\Testing\TestResponse;

/**
 * Avisos de Lemon Squeezy con la forma de su API (docs.lemonsqueezy.com › Webhooks): un recurso
 * JSON:API con meta.event_name y meta.custom_data, firmado con HMAC-SHA256 del cuerpo en X-Signature.
 */
trait LemonSqueezyPayloads
{
    protected const LS_SECRET = 'whsec-de-prueba';

    /**
     * Variantes de prueba de cada plan (y la anual de Inicial, «plan:meses») y la del pago único de
     * «Hazlo tú». Inicial no tiene la de cada 3 meses: ese ciclo solo se paga con QR.
     */
    protected const LS_VARIANTS = ['inicial' => '101', 'inicial:12' => '112', 'emprendedor' => '103', 'agencia' => '104', 'invitacion' => '199'];

    protected function configureLemonSqueezy(): void
    {
        config([
            'payments.enabled' => true,
            'payments.lemonsqueezy.api_key' => 'clave-de-prueba',
            'payments.lemonsqueezy.store_id' => '7',
            'payments.lemonsqueezy.signing_secret' => self::LS_SECRET,
            'payments.lemonsqueezy.variants' => self::LS_VARIANTS,
            // Sin depender del .env de quien corre las pruebas (LEMONSQUEEZY_TEST_MODE)
            'payments.lemonsqueezy.test_mode' => false,
        ]);
    }

    protected function lsSend(array $payload, ?string $signature = null): TestResponse
    {
        $body = json_encode($payload);
        $signature ??= hash_hmac('sha256', $body, self::LS_SECRET);

        return $this->call('POST', '/webhooks/lemonsqueezy', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature,
            'HTTP_X_EVENT_NAME' => $payload['meta']['event_name'],
        ], $body);
    }

    protected function lsEvent(string $event, string $type, string $id, array $attributes, array $custom = []): array
    {
        return [
            'meta' => array_filter(['event_name' => $event, 'custom_data' => $custom ?: null]),
            'data' => [
                'type' => $type,
                'id' => $id,
                'attributes' => $attributes + ['updated_at' => now()->toIso8601ZuluString(), 'test_mode' => true],
            ],
        ];
    }

    protected function lsOrderCreated(string $orderId, string $lsOrderId = '9001', string $plan = 'inicial', int $totalUsd = 900, string $status = 'paid'): array
    {
        return $this->lsEvent('order_created', 'orders', $lsOrderId, [
            'store_id' => 7,
            'customer_id' => 555,
            'identifier' => '3c2a1f0e-0000-4000-8000-000000000001',
            'order_number' => 1,
            'user_email' => 'comprador@correo.com',
            'currency' => 'USD',
            'subtotal' => $totalUsd,
            'tax' => 0,
            'total' => $totalUsd,
            'total_usd' => $totalUsd,
            'status' => $status,
            'refunded' => false,
            'refunded_amount' => 0,
            'first_order_item' => ['order_id' => (int) $lsOrderId, 'product_id' => 50, 'variant_id' => (int) self::LS_VARIANTS[$plan], 'price' => $totalUsd],
        ], ['order_id' => $orderId, 'user_id' => '1']);
    }

    protected function lsSubscription(string $event, string $subscriptionId, string $status, ?string $orderId = null, string $plan = 'inicial', bool $cancelled = false, ?string $renewsAt = null): array
    {
        return $this->lsEvent($event, 'subscriptions', $subscriptionId, [
            'store_id' => 7,
            'customer_id' => 555,
            'order_id' => 9001,
            'product_id' => 50,
            'variant_id' => (int) self::LS_VARIANTS[$plan],
            'status' => $status,
            'cancelled' => $cancelled,
            'renews_at' => $renewsAt ?? now()->addMonth()->toIso8601ZuluString(),
            'ends_at' => $cancelled ? now()->addMonth()->toIso8601ZuluString() : null,
            'user_email' => 'comprador@correo.com',
        ], $orderId ? ['order_id' => $orderId] : []);
    }

    protected function lsInvoice(string $event, string $invoiceId, string $subscriptionId, string $billingReason = 'renewal', string $status = 'paid', int $total = 900, bool $refunded = false): array
    {
        return $this->lsEvent($event, 'subscription-invoices', $invoiceId, [
            'store_id' => 7,
            'subscription_id' => (int) $subscriptionId,
            'customer_id' => 555,
            'billing_reason' => $billingReason,
            'currency' => 'USD',
            'status' => $status,
            'subtotal' => $total,
            'tax' => 0,
            'total' => $total,
            'total_usd' => $total,
            'refunded' => $refunded,
            'refunded_amount' => $refunded ? $total : 0,
        ]);
    }
}
