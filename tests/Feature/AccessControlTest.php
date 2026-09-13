<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesInvitations;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use CreatesInvitations, RefreshDatabase;

    public function test_client_cannot_open_or_export_another_clients_invitation(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $invitation = $this->createInvitation(['user_id' => $owner->id]);

        $this->actingAs($intruder);

        $this->get(route('client.invitation.show', $invitation))->assertForbidden();
        $this->get(route('client.export.excel', $invitation))->assertForbidden();
        $this->get(route('client.export.pdf', $invitation))->assertForbidden();
        $this->get(route('client.export.invitation-pdf', $invitation))->assertForbidden();
    }

    public function test_policy_grants_owner_read_access_and_admin_full_access(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $invitation = $this->createInvitation(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('view', $invitation));
        $this->assertTrue($owner->can('export', $invitation));
        $this->assertFalse($owner->can('update', $invitation));
        $this->assertTrue($admin->can('update', $invitation));
        $this->assertTrue($admin->can('manageGuests', $invitation));
    }

    public function test_guest_routes_are_scoped_to_their_invitation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $invitation = $this->createInvitation();
        $guest = $this->createInvitation()->guests()->create(['name' => 'Invitado de otra fiesta']);

        $this->actingAs($admin)
            ->delete(route('admin.guests.destroy', [$invitation, $guest]))
            ->assertNotFound();

        $this->assertModelExists($guest);
    }

    public function test_login_attempts_are_throttled(): void
    {
        config(['optimizations.rate_limits.login' => 2]);
        $credentials = ['username' => 'nadie', 'password' => 'incorrecta'];

        $this->post('/login', $credentials)->assertSessionHasErrors('username');
        $this->post('/login', $credentials)->assertSessionHasErrors('username');

        $this->post('/login', $credentials)
            ->assertSessionHasErrors(['username' => 'Demasiados intentos. Espera un momento e inténtalo de nuevo.']);
    }

    public function test_users_sign_in_with_their_username(): void
    {
        $client = User::factory()->create(['username' => 'maria.valenzuela', 'password' => 'k7mq-2hxa']);

        $this->post('/login', ['username' => 'Maria.Valenzuela ', 'password' => 'k7mq-2hxa'])
            ->assertRedirect(route('client.dashboard'));

        $this->assertAuthenticatedAs($client);
    }
}
