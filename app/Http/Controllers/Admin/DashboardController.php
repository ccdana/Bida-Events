<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\ViewModels\Admin\DashboardViewData;

class DashboardController extends Controller
{
    public function index(DashboardViewData $viewData)
    {
        $invitations = Invitation::with(['eventType', 'guests'])
            ->latest()
            ->get();

        return view('pages.admin.dashboard', $viewData->make($invitations));
    }
}
