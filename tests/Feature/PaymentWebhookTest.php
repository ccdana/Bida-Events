<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

/**
 * Los avisos de las pasarelas: se validan por firma, se guardan una sola vez y activan la
 * suscripción solo si el cobro corresponde a una orden nuestra y por el monto correcto.
 */
class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private PaymentOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        config(['payments.drivers.fake' => FakePaymentGateway::class]);

        $this->buyer = User::factory()->create();
        $this->order = PaymentOrder::forSubscription($this->buyer, 'aliado', 1, '14.00', 'USD', 'fake');
    }

    private function notify(array $payload, ?string $signature = null, string $provider = 'fake'): TestResponse
    {
        $signature ??= FakePaymentGateway::sign(json_encode($payload));

        return $this->postJson("/webhooks/{$provider}", $payload, ['X-Signature' => $signature]);
    }

    private function paid(array $overrides = []): array
    {
        return array_merge([
            'id' => 'evt_1',
            'type' => 'order.paid',
            'order' => $this->order->public_id,
            'charge' => 'cobro_1',
            'amount' => '14.00',
            'currency' => 'USD',
        ], $overrides);
    }

    public function test_a_charged_order_activates_the_subscription(): void
    {
        // Sin sesión ni token CSRF: lo llama el servidor de la pasarela
        $this->notify($this->paid())->assertOk();

        $this->assertTrue($this->order->fresh()->isPaid());
        $this->buyer->refresh();
        $this->assertTrue($this->buyer->isReseller());
        $this->assertTrue($this->buyer->hasActiveSubscription());
        $this->assertSame('2026-11-15', $this->buyer->subscription_renews_at->toDateString());

        $webhook = WebhookEvent::sole();
        $this->assertTrue($webhook->isProcessed());
        $this->assertNull($webhook->error);
        $this->assertSame('order.paid', $webhook->type);
        $this->assertSame('cobro_1', SubscriptionPayment::sole()->external_id);
    }

    public function test_a_repeated_notice_counts_once(): void
    {
        $this->notify($this->paid())->assertOk();
        $this->notify($this->paid())->assertOk();
        // Otro aviso del mismo cobro (la pasarela manda más de uno)
        $this->notify($this->paid(['id' => 'evt_2']))->assertOk();

        $this->assertSame(2, WebhookEvent::count());
        $this->assertSame(1, SubscriptionPayment::count());
        $this->assertSame('2026-11-15', $this->buyer->fresh()->subscription_renews_at->toDateString());
    }

    public function test_a_notice_without_a_valid_signature_is_rejected(): void
    {
        $this->notify($this->paid(), signature: 'inventada')->assertUnauthorized();
        $this->notify($this->paid(), signature: FakePaymentGateway::sign('otro contenido'))->assertUnauthorized();

        $this->assertSame(0, WebhookEvent::count());
        $this->assertTrue($this->order->fresh()->isPending());
        $this->assertFalse($this->buyer->fresh()->isReseller());
    }

    public function test_an_unknown_gateway_does_not_exist(): void
    {
        $this->notify($this->paid(), provider: 'otra')->assertNotFound();

        // La orden es de «fake»: el aviso de otra pasarela no la toca
        config(['payments.drivers.otra' => FakePaymentGateway::class]);
        $this->notify($this->paid(), provider: 'otra')->assertOk();
        $this->assertTrue($this->order->fresh()->isPending());
    }

    public function test_a_wrong_amount_does_not_activate_and_is_left_to_review(): void
    {
        $this->notify($this->paid(['amount' => '1.00']))->assertOk();

        $this->assertTrue($this->order->fresh()->isPending());
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());

        $webhook = WebhookEvent::sole();
        $this->assertTrue($webhook->isProcessed());
        $this->assertStringContainsString('no coincide', $webhook->error);

        $this->artisan('bida:salud', ['--sin-correo' => true])
            ->expectsOutputToContain('Pagos para revisar (7 días)')
            ->assertFailed();
    }

    public function test_a_charge_for_an_order_we_do_not_have_is_left_to_review(): void
    {
        $this->notify($this->paid(['order' => (string) Str::uuid()]))->assertOk();
        $this->notify($this->paid(['id' => 'evt_2', 'order' => 'no-es-un-uuid']))->assertOk();

        $this->assertSame(2, WebhookEvent::whereNotNull('error')->count());
        $this->assertSame(0, SubscriptionPayment::count());
    }

    public function test_rejected_and_expired_charges_close_the_order(): void
    {
        $this->notify(['id' => 'evt_9', 'type' => 'order.failed', 'order' => $this->order->public_id])->assertOk();
        $this->assertSame(PaymentOrder::FAILED, $this->order->fresh()->status);

        $other = PaymentOrder::forSubscription($this->buyer, 'aliado', 1, '14.00', 'USD', 'fake');
        $this->notify(['id' => 'evt_10', 'type' => 'order.expired', 'order' => $other->public_id])->assertOk();
        $this->assertSame(PaymentOrder::EXPIRED, $other->fresh()->status);

        $this->assertNull(WebhookEvent::whereNotNull('error')->first());
    }

    public function test_a_notice_the_gateway_does_not_care_about_is_acknowledged_and_not_stored(): void
    {
        $this->notify(['type' => 'ping'])->assertOk();

        $this->assertSame(0, WebhookEvent::count());
    }

    public function test_a_notice_that_fails_is_kept_and_can_be_applied_later(): void
    {
        // Una orden que no es de un plan: aplicarla falla
        $this->order->forceFill(['purpose' => 'package'])->save();

        $this->notify($this->paid())->assertOk();

        $webhook = WebhookEvent::sole();
        $this->assertFalse($webhook->isProcessed());
        $this->assertStringContainsString('no es de un plan', $webhook->error);
        $this->assertSame(1, $webhook->attempts);

        $this->travel(20)->minutes();
        $this->artisan('bida:salud', ['--sin-correo' => true])->expectsOutputToContain('Avisos de pago sin aplicar');

        // Corregido el problema, se vuelve a aplicar
        $this->order->forceFill(['purpose' => PaymentOrder::PURPOSE_SUBSCRIPTION])->save();
        $this->artisan('bida:reprocesar-avisos')->expectsOutputToContain('1 aviso(s) en la cola.')->assertSuccessful();

        $this->assertTrue($webhook->fresh()->isProcessed());
        $this->assertNull($webhook->fresh()->error);
        $this->assertTrue($this->order->fresh()->isPaid());
        $this->assertTrue($this->buyer->fresh()->hasActiveSubscription());
    }
}
