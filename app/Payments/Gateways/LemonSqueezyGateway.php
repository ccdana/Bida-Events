<?php

namespace App\Payments\Gateways;

use App\Models\PaymentOrder;
use App\Models\User;
use App\Payments\Checkout;
use App\Payments\Contracts\ManagesSubscriptions;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\Exceptions\InvalidWebhookSignature;
use App\Payments\GatewayEvent;
use DomainException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

/**
 * Tarjeta internacional con Lemon Squeezy (vendedor oficial: cobra los impuestos de cada país).
 * Cada plan es una variante de un producto de suscripción mensual; Lemon Squeezy cobra el primer
 * mes en su página de pago y después renueva solo, y avisa cada cosa a /webhooks/lemonsqueezy.
 *
 * Qué aviso se usa para qué (docs.lemonsqueezy.com › Webhooks › Event types):
 * - order_created: el primer pago. Trae nuestra orden en custom_data → activa la suscripción.
 * - subscription_payment_success con billing_reason «renewal»: cada renovación → suma un mes.
 *   El del primer pago («initial») se ignora: ya lo cubre order_created.
 * - subscription_created/updated/cancelled/expired…: el estado (atrasada, cancelada, cambio de plan).
 * - subscription_payment_failed: falló una renovación (Lemon Squeezy la reintenta unas dos semanas).
 * - order_refunded y subscription_payment_refunded: devoluciones.
 *
 * Lemon Squeezy no manda un id por aviso: el de cada uno se arma con el evento, el objeto y su
 * fecha de actualización, así un reintento suyo se reconoce como repetido.
 */
class LemonSqueezyGateway implements ManagesSubscriptions, PaymentGateway
{
    private const API = 'https://api.lemonsqueezy.com/v1';

    private const SUBSCRIPTION_EVENTS = [
        'subscription_created', 'subscription_updated', 'subscription_cancelled', 'subscription_resumed',
        'subscription_expired', 'subscription_paused', 'subscription_unpaused',
    ];

    public function isConfigured(): bool
    {
        return filled($this->config('api_key')) && filled($this->config('store_id')) && filled($this->config('signing_secret'));
    }

    public function supportsPlan(string $plan): bool
    {
        return filled($this->config("variants.{$plan}"));
    }

    public function createCheckout(PaymentOrder $order): Checkout
    {
        $variant = $this->config("variants.{$order->plan}") ?: throw new DomainException("El plan «{$order->plan}» no tiene variante en Lemon Squeezy.");
        $user = $order->user ?? throw new DomainException('La orden no tiene cuenta.');
        $expiresAt = now()->addDay();

        $attributes = [
            'product_options' => [
                'enabled_variants' => [(int) $variant],
                // Al pagar, vuelve a la página que espera el aviso del cobro
                'redirect_url' => route('client.subscription.order', $order->public_id),
            ],
            'checkout_options' => ['locale' => 'es'],
            'checkout_data' => [
                'email' => $user->email,
                'name' => $user->name,
                // Vuelve en todos los avisos de esta compra y de su suscripción
                'custom' => ['order_id' => $order->public_id, 'user_id' => (string) $user->id],
            ],
            'expires_at' => $expiresAt->toIso8601ZuluString(),
        ];

        if ($this->config('test_mode')) {
            $attributes['test_mode'] = true;
        }

        $response = $this->api()->post('/checkouts', [
            'data' => [
                'type' => 'checkouts',
                'attributes' => $attributes,
                'relationships' => [
                    'store' => ['data' => ['type' => 'stores', 'id' => (string) $this->config('store_id')]],
                    'variant' => ['data' => ['type' => 'variants', 'id' => (string) $variant]],
                ],
            ],
        ])->throw();

        return new Checkout(
            redirectUrl: $response->json('data.attributes.url'),
            providerRef: (string) $response->json('data.id'),
            expiresAt: $expiresAt,
        );
    }

    public function parseWebhook(Request $request): ?GatewayEvent
    {
        $secret = (string) $this->config('signing_secret');
        $body = $request->getContent();

        if ($secret === '' || ! hash_equals(hash_hmac('sha256', $body, $secret), (string) $request->header('X-Signature'))) {
            throw new InvalidWebhookSignature('La firma X-Signature no coincide con el signing secret.');
        }

        $payload = json_decode($body, true);

        if (! is_array($payload)) {
            return null;
        }

        $event = (string) Arr::get($payload, 'meta.event_name', $request->header('X-Event-Name', ''));
        $id = (string) Arr::get($payload, 'data.id', '');
        $attributes = (array) Arr::get($payload, 'data.attributes', []);
        $orderId = Arr::get($payload, 'meta.custom_data.order_id');
        $stamp = $event.':'.$id.':'.($attributes['updated_at'] ?? '');

        return match (true) {
            $event === 'order_created' => $this->orderCreated($stamp, $id, $orderId, $attributes),
            $event === 'order_refunded' => new GatewayEvent(
                id: $stamp.':'.($attributes['refunded_amount'] ?? ''),
                type: GatewayEvent::PAYMENT_REFUNDED,
                orderId: $orderId,
                externalId: 'order:'.$id,
                extra: ['full' => (bool) ($attributes['refunded'] ?? false)],
            ),
            in_array($event, self::SUBSCRIPTION_EVENTS, true) => new GatewayEvent(
                id: $stamp,
                type: GatewayEvent::SUBSCRIPTION_UPDATED,
                orderId: $orderId,
                externalId: $id,
                extra: [
                    'status' => $this->status($attributes),
                    'cancelled' => (bool) ($attributes['cancelled'] ?? false),
                    'plan' => $this->planFor($attributes['variant_id'] ?? null),
                    'customer_id' => isset($attributes['customer_id']) ? (string) $attributes['customer_id'] : null,
                    'renews_at' => $attributes['renews_at'] ?? null,
                    'ends_at' => $attributes['ends_at'] ?? null,
                ],
            ),
            $event === 'subscription_payment_success' => $this->invoicePaid($stamp, $id, $orderId, $attributes),
            $event === 'subscription_payment_failed' => new GatewayEvent(
                id: $stamp,
                type: GatewayEvent::SUBSCRIPTION_PAYMENT_FAILED,
                externalId: 'invoice:'.$id,
                extra: ['subscription_id' => (string) ($attributes['subscription_id'] ?? '')],
            ),
            $event === 'subscription_payment_refunded' => new GatewayEvent(
                id: $stamp.':'.($attributes['refunded_amount'] ?? ''),
                type: GatewayEvent::PAYMENT_REFUNDED,
                externalId: 'invoice:'.$id,
                extra: ['full' => (bool) ($attributes['refunded'] ?? false)],
            ),
            // subscription_payment_recovered llega junto a un subscription_payment_success; el resto no se usa
            default => null,
        };
    }

    /** Lemon Squeezy no permite buscar una compra por nuestros datos: el estado llega solo por sus avisos. */
    public function fetchStatus(PaymentOrder $order): ?GatewayEvent
    {
        return null;
    }

    /** El enlace al portal vale 24 horas: se pide cada vez que el cliente lo abre. */
    public function portalUrl(User $user): ?string
    {
        if ($user->payment_provider !== 'lemonsqueezy' || ! $user->provider_subscription_id) {
            return null;
        }

        $response = $this->api()->get('/subscriptions/'.$user->provider_subscription_id);

        return $response->successful() ? $response->json('data.attributes.urls.customer_portal') : null;
    }

    private function orderCreated(string $stamp, string $id, ?string $orderId, array $attributes): ?GatewayEvent
    {
        $status = $attributes['status'] ?? null;

        if ($status === 'failed') {
            return new GatewayEvent(id: $stamp, type: GatewayEvent::ORDER_FAILED, orderId: $orderId, externalId: 'order:'.$id);
        }

        if ($status !== 'paid') {
            return null;
        }

        return new GatewayEvent(
            id: $stamp,
            type: GatewayEvent::ORDER_PAID,
            orderId: $orderId,
            externalId: 'order:'.$id,
            // El monto lo pone Lemon Squeezy (precio de la variante, con impuestos): no se compara con
            // el de la orden; se comprueba el plan por la variante y se guarda lo que se cobró
            extra: [
                'plan' => $this->planFor(Arr::get($attributes, 'first_order_item.variant_id')),
                'customer_id' => isset($attributes['customer_id']) ? (string) $attributes['customer_id'] : null,
            ] + $this->charged($attributes),
        );
    }

    private function invoicePaid(string $stamp, string $id, ?string $orderId, array $attributes): ?GatewayEvent
    {
        if (($attributes['billing_reason'] ?? null) !== 'renewal' || ($attributes['status'] ?? null) !== 'paid') {
            return null;
        }

        return new GatewayEvent(
            id: $stamp,
            type: GatewayEvent::SUBSCRIPTION_RENEWED,
            orderId: $orderId,
            externalId: 'invoice:'.$id,
            extra: ['subscription_id' => (string) ($attributes['subscription_id'] ?? '')] + $this->charged($attributes),
        );
    }

    /** Lo cobrado, en dólares si viene (Lemon Squeezy cobra en la moneda del comprador). Montos en centavos. */
    private function charged(array $attributes): array
    {
        $inUsd = isset($attributes['total_usd']);
        $cents = $inUsd ? $attributes['total_usd'] : ($attributes['total'] ?? null);

        return [
            'charged_amount' => $cents === null ? null : number_format((int) $cents / 100, 2, '.', ''),
            'charged_currency' => $inUsd ? 'USD' : strtoupper((string) ($attributes['currency'] ?? 'USD')),
        ];
    }

    /** El estado de Lemon Squeezy en nuestras palabras. */
    private function status(array $attributes): string
    {
        return match ($attributes['status'] ?? null) {
            'active', 'on_trial' => 'active',
            // Pausada: no se cobra, así que se trata como un pago atrasado
            'past_due', 'unpaid', 'paused' => 'past_due',
            'cancelled' => 'cancelled',
            'expired' => 'expired',
            default => 'unknown',
        };
    }

    private function planFor(mixed $variantId): ?string
    {
        if ($variantId === null || $variantId === '') {
            return null;
        }

        foreach ((array) $this->config('variants') as $plan => $variant) {
            if ((string) $variant === (string) $variantId) {
                return $plan;
            }
        }

        return null;
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl(self::API)
            ->withToken((string) $this->config('api_key'))
            ->accept('application/vnd.api+json')
            ->contentType('application/vnd.api+json')
            ->timeout(15)
            ->retry(2, 500, throw: false);
    }

    private function config(string $key): mixed
    {
        return config("payments.lemonsqueezy.{$key}");
    }
}
