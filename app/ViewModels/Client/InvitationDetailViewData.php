<?php

namespace App\ViewModels\Client;

use App\Models\GuestContribution;
use App\Models\Invitation;
use App\Modules\Card\ReplyModule;
use App\Support\CloudinaryImage;
use Illuminate\Support\Collection;

class InvitationDetailViewData
{
    public function make(Invitation $invitation, Collection $guests, ?Collection $contributionRows = null): array
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
            'passesLabel' => $guest->passes_confirmed.'/'.$guest->passes_allocated,
            'dietaryRestrictions' => $guest->dietary_restrictions ?: 'Sin indicar',
        ])->values();

        // Fotos y canciones que el cliente puede ocultar de su invitación
        $contributions = ($contributionRows ?? new Collection)->map(fn (GuestContribution $contribution) => [
            'id' => $contribution->id,
            'isPhoto' => $contribution->type === 'live_photo',
            // Respuesta del destinatario de una tarjeta: se lee completa
            'isReply' => $contribution->type === ReplyModule::CONTRIBUTION_TYPE,
            'url' => $contribution->type === 'live_photo' ? CloudinaryImage::url($contribution->file_path, 200) : null,
            'text' => $contribution->type === 'live_photo' ? 'Foto del fotomural' : (string) $contribution->content_text,
            'meta' => collect([$contribution->guest?->name, $contribution->created_at?->diffForHumans()])->filter()->implode(' · '),
            'isHidden' => $contribution->moderation_status === GuestContribution::HIDDEN,
        ])->values();

        return compact(
            'invitation',
            'guests',
            'contributions',
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
