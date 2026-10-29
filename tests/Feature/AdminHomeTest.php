<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\PaymentOrder;
use App\Models\SubscriptionPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El inicio del administrador: tres números del momento (eventos de la semana, activas y cobrado en el mes), lo que pide
 * atención (revendedores por vencer y compras que siguen esperando) y una sola lista por fecha, con lo ya pasado al final.
 */
class AdminHomeTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_the_numbers_are_the_events_of_the_week_the_active_ones_and_what_was_collected_this_month(): void
    {
        $this->createInvitation(['title' => 'Esta semana', 'event_date' => now()->addDays(3)]);
        $this->createInvitation(['title' => 'Más adelante', 'event_date' => now()->addDays(30), 'status' => 'inactive']);
        $withGuests = $this->createInvitation(['title' => 'Con invitados', 'event_date' => now()->addDays(60)]);
        Guest::create(['invitation_id' => $withGuests->id, 'name' => 'Ana', 'passes_allocated' => 2]);

        // Cobrado este mes: una compra en línea pagada y un pago registrado a mano; lo del mes pasado no cuenta
        $reseller = User::factory()->reseller()->create();
        $paid = PaymentOrder::create(['user_id' => $reseller->id, 'purpose' => 'subscription', 'plan' => 'inicial', 'months' => 1, 'amount' => '10.00', 'currency' => 'USD', 'provider' => 'lemonsqueezy']);
        $paid->forceFill(['status' => PaymentOrder::PAID, 'paid_at' => now()])->save();
        SubscriptionPayment::create(['user_id' => $reseller->id, 'plan' => 'inicial', 'amount' => '250.00', 'currency' => 'BOB', 'provider' => SubscriptionPayment::MANUAL, 'paid_at' => now()->toDateString(), 'renews_until' => now()->addMonth()->toDateString()]);
        $old = PaymentOrder::create(['user_id' => $reseller->id, 'purpose' => 'subscription', 'plan' => 'inicial', 'months' => 1, 'amount' => '99.00', 'currency' => 'USD', 'provider' => 'lemonsqueezy']);
        $old->forceFill(['status' => PaymentOrder::PAID, 'paid_at' => now()->subMonths(2)])->save();

        $page = $this->actingAs($this->admin)->withoutVite()->get(route('admin.dashboard'))->assertOk();

        $page->assertViewHas('pulse', fn (array $pulse) => $pulse['weekEvents'] === 1
            && $pulse['active'] === 2 && $pulse['total'] === 3 && $pulse['guests'] === 1
            && collect($pulse['revenue'])->contains(fn (string $amount) => str_contains($amount, '10'))
            && collect($pulse['revenue'])->contains(fn (string $amount) => str_contains($amount, '250'))
            && ! collect($pulse['revenue'])->contains(fn (string $amount) => str_contains($amount, '99')));
        $page->assertSee('adm-pulse', false)
            ->assertSee('Esta semana')
            ->assertSee('Activas')
            ->assertSee('Este mes')
            // Sin los cuatro números grandes ni el párrafo de antes
            ->assertDontSee('Los eventos de tus clientes separados por tipo.');
    }

    public function test_what_needs_attention_shows_resellers_about_to_expire_and_purchases_that_keep_waiting(): void
    {
        $due = User::factory()->reseller('inicial', now()->addDays(3)->toDateString())->create(['name' => 'Rosa Revende']);
        User::factory()->reseller('inicial', now()->addDays(25)->toDateString())->create(['name' => 'Al día']);
        $buyer = User::factory()->create(['name' => 'Diego Hazlotu']);

        // Una compra de hace horas sigue esperando; una de hace un momento todavía puede estar pagándose
        $stale = PaymentOrder::create(['user_id' => $buyer->id, 'purpose' => 'subscription', 'plan' => 'inicial', 'months' => 1, 'amount' => '19.00', 'currency' => 'USD', 'provider' => 'libelula', 'expires_at' => now()->addDay()]);
        $stale->forceFill(['created_at' => now()->subHours(3)])->save();
        $recent = User::factory()->create(['name' => 'Recién Llegada']);
        PaymentOrder::create(['user_id' => $recent->id, 'purpose' => 'subscription', 'plan' => 'inicial', 'months' => 1, 'amount' => '19.00', 'currency' => 'USD', 'provider' => 'libelula', 'expires_at' => now()->addDay()]);

        $this->actingAs($this->admin)->withoutVite()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Por atender')
            ->assertSee('Rosa Revende')
            ->assertSee('vence en 3 días')
            ->assertDontSee('Al día')
            ->assertSee('Diego Hazlotu')
            ->assertSee('hace 3 horas')
            ->assertDontSee('Recién Llegada');
    }

    public function test_without_anything_to_attend_the_section_is_not_drawn(): void
    {
        $this->actingAs($this->admin)->withoutVite()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Por atender');
    }

    public function test_the_list_goes_by_date_with_what_already_happened_at_the_end_and_a_separator(): void
    {
        $this->createInvitation(['title' => 'Ya pasada', 'event_date' => now()->subDays(20)]);
        $this->createInvitation(['title' => 'Lejana', 'event_date' => now()->addDays(90)]);
        $this->createInvitation(['title' => 'Cercana', 'event_date' => now()->addDays(5)]);

        $this->actingAs($this->admin)->withoutVite()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Cercana', 'Lejana', 'Ya pasaron', 'Ya pasada'])
            // «Creadas hace poco» sigue siendo una opción de la barra lateral
            ->assertSee('Próximos primero')
            ->assertSee('Creadas hace poco');
    }

    public function test_recent_first_keeps_working_from_the_sidebar(): void
    {
        $old = $this->createInvitation(['title' => 'Primera creada', 'event_date' => now()->addDays(5)]);
        $old->forceFill(['created_at' => now()->subDays(10)])->save();
        $this->createInvitation(['title' => 'Última creada', 'event_date' => now()->addDays(50)]);

        $this->actingAs($this->admin)->withoutVite()->get(route('admin.dashboard', ['orden' => 'recientes']))
            ->assertOk()
            ->assertSeeInOrder(['Última creada', 'Primera creada']);
    }

    public function test_a_row_is_compact_and_its_card_opens_with_the_client_and_the_link(): void
    {
        $client = User::factory()->create(['name' => 'Familia Quispe', 'username' => 'familia.quispe']);
        $invitation = $this->createInvitation(['title' => 'XV de Valentina', 'user_id' => $client->id, 'event_date' => now()->addDays(23)]);

        $this->actingAs($this->admin)->withoutVite()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('faltan 23 días')
            ->assertSee('title="Invitados"', false)
            ->assertSee('title="Ver ficha"', false)
            ->assertSee('Familia Quispe')
            ->assertSee(route('admin.invitations.edit', $invitation), false)
            ->assertSee('Eliminar invitación')
            ->assertSee('class="adm-ficha"', false);
    }
}
