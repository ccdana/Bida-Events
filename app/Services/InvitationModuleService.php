<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\PollVote;
use App\Support\InvitationDefaults;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvitationModuleService
{
    public function __construct(
        protected InvitationStructuredDataService $structuredData
    ) {}

    public function normalizeModules(array $modules): array
    {
        $rawVisibility = $modules['config']['modulos'] ?? null;
        $normalized = array_replace_recursive(InvitationDefaults::emptyModules(), $modules);
        $normalized = $this->coerceObjectModules($normalized);
        $normalized['regalos'] = $this->coerceGiftModule($normalized['regalos'] ?? []);

        $defaults = InvitationDefaults::moduleVisibilityDefaults();
        foreach ($defaults as $code => $default) {
            $hasContent = $this->moduleHasContent($normalized, $code);

            if (is_array($rawVisibility) && array_key_exists($code, $rawVisibility)) {
                $explicitValue = (bool) $rawVisibility[$code];
                // If explicitly enabled, respect it. If explicitly disabled but has content, still show it.
                $normalized['config']['modulos'][$code] = $explicitValue || $hasContent;
                continue;
            }

            $normalized['config']['modulos'][$code] = $hasContent ?: $default;
        }

        return $normalized;
    }

    public function loadForDisplay(Invitation $invitation): array
    {
        $invitation->loadMissing('eventType');

        return $this->resolveModules($invitation);
    }

    /**
     * Módulos tal como están guardados: tablas normalizadas cuando existen y JSON para el resto.
     */
    public function storedModules(Invitation $invitation): array
    {
        $invitation->loadMissing('modulesData');

        return $this->structuredData->hydrate($invitation, $invitation->modules);
    }

    public function resolveModules(Invitation $invitation): array
    {
        return $this->normalizeModules($this->storedModules($invitation));
    }

    public function pollResults(Invitation $invitation, string $pollId, int $optionsCount): array
    {
        return $this->pollResultsFor($invitation, [$pollId => $optionsCount])[$pollId];
    }

    /**
     * Porcentajes de varias encuestas con una sola consulta agrupada.
     *
     * @param  array<string, int>  $optionCounts  id de encuesta => cantidad de opciones
     */
    public function pollResultsFor(Invitation $invitation, array $optionCounts): array
    {
        if ($optionCounts === []) {
            return [];
        }

        $counts = array_map(fn (int $total) => array_fill(0, max(0, $total), 0), $optionCounts);

        $rows = PollVote::query()
            ->where('invitation_id', $invitation->id)
            ->whereIn('poll_id', array_map('strval', array_keys($optionCounts)))
            ->selectRaw('poll_id, option_index, COUNT(*) as total')
            ->groupBy('poll_id', 'option_index')
            ->toBase()
            ->get();

        foreach ($rows as $row) {
            if (isset($counts[$row->poll_id][$row->option_index])) {
                $counts[$row->poll_id][$row->option_index] = (int) $row->total;
            }
        }

        return array_map(function (array $pollCounts) {
            $total = array_sum($pollCounts) ?: 1;

            return array_map(fn ($count) => round(($count / $total) * 100), $pollCounts);
        }, $counts);
    }

    /**
     * @return array{id: int|null, options_count: int}|null
     */
    public function pollReference(Invitation $invitation, string $pollId): ?array
    {
        if ($this->structuredData->isMigrated($invitation)) {
            $poll = $invitation->polls()->where('poll_key', $pollId)->withCount('options')->first();

            return $poll ? ['id' => $poll->id, 'options_count' => (int) $poll->options_count] : null;
        }

        $encuestas = $invitation->modulesData()->where('feature_code', 'encuestas')->first()?->json_data;
        $poll = collect($encuestas['preguntas'] ?? [])->firstWhere('id', $pollId);

        return $poll ? ['id' => null, 'options_count' => count($poll['opciones'] ?? [])] : null;
    }

    public function syncModule(Invitation $invitation, string $featureCode, array $jsonData): void
    {
        $invitation->modulesData()->updateOrCreate(
            ['feature_code' => $featureCode],
            ['json_data' => $jsonData]
        );

        $invitation->clearModulesCache();
    }

    public function syncAllModules(Invitation $invitation, array $modules): array
    {
        $normalized = $this->normalizeModules($modules);

        if (is_array($normalized['encuestas'] ?? null)) {
            $normalized['encuestas']['preguntas'] = $this->structuredData->ensurePollKeys($normalized['encuestas']['preguntas'] ?? []);
        }

        // JSON y tablas se escriben juntos mientras dure el periodo de fallback
        DB::transaction(function () use ($invitation, $normalized) {
            foreach (InvitationDefaults::moduleCodes() as $code) {
                $this->syncModule($invitation, $code, $normalized[$code] ?? []);
            }

            $this->structuredData->sync($invitation, $normalized);
        });

        return $normalized;
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

    protected function coerceObjectModules(array $modules): array
    {
        foreach (['bienvenida', 'musica', 'video', 'playlist', 'hashtag', 'post_evento', 'rsvp'] as $code) {
            $value = $modules[$code] ?? [];
            if (! is_array($value) || array_is_list($value)) {
                $modules[$code] = [];
            }
        }

        return $modules;
    }

    protected function coerceGiftModule(mixed $regalos): array
    {
        $data = is_array($regalos) ? $regalos : [];

        $data['sobres'] = $this->coerceGiftBlock(
            $data['sobres'] ?? null,
            ['titulo' => '', 'direccion' => '']
        );

        $data['banco'] = $this->coerceGiftBlock(
            $data['banco'] ?? null,
            ['banco' => '', 'titular' => '', 'ci' => '', 'cuenta' => '', 'qr_url' => '']
        );

        $data['opciones'] = is_array($data['opciones'] ?? null) ? array_values($data['opciones']) : [];
        $data['titulo'] ??= '';
        $data['tienda_url'] ??= '';
        $data['tienda_texto'] ??= '';

        return $data;
    }

    protected function coerceGiftBlock(mixed $value, array $defaults): array
    {
        if (! is_array($value) || array_is_list($value)) {
            return $defaults;
        }

        return array_replace($defaults, $value);
    }

    protected function moduleHasContent(array $modules, string $code): bool
    {
        return match ($code) {
            'bienvenida' => ! empty($modules['bienvenida']['nombre_quinceanera'] ?? null)
                || ! empty($modules['bienvenida']['subtitulo'] ?? null)
                || ! empty($modules['bienvenida']['mensaje'] ?? null)
                || ! empty($modules['bienvenida']['imagen_hero'] ?? null),
            'ubicacion' => ! empty($modules['ubicacion']['nombre_lugar'] ?? null)
                || ! empty($modules['ubicacion']['direccion'] ?? null)
                || ! empty($modules['ubicacion']['maps_url'] ?? null)
                || ! empty($modules['ubicacion']['imagen_lugar'] ?? null),
            'itinerario' => ! empty($modules['itinerario']['eventos'] ?? []),
            'dress_code' => ! empty($modules['dress_code']['titulo'] ?? null)
                || ! empty($modules['dress_code']['estilo'] ?? null)
                || ! empty($modules['dress_code']['descripcion'] ?? null)
                || ! empty($modules['dress_code']['sugerencias'] ?? []),
            'destacados' => ! empty($modules['destacados']['chambelanes'] ?? [])
                || ! empty($modules['destacados']['damitas'] ?? [])
                || ! empty($modules['destacados']['padrinos'] ?? []),
            'galeria' => ! empty($modules['galeria']['fotos'] ?? []),
            'video' => ! empty($modules['video']['video_url'] ?? null) || ! empty($modules['video']['poster'] ?? null),
            'musica' => ! empty($modules['musica']['audio_url'] ?? null) || ! empty($modules['musica']['titulo'] ?? null),
            'playlist' => ! empty($modules['playlist']['titulo'] ?? null)
                || ! empty($modules['playlist']['descripcion'] ?? null)
                || ! empty($modules['playlist']['placeholder'] ?? null),
            'hashtag' => ! empty($modules['hashtag']['hashtag'] ?? null),
            'encuestas' => ! empty($modules['encuestas']['preguntas'] ?? []),
            'regalos' => ! empty($modules['regalos']['titulo'] ?? null)
                || ! empty($modules['regalos']['tienda_url'] ?? null)
                || ! empty($modules['regalos']['opciones'] ?? [])
                || ! empty($modules['regalos']['sobres']['titulo'] ?? null)
                || ! empty($modules['regalos']['sobres']['direccion'] ?? null)
                || ! empty($modules['regalos']['banco']['banco'] ?? null)
                || ! empty($modules['regalos']['banco']['titular'] ?? null)
                || ! empty($modules['regalos']['banco']['cuenta'] ?? null)
                || ! empty($modules['regalos']['banco']['ci'] ?? null)
                || ! empty($modules['regalos']['banco']['qr_url'] ?? null),
            'rsvp' => ! empty($modules['rsvp']['titulo_confirmacion'] ?? null)
                || ! empty($modules['rsvp']['mensaje_personalizado'] ?? null)
                || ! empty($modules['rsvp']['texto_confirmado'] ?? null)
                || ! empty($modules['rsvp']['texto_declinado'] ?? null),
            'post_evento' => ! empty($modules['post_evento']['titulo'] ?? null)
                || ! empty($modules['post_evento']['descripcion'] ?? null)
                || ! empty($modules['post_evento']['enlace_externo'] ?? null)
                || ! empty($modules['post_evento']['fotos'] ?? []),
            default => false,
        };
    }
}
