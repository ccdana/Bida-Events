<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreGuestRequest;
use App\Models\Guest;
use App\Models\Invitation;

/**
 * El cliente arma su propia lista de invitados. Puede agregar y, mientras nadie haya respondido,
 * quitar; lo demás (mesas, pases confirmados) lo mueve el equipo desde el panel.
 *
 * La pertenencia del invitado a la invitación la garantiza scopeBindings() en routes/web.php y
 * el permiso, la policy (manageOwnGuests).
 */
class GuestController extends Controller
{
    public function store(StoreGuestRequest $request, Invitation $invitation)
    {
        $guest = $invitation->guests()->create($request->validated());

        // El enlace personal se muestra al volver: es lo que el cliente va a enviar por WhatsApp
        return back()->with('guest', [
            'name' => $guest->name,
            'link' => route('invitation.guest', [$invitation->slug, $guest->qr_code_token]),
        ]);
    }

    public function destroy(Invitation $invitation, Guest $guest)
    {
        abort_unless($guest->status === 'pending', 403, 'Este invitado ya respondió: escríbenos para cambiarlo.');

        $guest->delete();

        return back()->with('success', $guest->name.' ya no está en tu lista.');
    }
}
