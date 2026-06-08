<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

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
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
