<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\ResellerSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

/**
 * El cliente de cada evento del revendedor: uno por evento, que ve sus invitados sin poder editar,
 * y que el revendedor elimina y vuelve a crear si se equivocó.
 */
class ResellerClientTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_the_reseller_creates_one_client_per_event(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id]);

        $this->actingAs($reseller)
            ->post(route('client.invitations.client.store', $invitation), ['name' => 'Familia Quispe'])
            ->assertRedirect()
            ->assertSessionHas('client_credentials');

        $client = User::where('name', 'Familia Quispe')->firstOrFail();
        $this->assertSame($client->id, $invitation->fresh()->user_id);
        $this->assertSame($reseller->id, $client->created_by_reseller_id);
        $this->assertFalse($client->isReseller());

        // Un segundo cliente para el mismo evento no se crea
        $this->actingAs($reseller)
            ->post(route('client.invitations.client.store', $invitation), ['name' => 'Otro'])
            ->assertSessionHasErrors('client');
        $this->assertDatabaseMissing('users', ['name' => 'Otro']);

        // El cliente ve su evento, pero no lo edita
        $this->actingAs($client)->withoutVite()->get(route('client.invitation.show', $invitation))->assertOk();
        $this->assertFalse($client->can('update', $invitation));
    }

    public function test_a_wrong_client_is_deleted_and_another_one_is_created(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();
        $invitation = $this->createInvitation(['reseller_id' => $reseller->id]);

        $this->actingAs($reseller)->post(route('client.invitations.client.store', $invitation), ['name' => 'Nombre mal escrito']);
        $wrong = User::where('name', 'Nombre mal escrito')->firstOrFail();

        $this->actingAs($reseller)
            ->delete(route('client.invitations.client.destroy', $invitation))
            ->assertRedirect();

        $this->assertModelMissing($wrong);
        $this->assertNull($invitation->fresh()->user_id);
        // La invitación sigue a cargo del revendedor
        $this->assertSame($reseller->id, $invitation->fresh()->reseller_id);

        $this->actingAs($reseller)->post(route('client.invitations.client.store', $invitation), ['name' => 'Nombre bien escrito']);
        $this->assertSame('Nombre bien escrito', $invitation->fresh()->user->name);
    }

    public function test_the_reseller_cannot_touch_clients_of_other_events(): void
    {
        $reseller = User::factory()->reseller('aliado')->create();
        $foreign = $this->createInvitation(['reseller_id' => User::factory()->reseller()->create()->id]);

        $this->actingAs($reseller)
            ->post(route('client.invitations.client.store', $foreign), ['name' => 'Intruso'])
            ->assertForbidden();

        // Ni borrar un cliente que creó el equipo en una invitación suya
        $teamClient = User::factory()->create();
        $own = $this->createInvitation(['reseller_id' => $reseller->id, 'user_id' => $teamClient->id]);

        $this->actingAs($reseller)
            ->delete(route('client.invitations.client.destroy', $own))
            ->assertForbidden();
        $this->assertModelExists($teamClient);
    }

    public function test_every_plan_creates_clients_but_not_more_per_month_than_its_invitations(): void
    {
        // Plan Inicial: 3 invitaciones al mes, así que 3 accesos de cliente
        $reseller = User::factory()->reseller('inicial')->create();
        $invitations = collect(range(1, 4))->map(fn () => $this->createInvitation(['reseller_id' => $reseller->id]));

        foreach ($invitations->take(3) as $index => $invitation) {
            $this->actingAs($reseller)
                ->post(route('client.invitations.client.store', $invitation), ['name' => "Familia {$index}"])
                ->assertSessionHas('client_credentials');
        }

        $this->actingAs($reseller)
            ->post(route('client.invitations.client.store', $invitations[3]), ['name' => 'Familia de más'])
            ->assertSessionHasErrors('client');
        $this->assertDatabaseMissing('users', ['name' => 'Familia de más']);

        $this->actingAs($reseller)
            ->withoutVite()
            ->get(route('client.invitation.show', $invitations[3]))
            ->assertOk()
            ->assertSee('Ya creaste todos los accesos de cliente de este mes');

        // Si uno se creó mal y se borra, deja lugar para el correcto
        $this->actingAs($reseller)->delete(route('client.invitations.client.destroy', $invitations[0]));

        $this->actingAs($reseller)
            ->post(route('client.invitations.client.store', $invitations[3]), ['name' => 'Familia correcta'])
            ->assertSessionHas('client_credentials');

        // El mes siguiente vuelve a tener cupo
        $this->travel(1)->months();
        $this->assertTrue(ResellerSubscription::canCreateClients($reseller->fresh()));
    }
}
