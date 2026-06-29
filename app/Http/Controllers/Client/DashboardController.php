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
            ->select('id', 'user_id', 'event_type_id', 'slug', 'title', 'event_date', 'status', 'created_at')
            ->with([
                'eventType:id,name,slug',
                'guests:id,invitation_id,status',
            ])
            ->withCount(['guests', 'contributions'])
            ->latest()
            ->paginate(15);

        return view('client.dashboard', compact('invitations'));
    }

    public function show(Invitation $invitation)
    {
        abort_unless(auth()->id() === $invitation->user_id, 403);

        $invitation->loadMissing(['eventType', 'modulesData']);

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'status', 'passes_allocated', 'passes_confirmed', 'dietary_restrictions')
            ->orderBy('name')
            ->get();

        $confirmed = $guests->where('status', 'confirmed');
        $declined = $guests->where('status', 'declined');
        $pending = $guests->where('status', 'pending');
        $totalPasses = $guests->sum('passes_confirmed');
        $totalAllocatedPasses = $guests->sum('passes_allocated');
        $confirmationRate = $totalAllocatedPasses > 0
            ? round(($totalPasses / $totalAllocatedPasses) * 100, 1)
            : 0;

        return view('client.invitation', compact(
            'invitation',
            'guests',
            'confirmed',
            'declined',
            'pending',
            'totalPasses',
            'totalAllocatedPasses',
            'confirmationRate'
        ));
    }
}
