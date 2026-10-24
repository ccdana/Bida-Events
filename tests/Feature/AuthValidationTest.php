<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\EmailTypos;
use App\Support\PasswordPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Lo que se le exige a quien crea una cuenta o cambia su contraseña: correo único (sin distinguir mayúsculas), un nombre
 * que sea un nombre y una contraseña con letras y números, ni muy corta ni más larga de lo que bcrypt lee, y distinta del
 * correo y del nombre. La misma política vale al crear la cuenta, al recuperar la contraseña y al cambiarla en «Mi cuenta».
 */
class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth.registration' => true, 'services.google.client_id' => null, 'services.google.client_secret' => null]);

        // Estas pruebas envían el formulario muchas veces seguidas: el límite de intentos tiene sus propias pruebas
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function register(array $data): \Illuminate\Testing\TestResponse
    {
        return $this->from(route('register'))->post(route('register.store'), $data + [
            'name' => 'María Valenzuela',
            'email' => 'maria@correo.com',
            'password' => 'clave-segura-2026',
        ]);
    }

    public function test_a_valid_registration_creates_the_account(): void
    {
        $this->register([])->assertRedirect(route('verification.notice'));

        $this->assertSame(1, User::where('email', 'maria@correo.com')->count());
    }

    public function test_the_email_is_unique_whatever_its_case_and_the_page_offers_both_ways_out(): void
    {
        User::factory()->create(['email' => 'maria@correo.com']);

        $this->register(['email' => '  MARIA@Correo.com '])->assertSessionHasErrors(['email' => 'Este correo ya tiene una cuenta.']);
        $this->assertSame(1, User::where('email', 'maria@correo.com')->count());

        // En vez de explicarlo, el formulario da los dos caminos: ingresar o recuperar la contraseña
        $this->followingRedirects()->withoutVite()->register(['email' => 'maria@correo.com'])
            ->assertSee('Este correo ya tiene una cuenta.')
            ->assertSee(route('login'), false)
            ->assertSee(route('password.request'), false);
    }

    public function test_the_name_has_to_be_a_name(): void
    {
        foreach (['A', ' ', '12', 'Ver http://spam.example', 'visita www.spam.example', '<b>Ana</b>', 'ana@correo.com'] as $bad) {
            $this->register(['name' => $bad])->assertSessionHasErrors('name');
        }
        $this->assertSame(0, User::count());

        // Nombres reales con acentos, apóstrofes, guiones y espacios de más
        foreach (['José-Luis Quispe', "Ana O'Brien", 'Ñusta  Mamani'] as $index => $good) {
            $this->register(['name' => $good, 'email' => "persona{$index}@correo.com"])->assertSessionDoesntHaveErrors('name');
            // Crear la cuenta inicia la sesión: se sale para registrar la siguiente
            \Illuminate\Support\Facades\Auth::logout();
            $this->flushSession();
        }
        $this->assertSame('Ñusta Mamani', User::where('email', 'persona2@correo.com')->value('name'));
    }

    public function test_the_password_needs_letters_and_numbers_and_a_sensible_length(): void
    {
        $this->register(['password' => 'sololetras'])->assertSessionHasErrors('password');
        $this->register(['password' => '1234567890'])->assertSessionHasErrors('password');
        $this->register(['password' => 'abc123'])->assertSessionHasErrors(['password' => 'La contraseña necesita al menos 8 caracteres.']);
        // bcrypt solo lee 72 caracteres: una más larga solo parecería más segura
        $this->register(['password' => str_repeat('a1', 37)])->assertSessionHasErrors(['password' => 'La contraseña puede tener hasta 72 caracteres.']);
        $this->assertSame(0, User::count());

        $this->register(['password' => str_repeat('a1', 36)])->assertSessionDoesntHaveErrors('password');
    }

    public function test_the_password_cannot_be_the_email_the_name_or_the_start_of_the_email(): void
    {
        $this->register(['email' => 'maria1@correo.com', 'password' => 'maria1@correo.com'])->assertSessionHasErrors(['password' => 'La contraseña no puede ser tu correo ni tu nombre.']);
        $this->register(['email' => 'maria12345@correo.com', 'password' => 'Maria12345'])->assertSessionHasErrors('password');
        $this->register(['name' => 'Maria 2026', 'password' => 'maria 2026'])->assertSessionHasErrors('password');
        $this->assertSame(0, User::count());
    }

    public function test_recovering_the_password_follows_the_same_policy(): void
    {
        $user = User::factory()->create(['email' => 'ana@correo.com', 'password' => 'clave-vieja-1']);
        $token = Password::createToken($user);

        $this->post(route('password.store'), ['token' => $token, 'email' => 'ana@correo.com', 'password' => 'sololetras'])->assertSessionHasErrors('password');
        $this->post(route('password.store'), ['token' => $token, 'email' => 'ana@correo.com', 'password' => 'ana@correo.com'])->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('clave-vieja-1', $user->fresh()->password));

        $this->post(route('password.store'), ['token' => $token, 'email' => 'ana@correo.com', 'password' => 'clave-nueva-2'])->assertSessionDoesntHaveErrors('password');
        $this->assertTrue(Hash::check('clave-nueva-2', $user->fresh()->password));
    }

    public function test_changing_the_password_from_my_account_follows_the_same_policy(): void
    {
        $user = User::factory()->create(['name' => 'Ana Quispe', 'email' => 'ana@correo.com', 'password' => 'clave-vieja-1']);
        $this->actingAs($user);

        foreach (['sololetras', 'ana@correo.com', 'ana quispe', str_repeat('a1', 40)] as $bad) {
            $this->from(route('client.account'))
                ->put(route('client.account.password'), ['current_password' => 'clave-vieja-1', 'password' => $bad, 'password_confirmation' => $bad])
                ->assertSessionHasErrors('password');
        }
        $this->assertTrue(Hash::check('clave-vieja-1', $user->fresh()->password));

        $this->put(route('client.account.password'), ['current_password' => 'clave-vieja-1', 'password' => 'clave-nueva-2', 'password_confirmation' => 'clave-nueva-2'])
            ->assertSessionDoesntHaveErrors();
        $this->assertTrue(Hash::check('clave-nueva-2', $user->fresh()->password));
    }

    public function test_the_policy_is_written_once(): void
    {
        $this->assertSame(8, PasswordPolicy::MIN);
        $this->assertSame(72, PasswordPolicy::MAX);
        $this->assertArrayHasKey('password.letters', PasswordPolicy::messages());
        $this->assertArrayHasKey('password.numbers', PasswordPolicy::messages());
    }

    public function test_known_domain_typos_get_a_suggestion_and_nothing_else_does(): void
    {
        $this->assertSame('maria@gmail.com', EmailTypos::suggest('maria@gmial.com'));
        $this->assertSame('maria@gmail.com', EmailTypos::suggest(' Maria@GMAIL.CON '));
        $this->assertSame('luis@hotmail.com', EmailTypos::suggest('luis@hotmial.com'));
        $this->assertNull(EmailTypos::suggest('maria@gmail.com'));
        $this->assertNull(EmailTypos::suggest('maria@empresa.com.bo'));
        $this->assertNull(EmailTypos::suggest('sin-arroba'));
        $this->assertNull(EmailTypos::suggest('@gmial.com'));

        // El formulario de registro lleva la lista y pregunta «¿quisiste decir…?» al salir del campo, sin bloquear el envío
        $this->withoutVite()->get(route('register'))->assertOk()
            ->assertSee('gmial.com', false)
            ->assertSee('¿Quisiste decir')
            ->assertSee('@blur="touched = true"', false);
    }

    public function test_the_sign_in_form_remembers_by_default_and_google_is_a_round_button_below_it(): void
    {
        $this->withoutVite()->get(route('login'))->assertOk()
            ->assertSee('name="remember" value="1" aria-label="Mantener la sesión iniciada" checked', false)
            ->assertDontSee('site-google', false);

        config(['services.google.client_id' => 'id-de-prueba', 'services.google.client_secret' => 'secreto']);

        // Solo la «G» en un círculo, con su nombre en el aria-label, después del formulario y no antes
        $this->withoutVite()->get(route('login'))->assertOk()
            ->assertSeeInOrder(['</form>', 'class="site-google"', 'aria-label="Continuar con Google"'], false)
            ->assertDontSee('o con tu correo');
    }
}
