<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Auth\Concerns\RedirectsAfterLogin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use RedirectsAfterLogin;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Escribe tu usuario o tu correo.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        // Un mismo campo para el usuario que da el equipo y para el correo de las cuentas del sitio.
        // Los dos se guardan en minúsculas: "Maria.Valenzuela" también funciona
        $login = Str::lower(trim((string) $request->input('username')));
        $credentials = [
            str_contains($login, '@') ? 'email' : 'username' => $login,
            'password' => (string) $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectAfterLogin($request, Auth::user());
        }

        return back()->withErrors([
            'username' => 'El usuario, el correo o la contraseña no son correctos.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
