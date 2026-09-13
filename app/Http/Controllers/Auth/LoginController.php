<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Escribe tu usuario.',
            'password.required' => 'Escribe tu contraseña.',
        ]);

        // Los usuarios se guardan en minúsculas: "Maria.Valenzuela" también funciona
        $credentials = [
            'username' => Str::lower(trim((string) $request->input('username'))),
            'password' => (string) $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            $intended = $request->session()->get('url.intended');
            if (is_string($intended)) {
                $path = parse_url($intended, PHP_URL_PATH) ?: '/';

                if (in_array($path, ['/', '/login', '/client/login', '/dashboard'], true)) {
                    $request->session()->forget('url.intended');
                    $intended = null;
                }
            }

            if ($user->isAdmin()) {
                return $intended
                    ? redirect()->intended(route('admin.dashboard'))
                    : redirect()->route('admin.dashboard');
            }

            return $intended
                ? redirect()->intended(route('client.dashboard'))
                : redirect()->route('client.dashboard');
        }

        return back()->withErrors([
            'username' => 'El usuario o la contraseña no son correctos.',
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
