<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $invitations = Invitation::with(['eventType', 'guests'])
            ->latest()
            ->get();

        return view('admin.dashboard', compact('invitations'));
    }
}
