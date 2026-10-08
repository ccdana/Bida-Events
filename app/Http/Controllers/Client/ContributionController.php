<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Services\InvitationCacheService;
use Illuminate\Http\Request;

/**
 * Moderación de lo que suben los invitados. Ocultar no borra: la foto o la canción quedan
 * guardadas y solo dejan de mostrarse en la invitación.
 *
 * La pertenencia del aporte a la invitación la garantiza scopeBindings() en routes/web.php.
 */
class ContributionController extends Controller
{
    public function update(Request $request, Invitation $invitation, GuestContribution $contribution)
    {
        $validated = $request->validate([
            'moderation_status' => ['required', 'in:'.GuestContribution::VISIBLE.','.GuestContribution::HIDDEN],
        ]);

        $contribution->update(['moderation_status' => $validated['moderation_status']]);

        if ($contribution->type === 'song_request') {
            InvitationCacheService::forgetPlaylist($invitation->id);
        } elseif ($contribution->type === 'live_photo') {
            // Las respuestas de tarjeta no están en ninguna caché pública
            InvitationCacheService::forgetFotomural($invitation->id);
        }

        $hidden = $validated['moderation_status'] === GuestContribution::HIDDEN;
        $message = $hidden
            ? 'Listo, ya no se muestra en la invitación.'
            : 'Listo, vuelve a mostrarse en la invitación.';

        // El panel lo cambia sin recargar la página
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'hidden' => $hidden, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
