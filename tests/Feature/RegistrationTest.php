<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.registration' => true, 'services.google.client_id' => null, 'services.google.client_secret' => null]);
    }

    public function test_arriving_with_a_plan_shows_it_and_continues_to_its_payment_after_confirming(): void
    {
        $this->withoutVite()->get('/registro?plan=aliado')
            ->assertOk()
            ->assertSee('Tu plan:')
            ->assertSee('Aliado')
            ->assertSee('Paso 2 de 4');

        // El plan viaja con la sesión: al confirmar el correo sigue el pago de ese plan
        $this->assertSame(route('client.subscription', ['plan' => 'aliado']), session('url.intended'));

        // Un plan que no existe no se guarda
        $this->flushSession();
        $this->withoutVite()->get('/registro?plan=inventado')->assertOk()->assertDontSee('Tu plan:');
        $this->assertNull(session('url.intended'));
    }

    public function test_a_visitor_opens_an_account_and_is_asked_to_confirm_the_email(): void
    {
        Notification::fake();

        $this->post('/registro', [
            'name' => ' María José Valenzuela ',
            'email' => ' Maria@Correo.com ',
            'password' => 'clave-segura-1',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'maria@correo.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame('María José Valenzuela', $user->name);
        $this->assertSame('maria.valenzuela', $user->username);
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isReseller());
        $this->assertFalse($user->hasVerifiedEmail());

        Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Confirma tu correo' && str_contains($mail->actionUrl, '/correo/confirmar/');
        });
    }

    public function test_the_email_must_be_new_whatever_its_case(): void
    {
        User::factory()->create(['email' => 'ana@correo.com']);

        $this->from('/registro')
            ->post('/registro', ['name' => 'Ana', 'email' => 'ANA@correo.com', 'password' => 'clave-segura-1'])
            ->assertRedirect('/registro')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::count());
    }

    public function test_registration_can_be_closed(): void
    {
        config(['auth.registration' => false]);

        $this->get('/registro')->assertNotFound();
        $this->post('/registro', ['name' => 'Ana', 'email' => 'ana@correo.com', 'password' => 'clave-segura-1'])->assertNotFound();
        $this->withoutVite()->get('/login')->assertOk()->assertDontSee('Crea tu cuenta');

        $this->assertSame(0, User::count());
    }

    public function test_the_login_page_offers_the_new_ways_in(): void
    {
        $this->withoutVite()->get('/login')
            ->assertOk()
            ->assertSee('Usuario o correo')
            ->assertSee(route('register'))
            ->assertSee(route('password.request'))
            // Sin credenciales de Google en .env no aparece el botón
            ->assertDontSee('Continuar con Google');

        config(['services.google.client_id' => 'id-de-prueba', 'services.google.client_secret' => 'secreto']);

        $this->withoutVite()->get('/login')->assertSee('Continuar con Google')->assertSee(route('auth.google'));
    }

    public function test_the_link_in_the_email_confirms_the_address(): void
    {
        $user = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);

        $this->actingAs($user)->get($url)
            ->assertRedirect(route('client.dashboard'))
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // El aviso llega a verse en el panel (no se gasta en una redirección intermedia)
        $other = User::factory()->unverified()->create();
        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $other->id, 'hash' => sha1($other->email)]);

        $this->actingAs($other)->withoutVite()->followingRedirects()->get($url)
            ->assertOk()
            ->assertSee('Listo: tu correo quedó confirmado.')
            ->assertDontSee('Confirma tu correo:');
    }

    public function test_a_link_for_another_account_or_tampered_does_not_confirm(): void
    {
        $user = User::factory()->unverified()->create();
        $other = User::factory()->unverified()->create();

        $forOther = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $other->id, 'hash' => sha1($other->email)]);
        $this->actingAs($user)->get($forOther)->assertForbidden();

        $unsigned = route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($unsigned)->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertFalse($other->fresh()->hasVerifiedEmail());
    }

    public function test_the_notice_shows_the_email_and_resends_the_link(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'luis@correo.com']);

        $this->actingAs($user)->withoutVite()->get(route('verification.notice'))->assertOk()->assertSee('luis@correo.com');

        $this->actingAs($user)->from(route('verification.notice'))
            ->post(route('verification.send'))
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('status');

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);

        // Ya confirmada (o sin correo, como las cuentas del equipo): no hay nada que confirmar
        $verified = User::factory()->create();
        $this->actingAs($verified)->get(route('verification.notice'))->assertRedirect(route('dashboard'));

        $withoutEmail = User::factory()->create(['email' => null, 'email_verified_at' => null]);
        $this->actingAs($withoutEmail)->get(route('verification.notice'))->assertRedirect(route('dashboard'));
    }

    public function test_the_panel_reminds_an_unconfirmed_email(): void
    {
        $user = User::factory()->unverified()->create(['email' => 'luis@correo.com']);

        $this->actingAs($user)->withoutVite()->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Confirma tu correo')
            ->assertSee('luis@correo.com')
            // Una cuenta nueva sin eventos ve los planes
            ->assertSee(route('diy').'#comparar', false);

        $this->actingAs(User::factory()->create())->withoutVite()->get(route('client.dashboard'))->assertDontSee('Confirma tu correo');
    }

    public function test_accounts_sign_in_with_their_email_too(): void
    {
        $user = User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave-segura-1']);

        $this->post('/login', ['username' => ' Ana@Correo.com', 'password' => 'clave-segura-1'])
            ->assertRedirect(route('client.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_with_the_email_does_not_sign_in(): void
    {
        User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave-segura-1']);

        $this->from('/login')
            ->post('/login', ['username' => 'ana@correo.com', 'password' => 'otra'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');

        $this->assertGuest();
    }
}
