<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Invitation $invitation)
    {
        $guests = $invitation->guests()->orderBy('name')->get();

        return view('admin.guests.index', compact('invitation', 'guests'));
    }

    public function store(Request $request, Invitation $invitation)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'passes_allocated' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $invitation->guests()->create([
            ...$validated,
            'qr_code_token' => InvitationModuleService::generateGuestToken(),
        ]);

        return back()->with('success', 'Invitado agregado.');
    }

    public function update(Request $request, Invitation $invitation, Guest $guest)
    {
        abort_unless($guest->invitation_id === $invitation->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'passes_allocated' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $guest->update($validated);

        return back()->with('success', 'Invitado actualizado.');
    }

    public function destroy(Invitation $invitation, Guest $guest)
    {
        abort_unless($guest->invitation_id === $invitation->id, 404);
        $guest->delete();

        return back()->with('success', 'Invitado eliminado.');
    }
}
