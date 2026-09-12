<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Guest\StoreGuestRequest;
use App\Http\Requests\Admin\Guest\UpdateGuestRequest;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\InvitationModuleService;

/**
 * La pertenencia del invitado a la invitación la garantiza scopeBindings() en routes/web.php.
 */
class GuestController extends Controller
{
    public function index(Invitation $invitation)
    {
        $guests = $invitation->guests()->orderBy('name')->get();

        return view('admin.guests.index', compact('invitation', 'guests'));
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

    public function destroy(Invitation $invitation, Guest $guest)
    {
        $guest->delete();

        return back()->with('success', 'Invitado eliminado.');
    }
}
