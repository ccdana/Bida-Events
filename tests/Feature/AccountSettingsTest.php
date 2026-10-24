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
}
