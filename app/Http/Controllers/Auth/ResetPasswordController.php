<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/**
 * La contraseña nueva, desde el enlace del correo (vale 60 minutos y una sola vez). Abrir ese enlace
 * prueba que el correo es de quien lo pidió, así que el correo queda confirmado. Todas las sesiones
 * abiertas se cierran (también la de quien lo pidió desde «Mi cuenta») y se entra con la nueva.
 */
class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', PasswordRule::min(8)],
        ], [
            'email.required' => __('Escribe el correo de tu cuenta.'),
            'email.email' => __('Revisa el correo: parece que le falta algo.'),
            'password.required' => __('Elige una contraseña nueva.'),
            'password.min' => __('La contraseña necesita al menos 8 caracteres.'),
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                // Quien entró con la contraseña anterior queda afuera (las sesiones se guardan en la base)
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                }

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('El enlace venció o ya se usó. Pide uno nuevo desde «¿Olvidaste tu contraseña?».')]);
        }

        // Si lo pidió desde «Mi cuenta», también sale de esta sesión y entra con la contraseña nueva
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->to(lroute('login'))
            ->withInput(['username' => $request->input('email')])
            ->with('status', __('Tu contraseña cambió. Entra con tu correo y la contraseña nueva.'));
    }
}
