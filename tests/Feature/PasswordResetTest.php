<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_link_goes_to_the_account_email_in_spanish(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'ana@correo.com']);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'Ana@Correo.com'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Crea una contraseña nueva'
                && str_contains($mail->actionUrl, '/contrasena/nueva/'.$notification->token)
                && str_contains($mail->actionUrl, 'email=ana%40correo.com');
        });
    }

    public function test_an_unknown_email_gets_the_same_answer(): void
    {
        Notification::fake();
        User::factory()->create(['email' => 'ana@correo.com']);

        $known = $this->post(route('password.email'), ['email' => 'ana@correo.com'])->assertSessionHasNoErrors()->getSession()->get('status');
        $unknown = $this->post(route('password.email'), ['email' => 'nadie@correo.com'])->assertSessionHasNoErrors()->getSession()->get('status');

        $this->assertNotEmpty($known);
        $this->assertSame($known, $unknown);
        Notification::assertSentTimes(ResetPassword::class, 1);
    }

    public function test_the_new_password_works_and_confirms_the_email(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'ana@correo.com', 'password' => 'clave-vieja-1']);
        $token = Password::createToken($user);

        $this->withoutVite()->get(route('password.reset', ['token' => $token, 'email' => 'ana@correo.com']))
            ->assertOk()
            ->assertSee('ana@correo.com');

        $this->post(route('password.store'), ['token' => $token, 'email' => 'ana@correo.com', 'password' => 'clave-nueva-1'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue(Hash::check('clave-nueva-1', $user->password));
        $this->assertTrue($user->hasVerifiedEmail());

        // El enlace sirve una sola vez
        $this->from(route('password.reset', ['token' => $token]))
            ->post(route('password.store'), ['token' => $token, 'email' => 'ana@correo.com', 'password' => 'otra-clave-1'])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('clave-nueva-1', $user->fresh()->password));
    }

    public function test_an_invalid_token_changes_nothing(): void
    {
        $user = User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave-vieja-1']);

        $this->from(route('password.reset', ['token' => 'inventado']))
            ->post(route('password.store'), ['token' => 'inventado', 'email' => 'ana@correo.com', 'password' => 'clave-nueva-1'])
            ->assertRedirect(route('password.reset', ['token' => 'inventado']))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('clave-vieja-1', $user->fresh()->password));
    }

    public function test_resetting_while_signed_in_signs_out(): void
    {
        $user = User::factory()->create(['email' => 'ana@correo.com']);
        $token = Password::createToken($user);

        $this->actingAs($user)
            ->post(route('password.store'), ['token' => $token, 'email' => 'ana@correo.com', 'password' => 'clave-nueva-1'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
