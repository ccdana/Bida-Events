<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * El enlace de puerta de una invitación: lo crea el organizador (o su revendedor) desde el panel y
 * lo comparte con quien controla la entrada. Crear uno nuevo deja sin acceso a los teléfonos que
 * tenían el anterior; quitarlo cierra la puerta para todos. Ver Public\DoorController.
 */
class DoorAccessController extends Controller
{
    public function store(Invitation $invitation): RedirectResponse
    {
        $renewing = $invitation->door_token !== null;

        $invitation->forceFill(['door_token' => Str::random(48)])->saveQuietly();

        return back()->with('success', $renewing
            ? 'Enlace de puerta nuevo: el anterior ya no funciona.'
            : 'Listo: comparte el enlace de puerta con quien controle la entrada.');
    }

    public function destroy(Invitation $invitation): RedirectResponse
    {
        $invitation->forceFill(['door_token' => null])->saveQuietly();

        return back()->with('success', 'Se cerró el control de entrada: el enlace de puerta ya no funciona.');
    }
}
