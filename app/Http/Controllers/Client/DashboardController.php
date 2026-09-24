<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Modules\Card\ReplyModule;
use App\Support\InvitationFilters;
use App\ViewModels\Client\DashboardViewData as ClientDashboardViewData;
use App\ViewModels\Client\InvitationDetailViewData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, ClientDashboardViewData $viewData)
    {
        $user = $request->user();
        // Filtros de la barra lateral del revendedor (sin «origen»: todo lo suyo lo armó él)
        $filters = InvitationFilters::fromRequest($request, withOrigin: false);
        $search = $filters['q'];

        // Las suyas como cliente y, si es revendedor, las que arma para sus clientes
        $scope = fn () => Invitation::query()->where(fn ($query) => $query->where('user_id', $user->id)->orWhere('reseller_id', $user->id));

        $invitations = InvitationFilters::apply($scope(), $filters)
            ->when($filters['orden'] === '', fn ($query) => $query->reorder()->latest('event_date'))
            ->select('id', 'user_id', 'reseller_id', 'event_type_id', 'slug', 'template', 'package', 'title', 'event_date', 'status', 'expires_at', 'created_at')
            ->with([
                'eventType:id,name,slug',
                'guests:id,invitation_id,status,passes_confirmed',
            ])
            ->withCount([
                'contributions',
                // Las tarjetas se miden por las respuestas que dejó quien las recibió
                'contributions as replies_count' => fn ($query) => $query->where('type', ReplyModule::CONTRIBUTION_TYPE),
            ])
            ->get();

        $data = $viewData->make($invitations, $search, $user);

        // El revendedor maneja muchos eventos: filtra desde la barra lateral
        if ($user->isReseller()) {
            $data += [
                'filterValues' => $filters,
                'filterGroups' => InvitationFilters::sidebar($scope(), $filters, 'client.dashboard', withOrigin: false),
                'isFiltered' => InvitationFilters::isFiltered($filters),
            ];
        }

        return view('client.dashboard', $data);
    }

    public function show(Invitation $invitation, InvitationDetailViewData $viewData)
    {
        $invitation->loadMissing('eventType', 'user');

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'phone', 'status', 'passes_allocated', 'passes_confirmed', 'dietary_restrictions', 'table_number', 'qr_code_token', 'checked_in_passes', 'checked_in_at')
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
