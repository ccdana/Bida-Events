<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\PollVote;
use App\Modules\ModuleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Puerta de entrada a los módulos de una invitación. Cada módulo lee y guarda sus propias tablas
 * (app/Modules); este servicio arma el conjunto, completa la forma vacía y decide la visibilidad.
 */
class InvitationModuleService
{
    public function __construct(
        protected ModuleRegistry $registry
    ) {}

    /**
     * Completa la forma de todos los módulos y resuelve qué se muestra: un módulo encendido se
     * muestra, y uno apagado también si tiene contenido cargado.
     */
    public function normalizeModules(array $modules): array
    {
        $rawVisibility = $modules['config']['modulos'] ?? null;
        $normalized = array_replace_recursive($this->registry->emptyModules(), $modules);
        $normalized = $this->coerceObjectModules($normalized);
        $normalized['regalos'] = $this->coerceGiftModule($normalized['regalos'] ?? []);

        foreach ($this->registry->visibilityDefaults() as $code => $default) {
            $data = is_array($normalized[$code] ?? null) ? $normalized[$code] : [];
            $hasContent = $this->registry->get($code)->hasContent($data);

            if (is_array($rawVisibility) && array_key_exists($code, $rawVisibility)) {
                $normalized['config']['modulos'][$code] = (bool) $rawVisibility[$code] || $hasContent;

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

    /** Módulos tal como están guardados en sus tablas, sin completar la forma. */
    public function storedModules(Invitation $invitation): array
    {
        return $this->registry->load($invitation);
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

        // El editor y las plantillas usan poll_key; los votos apuntan a la fila de la encuesta
        $pollIds = $invitation->polls()
            ->whereIn('poll_key', array_map('strval', array_keys($optionCounts)))
            ->pluck('id', 'poll_key');

        if ($pollIds->isNotEmpty()) {
            $keyById = $pollIds->flip();

            $rows = PollVote::query()
                ->whereIn('invitation_poll_id', $pollIds->values())
                ->selectRaw('invitation_poll_id, option_index, COUNT(*) as total')
                ->groupBy('invitation_poll_id', 'option_index')
                ->toBase()
                ->get();

            foreach ($rows as $row) {
                $key = $keyById[$row->invitation_poll_id] ?? null;

                if ($key !== null && isset($counts[$key][$row->option_index])) {
                    $counts[$key][$row->option_index] = (int) $row->total;
                }
            }
        }

        return array_map(function (array $pollCounts) {
            $total = array_sum($pollCounts) ?: 1;

            return array_map(fn ($count) => round(($count / $total) * 100), $pollCounts);
        }, $counts);
    }

    /**
     * @return array{id: int, options_count: int}|null
     */
    public function pollReference(Invitation $invitation, string $pollId): ?array
    {
        $poll = $invitation->polls()->where('poll_key', $pollId)->withCount('options')->first();

        return $poll ? ['id' => $poll->id, 'options_count' => (int) $poll->options_count] : null;
    }

    /** Guarda todos los módulos en sus tablas, todo o nada. */
    public function syncAllModules(Invitation $invitation, array $modules): array
    {
        $normalized = $this->normalizeModules($modules);

        // Las encuestas nuevas reciben su clave antes de guardar, para devolverla al editor
        if (is_array($normalized['encuestas']['preguntas'] ?? null)) {
            $normalized['encuestas']['preguntas'] = $this->ensurePollKeys($normalized['encuestas']['preguntas']);
        }

        DB::transaction(fn () => $this->registry->save($invitation, $normalized));

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

    /** Asigna una clave estable a las encuestas que no la tengan. */
    protected function ensurePollKeys(array $questions): array
    {
        foreach ($questions as $index => $poll) {
            if (is_array($poll) && (! is_scalar($poll['id'] ?? null) || (string) $poll['id'] === '')) {
                $questions[$index]['id'] = 'poll-'.Str::lower(Str::random(12));
            }
        }

        return $questions;
    }

    /** Un módulo que debe ser objeto y llegó como lista vacía vuelve a su forma. */
    protected function coerceObjectModules(array $modules): array
    {
        foreach ($this->registry->all() as $code => $module) {
            if (! is_object($module->defaults())) {
                continue;
            }

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

        $data['sobres'] = $this->coerceGiftBlock($data['sobres'] ?? null, ['titulo' => '', 'direccion' => '']);
        $data['banco'] = $this->coerceGiftBlock($data['banco'] ?? null, ['banco' => '', 'titular' => '', 'ci' => '', 'cuenta' => '', 'qr_url' => '']);
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
}
