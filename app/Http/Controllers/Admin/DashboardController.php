<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Support\InvitationFilters;
use App\Support\ShowcaseDemos;
use App\ViewModels\Admin\DashboardViewData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Las invitaciones del panel, en dos listas que no se mezclan: las de los clientes y las de muestra
 * (las que el sitio enseña para probar: portada, temporadas, «Hazlo tú» y páginas por evento).
 */
class DashboardController extends Controller
{
    public function index(Request $request, DashboardViewData $viewData)
    {
        return $this->listing($request, $viewData, showcase: false);
    }

    public function showcase(Request $request, DashboardViewData $viewData)
    {
        return $this->listing($request, $viewData, showcase: true);
    }

    private function listing(Request $request, DashboardViewData $viewData, bool $showcase)
    {
        $route = $showcase ? 'admin.showcase' : 'admin.dashboard';
        $demoSlugs = ShowcaseDemos::slugs();
        $scope = fn (): Builder => Invitation::query()->when(
            $showcase,
            fn (Builder $query) => $query->whereIn('slug', $demoSlugs),
            fn (Builder $query) => $query->whereNotIn('slug', $demoSlugs),
        );

        // Filtros de la barra lateral (?q, ?tipo, ?estado, ?origen, ?orden): App\Support\InvitationFilters
        $filters = InvitationFilters::fromRequest($request);

        // Solo la página que se ve, y los invitados los cuenta la base
        $invitations = InvitationFilters::apply($scope(), $filters)
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

        return view('admin.dashboard', $viewData->make($invitations, $filters['tipo'], $filters['q'], $scope()) + [
            'filterValues' => $filters,
            'filterGroups' => InvitationFilters::sidebar($scope(), $filters, $route),
            'filterRoute' => $route,
            'isFiltered' => InvitationFilters::isFiltered($filters),
            'isShowcase' => $showcase,
            'listRoute' => $route,
        ]);
    }
}
