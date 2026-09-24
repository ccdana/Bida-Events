<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Todos los clientes en un solo lugar: los del equipo (los que se crean desde el editor) y los que
 * crea cada revendedor para sus eventos. También aparece el revendedor que es cliente de una
 * invitación armada por el equipo. Filtra por origen, por revendedor y por nombre o usuario.
 */
class ClientController extends Controller
{
    public const ORIGINS = ['equipo', 'revendedores'];

    public function index(Request $request): View
    {
        $origin = in_array($request->query('origen'), self::ORIGINS, true) ? $request->query('origen') : '';
        $resellerId = (int) $request->query('revendedor') ?: null;
        $search = trim(mb_substr((string) $request->query('q'), 0, 80));

        $clients = $this->clients()
            ->when($origin === 'equipo', fn (Builder $query) => $query->whereNull('created_by_reseller_id'))
            ->when($origin === 'revendedores' || $resellerId, fn (Builder $query) => $query->whereNotNull('created_by_reseller_id'))
            ->when($resellerId, fn (Builder $query) => $query->where('created_by_reseller_id', $resellerId))
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")))
            ->with([
                'createdByReseller:id,name,business_name',
                'invitations' => fn ($query) => $query->select('id', 'user_id', 'reseller_id', 'title', 'slug', 'event_date', 'status')->latest('event_date'),
            ])
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        // Los revendedores con cuántos clientes crearon, para filtrar por cada uno
        $resellers = User::query()
            ->where('is_reseller', true)
            ->withCount('resellerClients')
            ->orderBy('name')
            ->get(['id', 'name', 'business_name']);

        return view('admin.clients.index', [
            'clients' => $clients,
            'resellers' => $resellers,
            'origin' => $origin,
            'resellerId' => $resellerId,
            'search' => $search,
            'counts' => [
                '' => $this->clients()->count(),
                'equipo' => $this->clients()->whereNull('created_by_reseller_id')->count(),
                'revendedores' => $this->clients()->whereNotNull('created_by_reseller_id')->count(),
            ],
        ]);
    }

    /**
     * Quien entra a ver un evento como cliente: cualquier cuenta que no sea del equipo ni revendedor,
     * más el revendedor que tiene a su nombre una invitación armada por el equipo (ahí es cliente).
     */
    private function clients(): Builder
    {
        return User::query()
            ->where('is_admin', false)
            ->where(fn (Builder $query) => $query
                ->where('is_reseller', false)
                ->orWhereHas('invitations', fn (Builder $query) => $query->whereNull('reseller_id')));
    }
}
