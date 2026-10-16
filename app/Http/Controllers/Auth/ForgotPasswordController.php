<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * «Olvidé mi contraseña»: se pide el correo de la cuenta y llega un enlace para crear una nueva.
 * La respuesta es la misma haya o no una cuenta con ese correo, para no revelar quién tiene cuenta.
 * Las cuentas sin correo (las que arma el equipo) siguen pidiendo la clave nueva al equipo.
 */
class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [
            'email.required' => __('Escribe el correo de tu cuenta.'),
            'email.email' => __('Revisa el correo: parece que le falta algo.'),
        ]);

        // INVALID_USER y RESET_THROTTLED se responden igual que RESET_LINK_SENT
        Password::sendResetLink($request->only('email'));

        return back()
            ->withInput($request->only('email'))
            ->with('status', __('Si hay una cuenta con ese correo, te llegará un enlace para crear una contraseña nueva. Revisa también la carpeta de spam.'));
    }
}
