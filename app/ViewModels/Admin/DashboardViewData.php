<?php

namespace App\ViewModels\Admin;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DashboardViewData
{
    public function make(LengthAwarePaginator $invitations): array
    {
        $items = collect($invitations->items())->map(fn (Invitation $invitation) => [
            'invitation' => $invitation,
            'statusClass' => $invitation->status === 'active' ? 'is-active' : 'is-draft',
            'statusLabel' => $invitation->status === 'active' ? 'Activa' : 'Inactiva',
            'guestCount' => (int) ($invitation->guests_count ?? 0),
            'eventTypeName' => $invitation->eventType?->name ?? 'Sin tipo',
            'eventDateLabel' => $invitation->event_date?->format('d-m-Y H:i') ?? '',
        ])->values();

        // Las cifras son de todas las invitaciones, no solo de la página que se muestra
        $active = Invitation::where('status', 'active')->count();

        $metrics = [
            'total' => $invitations->total(),
            'active' => $active,
            'inactive' => $invitations->total() - $active,
            'guests' => Guest::count(),
        ];

        return compact('items', 'metrics', 'invitations');
    }
}
