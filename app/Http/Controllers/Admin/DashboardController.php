<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use App\Models\Invitation;
use App\ViewModels\Admin\DashboardViewData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardViewData $viewData)
    {
        // ?tipo=xv-anos|bodas|… es el tipo de evento y ?tipo=card|invitation el producto entero;
        // ?q busca por nombre del evento, enlace o cliente
        $type = trim((string) $request->query('tipo', ''));
        $kind = in_array($type, DashboardViewData::KINDS, true) ? $type : null;
        $search = trim((string) $request->query('q', ''));

        // Un tipo que no existe no esconde nada: se muestra todo, como si no hubiera filtro
        if ($type !== '' && ! $kind && ! EventType::where('slug', $type)->exists()) {
            $type = '';
        }

        // Solo la página que se ve, y los invitados los cuenta la base
        $invitations = Invitation::query()
            ->select('id', 'user_id', 'event_type_id', 'slug', 'template', 'title', 'event_date', 'status', 'expires_at', 'created_at')
            ->with(['eventType:id,name,slug,kind', 'user:id,name,username'])
            ->withCount([
                'guests',
                'guests as confirmed_guests_count' => fn ($query) => $query->where('status', 'confirmed'),
                'guests as pending_guests_count' => fn ($query) => $query->where('status', 'pending'),
                'contributions',
            ])
            ->withSum('guests as confirmed_passes_sum', 'passes_confirmed')
            ->when($kind, fn ($query) => $query->whereHas('eventType', fn ($eventType) => $eventType->where('kind', $kind)))
            ->when($type !== '' && ! $kind, fn ($query) => $query->whereHas('eventType', fn ($eventType) => $eventType->where('slug', $type)))
            ->when($search !== '', fn ($query) => $query->where(fn ($where) => $where
                ->where('title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.dashboard', $viewData->make($invitations, $type, $search));
    }
}
