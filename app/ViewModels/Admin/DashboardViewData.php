<?php

namespace App\ViewModels\Admin;

use App\Models\Invitation;
use Illuminate\Support\Collection;

class DashboardViewData
{
    public function make(Collection $invitations): array
    {
        $items = $invitations->map(fn (Invitation $invitation) => [
            'invitation' => $invitation,
            'statusClass' => $invitation->status === 'active' ? 'is-active' : 'is-draft',
            'statusLabel' => $invitation->status === 'active' ? 'Activa' : 'Inactiva',
            'guestCount' => $invitation->guests?->count() ?? 0,
            'eventTypeName' => $invitation->eventType?->name ?? 'Sin tipo',
            'eventDateLabel' => $invitation->event_date?->format('d-m-Y H:i') ?? '',
        ])->values();

        $metrics = [
            'total' => $items->count(),
            'active' => $invitations->where('status', 'active')->count(),
            'inactive' => $invitations->where('status', '!=', 'active')->count(),
            'guests' => $items->sum('guestCount'),
        ];

        return compact('items', 'metrics');
    }
}
