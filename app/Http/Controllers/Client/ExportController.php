<?php

namespace App\Http\Controllers\Client;

use App\Exports\GuestsExport;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function guestsExcel(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'phone', 'passes_allocated', 'passes_confirmed', 'status', 'dietary_restrictions', 'table_number', 'confirmed_at')
            ->orderBy('name')
            ->get();

        $stats = $this->buildGuestStats($guests);
        $modulos = $invitation->modulesData->pluck('json_data', 'feature_code')->toArray();

        return Excel::download(
            new GuestsExport($invitation, $stats, $modulos, $guests),
            "confirmados-{$invitation->slug}.xlsx"
        );
    }

    public function guestsPdf(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'phone', 'passes_allocated', 'passes_confirmed', 'status', 'dietary_restrictions', 'table_number', 'confirmed_at')
            ->orderBy('name')
            ->get();

        $stats = $this->buildGuestStats($guests);
        $summary = $this->buildSummaryInsights($guests, $stats);

        $pdf = Pdf::loadView('client.exports.guests-pdf', compact('invitation', 'guests', 'stats', 'summary'));

        return $pdf->download("confirmados-{$invitation->slug}.pdf");
    }

    public function invitationPdf(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $invitation->loadMissing(['modulesData']);
        $modulos = $invitation->modulesData->pluck('json_data', 'feature_code')->toArray();
        $guests = $invitation->guests()->select('id', 'invitation_id', 'status', 'passes_allocated', 'passes_confirmed')->get();
        $stats = $this->buildGuestStats($guests);

        $config = $modulos['config'] ?? [];
        $colors = $config['colores'] ?? [];
        $themeColors = [
            'primary' => $colors['primary'] ?? '#C9A96E',
            'secondary' => $colors['secondary'] ?? '#2C1810',
            'accent' => $colors['accent'] ?? '#F5E6D3',
            'text' => $colors['text'] ?? '#1A1A1A',
            'background' => $colors['background'] ?? '#FFFAF5',
        ];

        $location = $modulos['ubicacion'] ?? [];
        $itinerary = $modulos['itinerario']['eventos'] ?? [];
        $giftData = $modulos['regalos'] ?? [];
        $hashtag = $modulos['hashtag'] ?? [];
        $music = $modulos['musica'] ?? [];
        $rsvp = $modulos['rsvp'] ?? [];
        $dressCode = $modulos['dress_code'] ?? [];
        $destacados = $modulos['destacados'] ?? [];
        $postEvent = $modulos['post_evento'] ?? [];

        $pdf = Pdf::loadView('client.exports.invitation-pdf', compact(
            'invitation',
            'modulos',
            'stats',
            'themeColors',
            'location',
            'itinerary',
            'giftData',
            'hashtag',
            'music',
            'rsvp',
            'dressCode',
            'destacados',
            'postEvent'
        ));

        return $pdf->download("invitacion-{$invitation->slug}.pdf");
    }

    private function buildGuestStats($guests): array
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

    private function buildSummaryInsights($guests, array $stats): array
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
}