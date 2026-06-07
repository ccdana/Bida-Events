<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;

class DashboardController extends Controller
{
    public function index()
    {
        $invitations = auth()->user()
            ->invitations()
            ->with(['guests', 'eventType'])
            ->latest()
            ->get();

        return view('client.dashboard', compact('invitations'));
    }

    public function show(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $guests = $invitation->guests()->orderBy('name')->get();
        $confirmed = $guests->where('status', 'confirmed');
        $declined = $guests->where('status', 'declined');
        $pending = $guests->where('status', 'pending');
        $totalPasses = $confirmed->sum('passes_confirmed');

        return view('client.invitation', compact(
            'invitation',
            'guests',
            'confirmed',
            'declined',
            'pending',
            'totalPasses'
        ));
    }
}
