<?php

namespace App\ViewModels\Client;

use App\Models\Invitation;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardViewData
{
    public function make(LengthAwarePaginator $invitations): array
    {
        $items = collect($invitations->items())->map(fn (Invitation $invitation) => [
            'invitation' => $invitation,
            'confirmed' => $invitation->guests->where('status', 'confirmed')->count(),
            'pending' => $invitation->guests->where('status', 'pending')->count(),
            'declined' => $invitation->guests->where('status', 'declined')->count(),
            'statusLabel' => $invitation->status === 'active' ? 'Activa' : 'Inactiva',
            'statusClass' => $invitation->status === 'active' ? 'is-success' : 'is-primary',
        ])->values();

        return [
            'invitations' => $invitations,
            'items' => $items,
        ];
    }
}
