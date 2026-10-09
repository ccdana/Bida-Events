<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionActivator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Todo pago (a mano, tarjeta o QR) pasa por el mismo lugar: extiende la suscripción una sola vez.
 */
class SubscriptionActivatorTest extends TestCase
{
    use RefreshDatabase;

    private SubscriptionActivator $activator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        $this->activator = app(SubscriptionActivator::class);
    }

    public function test_the_first_payment_turns_an_account_into_an_active_reseller(): void
    {
        // Una cuenta abierta desde el sitio, todavía sin plan
        $user = User::factory()->create();

        $payment = $this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'provider' => 'fake', 'external_id' => 'cobro-1']);

        $user->refresh();
        $this->assertTrue($user->isReseller());
        $this->assertTrue($user->hasActiveSubscription());
        $this->assertSame('aliado', $user->reseller_plan);
        $this->assertSame('2026-11-15', $user->subscription_renews_at->toDateString());
        $this->assertSame('fake', $payment->provider);
        $this->assertFalse($payment->isManual());
        $this->assertSame('2026-11-15', $payment->renews_until->toDateString());
    }

    public function test_months_paid_in_advance_extend_that_many_months(): void
    {
        $user = User::factory()->reseller('aliado', '2026-11-01')->create();

        $this->activator->recordPayment($user, 'aliado', ['amount' => 40, 'currency' => 'USD'], months: 3);
        $this->assertSame('2027-02-01', $user->fresh()->subscription_renews_at->toDateString());

        $this->activator->recordPayment($user, 'emprendedor', ['amount' => 300, 'currency' => 'USD'], months: 12);
        $this->assertSame('2028-02-01', $user->fresh()->subscription_renews_at->toDateString());
        $this->assertSame('emprendedor', $user->fresh()->reseller_plan);
    }

    public function test_the_same_payment_is_recorded_once(): void
    {
        $user = User::factory()->create();
        $order = PaymentOrder::forSubscription($user, 'aliado', 1, '14.00', 'USD', 'fake');
        $token = (string) Str::uuid();

        $this->assertNotNull($this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'request_token' => $token]));
        $this->assertNull($this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'request_token' => $token]));

        $this->assertNotNull($this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'provider' => 'fake', 'external_id' => 'cobro-9']));
        $this->assertNull($this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'provider' => 'fake', 'external_id' => 'cobro-9']));

        $this->assertNotNull($this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'provider' => 'fake', 'payment_order_id' => $order->id]));
        $this->assertNull($this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD', 'provider' => 'fake', 'payment_order_id' => $order->id]));

        $this->assertSame(3, SubscriptionPayment::count());
        $this->assertSame('2027-01-15', $user->fresh()->subscription_renews_at->toDateString());
    }

    public function test_a_charged_order_is_paid_once_and_records_its_payment(): void
    {
        $user = User::factory()->create();
        $order = PaymentOrder::forSubscription($user, 'emprendedor', 1, '29.00', 'USD', 'fake');

        $this->assertTrue($order->isPending());
        $this->assertMatchesRegularExpression('/^BE-[2-9A-HJKMNP-Z]{6}$/', $order->reference_code);
        $this->assertTrue(Str::isUuid($order->public_id));

        $payment = $this->activator->completeOrder($order, 'cobro-55');

        $order->refresh();
        $this->assertTrue($order->isPaid());
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($order->payment->is($payment));
        $this->assertSame('cobro-55', $payment->external_id);
        $this->assertSame('29.00', $payment->amount);
        $this->assertSame('emprendedor', $user->fresh()->reseller_plan);

        // El mismo aviso otra vez no suma otro mes
        $this->assertNull($this->activator->completeOrder($order, 'cobro-55'));
        $this->assertSame(1, SubscriptionPayment::count());
        $this->assertSame('2026-11-15', $user->fresh()->subscription_renews_at->toDateString());
    }

    public function test_an_order_given_up_as_expired_still_activates_if_it_gets_paid(): void
    {
        $user = User::factory()->create();
        $order = PaymentOrder::forSubscription($user, 'aliado', 1, '14.00', 'USD', 'fake');
        $order->close(PaymentOrder::EXPIRED, 'QR vencido');

        $this->assertNotNull($this->activator->completeOrder($order->fresh()));
        $this->assertTrue($order->fresh()->isPaid());
        $this->assertTrue($user->fresh()->hasActiveSubscription());

        // Una devuelta no se vuelve a aplicar
        $refunded = PaymentOrder::forSubscription($user, 'aliado', 1, '14.00', 'USD', 'fake');
        $refunded->forceFill(['status' => PaymentOrder::REFUNDED])->save();
        $this->assertNull($this->activator->completeOrder($refunded));
    }

    public function test_a_paid_order_cannot_be_closed(): void
    {
        $order = PaymentOrder::forSubscription(User::factory()->create(), 'aliado', 1, '14.00', 'USD', 'fake');
        $this->activator->completeOrder($order);

        $this->assertFalse($order->fresh()->close(PaymentOrder::FAILED));
        $this->assertTrue($order->fresh()->isPaid());
    }

    public function test_only_the_latest_payment_is_annulled_and_the_date_goes_back(): void
    {
        $user = User::factory()->create();
        $first = $this->activator->recordPayment($user, 'aliado', ['amount' => 14, 'currency' => 'USD']);
        $second = $this->activator->recordPayment($user, 'agencia', ['amount' => 59, 'currency' => 'USD']);

        $this->assertFalse($this->activator->annulLatest($first));
        $this->assertTrue($this->activator->annulLatest($second));

        $user->refresh();
        $this->assertSame('2026-11-15', $user->subscription_renews_at->toDateString());
        $this->assertSame('aliado', $user->reseller_plan);
        $this->assertModelMissing($second);
    }
}
