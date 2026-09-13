<?php

namespace App\ViewModels\Client;

use App\Models\Guest;
use App\Models\Invitation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Datos del reporte de invitados (Excel y PDF): cifras para planificar, grupos
 * por estado, mesas, restricciones alimentarias y qué hacer a continuación.
 */
class GuestReportData
{
    public function make(Invitation $invitation, Collection $guests): array
    {
        $rows = $guests->map(fn (Guest $guest) => $this->row($invitation, $guest))
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $confirmed = $rows->where('status', 'confirmed')->values();
        $declined = $rows->where('status', 'declined')->values();
        $pending = $rows->where('status', 'pending')
            ->sortBy([['allocated', 'desc'], ['name', 'asc']])
            ->values();

        $stats = [
            'totalGuests' => $rows->count(),
            'confirmedGuests' => $confirmed->count(),
            'pendingGuests' => $pending->count(),
            'declinedGuests' => $declined->count(),
            'respondedGuests' => $confirmed->count() + $declined->count(),
            'allocatedPasses' => (int) $rows->sum('allocated'),
            'confirmedPeople' => (int) $confirmed->sum('confirmed'),
            'pendingPeople' => (int) $pending->sum('allocated'),
            'releasedPasses' => (int) $rows->sum('free'),
        ];
        $stats['maxPeople'] = $stats['confirmedPeople'] + $stats['pendingPeople'];
        $stats['responseRate'] = $this->percent($stats['respondedGuests'], $stats['totalGuests']);
        $stats['attendanceRate'] = $this->percent($stats['confirmedPeople'], $stats['allocatedPasses']);

        $dietary = $confirmed->filter(fn (array $row) => $row['dietary'] !== null)->values();

        $tables = $confirmed->filter(fn (array $row) => $row['table'] !== null)
            ->groupBy('table')
            ->map(fn (Collection $items, $table) => [
                'table' => (string) $table,
                'guests' => $items->count(),
                'people' => (int) $items->sum('confirmed'),
            ])
            ->sortKeys(SORT_NATURAL)
            ->values();

        $unassigned = $tables->isNotEmpty() ? $confirmed->whereNull('table')->count() : 0;
        $eventDate = $invitation->event_date;
        $daysLeft = $eventDate ? (int) now()->startOfDay()->diffInDays($eventDate->copy()->startOfDay(), false) : null;

        return [
            'invitation' => $invitation,
            'eventDateLabel' => $eventDate ? Str::ucfirst($eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y, H:i')) : '',
            'generatedAt' => now()->locale('es')->translatedFormat('j \d\e F \d\e Y, H:i'),
            'daysLeft' => $daysLeft,
            'stats' => $stats,
            'rows' => $rows,
            'groups' => compact('confirmed', 'pending', 'declined'),
            'dietary' => $dietary,
            'tables' => $tables,
            'unassignedConfirmed' => $unassigned,
            'actions' => $this->actions($stats, $daysLeft, $dietary->count(), $unassigned),
        ];
    }

    private function row(Invitation $invitation, Guest $guest): array
    {
        $status = in_array($guest->status, ['confirmed', 'declined'], true) ? $guest->status : 'pending';
        $allocated = (int) $guest->passes_allocated;
        $confirmed = $status === 'confirmed' ? (int) $guest->passes_confirmed : 0;

        // Los celulares bolivianos de 8 dígitos se completan con el código de país para WhatsApp
        $phoneDigits = preg_replace('/\D+/', '', (string) $guest->phone);
        if (strlen($phoneDigits) === 8) {
            $phoneDigits = "591{$phoneDigits}";
        }

        return [
            'name' => (string) $guest->name,
            'phone' => filled($guest->phone) ? (string) $guest->phone : null,
            'status' => $status,
            'statusLabel' => ['confirmed' => 'Confirmado', 'declined' => 'No asistirá', 'pending' => 'Pendiente'][$status],
            'allocated' => $allocated,
            'confirmed' => $confirmed,
            'free' => match ($status) {
                'confirmed' => max(0, $allocated - $confirmed),
                'declined' => $allocated,
                default => 0,
            },
            'table' => filled($guest->table_number) ? (string) $guest->table_number : null,
            'dietary' => filled($guest->dietary_restrictions) ? trim((string) $guest->dietary_restrictions) : null,
            'confirmedAt' => $status === 'confirmed' ? $guest->confirmed_at : null,
            'link' => route('invitation.guest', [$invitation->slug, $guest->qr_code_token]),
            'whatsapp' => strlen($phoneDigits) >= 8 ? "https://wa.me/{$phoneDigits}" : null,
        ];
    }

    private function actions(array $stats, ?int $daysLeft, int $dietaryCount, int $unassigned): array
    {
        if ($stats['totalGuests'] === 0) {
            return ['Todavía no hay invitados registrados. Cuando se agreguen, este reporte mostrará sus respuestas.'];
        }

        $actions = [];

        if ($stats['pendingGuests'] > 0) {
            $when = match (true) {
                $daysLeft === null || $daysLeft < 0 => '',
                $daysLeft === 0 => ' El evento es hoy.',
                default => ' Faltan '.$this->count($daysLeft, 'día', 'días').' para el evento.',
            };

            $actions[] = 'Contacta a '.$this->count($stats['pendingGuests'], 'invitado que no respondió', 'invitados que no respondieron')
                .': representan hasta '.$this->count($stats['pendingPeople'], 'persona', 'personas').' más.'.$when;
        }

        $actions[] = $stats['pendingPeople'] > 0
            ? "Para comida, sillas y recuerdos, planifica entre {$stats['confirmedPeople']} y {$stats['maxPeople']} personas."
            : 'Todos respondieron: planifica comida, sillas y recuerdos para '.$this->count($stats['confirmedPeople'], 'persona', 'personas').'.';

        if ($stats['releasedPasses'] > 0) {
            $actions[] = 'Hay '.$this->count($stats['releasedPasses'], 'pase libre', 'pases libres').' de invitados que confirmaron menos personas o no asistirán. Puedes reasignarlos.';
        }

        if ($dietaryCount > 0) {
            $actions[] = $this->count($dietaryCount, 'invitado confirmado indicó', 'invitados confirmados indicaron').' restricciones alimentarias: compártelas con el catering.';
        }

        if ($unassigned > 0) {
            $actions[] = $this->count($unassigned, 'invitado confirmado aún no tiene', 'invitados confirmados aún no tienen').' mesa asignada.';
        }

        return $actions;
    }

    private function percent(int $part, int $total): int
    {
        return $total > 0 ? (int) round($part / $total * 100) : 0;
    }

    private function count(int $value, string $singular, string $plural): string
    {
        return $value.' '.($value === 1 ? $singular : $plural);
    }
}
