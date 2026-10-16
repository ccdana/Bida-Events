<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\User;
use App\Payments\Exceptions\InvalidWebhookSignature;
use App\Payments\GatewayEvent;
use App\Payments\Gateways\LemonSqueezyGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\TestCase;

/**
 * El driver de Lemon Squeezy por dentro: la compra que abre en su API y cómo traduce cada aviso.
 */
class LemonSqueezyGatewayTest extends TestCase
{
    use LemonSqueezyPayloads, RefreshDatabase;

    private LemonSqueezyGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureLemonSqueezy();
        $this->gateway = app(LemonSqueezyGateway::class);
    }

    private function parse(array $payload, ?string $signature = null): ?GatewayEvent
    {
        $body = json_encode($payload);
        $request = Request::create('/webhooks/lemonsqueezy', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, self::LS_SECRET),
        ], $body);

        return $this->gateway->parseWebhook($request);
    }

    public function test_it_needs_its_credentials_and_a_variant_per_plan(): void
    {
        $this->assertTrue($this->gateway->isConfigured());
        $this->assertTrue($this->gateway->supportsPlan('inicial'));

        config(['payments.lemonsqueezy.variants.agencia' => null]);
        $this->assertFalse($this->gateway->supportsPlan('agencia'));

        config(['payments.lemonsqueezy.signing_secret' => null]);
        $this->assertFalse($this->gateway->isConfigured());
    }

    public function test_the_checkout_carries_our_order_and_comes_back_to_it(): void
    {
        Http::fake(['api.lemonsqueezy.com/v1/checkouts' => Http::response([
            'data' => ['type' => 'checkouts', 'id' => 'chk-77', 'attributes' => ['url' => 'https://bida.lemonsqueezy.com/checkout/custom/abc?signature=x']],
        ], 201)]);

        $user = User::factory()->create(['name' => 'Carla Mendoza', 'email' => 'carla@correo.com']);
        $order = PaymentOrder::forSubscription($user, 'emprendedor', 1, '29.00', 'USD', 'lemonsqueezy');

        $checkout = $this->gateway->createCheckout($order);

        $this->assertSame('https://bida.lemonsqueezy.com/checkout/custom/abc?signature=x', $checkout->redirectUrl);
        $this->assertSame('chk-77', $checkout->providerRef);
        $this->assertNotNull($checkout->expiresAt);

        Http::assertSent(function (HttpRequest $request) use ($order) {
            $data = $request->data()['data'];

            return $request->url() === 'https://api.lemonsqueezy.com/v1/checkouts'
                && $request->hasHeader('Authorization', 'Bearer clave-de-prueba')
                && $request->hasHeader('Accept', 'application/vnd.api+json')
                && $data['relationships']['store']['data']['id'] === '7'
                && $data['relationships']['variant']['data']['id'] === '103'
                && $data['attributes']['product_options']['enabled_variants'] === [103]
                && $data['attributes']['product_options']['redirect_url'] === route('client.subscription.order', $order->public_id)
                && $data['attributes']['checkout_data']['email'] === 'carla@correo.com'
                && $data['attributes']['checkout_data']['custom']['order_id'] === $order->public_id
                && $data['attributes']['checkout_options']['locale'] === 'es'
                && ! isset($data['attributes']['test_mode']);
        });
    }

    public function test_test_mode_is_sent_when_configured(): void
    {
        config(['payments.lemonsqueezy.test_mode' => true]);
        Http::fake(['api.lemonsqueezy.com/*' => Http::response(['data' => ['id' => '1', 'attributes' => ['url' => 'https://x.lemonsqueezy.com/c']]], 201)]);

        $this->gateway->createCheckout(PaymentOrder::forSubscription(User::factory()->create(), 'inicial', 1, '9.00', 'USD', 'lemonsqueezy'));

        Http::assertSent(fn (HttpRequest $request) => $request->data()['data']['attributes']['test_mode'] === true);
    }

    public function test_an_unsigned_or_tampered_notice_is_rejected(): void
    {
        $payload = $this->lsOrderCreated('5f1d7a0e-1111-4222-8333-444455556666');

        $this->expectException(InvalidWebhookSignature::class);
        $this->parse($payload, signature: hash_hmac('sha256', 'otro cuerpo', self::LS_SECRET));
    }

    public function test_a_paid_order_is_the_first_payment_with_its_plan_and_what_was_charged(): void
    {
        $event = $this->parse($this->lsOrderCreated('5f1d7a0e-1111-4222-8333-444455556666', '9001', 'emprendedor', 3190));

        $this->assertSame(GatewayEvent::ORDER_PAID, $event->type);
        $this->assertSame('5f1d7a0e-1111-4222-8333-444455556666', $event->orderId);
        $this->assertSame('order:9001', $event->externalId);
        // El monto no se compara con la orden (Lemon Squeezy suma impuestos): se guarda lo cobrado
        $this->assertNull($event->amount);
        $this->assertSame('emprendedor', $event->extra['plan']);
        $this->assertSame('31.90', $event->extra['charged_amount']);
        $this->assertSame('USD', $event->extra['charged_currency']);
        $this->assertSame('555', $event->extra['customer_id']);

        // Rechazada: cierra la orden; pendiente: todavía no hay nada que hacer
        $this->assertSame(GatewayEvent::ORDER_FAILED, $this->parse($this->lsOrderCreated('5f1d7a0e-1111-4222-8333-444455556666', status: 'failed'))->type);
        $this->assertNull($this->parse($this->lsOrderCreated('5f1d7a0e-1111-4222-8333-444455556666', status: 'pending')));
    }

    public function test_only_renewal_invoices_add_a_month(): void
    {
        $renewal = $this->parse($this->lsInvoice('subscription_payment_success', '7001', '3001'));

        $this->assertSame(GatewayEvent::SUBSCRIPTION_RENEWED, $renewal->type);
        $this->assertSame('invoice:7001', $renewal->externalId);
        $this->assertSame('3001', $renewal->extra['subscription_id']);
        $this->assertSame('9.00', $renewal->extra['charged_amount']);

        // La del primer pago ya la cubre order_created; la de un cambio de plan no suma un mes
        $this->assertNull($this->parse($this->lsInvoice('subscription_payment_success', '7002', '3001', 'initial')));
        $this->assertNull($this->parse($this->lsInvoice('subscription_payment_success', '7003', '3001', 'updated')));

        $failed = $this->parse($this->lsInvoice('subscription_payment_failed', '7004', '3001', status: 'pending'));
        $this->assertSame(GatewayEvent::SUBSCRIPTION_PAYMENT_FAILED, $failed->type);
        $this->assertSame('3001', $failed->extra['subscription_id']);
    }

    public function test_subscription_changes_are_translated_to_our_states(): void
    {
        $cases = [
            ['subscription_created', 'active', false, 'active'],
            ['subscription_updated', 'on_trial', false, 'active'],
            ['subscription_updated', 'past_due', false, 'past_due'],
            ['subscription_updated', 'unpaid', false, 'past_due'],
            ['subscription_paused', 'paused', false, 'past_due'],
            ['subscription_cancelled', 'cancelled', true, 'cancelled'],
            ['subscription_expired', 'expired', true, 'expired'],
        ];

        foreach ($cases as [$name, $status, $cancelled, $expected]) {
            $event = $this->parse($this->lsSubscription($name, '3001', $status, plan: 'agencia', cancelled: $cancelled));

            $this->assertSame(GatewayEvent::SUBSCRIPTION_UPDATED, $event->type, $name);
            $this->assertSame('3001', $event->externalId);
            $this->assertSame($expected, $event->extra['status'], "{$name} con {$status}");
            $this->assertSame($cancelled, $event->extra['cancelled']);
            $this->assertSame('agencia', $event->extra['plan']);
        }
    }

    public function test_refunds_and_notices_without_use(): void
    {
        $invoiceRefund = $this->parse($this->lsInvoice('subscription_payment_refunded', '7001', '3001', status: 'refunded', refunded: true));
        $this->assertSame(GatewayEvent::PAYMENT_REFUNDED, $invoiceRefund->type);
        $this->assertSame('invoice:7001', $invoiceRefund->externalId);
        $this->assertTrue($invoiceRefund->extra['full']);

        $orderRefund = $this->parse(['meta' => ['event_name' => 'order_refunded'], 'data' => ['type' => 'orders', 'id' => '9001', 'attributes' => ['refunded' => false, 'refunded_amount' => 500, 'updated_at' => 'x']]]);
        $this->assertSame('order:9001', $orderRefund->externalId);
        $this->assertFalse($orderRefund->extra['full']);

        $this->assertNull($this->parse($this->lsEvent('subscription_payment_recovered', 'subscription-invoices', '7001', [])));
        $this->assertNull($this->parse($this->lsEvent('license_key_created', 'license-keys', '1', [])));
    }

    public function test_the_same_notice_resent_keeps_its_id_and_a_new_change_gets_another(): void
    {
        $payload = $this->lsSubscription('subscription_updated', '3001', 'active');

        $this->assertSame($this->parse($payload)->id, $this->parse($payload)->id);

        $this->travel(1)->minutes();
        $this->assertNotSame($this->parse($payload)->id, $this->parse($this->lsSubscription('subscription_updated', '3001', 'active'))->id);
    }

    public function test_the_customer_portal_link_is_asked_for_when_needed(): void
    {
        Http::fake(['api.lemonsqueezy.com/v1/subscriptions/3001' => Http::response([
            'data' => ['id' => '3001', 'attributes' => ['urls' => ['customer_portal' => 'https://bida.lemonsqueezy.com/billing?expires=1']]],
        ])]);

        $user = User::factory()->reseller()->create(['payment_provider' => 'lemonsqueezy', 'provider_subscription_id' => '3001']);

        $this->assertSame('https://bida.lemonsqueezy.com/billing?expires=1', $this->gateway->portalUrl($user));
        $this->assertNull($this->gateway->portalUrl(User::factory()->reseller()->create()));
    }
}
