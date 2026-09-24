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

        $user = $request->user();

        // Las suyas como cliente y, si es revendedor, las que arma para sus clientes
        $invitations = Invitation::query()
            ->where(fn ($query) => $query->where('user_id', $user->id)->orWhere('reseller_id', $user->id))
            ->select('id', 'user_id', 'reseller_id', 'event_type_id', 'slug', 'template', 'title', 'event_date', 'status', 'expires_at', 'created_at')
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

        return view('client.dashboard', $viewData->make($invitations, $search, $request->user()));
    }

    public function show(Invitation $invitation, InvitationDetailViewData $viewData)
    {
        $invitation->loadMissing('eventType', 'user');

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
