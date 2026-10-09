<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Confirmar el correo: el aviso «revisa tu bandeja», el enlace que llega por correo (firmado y con
 * vencimiento) y el reenvío. Una cuenta sin correo (las que arma el equipo) no tiene nada que confirmar.
 */
class VerifyEmailController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail() || blank($user->email)) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-email', ['email' => $user->email]);
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        // Marca el correo como verificado y dispara el evento Verified (si ya lo estaba, no hace nada)
        $request->fulfill();

        // Directo al panel: pasar por /dashboard (otra redirección) gastaría el aviso antes de mostrarlo
        $panel = $request->user()->isAdmin() ? 'admin.dashboard' : 'client.dashboard';

        return redirect()->route($panel)->with('success', 'Listo: tu correo quedó confirmado.');
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail() || blank($user->email)) {
            return redirect()->route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Te enviamos otro enlace a '.$user->email.'.');
    }
}
