<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientCredentialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_client_with_only_a_name(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        User::factory()->create(['username' => 'maria.valenzuela']);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.clients.store'), ['name' => 'María José Valenzuela'])
            ->assertCreated()
            ->assertJsonPath('client.username', 'maria.valenzuela2');

        $password = $response->json('client.password');
        $this->assertMatchesRegularExpression('/^[a-z2-9]{4}-[a-z2-9]{4}$/', $password);

        $client = User::where('username', 'maria.valenzuela2')->firstOrFail();
        $this->assertFalse($client->isAdmin());
        $this->assertNull($client->email);
        $this->assertSame($password, $client->access_password);
        $this->assertNotSame($password, $client->getRawOriginal('access_password'));

        // El cliente entra con el usuario y la contraseña generados
        auth()->logout();
        $this->post('/login', ['username' => 'maria.valenzuela2', 'password' => $password])
            ->assertRedirect(route('client.dashboard'));
    }

    public function test_client_name_is_required(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->postJson(route('admin.clients.store'), ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_invitation_status_only_accepts_active_or_inactive(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.invitations.store'), ['status' => 'draft'])
            ->assertSessionHasErrors('status');
    }
}
