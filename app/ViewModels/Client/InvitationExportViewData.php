<?php

namespace App\ViewModels\Client;

use App\Models\Invitation;
use Illuminate\Support\Collection;

class InvitationExportViewData
{
    public function buildGuestStats(Collection $guests): array
    {
        $totalGuests = $guests->count();
        $confirmedGuests = $guests->where('status', 'confirmed')->count();
        $pendingGuests = $guests->where('status', 'pending')->count();
        $declinedGuests = $guests->where('status', 'declined')->count();
        $allocatedPasses = (int) $guests->sum('passes_allocated');
        $confirmedPasses = (int) $guests->sum('passes_confirmed');
        $remainingPasses = max(0, $allocatedPasses - $confirmedPasses);
        $confirmationRate = $allocatedPasses > 0
            ? round(($confirmedPasses / $allocatedPasses) * 100, 1)
            : 0;

        return [
            'totalGuests' => $totalGuests,
            'confirmedGuests' => $confirmedGuests,
            'pendingGuests' => $pendingGuests,
            'declinedGuests' => $declinedGuests,
            'allocatedPasses' => $allocatedPasses,
            'confirmedPasses' => $confirmedPasses,
            'remainingPasses' => $remainingPasses,
            'confirmationRate' => $confirmationRate,
        ];
    }

    public function buildSummaryInsights(Collection $guests, array $stats): array
    {
        $maxPassesGuest = $guests->sortByDesc('passes_allocated')->first();
        $mostConfirmedGuest = $guests->sortByDesc('passes_confirmed')->first();
        $confirmedRatio = $stats['allocatedPasses'] > 0
            ? round(($stats['confirmedPasses'] / max(1, $stats['allocatedPasses'])) * 100, 1)
            : 0;

        return [
            'maxPassesGuest' => $maxPassesGuest?->name,
            'maxPassesValue' => (int) ($maxPassesGuest?->passes_allocated ?? 0),
            'mostConfirmedGuest' => $mostConfirmedGuest?->name,
            'mostConfirmedValue' => (int) ($mostConfirmedGuest?->passes_confirmed ?? 0),
            'confirmedRatio' => $confirmedRatio,
        ];
    }

    public function buildGuestRows(Collection $guests): array
    {
        return $guests->map(function ($guest) {
            $allocated = (int) $guest->passes_allocated;
            $confirmed = (int) $guest->passes_confirmed;
            $remaining = max(0, $allocated - $confirmed);
            $coverage = $allocated > 0 ? round(($confirmed / $allocated) * 100, 1) : 0;

            return [
                'guest' => $guest,
                'allocated' => $allocated,
                'confirmed' => $confirmed,
                'remaining' => $remaining,
                'coverage' => $coverage,
                'statusLabel' => match ($guest->status) {
                    'confirmed' => 'Confirmado',
                    'declined' => 'No asistirá',
                    default => 'Pendiente',
                },
            ];
        })->values()->all();
    }

    public function buildThemeColors(array $modulos): array
    {
        $config = $modulos['config'] ?? [];
        $colors = $config['colores'] ?? [];

        return [
            'primary' => $colors['primary'] ?? '#C9A96E',
            'secondary' => $colors['secondary'] ?? '#2C1810',
            'accent' => $colors['accent'] ?? '#F5E6D3',
            'text' => $colors['text'] ?? '#1A1A1A',
            'background' => $colors['background'] ?? '#FFFAF5',
        ];
    }

    public function buildInvitationPdfViewData(Invitation $invitation, array $modulos, array $stats): array
    {
        return [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'stats' => $stats,
            'themeColors' => $this->buildThemeColors($modulos),
            'location' => $modulos['ubicacion'] ?? [],
            'itinerary' => $modulos['itinerario']['eventos'] ?? [],
            'giftData' => $modulos['regalos'] ?? [],
            'hashtag' => $modulos['hashtag'] ?? [],
            'music' => $modulos['musica'] ?? [],
            'rsvp' => $modulos['rsvp'] ?? [],
            'dressCode' => $modulos['dress_code'] ?? [],
            'destacados' => $modulos['destacados'] ?? [],
            'postEvent' => $modulos['post_evento'] ?? [],
        ];
    }
}
