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
            'statusLabel' => $this->statusLabel($invitation->status),
            'statusClass' => $this->statusClass($invitation->status),
        ])->values();

        return [
            'invitations' => $invitations,
            'items' => $items,
        ];
    }

    protected function statusLabel(string $status): string
    {
        return match (strtolower($status)) {
            'draft', 'borrador' => 'Borrador',
            'active', 'activa' => 'Activa',
            'suspended' => 'Suspendida',
            'expired' => 'Expirada',
            default => $status,
        };
    }

    protected function statusClass(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'activa' => 'is-success',
            'draft', 'borrador' => 'is-warning',
            'suspended', 'expired' => 'is-danger',
            default => 'is-primary',
        };
    }
}
