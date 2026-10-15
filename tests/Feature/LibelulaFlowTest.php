<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\WebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\LibelulaApi;
use Tests\TestCase;

/**
 * Pagar con QR en Bolivia (Libélula) desde el panel: elegir meses, escanear, y el plan se activa
 * cuando Libélula confirma el pago, llegue o no su aviso.
 */
class LibelulaFlowTest extends TestCase
{
    use LibelulaApi, RefreshDatabase;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-15 12:00:00', 'America/La_Paz'));
        // El flujo del QR con el plan Inicial y sin descuento por adelantado (PlansAndCyclesTest
        // prueba el catálogo y los descuentos)
        config(['payments.prepay' => [1 => 0, 3 => 0, 12 => 0]]);
        $this->configureLibelula();
        $this->fakeLibelula();
        $this->buyer = User::factory()->create(['email' => 'comprador@correo.com']);
    }

    private function buy(string $plan = 'inicial', int $months = 3): PaymentOrder
    {
        $this->actingAs($this->buyer)
            ->post(route('client.subscription.store'), ['plan' => $plan, 'method' => 'qr', 'months' => $months])
            ->assertRedirect();

        return PaymentOrder::latest('id')->firstOrFail();
    }

    public function test_the_plans_page_offers_paying_in_bolivia_for_several_months(): void
    {
        $this->actingAs($this->buyer)->withoutVite()->get(route('client.subscription'))
            ->assertOk()
            ->assertSee('Pago en Bolivia: QR, Tigo Money o tarjeta boliviana')
            ->assertSee('Cuántos meses pagas')
            ->assertSee('12 meses')
            // Precio de hoy del Inicial (US$ 9) al cambio oficial: Bs 63 al mes, Bs 188 por 3 meses
            ->assertSee('63.00', false)
            ->assertSee('188.00', false);
    }

    public function test_paying_by_qr_shows_the_qr_in_bolivianos_for_the_chosen_months(): void
    {
        $order = $this->buy('inicial', 3);

        $this->assertSame('libelula', $order->provider);
        $this->assertSame('BOB', $order->currency);
        $this->assertSame(3, $order->months);
        $this->assertSame('188.00', $order->amount);
        $this->assertSame(self::LIBELULA_TRANSACTION, $order->provider_ref);
        $this->assertStringStartsWith('data:image/png;base64,', $order->meta['qr_image']);

        $this->actingAs($this->buyer)->withoutVite()->get(route('client.subscription.order', $order->public_id))
            ->assertOk()
            ->assertSee('Escanea el QR para pagar')
            ->assertSee('Bs 188')
            ->assertSee('Plan Inicial · 3 meses')
            ->assertSee('Pagar con Tigo Money o tarjeta')
            ->assertSee($order->meta['qr_image'], false);
    }

    public function test_the_notice_from_libelula_activates_the_months_paid(): void
    {
        $order = $this->buy('inicial', 3);
        $this->libelulaReceives($order->public_id, '188');

        // Libélula avisa con un GET a la callback_url, agregando ?transaction_id=…
        $this->get(route('webhooks.payments', ['provider' => 'libelula', 'orden' => $order->public_id, 'transaction_id' => self::LIBELULA_TRANSACTION]))->assertOk();
        // y lo puede repetir (pagos en segundo plano)
        $this->get(route('webhooks.payments', ['provider' => 'libelula', 'orden' => $order->public_id, 'transaction_id' => self::LIBELULA_TRANSACTION]))->assertOk();

        $this->buyer->refresh();
        $this->assertTrue($this->buyer->isReseller());
        $this->assertTrue($this->buyer->hasActiveSubscription());
        $this->assertSame('2027-01-15', $this->buyer->subscription_renews_at->toDateString());
        // No se renueva sola: el próximo pago lo hace el revendedor
        $this->assertFalse($this->buyer->renewsAutomatically());

        $payment = SubscriptionPayment::sole();
        $this->assertSame('libelula', $payment->provider);
        $this->assertSame('188.00', $payment->amount);
        $this->assertSame('BOB', $payment->currency);
        $this->assertSame('QR', $payment->providerLabel());
        $this->assertTrue($order->fresh()->isPaid());
    }

    public function test_a_forged_notice_without_a_payment_activates_nothing(): void
    {
        $order = $this->buy();

        $this->get(route('webhooks.payments', ['provider' => 'libelula', 'orden' => $order->public_id, 'transaction_id' => self::LIBELULA_TRANSACTION]))->assertOk();

        $this->assertTrue($order->fresh()->isPending());
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());
        $this->assertSame(0, WebhookEvent::count());
    }

    public function test_a_payment_for_a_different_amount_is_left_to_review(): void
    {
        $order = $this->buy('inicial', 3);
        $this->libelulaReceives($order->public_id, '63');

        $this->get(route('webhooks.payments', ['provider' => 'libelula', 'orden' => $order->public_id, 'transaction_id' => self::LIBELULA_TRANSACTION]))->assertOk();

        $this->assertTrue($order->fresh()->isPending());
        $this->assertFalse($this->buyer->fresh()->hasActiveSubscription());
        $this->assertStringContainsString('no coincide', WebhookEvent::sole()->error);
    }

    public function test_a_lost_notice_is_caught_by_the_periodic_check(): void
    {
        $order = $this->buy('emprendedor', 1);
        $this->libelulaReceives($order->public_id, '202');

        // El aviso nunca llegó; la verificación cada 5 minutos encuentra el pago
        $this->artisan('bida:verificar-pagos')->expectsOutputToContain('Cobros confirmados: 1')->assertSuccessful();

        $this->assertTrue($order->fresh()->isPaid());
        $this->assertSame('2026-11-15', $this->buyer->fresh()->subscription_renews_at->toDateString());

        // Correrla otra vez no suma otro mes
        $this->artisan('bida:verificar-pagos')->expectsOutputToContain('Cobros confirmados: 0')->assertSuccessful();
        $this->assertSame(1, SubscriptionPayment::count());
    }

    public function test_an_unpaid_qr_expires_and_still_activates_if_paid_late(): void
    {
        $order = $this->buy();

        $this->travelTo(Carbon::parse('2026-10-17 12:00:00', 'America/La_Paz'));
        $this->artisan('bida:verificar-pagos')->expectsOutputToContain('Vencidos: 1')->assertSuccessful();
        $this->assertSame(PaymentOrder::EXPIRED, $order->fresh()->status);

        $this->actingAs($this->buyer)->withoutVite()->get(route('client.subscription.order', $order->public_id))
            ->assertOk()
            ->assertSee('Este cobro venció sin pagarse');

        // Se pagó justo al vencer: el aviso llega después y se activa igual
        $this->libelulaReceives($order->public_id, '188');
        $this->get(route('webhooks.payments', ['provider' => 'libelula', 'orden' => $order->public_id, 'transaction_id' => self::LIBELULA_TRANSACTION]))->assertOk();
        $this->assertTrue($order->fresh()->isPaid());
        $this->assertTrue($this->buyer->fresh()->hasActiveSubscription());
    }

    public function test_paying_again_by_qr_extends_from_the_current_due_date(): void
    {
        $reseller = User::factory()->reseller('inicial', '2026-10-20')->create(['email' => 'revendedor@correo.com']);
        $this->buyer = $reseller;

        $order = $this->buy('inicial', 1);
        $this->libelulaReceives($order->public_id, '63');
        $this->artisan('bida:verificar-pagos')->assertSuccessful();

        // Pagar antes de vencer no hace perder días
        $this->assertSame('2026-11-20', $reseller->fresh()->subscription_renews_at->toDateString());
    }

    public function test_months_are_only_for_qr_and_the_amount_follows_the_prepay_discount(): void
    {
        config(['payments.prepay' => [1 => 0, 3 => 0, 12 => 10]]);

        $order = $this->buy('inicial', 12);

        // 9 × 12 = 108 US$, con 10 % menos = 97,20 US$ → Bs 677
        $this->assertSame('677.00', $order->amount);
        $this->assertSame(12, $order->months);

        $this->actingAs($this->buyer)->post(route('client.subscription.store'), ['plan' => 'inicial', 'method' => 'qr', 'months' => 7])
            ->assertSessionHasErrors('months');
    }
}
