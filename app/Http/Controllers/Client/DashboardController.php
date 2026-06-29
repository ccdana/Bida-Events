<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\ViewModels\Client\DashboardViewData as ClientDashboardViewData;
use App\ViewModels\Client\InvitationDetailViewData;

class DashboardController extends Controller
{
    public function index(ClientDashboardViewData $viewData)
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

        return view('pages.client.dashboard', $viewData->make($invitations));
    }

    public function show(Invitation $invitation, InvitationDetailViewData $viewData)
    {
        abort_unless(auth()->id() === $invitation->user_id, 403);

        $invitation->loadMissing(['eventType', 'modulesData']);

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'status', 'passes_allocated', 'passes_confirmed', 'dietary_restrictions')
            ->orderBy('name')
            ->get();

        return view('pages.client.invitation', $viewData->make($invitation, $guests));
    }
}
