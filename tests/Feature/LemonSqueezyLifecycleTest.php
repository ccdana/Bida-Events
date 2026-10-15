<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\TestCase;

/**
 * La vida de una suscripción con tarjeta, aviso por aviso, tal como los manda Lemon Squeezy:
 * compra, renovaciones, cobro fallido, cancelación, cambio de plan y devolución.
 */
class LemonSqueezyLifecycleTest extends TestCase
{
    use LemonSqueezyPayloads, RefreshDatabase;

    private User $buyer;

    private PaymentOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        $this->configureLemonSqueezy();
        config(['payments.grace_days' => 3]);

        $this->buyer = User::factory()->create(['email' => 'comprador@correo.com']);
        $this->order = PaymentOrder::forSubscription($this->buyer, 'inicial', 1, '9.00', 'USD', 'lemonsqueezy');
    }

    /** La compra como la avisa Lemon Squeezy: order_created, subscription_created y el cobro inicial. */
    private function purchase(): void
    {
        $this->lsSend($this->lsOrderCreated($this->order->public_id, '9001', 'inicial', 900))->assertOk();
        $this->lsSend($this->lsSubscription('subscription_created', '3001', 'active', $this->order->public_id))->assertOk();
        $this->lsSend($this->lsInvoice('subscription_payment_success', '7000', '3001', 'initial'))->assertOk();
    }

    public function test_the_purchase_activates_a_subscription_that_renews_by_itself(): void
    {
        $this->purchase();

        $this->buyer->refresh();
        $this->assertTrue($this->buyer->isReseller());
        $this->assertTrue($this->buyer->hasActiveSubscription());
        $this->assertSame('inicial', $this->buyer->reseller_plan);
        $this->assertSame('2026-11-15', $this->buyer->subscription_renews_at->toDateString());
        $this->assertSame('lemonsqueezy', $this->buyer->payment_provider);
        $this->assertSame('3001', $this->buyer->provider_subscription_id);
        $this->assertSame('555', $this->buyer->provider_customer_id);
        $this->assertTrue($this->buyer->renewsAutomatically());

        // Un solo pago por la compra (el cobro inicial no se suma otra vez)
        $payment = SubscriptionPayment::sole();
        $this->assertSame('order:9001', $payment->external_id);
        $this->assertSame('9.00', $payment->amount);
        $this->assertTrue($this->order->fresh()->isPaid());
        $this->assertSame(0, WebhookEvent::whereNotNull('error')->count());
    }

    public function test_the_subscription_notice_can_arrive_before_the_order(): void
    {
        $this->lsSend($this->lsSubscription('subscription_created', '3001', 'active', $this->order->public_id))->assertOk();
        $this->lsSend($this->lsOrderCreated($this->order->public_id))->assertOk();

        $this->buyer->refresh();
        $this->assertTrue($this->buyer->hasActiveSubscription());
        $this->assertSame('3001', $this->buyer->provider_subscription_id);
    }

    public function test_each_renewal_adds_a_month(): void
    {
        $this->purchase();

        $this->travelTo(Carbon::parse('2026-11-15 09:00:00', 'America/La_Paz'));
        $this->lsSend($this->lsInvoice('subscription_payment_success', '7001', '3001'))->assertOk();
        // El mismo aviso reintentado no suma otro mes
        $this->lsSend($this->lsInvoice('subscription_payment_success', '7001', '3001'))->assertOk();

        $this->assertSame('2026-12-15', $this->buyer->fresh()->subscription_renews_at->toDateString());
        $this->assertSame(2, SubscriptionPayment::count());
        $this->assertSame('invoice:7001', SubscriptionPayment::latest('id')->first()->external_id);
    }

    public function test_a_failed_renewal_keeps_the_editor_during_the_grace_days_and_recovers(): void
    {
        $this->purchase();

        $this->travelTo(Carbon::parse('2026-11-15 09:00:00', 'America/La_Paz'));
        $this->lsSend($this->lsInvoice('subscription_payment_failed', '7001', '3001', status: 'pending'))->assertOk();
        $this->lsSend($this->lsSubscription('subscription_updated', '3001', 'past_due'))->assertOk();

        $this->buyer->refresh();
        $this->assertSame('past_due', $this->buyer->subscription_status);
        $this->assertTrue($this->buyer->hasActiveSubscription());

        $this->withoutVite()->actingAs($this->buyer)->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('No pudimos cobrar la renovación con tu tarjeta');

        // Pasados los días de gracia, se bloquea mientras la pasarela sigue reintentando
        $this->travelTo(Carbon::parse('2026-11-19 09:00:00', 'America/La_Paz'));
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());

        // El aviso del estado llega antes que el del cobro: por sí solo no la reactiva ni le suma días
        $this->lsSend($this->lsSubscription('subscription_updated', '3001', 'active', renewsAt: '2026-12-15T16:00:00Z'))->assertOk();
        $this->assertSame('past_due', $this->buyer->fresh()->subscription_status);
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());

        // El reintento cobra: vuelve a estar al día, y queda con la fecha de cobro de la pasarela
        $this->lsSend($this->lsInvoice('subscription_payment_success', '7002', '3001'))->assertOk();
        $this->assertSame('active', $this->buyer->fresh()->subscription_status);
        $this->travel(1)->minutes();
        $this->lsSend($this->lsSubscription('subscription_updated', '3001', 'active', renewsAt: '2026-12-15T16:00:00Z'))->assertOk();

        $this->buyer->refresh();
        $this->assertTrue($this->buyer->hasActiveSubscription());
        $this->assertSame('2026-12-15', $this->buyer->subscription_renews_at->toDateString());
        $this->assertSame(2, SubscriptionPayment::count());
    }

    public function test_cancelling_keeps_what_was_paid_and_expiring_ends_it(): void
    {
        $this->purchase();

        $this->lsSend($this->lsSubscription('subscription_cancelled', '3001', 'cancelled', cancelled: true))->assertOk();

        $this->buyer->refresh();
        $this->assertTrue($this->buyer->cancel_at_period_end);
        $this->assertTrue($this->buyer->hasActiveSubscription());
        $this->assertFalse($this->buyer->renewsAutomatically());

        $this->travelTo(Carbon::parse('2026-11-15 12:00:00', 'America/La_Paz'));
        $this->lsSend($this->lsSubscription('subscription_expired', '3001', 'expired', cancelled: true))->assertOk();

        $this->buyer->refresh();
        $this->assertSame('canceled', $this->buyer->subscription_status);
        $this->assertFalse($this->buyer->hasActiveSubscription());
        $this->assertFalse($this->buyer->hasRecurringSubscription());
    }

    public function test_resuming_a_cancellation_renews_again(): void
    {
        $this->purchase();
        $this->lsSend($this->lsSubscription('subscription_cancelled', '3001', 'cancelled', cancelled: true))->assertOk();
        $this->travel(1)->minutes();
        $this->lsSend($this->lsSubscription('subscription_resumed', '3001', 'active'))->assertOk();

        $this->assertTrue($this->buyer->fresh()->renewsAutomatically());
    }

    public function test_a_plan_change_from_the_portal_applies_right_away(): void
    {
        $this->purchase();

        $this->lsSend($this->lsSubscription('subscription_updated', '3001', 'active', plan: 'agencia'))->assertOk();

        $this->assertSame('agencia', $this->buyer->fresh()->reseller_plan);
        // La fecha no cambia: la pasarela cobra la diferencia en la próxima renovación
        $this->assertSame('2026-11-15', $this->buyer->fresh()->subscription_renews_at->toDateString());
    }

    public function test_a_full_refund_annuls_the_payment_and_a_partial_one_does_not(): void
    {
        $this->purchase();
        $this->travelTo(Carbon::parse('2026-11-15 09:00:00', 'America/La_Paz'));
        $this->lsSend($this->lsInvoice('subscription_payment_success', '7001', '3001'))->assertOk();

        $this->lsSend($this->lsEvent('subscription_payment_refunded', 'subscription-invoices', '7001', ['subscription_id' => 3001, 'refunded' => false, 'refunded_amount' => 500, 'total' => 900]))->assertOk();
        $this->assertSame(2, SubscriptionPayment::count());

        $this->lsSend($this->lsInvoice('subscription_payment_refunded', '7001', '3001', status: 'refunded', refunded: true))->assertOk();

        $this->assertSame(1, SubscriptionPayment::count());
        $this->assertSame('2026-11-15', $this->buyer->fresh()->subscription_renews_at->toDateString());
        $this->assertSame(0, WebhookEvent::whereNotNull('error')->count());
    }

    public function test_refunding_the_purchase_marks_the_order(): void
    {
        $this->purchase();

        $this->lsSend($this->lsEvent('order_refunded', 'orders', '9001', ['refunded' => true, 'refunded_amount' => 900, 'status' => 'refunded']))->assertOk();

        $this->assertSame(PaymentOrder::REFUNDED, $this->order->fresh()->status);
        $this->assertNull($this->buyer->fresh()->subscription_renews_at);
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());
    }

    public function test_a_charge_for_another_plan_than_the_order_is_left_to_review(): void
    {
        $this->lsSend($this->lsOrderCreated($this->order->public_id, '9001', 'agencia', 5900))->assertOk();

        $this->assertTrue($this->order->fresh()->isPending());
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());
        $this->assertStringContainsString('agencia', WebhookEvent::sole()->error);
    }

    public function test_a_renewal_of_a_subscription_we_do_not_know_is_left_to_review(): void
    {
        $this->lsSend($this->lsInvoice('subscription_payment_success', '7001', '8888'))->assertOk();

        $this->assertSame(0, SubscriptionPayment::count());
        $this->assertStringContainsString('8888', WebhookEvent::sole()->error);
    }

    public function test_a_bad_signature_is_rejected(): void
    {
        $this->lsSend($this->lsOrderCreated($this->order->public_id), signature: 'firma-falsa')->assertUnauthorized();

        $this->assertSame(0, WebhookEvent::count());
    }
}
