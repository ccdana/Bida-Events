<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsAfterLogin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuthOptions;
use App\Support\ClientCredentials;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * Entrar con Google. La cuenta se busca por la cuenta de Google ya vinculada y, si no, por el
 * correo: solo se vincula si Google confirma que ese correo es de quien entra. Sin cuenta, se crea
 * una (si el registro está abierto) con el correo ya confirmado.
 */
class GoogleController extends Controller
{
    use RedirectsAfterLogin;

    public function redirect(): SymfonyRedirect
    {
        abort_unless(AuthOptions::googleEnabled(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request, ClientCredentials $credentials): RedirectResponse
    {
        abort_unless(AuthOptions::googleEnabled(), 404);

        // Canceló en la pantalla de Google
        if ($request->has('error')) {
            return $this->failed('No se completó el ingreso con Google.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            // Volvió con el botón «atrás» o la sesión venció mientras estaba en Google
            return $this->failed('El ingreso con Google se interrumpió. Inténtalo de nuevo.');
        } catch (Throwable $e) {
            report($e);

            return $this->failed('No pudimos entrar con Google. Inténtalo de nuevo en un momento.');
        }

        $googleId = (string) $google->getId();
        $email = Str::lower(trim((string) $google->getEmail()));

        if ($googleId === '' || $email === '' || ! ($google->user['email_verified'] ?? false)) {
            return $this->failed('Tu cuenta de Google no tiene un correo confirmado. Entra con tu correo y contraseña.');
        }

        $user = User::where('google_id', $googleId)->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user?->google_id) {
                return $this->failed('Esa cuenta ya está vinculada con otra cuenta de Google.');
            }

            if ($user) {
                $this->link($user, $google);
            } elseif (AuthOptions::registrationOpen()) {
                $user = $this->register($google, $email, $credentials);
            } else {
                return $this->failed('No hay ninguna cuenta con ese correo.');
            }
        } elseif ($google->getAvatar() !== $user->avatar_url) {
            $user->forceFill(['avatar_url' => $google->getAvatar()])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return $this->redirectAfterLogin($request, $user);
    }

    /**
     * Vincula Google a una cuenta que ya tenía ese correo. Si el correo nunca se confirmó, la
     * contraseña podría ser de otra persona que lo escribió al registrarse: se reemplaza y el dueño
     * real del correo puede crear otra con «¿Olvidaste tu contraseña?».
     */
    private function link(User $user, SocialiteUser $google): void
    {
        $user->forceFill([
            'google_id' => (string) $google->getId(),
            'avatar_url' => $google->getAvatar(),
        ]);

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill([
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(40)),
                'remember_token' => Str::random(60),
            ]);
        }

        $user->save();
    }

    private function register(SocialiteUser $google, string $email, ClientCredentials $credentials): User
    {
        $name = trim((string) $google->getName()) ?: Str::before($email, '@');

        $user = (new User)->forceFill([
            'name' => Str::limit($name, 120, ''),
            'username' => $credentials->username($name),
            'email' => $email,
            'email_verified_at' => now(),
            // Sin contraseña propia: si algún día la quiere, la crea con «¿Olvidaste tu contraseña?»
            'password' => Hash::make(Str::random(40)),
            'google_id' => (string) $google->getId(),
            'avatar_url' => $google->getAvatar(),
        ]);
        $user->save();

        event(new Registered($user));

        return $user;
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['username' => $message]);
    }
}
