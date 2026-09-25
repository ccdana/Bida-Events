<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * La cuenta del revendedor: sus datos y el cambio de contraseña. La contraseña que le dio el
 * administrador es dictable; aquí la cambia por una propia. Las demás sesiones abiertas se cierran.
 */
class AccountController extends Controller
{
    public function edit(): View
    {
        return view('client.account', ['user' => auth()->user()]);
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
