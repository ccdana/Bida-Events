<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\PollVote;
use Illuminate\Support\Str;

class InvitationModuleService
{
    public function loadForDisplay(Invitation $invitation): array
    {
        $invitation->load(['modulesData', 'eventType', 'plan']);

        return $invitation->modules;
    }

    public function pollResults(Invitation $invitation, string $pollId, int $optionsCount): array
    {
        $votes = PollVote::query()
            ->where('invitation_id', $invitation->id)
            ->where('poll_id', $pollId)
            ->get();

        $counts = array_fill(0, $optionsCount, 0);
        foreach ($votes as $vote) {
            if (isset($counts[$vote->option_index])) {
                $counts[$vote->option_index]++;
            }
        }

        $total = array_sum($counts) ?: 1;

        return array_map(fn ($count) => round(($count / $total) * 100), $counts);
    }

    public function syncModule(Invitation $invitation, string $featureCode, array $jsonData): void
    {
        $invitation->modulesData()->updateOrCreate(
            ['feature_code' => $featureCode],
            ['json_data' => $jsonData]
        );
    }

    public function googleCalendarUrl(Invitation $invitation, array $ubicacion): string
    {
        $start = $invitation->event_date->format('Ymd\THis');
        $end = $invitation->event_date->copy()->addHours(6)->format('Ymd\THis');
        $title = urlencode($invitation->title);
        $details = urlencode($ubicacion['nombre_lugar'] ?? $invitation->title);
        $location = urlencode($ubicacion['direccion'] ?? '');

        return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$start}/{$end}&details={$details}&location={$location}";
    }

    public static function generateGuestToken(): string
    {
        return Str::random(32);
    }
}
