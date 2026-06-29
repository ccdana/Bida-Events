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
            'statusClass' => $this->statusClass($invitation->status),
            'guestCount' => $invitation->guests?->count() ?? 0,
            'eventTypeName' => $invitation->eventType?->name ?? 'Sin tipo',
            'eventDateLabel' => $invitation->event_date?->format('d/m/Y H:i') ?? '',
        ])->values();

        $metrics = [
            'total' => $items->count(),
            'active' => $invitations->where('status', 'activo')->count(),
            'draft' => $invitations->where('status', 'borrador')->count(),
            'guests' => $items->sum('guestCount'),
        ];

        return compact('items', 'metrics');
    }

    protected function statusClass(string $status): string
    {
        return match ($status) {
            'activo', 'active' => 'is-active',
            'borrador', 'draft' => 'is-draft',
            'suspended' => 'is-suspended',
            'expired' => 'is-expired',
            default => 'is-draft',
        };
    }
}
