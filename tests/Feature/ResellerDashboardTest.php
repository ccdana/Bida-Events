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
        $reseller = User::factory()->reseller('inicial', now()->addDays(20)->toDateString())->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id, 'title' => 'Boda de un cliente']);

        $this->actingAs($reseller)
            ->withoutVite()
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Plan Inicial')
            ->assertSee('1 de 5')
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

        // Vencido y pasados los días de gracia (config «payments.grace_days»)
        $expired = User::factory()->reseller(renewsAt: now()->subDays(10)->toDateString())->create();
        $invitation = $this->createInvitation(['reseller_id' => $expired->id]);

        $this->actingAs($expired)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Tu suscripción venció')
            ->assertSee('Renueva tu suscripción para crear invitaciones.')
            // Vencido: sin enlace al editor ni a crear
            ->assertDontSee(route('client.invitations.create'), false)
            ->assertDontSee(route('client.invitations.edit', $invitation), false);
    }

    public function test_without_quota_it_still_creates_drafts_and_says_when_it_can_publish(): void
    {
        $reseller = User::factory()->reseller()->create();

        foreach (range(1, 5) as $ignored) {
            $this->createInvitation(['reseller_id' => $reseller->id]);
        }

        // El cupo cuenta las publicadas: sin cupo igual arma borradores, y se le dice cuándo publica
        $this->actingAs($reseller)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('5 de 5')
            ->assertSee('invitaciones publicadas este mes')
            ->assertSee('Ya publicaste todas las de este mes')
            ->assertSee(route('client.invitations.create'), false);
    }

    public function test_a_normal_client_does_not_see_the_reseller_block(): void
    {
        $client = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $client->id]);

        // Sin plan ni cupo: puede armar la suya con «Hazlo tú», no con el editor del revendedor
        $this->actingAs($client)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertDontSee('invitaciones creadas este mes')
            ->assertDontSee(route('client.invitations.create'), false)
            ->assertDontSee(route('client.invitations.edit', $invitation), false)
            ->assertSee(route('client.diy.create'), false);
    }
}
