<?php

namespace App\ViewModels\Client;

use App\Models\Invitation;
use Illuminate\Support\Collection;

class InvitationDetailViewData
{
    public function make(Invitation $invitation, Collection $guests): array
    {
        $confirmed = $guests->where('status', 'confirmed');
        $declined = $guests->where('status', 'declined');
        $pending = $guests->where('status', 'pending');
        $totalPasses = (int) $guests->sum('passes_confirmed');
        $totalAllocatedPasses = (int) $guests->sum('passes_allocated');
        $confirmationRate = $totalAllocatedPasses > 0
            ? round(($totalPasses / $totalAllocatedPasses) * 100, 1)
            : 0;

        $rows = $guests->map(fn ($guest) => [
            'guest' => $guest,
            'statusLabel' => match ($guest->status) {
                'confirmed' => 'Confirmado',
                'declined' => 'No asiste',
                'pending' => 'Pendiente',
                default => ucfirst((string) $guest->status),
            },
            'statusClass' => match ($guest->status) {
                'confirmed' => 'is-confirmed',
                'declined' => 'is-declined',
                default => 'is-pending',
            },
            'passesLabel' => $guest->passes_confirmed . '/' . $guest->passes_allocated,
            'dietaryRestrictions' => $guest->dietary_restrictions ?: 'Sin indicar',
        ])->values();

        return compact(
            'invitation',
            'guests',
            'confirmed',
            'declined',
            'pending',
            'totalPasses',
            'totalAllocatedPasses',
            'confirmationRate',
            'rows'
        );
    }
}
