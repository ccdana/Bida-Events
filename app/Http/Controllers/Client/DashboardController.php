<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Modules\Card\ReplyModule;
use App\ViewModels\Client\DashboardViewData as ClientDashboardViewData;
use App\ViewModels\Client\InvitationDetailViewData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, ClientDashboardViewData $viewData)
    {
        $search = trim((string) $request->query('q', ''));

        $invitations = auth()->user()
            ->invitations()
            ->select('id', 'user_id', 'event_type_id', 'slug', 'template', 'title', 'event_date', 'status', 'expires_at', 'created_at')
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')))
            ->with([
                'eventType:id,name,slug',
                'guests:id,invitation_id,status,passes_confirmed',
            ])
            ->withCount([
                'contributions',
                // Las tarjetas se miden por las respuestas que dejó quien las recibió
                'contributions as replies_count' => fn ($query) => $query->where('type', ReplyModule::CONTRIBUTION_TYPE),
            ])
            ->latest('event_date')
            ->get();

        return view('client.dashboard', $viewData->make($invitations, $search));
    }

    public function show(Invitation $invitation, InvitationDetailViewData $viewData)
    {
        $invitation->loadMissing('eventType');

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'phone', 'status', 'passes_allocated', 'passes_confirmed', 'dietary_restrictions', 'table_number', 'qr_code_token')
            ->orderBy('name')
            ->get();

        $contributions = $invitation->contributions()
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(60)
            ->get();

        return view('client.invitation', $viewData->make($invitation, $guests, $contributions));
    }
}
