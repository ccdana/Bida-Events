<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\ViewModels\Admin\DashboardViewData;

class DashboardController extends Controller
{
    public function index(DashboardViewData $viewData)
    {
        // Solo la página que se ve, y el número de invitados lo cuenta la base
        $invitations = Invitation::query()
            ->select('id', 'user_id', 'event_type_id', 'slug', 'title', 'event_date', 'status', 'created_at')
            ->with('eventType:id,name')
            ->withCount('guests')
            ->latest()
            ->paginate(20);

        return view('admin.dashboard', $viewData->make($invitations));
    }
}
