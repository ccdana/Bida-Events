<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Guest\StoreGuestRequest;
use App\Http\Requests\Admin\Guest\UpdateGuestRequest;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use Illuminate\Http\Request;

/**
 * La pertenencia del invitado a la invitación la garantiza scopeBindings() en routes/web.php.
 */
class GuestController extends Controller
{
    public function index(Request $request, Invitation $invitation)
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('estado', '');

        $guests = $invitation->guests()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when(in_array($status, ['confirmed', 'declined', 'pending'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.guests.index', compact('invitation', 'guests', 'search', 'status'));
    }

    public function store(StoreGuestRequest $request, Invitation $invitation)
    {
        $validated = $request->validated();

        $invitation->guests()->create([
            ...$validated,
            'qr_code_token' => InvitationModuleService::generateGuestToken(),
        ]);

        return back()->with('success', 'Invitado agregado.');
    }

    public function update(UpdateGuestRequest $request, Invitation $invitation, Guest $guest)
    {
        $guest->update($request->validated());

        return back()->with('success', 'Invitado actualizado.');
    }

    /**
     * Cambia el enlace personal de un invitado. Sirve cuando el enlace se envió a quien no era
     * o terminó en un grupo: el anterior deja de funcionar al instante.
     */
    public function regenerateToken(Invitation $invitation, Guest $guest)
    {
        $guest->update(['qr_code_token' => InvitationModuleService::generateGuestToken()]);

        return back()->with('success', 'Listo, el enlace anterior de '.$guest->name.' ya no funciona.');
    }

    public function destroy(Invitation $invitation, Guest $guest)
    {
        $guest->delete();

        return back()->with('success', 'Invitado eliminado.');
    }
}
