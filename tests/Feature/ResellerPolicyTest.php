<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * Quién crea y quién edita: el administrador todo; el revendedor al día, solo lo suyo; el cliente
 * normal y el revendedor vencido, nada.
 */
class ResellerPolicyTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_only_an_active_reseller_creates_invitations(): void
    {
        $this->assertTrue(User::factory()->reseller()->create()->can('create', Invitation::class));
        $this->assertTrue(User::factory()->create(['is_admin' => true])->can('create', Invitation::class));

        $this->assertFalse(User::factory()->create()->can('create', Invitation::class));
        $this->assertFalse(User::factory()->reseller(renewsAt: now()->subDay()->toDateString())->create()->can('create', Invitation::class));
        $this->assertFalse(User::factory()->reseller(status: 'canceled')->create()->can('create', Invitation::class));
    }

    public function test_the_reseller_edits_only_its_own_invitations_while_it_is_up_to_date(): void
    {
        $reseller = User::factory()->reseller()->create();
        $own = $this->createInvitation(['reseller_id' => $reseller->id]);
        $foreign = $this->createInvitation(['reseller_id' => User::factory()->reseller()->create()->id]);
        $unassigned = $this->createInvitation();

        $this->assertTrue($reseller->can('update', $own));
        $this->assertFalse($reseller->can('update', $foreign));
        $this->assertFalse($reseller->can('update', $unassigned));

        // Vencido: sigue viendo lo suyo, pero ya no lo edita
        $reseller->update(['subscription_renews_at' => now()->subDay()->toDateString()]);
        $this->assertFalse($reseller->fresh()->can('update', $own));
        $this->assertTrue($reseller->fresh()->can('view', $own));
    }

    public function test_a_normal_client_still_cannot_edit_its_invitation(): void
    {
        $client = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $client->id]);

        $this->assertFalse($client->can('update', $invitation));
        $this->assertTrue(User::factory()->create(['is_admin' => true])->can('update', $invitation));
    }

    public function test_the_reseller_middleware_lets_only_resellers_through(): void
    {
        $this->actingAs(User::factory()->create())->get(route('client.invitations.create'))->assertForbidden();
        // Un revendedor vencido entra a la sección, pero la policy no lo deja crear
        $this->actingAs(User::factory()->reseller(renewsAt: now()->subDay()->toDateString())->create())
            ->get(route('client.invitations.create'))
            ->assertForbidden();
    }
}
