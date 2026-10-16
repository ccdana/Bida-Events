<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdateEmailRequest;
use App\Http\Requests\Client\UpdatePasswordRequest;
use App\Payments\PaymentGateways;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * La cuenta de cada cliente (y de cada revendedor): sus datos, el correo y el cambio de contraseña.
 * La contraseña que da el equipo es dictable; aquí se cambia por una propia y las demás sesiones
 * abiertas se cierran. Con el correo confirmado puede recuperar la contraseña sin pedirla al equipo.
 */
class AccountController extends Controller
{
    public function edit(PaymentGateways $gateways): View
    {
        $user = auth()->user();

        return view('client.account', [
            'user' => $user,
            // Plan y pagos: los últimos pagos (a mano, tarjeta o QR) y si puede pagar en línea
            'payments' => $user->subscriptionPayments()->latest('paid_at')->latest('id')->limit(6)->get(),
            'payOnline' => (bool) $gateways->availableMethods(),
        ]);
    }

    public function updateEmail(UpdateEmailRequest $request): RedirectResponse
    {
        $user = $request->user();
        $email = $request->validated('email');

        if ($email === $user->email) {
            return back()->with('success', 'Tu correo sigue siendo el mismo.');
        }

        // El correo nuevo queda sin confirmar hasta que abra el enlace que le llega
        $user->forceFill(['email' => $email, 'email_verified_at' => null])->save();
        $user->sendEmailVerificationNotification();

        return back()->with('success', 'Listo: te enviamos un enlace a '.$email.' para confirmarlo.');
    }

    /**
     * Enlace por correo para crear una contraseña sin saber la actual: para quien entró con Google
     * (no tiene una propia) o la olvidó. Solo a un correo ya confirmado.
     */
    public function sendPasswordLink(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (blank($user->email) || ! $user->hasVerifiedEmail()) {
            return back()->withErrors(['password_link' => 'Primero agrega y confirma tu correo.']);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors(['password_link' => 'Ya te enviamos un enlace hace un momento. Revisa tu correo.']);
        }

        return back()->with('success', 'Te enviamos un enlace a '.$user->email.' para crear tu contraseña.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill(['password' => Hash::make($request->validated('password'))])->save();

        // Quien tenga la contraseña anterior queda afuera de sus otros dispositivos
        auth()->logoutOtherDevices($request->validated('password'));

        return back()->with('success', 'Listo: tu contraseña cambió. La próxima vez entra con la nueva.');
    }
}
