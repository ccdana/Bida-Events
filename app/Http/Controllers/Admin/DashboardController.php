<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Support\InvitationFilters;
use App\ViewModels\Admin\DashboardViewData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardViewData $viewData)
    {
        // Filtros de la barra lateral (?q, ?tipo, ?estado, ?origen, ?orden): App\Support\InvitationFilters
        $filters = InvitationFilters::fromRequest($request);

        // Solo la página que se ve, y los invitados los cuenta la base
        $invitations = InvitationFilters::apply(Invitation::query(), $filters)
            ->select('id', 'user_id', 'reseller_id', 'event_type_id', 'slug', 'template', 'package', 'title', 'event_date', 'status', 'expires_at', 'created_at')
            ->with(['eventType:id,name,slug,kind', 'user:id,name,username'])
            ->withCount([
                'guests',
                'guests as confirmed_guests_count' => fn ($query) => $query->where('status', 'confirmed'),
                'guests as pending_guests_count' => fn ($query) => $query->where('status', 'pending'),
                'contributions',
            ])
            ->withSum('guests as confirmed_passes_sum', 'passes_confirmed')
            ->paginate(20)
            ->withQueryString();

        return view('admin.dashboard', $viewData->make($invitations, $filters['tipo'], $filters['q']) + [
            'filterValues' => $filters,
            'filterGroups' => InvitationFilters::sidebar(Invitation::query(), $filters, 'admin.dashboard'),
            'isFiltered' => InvitationFilters::isFiltered($filters),
        ]);
    }
}
