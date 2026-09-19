<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\ViewModels\Admin\DashboardViewData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardViewData $viewData)
    {
        // ?tipo=invitation|card filtra por lo que declara el tipo de evento; cualquier otro valor muestra todo
        $kind = in_array($request->query('tipo'), DashboardViewData::KINDS, true) ? $request->query('tipo') : null;

        // Solo la página que se ve, y el número de invitados lo cuenta la base
        $invitations = Invitation::query()
            ->select('id', 'user_id', 'event_type_id', 'slug', 'title', 'event_date', 'status', 'created_at')
            ->with('eventType:id,name,kind')
            ->withCount('guests')
            ->when($kind, fn ($query) => $query->whereHas('eventType', fn ($type) => $type->where('kind', $kind)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.dashboard', $viewData->make($invitations, $kind));
    }
}
