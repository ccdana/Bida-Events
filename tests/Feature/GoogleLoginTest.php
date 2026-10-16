<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'auth.registration' => true,
            'services.google.client_id' => 'id-de-prueba',
            'services.google.client_secret' => 'secreto',
        ]);
    }

    /** Lo que devolvería Google al volver al sitio. */
    private function googleReturns(string $id = 'google-123', string $email = 'ana@gmail.com', bool $verified = true, string $name = 'Ana Torrico'): void
    {
        $user = (new GoogleUser)
            ->setRaw(['sub' => $id, 'email' => $email, 'email_verified' => $verified, 'name' => $name])
            ->map(['id' => $id, 'name' => $name, 'email' => $email, 'avatar' => 'https://lh3.googleusercontent.com/a/foto']);

        Socialite::shouldReceive('driver->user')->andReturn($user);
    }

    public function test_the_button_sends_to_google_only_when_configured(): void
    {
        $this->get(route('auth.google'))->assertRedirectContains('accounts.google.com');

        config(['services.google.client_id' => null]);
        $this->get(route('auth.google'))->assertNotFound();
        $this->get(route('auth.google.callback'))->assertNotFound();
    }

    public function test_a_new_person_gets_an_account_with_the_email_confirmed(): void
    {
        $this->googleReturns();

        $this->get(route('auth.google.callback'))->assertRedirect(route('client.dashboard'));

        $user = User::where('google_id', 'google-123')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('ana@gmail.com', $user->email);
        $this->assertSame('ana.torrico', $user->username);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame('https://lh3.googleusercontent.com/a/foto', $user->avatar_url);
        $this->assertFalse($user->isAdmin());
    }

    public function test_coming_back_signs_into_the_same_account(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123', 'email' => 'otro@correo.com']);
        $this->googleReturns();

        $this->get(route('auth.google.callback'))->assertRedirect(route('client.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(1, User::count());
    }

    public function test_an_existing_confirmed_account_is_linked_and_keeps_its_password(): void
    {
        $user = User::factory()->create(['email' => 'ana@gmail.com', 'password' => 'clave-propia-1']);
        $this->googleReturns();

        $this->get(route('auth.google.callback'))->assertRedirect(route('client.dashboard'));

        $user->refresh();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-123', $user->google_id);
        $this->assertTrue(Hash::check('clave-propia-1', $user->password));
    }

    public function test_linking_an_unconfirmed_account_replaces_a_password_that_may_be_someone_elses(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'ana@gmail.com', 'password' => 'clave-de-otro-1']);
        $this->googleReturns();

        $this->get(route('auth.google.callback'))->assertRedirect(route('client.dashboard'));

        $user->refresh();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertFalse(Hash::check('clave-de-otro-1', $user->password));
    }

    public function test_a_google_email_that_is_not_confirmed_is_rejected(): void
    {
        $user = User::factory()->create(['email' => 'ana@gmail.com']);
        $this->googleReturns(verified: false);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_an_account_linked_to_another_google_account_is_not_taken(): void
    {
        User::factory()->create(['email' => 'ana@gmail.com', 'google_id' => 'google-999']);
        $this->googleReturns();

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_with_registration_closed_only_existing_accounts_get_in(): void
    {
        config(['auth.registration' => false]);
        $this->googleReturns();

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->assertSame(0, User::count());

        $user = User::factory()->create(['email' => 'ana@gmail.com']);
        $this->get(route('auth.google.callback'))->assertRedirect(route('client.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_cancelling_or_an_expired_state_goes_back_to_the_login(): void
    {
        $this->get(route('auth.google.callback', ['error' => 'access_denied']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');

        Socialite::shouldReceive('driver->user')->andThrow(new InvalidStateException);

        $this->get(route('auth.google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
