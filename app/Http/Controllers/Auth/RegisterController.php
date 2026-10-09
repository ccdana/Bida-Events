<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\AuthOptions;
use App\Support\ClientCredentials;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Cuenta nueva desde el sitio, con correo y contraseña. Queda como cliente sin plan: entra a su
 * panel enseguida y recibe un correo para confirmar la dirección (la compra lo exige).
 */
class RegisterController extends Controller
{
    public function create(): View
    {
        abort_unless(AuthOptions::registrationOpen(), 404);

        return view('auth.register');
    }

    public function store(RegisterRequest $request, ClientCredentials $credentials): RedirectResponse
    {
        abort_unless(AuthOptions::registrationOpen(), 404);

        $user = User::create([
            'name' => $request->validated('name'),
            // El usuario de siempre sigue existiendo (maria.valenzuela), aunque entre con el correo
            'username' => $credentials->username($request->validated('name')),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        // Envía el correo de verificación (Illuminate\Auth\Listeners\SendEmailVerificationNotification)
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
