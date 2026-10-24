<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_team_client_adds_an_email_and_must_confirm_it(): void
    {
        Notification::fake();
        $client = User::factory()->create(['email' => null, 'email_verified_at' => null, 'password' => 'luna-brillante-473']);

        $this->actingAs($client)->withoutVite()->get(route('client.account'))->assertOk()->assertSee('Agregar un correo');

        $this->actingAs($client)->from(route('client.account'))
            ->put(route('client.account.email'), ['email' => ' Maria@Correo.com ', 'email_current_password' => 'luna-brillante-473'])
            ->assertRedirect(route('client.account'))
            ->assertSessionHas('success');

        $client->refresh();
        $this->assertSame('maria@correo.com', $client->email);
        $this->assertFalse($client->hasVerifiedEmail());
        Notification::assertSentTo($client, VerifyEmail::class);
    }

    public function test_changing_the_email_needs_the_password_and_a_free_address(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'ocupado@correo.com']);
        $client = User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave-propia-1']);

        $this->actingAs($client)->from(route('client.account'))
            ->put(route('client.account.email'), ['email' => 'nuevo@correo.com', 'email_current_password' => 'otra'])
            ->assertSessionHasErrors('email_current_password');

        $this->actingAs($client)->from(route('client.account'))
            ->put(route('client.account.email'), ['email' => 'OCUPADO@correo.com', 'email_current_password' => 'clave-propia-1'])
            ->assertSessionHasErrors('email');

        $this->assertSame('ana@correo.com', $client->fresh()->email);
        $this->assertTrue($client->fresh()->hasVerifiedEmail());
        Notification::assertNothingSent();
    }

    public function test_a_confirmed_email_gets_a_link_to_create_a_password(): void
    {
        Notification::fake();
        $client = User::factory()->create(['email' => 'ana@gmail.com', 'google_id' => 'google-123']);

        $this->actingAs($client)->withoutVite()->get(route('client.account'))
            ->assertOk()
            ->assertSee('Entras con Google')
            ->assertSee('Crear una contraseña propia');

        $this->actingAs($client)->from(route('client.account'))
            ->post(route('client.account.password-link'))
            ->assertRedirect(route('client.account'))
            ->assertSessionHas('success');

        Notification::assertSentTo($client, ResetPassword::class);
    }

    public function test_no_link_without_a_confirmed_email(): void
    {
        Notification::fake();
        $client = User::factory()->unverified()->create();

        $this->actingAs($client)->from(route('client.account'))
            ->post(route('client.account.password-link'))
            ->assertSessionHasErrors('password_link');

        Notification::assertNothingSent();
    }

    public function test_the_username_changes_with_the_password_and_the_old_one_is_freed(): void
    {
        $client = User::factory()->create(['username' => 'maria.valenzuela', 'password' => 'clave-propia-1']);
        $other = User::factory()->create(['username' => 'otra.persona']);

        $page = $this->actingAs($client)->withoutVite()->get(route('client.account'))->assertOk();
        $page->assertSee('maria.valenzuela')->assertSee('Cambiar el usuario')->assertSee(route('client.account.username'), false);

        // Lo escrito se guarda en minúsculas y sin espacios de más
        $this->actingAs($client)->from(route('client.account'))
            ->put(route('client.account.username'), ['username' => '  María.V-2026 ', 'username_current_password' => 'clave-propia-1'])
            ->assertSessionHasErrors('username');

        $this->actingAs($client)->from(route('client.account'))
            ->put(route('client.account.username'), ['username' => '  Maria.V-2026 ', 'username_current_password' => 'clave-propia-1'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
        $this->assertSame('maria.v-2026', $client->fresh()->username);

        // El anterior queda libre: otra persona lo puede tomar
        $this->actingAs($other)->put(route('client.account.username'), ['username' => 'maria.valenzuela', 'username_current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame('maria.valenzuela', $other->fresh()->username);

        // Y con el nuevo se entra (el usuario sirve para ingresar, como el correo)
        auth()->logout();
        $this->post(route('login.attempt'), ['username' => 'Maria.V-2026', 'password' => 'clave-propia-1'])->assertRedirect();
        $this->assertAuthenticatedAs($client->fresh());
    }

    public function test_the_new_username_has_to_be_valid_free_and_confirmed_with_the_password(): void
    {
        // Se envía el formulario muchas veces seguidas: el límite de intentos no es lo que se prueba aquí
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        User::factory()->create(['username' => 'ocupado']);
        $client = User::factory()->create(['username' => 'ana.quispe', 'password' => 'clave-propia-1']);
        $this->actingAs($client);

        $bad = [
            'ab' => 'username',                         // muy corto
            str_repeat('a', 31) => 'username',          // muy largo
            'con espacio' => 'username',                // sin espacios
            '.empieza' => 'username',                   // sin signo al empezar
            'termina-' => 'username',                   // ni al terminar
            'doble..punto' => 'username',               // ni repetido
            'ana@correo.com' => 'username',             // no se parece a un correo
            'ocupado' => 'username',                    // lo usa otra cuenta
            'OCUPADO' => 'username',                    // aunque lo escriban distinto
            'admin' => 'username',                      // reservados del sistema
            'soporte' => 'username',
        ];

        foreach ($bad as $username => $field) {
            $this->from(route('client.account'))
                ->put(route('client.account.username'), ['username' => (string) $username, 'username_current_password' => 'clave-propia-1'])
                ->assertSessionHasErrors($field);
        }

        // Sin la contraseña actual, o con una equivocada, no cambia
        $this->from(route('client.account'))->put(route('client.account.username'), ['username' => 'ana.nueva'])->assertSessionHasErrors('username_current_password');
        $this->from(route('client.account'))->put(route('client.account.username'), ['username' => 'ana.nueva', 'username_current_password' => 'otra'])->assertSessionHasErrors('username_current_password');

        $this->assertSame('ana.quispe', $client->fresh()->username);
    }

    public function test_keeping_the_same_username_is_fine_even_if_the_team_gave_a_long_one(): void
    {
        $long = 'estudio.fotografico.de.eventos.2026.bolivia';
        $client = User::factory()->create(['username' => $long, 'password' => 'clave-propia-1']);

        $this->actingAs($client)->from(route('client.account'))
            ->put(route('client.account.username'), ['username' => $long, 'username_current_password' => 'clave-propia-1'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Tu usuario sigue siendo el mismo.');
        $this->assertSame($long, $client->fresh()->username);
    }
}
