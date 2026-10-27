<?php

namespace Tests\Feature;

use App\Models\User;
use App\Payments\PaymentGateways;
use App\Support\SubscriptionMovements;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\LemonSqueezyPayloads;
use Tests\TestCase;

/**
 * «Mi cuenta» muestra los movimientos de la tarjeta (cada cobro, devolución y su factura) pedidos a la pasarela, sin que el
 * cliente tenga que iniciar sesión en ella: solo los de su suscripción, con caché corta y sin trabar la página si la
 * pasarela falla.
 */
class SubscriptionMovementsTest extends TestCase
{
    use LemonSqueezyPayloads, RefreshDatabase;

    private function invoice(string $createdAt, int $cents, string $status, string $reason, bool $test = true, ?string $url = 'https://app.lemonsqueezy.com/my-orders/1/subscription-invoice/9?signature=abc'): array
    {
        return ['type' => 'subscription-invoices', 'id' => (string) crc32($createdAt), 'attributes' => [
            'created_at' => $createdAt, 'total' => $cents, 'currency' => 'USD', 'status' => $status, 'refunded' => $status === 'refunded',
            'billing_reason' => $reason, 'test_mode' => $test, 'urls' => ['invoice_url' => $url],
        ]];
    }

    private function fakeInvoices(array $data, int $status = 200): void
    {
        $this->configureLemonSqueezy();
        Http::fake(['api.lemonsqueezy.com/v1/subscription-invoices*' => Http::response(['data' => $data], $status)]);
    }

    private function subscriber(): User
    {
        return User::factory()->reseller('inicial', now()->addDays(10)->toDateString())->create([
            'payment_provider' => 'lemonsqueezy', 'provider_subscription_id' => '3001',
        ]);
    }

    public function test_the_account_lists_the_card_movements_from_the_gateway_for_its_own_subscription(): void
    {
        $this->fakeInvoices([
            $this->invoice('2026-09-10T15:00:00.000000Z', 1000, 'paid', 'initial'),
            $this->invoice('2026-10-10T15:00:00.000000Z', 1000, 'refunded', 'renewal'),
            $this->invoice('2026-11-10T15:00:00.000000Z', 2000, 'paid', 'updated', false, null),
        ]);

        $this->actingAs($this->subscriber())->withoutVite()->get(route('client.account'))
            ->assertOk()
            ->assertSee('Movimientos')
            // Del más nuevo al más viejo, con su concepto y su monto
            ->assertSeeInOrder(['10/11/2026', 'Cambio de plan', 'US$ 20', '10/10/2026', 'Renovación', 'US$ 10', '10/09/2026', 'Primer pago'])
            // Estado con ícono (su palabra queda para lectores de pantalla), pago de prueba y la factura en otra pestaña
            ->assertSee('Pagado')
            ->assertSee('Devuelto')
            ->assertSee('Pago de prueba')
            ->assertSee('href="https://app.lemonsqueezy.com/my-orders/1/subscription-invoice/9?signature=abc" target="_blank"', false)
            ->assertSee('aria-label="Ver la factura"', false);

        // Se pidieron solo las facturas de su suscripción, con la clave de la tienda
        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/subscription-invoices')
            && str_contains(urldecode($request->url()), 'filter[subscription_id]=3001'));
    }

    public function test_the_list_is_kept_a_few_minutes_so_the_page_does_not_call_the_gateway_every_time(): void
    {
        $this->fakeInvoices([$this->invoice('2026-09-10T15:00:00.000000Z', 1000, 'paid', 'initial')]);
        $user = $this->subscriber();

        $this->actingAs($user)->withoutVite()->get(route('client.account'))->assertOk();
        $this->actingAs($user)->withoutVite()->get(route('client.account'))->assertOk()->assertSee('Primer pago');

        Http::assertSentCount(1);
    }

    public function test_when_the_gateway_fails_the_page_still_works_with_the_payments_we_recorded_and_does_not_insist(): void
    {
        $this->fakeInvoices([], 500);
        $user = $this->subscriber();

        $this->actingAs($user)->withoutVite()->get(route('client.account'))
            ->assertOk()
            ->assertDontSee('Movimientos')
            ->assertSee('Administrar');
        $this->actingAs($user)->withoutVite()->get(route('client.account'))->assertOk();

        // La falla se recuerda un minuto: no se vuelve a llamar en cada visita
        Http::assertSentCount(1);
    }

    public function test_a_gateway_that_throws_is_treated_as_having_no_movements(): void
    {
        $this->configureLemonSqueezy();
        Http::fake(['api.lemonsqueezy.com/v1/subscription-invoices*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('sin red')]);
        $user = $this->subscriber();

        $this->assertSame([], SubscriptionMovements::for($user, app(PaymentGateways::class)));
        $this->actingAs($user)->withoutVite()->get(route('client.account'))->assertOk();
    }

    public function test_a_client_without_a_card_subscription_never_asks_the_gateway(): void
    {
        $this->configureLemonSqueezy();
        Http::fake();

        $this->actingAs(User::factory()->reseller('inicial', now()->addDays(10)->toDateString())->create())->withoutVite()->get(route('client.account'))->assertOk()->assertDontSee('Movimientos');
        $this->actingAs(User::factory()->create())->withoutVite()->get(route('client.account'))->assertOk();

        Http::assertNothingSent();
    }

    public function test_every_status_has_its_own_icon_and_amounts_come_in_dollars_not_cents(): void
    {
        $this->fakeInvoices([
            $this->invoice('2026-10-04T15:00:00.000000Z', 2950, 'paid', 'renewal'),
            $this->invoice('2026-10-03T15:00:00.000000Z', 1000, 'void', 'renewal'),
            $this->invoice('2026-10-02T15:00:00.000000Z', 1000, 'pending', 'renewal'),
            $this->invoice('2026-10-01T15:00:00.000000Z', 1000, 'partial_refund', 'renewal'),
        ]);

        $page = $this->actingAs($this->subscriber())->withoutVite()->get(route('client.account'))->assertOk();

        $page->assertSee('US$ 29,50')->assertSee('Pagado')->assertSee('Anulado')->assertSee('Pendiente')->assertSee('Devuelto');
    }
}
