<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El panel del revendedor: el botón para crear, el cupo usado del mes y el aviso de vencimiento.
 * El cliente normal sigue viendo su panel de siempre.
 */
class ResellerDashboardTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_reseller_sees_its_quota_and_the_button_to_create(): void
    {
        $reseller = User::factory()->reseller('aliado', now()->addDays(20)->toDateString())->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id, 'title' => 'Boda de un cliente']);

        $this->actingAs($reseller)
            ->withoutVite()
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Plan Aliado')
            ->assertSee('1 de 8')
            ->assertSee(route('client.invitations.create'), false)
            ->assertSee(route('client.invitations.edit', $invitation), false)
            ->assertDontSee('Tu suscripción vence');
    }

    public function test_the_reseller_is_warned_before_and_after_the_due_date(): void
    {
        $soon = User::factory()->reseller(renewsAt: now()->addDays(3)->toDateString())->create();

        $this->actingAs($soon)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Tu suscripción vence en 3 días');

        $expired = User::factory()->reseller(renewsAt: now()->subDays(2)->toDateString())->create();
        $invitation = $this->createInvitation(['reseller_id' => $expired->id]);

        $this->actingAs($expired)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Tu suscripción venció')
            ->assertSee('Renueva tu suscripción para crear invitaciones.')
            // Vencido: sin enlace al editor ni a crear
            ->assertDontSee(route('client.invitations.create'), false)
            ->assertDontSee(route('client.invitations.edit', $invitation), false);
    }

    public function test_without_quota_the_button_explains_why(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();

        foreach (range(1, 8) as $ignored) {
            $this->createInvitation(['reseller_id' => $reseller->id]);
        }

        $this->actingAs($reseller)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('8 de 8')
            ->assertSee('Ya usaste el cupo de este mes.')
            ->assertDontSee(route('client.invitations.create'), false);
    }

    public function test_a_normal_client_does_not_see_the_reseller_block(): void
    {
        $client = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $client->id]);

        $this->actingAs($client)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertDontSee('Nueva invitación')
            ->assertDontSee(route('client.invitations.edit', $invitation), false);
    }
}
