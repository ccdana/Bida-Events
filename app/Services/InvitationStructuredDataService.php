<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationGalleryImage;
use App\Models\InvitationItineraryItem;
use App\Models\InvitationPoll;
use App\Models\InvitationPollOption;
use App\Models\InvitationSetting;
use App\Models\PollVote;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Módulos que ya viven en tablas normalizadas: config, itinerario, galería y encuestas.
 * El resto sigue en invitation_data.json_data, que además actúa como fallback
 * mientras una invitación no tenga fila en invitation_settings.
 */
class InvitationStructuredDataService
{
    public const RELATIONS = ['settings', 'itineraryItems', 'galleryImages', 'polls.options'];

    protected const CONFIG_KEYS = ['template', 'colores', 'tipografias', 'modulos'];

    protected const ITINERARY_KEYS = ['hora', 'titulo', 'icono', 'descripcion'];

    protected const GALLERY_KEYS = ['url', 'alt'];

    protected const POLL_KEYS = ['id', 'tipo', 'pregunta', 'opciones'];

    /** @var array<int, true> */
    protected static array $reportedFallbacks = [];

    public function isMigrated(Invitation $invitation): bool
    {
        if (! $invitation->exists) {
            return false;
        }

        $invitation->loadMissing('settings');

        return $invitation->settings !== null;
    }

    /**
     * Superpone los datos de las tablas sobre los módulos JSON con la misma forma que espera el editor.
     */
    public function hydrate(Invitation $invitation, array $modules): array
    {
        if (! $this->isMigrated($invitation)) {
            $this->reportJsonFallback($invitation, $modules);

            return $modules;
        }

        $invitation->loadMissing(self::RELATIONS);

        $modules['config'] = $this->configFromSettings($invitation->settings);

        $modules['itinerario'] = [
            ...$this->arrayValue($modules['itinerario'] ?? null),
            'eventos' => $invitation->itineraryItems
                ->map(fn (InvitationItineraryItem $item) => array_merge($item->meta ?? [], [
                    'hora' => $item->time ?? '',
                    'titulo' => $item->title,
                    'icono' => $item->icon ?? 'star',
                    'descripcion' => $item->description ?? '',
                ]))
                ->all(),
        ];

        $modules['galeria'] = [
            ...$this->arrayValue($modules['galeria'] ?? null),
            'fotos' => $invitation->galleryImages
                ->where('collection', InvitationGalleryImage::COLLECTION_GALLERY)
                ->map(fn (InvitationGalleryImage $image) => $this->galleryImageValue($image))
                ->values()
                ->all(),
        ];

        $modules['encuestas'] = [
            ...$this->arrayValue($modules['encuestas'] ?? null),
            'preguntas' => $invitation->polls
                ->map(fn (InvitationPoll $poll) => array_merge($poll->meta ?? [], [
                    'id' => $poll->poll_key,
                    'tipo' => $poll->type,
                    'pregunta' => $poll->question,
                    'opciones' => $poll->options->pluck('label')->all(),
                ]))
                ->all(),
        ];

        return $modules;
    }

    /**
     * Reemplaza en una transacción los datos normalizados de la invitación.
     *
     * @return array{itinerario: array, galeria: array, encuestas: array, skipped: list<string>, warnings: list<string>}
     */
    public function sync(Invitation $invitation, array $modules): array
    {
        $itinerary = $this->itineraryRows($modules['itinerario'] ?? null);
        $gallery = $this->galleryRows($modules['galeria'] ?? null);
        $polls = $this->pollRows($modules['encuestas'] ?? null);

        DB::transaction(function () use ($invitation, $modules, $itinerary, $gallery, $polls) {
            InvitationSetting::updateOrCreate(
                ['invitation_id' => $invitation->id],
                $this->settingsAttributes($modules['config'] ?? null)
            );

            $invitation->itineraryItems()->delete();
            $invitation->itineraryItems()->createMany($itinerary['rows']);

            $invitation->galleryImages()->where('collection', InvitationGalleryImage::COLLECTION_GALLERY)->delete();
            $invitation->galleryImages()->createMany($gallery['rows']);

            $this->syncPolls($invitation, $polls['rows']);
        });

        foreach (self::RELATIONS as $relation) {
            $invitation->unsetRelation(Str::before($relation, '.'));
        }

        return [
            'itinerario' => ['detected' => $itinerary['detected'], 'created' => count($itinerary['rows'])],
            'galeria' => ['detected' => $gallery['detected'], 'created' => count($gallery['rows'])],
            'encuestas' => [
                'detected' => $polls['detected'],
                'created' => count($polls['rows']),
                'options' => array_sum(array_map(fn (array $row) => count($row['options']), $polls['rows'])),
            ],
            'skipped' => [...$itinerary['skipped'], ...$gallery['skipped'], ...$polls['skipped']],
            'warnings' => [...$itinerary['warnings'], ...$gallery['warnings'], ...$polls['warnings']],
        ];
    }

    /**
     * Compara lo guardado en tablas con lo que produciría el JSON (conteos, contenido y orden).
     *
     * @return list<string>
     */
    public function verify(Invitation $invitation, array $modules): array
    {
        $invitation->load(self::RELATIONS);
        $differences = [];

        $this->compareRows(
            $differences,
            'config',
            [$this->settingsAttributes($modules['config'] ?? null)],
            $invitation->settings
                ? [$invitation->settings->only(['template', 'colors', 'typography', 'module_visibility', 'extra'])]
                : []
        );

        $this->compareRows(
            $differences,
            'itinerario',
            $this->itineraryRows($modules['itinerario'] ?? null)['rows'],
            $invitation->itineraryItems->map->only(['time', 'title', 'icon', 'description', 'meta', 'sort_order'])->all()
        );

        $this->compareRows(
            $differences,
            'galeria',
            $this->galleryRows($modules['galeria'] ?? null)['rows'],
            $invitation->galleryImages
                ->where('collection', InvitationGalleryImage::COLLECTION_GALLERY)
                ->map->only(['collection', 'url', 'media_type', 'alt_text', 'is_cover', 'status', 'meta', 'sort_order'])
                ->values()
                ->all()
        );

        $this->compareRows(
            $differences,
            'encuestas',
            $this->pollRows($modules['encuestas'] ?? null)['rows'],
            $invitation->polls
                ->map(fn (InvitationPoll $poll) => [
                    ...$poll->only(['poll_key', 'question', 'type', 'is_enabled', 'meta', 'sort_order']),
                    'options' => $poll->options->pluck('label')->all(),
                ])
                ->all()
        );

        return $differences;
    }

    /**
     * Asigna un id estable a las encuestas que no lo tengan, antes de guardarlas en JSON y tablas.
     */
    public function ensurePollKeys(mixed $questions): mixed
    {
        if (! is_array($questions)) {
            return $questions;
        }

        foreach ($questions as $index => $poll) {
            if (is_array($poll) && (! is_scalar($poll['id'] ?? null) || (string) $poll['id'] === '')) {
                $questions[$index]['id'] = 'poll-'.Str::lower(Str::random(12));
            }
        }

        return $questions;
    }

    public function settingsAttributes(mixed $config): array
    {
        $config = $this->arrayValue($config);

        return [
            'template' => is_string($config['template'] ?? null) ? $config['template'] : null,
            'colors' => is_array($config['colores'] ?? null) ? $config['colores'] : null,
            'typography' => is_array($config['tipografias'] ?? null) ? $config['tipografias'] : null,
            'module_visibility' => is_array($config['modulos'] ?? null) ? $config['modulos'] : null,
            'extra' => Arr::except($config, self::CONFIG_KEYS) ?: null,
        ];
    }

    public function itineraryRows(mixed $module): array
    {
        $result = $this->emptyResult();
        $events = $this->arrayValue($module)['eventos'] ?? [];

        if (! is_array($events)) {
            $result['skipped'][] = 'itinerario.eventos: se esperaba una lista';

            return $result;
        }

        foreach (array_values($events) as $index => $event) {
            $result['detected']++;
            $path = "itinerario.eventos[{$index}]";

            if (! is_array($event)) {
                $result['skipped'][] = "{$path}: se esperaba un objeto";
                continue;
            }

            $result['rows'][] = [
                'time' => $this->stringValue($event['hora'] ?? null, 50, "{$path}.hora", $result['warnings']),
                'title' => $this->stringValue($event['titulo'] ?? null, 255, "{$path}.titulo", $result['warnings']) ?? '',
                'icon' => $this->stringValue($event['icono'] ?? null, 50, "{$path}.icono", $result['warnings']),
                'description' => $this->stringValue($event['descripcion'] ?? null, null, "{$path}.descripcion", $result['warnings']),
                'meta' => $this->metaValue($event, self::ITINERARY_KEYS),
                'sort_order' => count($result['rows']),
            ];
        }

        return $result;
    }

    public function galleryRows(mixed $module): array
    {
        $result = $this->emptyResult();
        $photos = $this->arrayValue($module)['fotos'] ?? [];

        if (! is_array($photos)) {
            $result['skipped'][] = 'galeria.fotos: se esperaba una lista';

            return $result;
        }

        foreach (array_values($photos) as $index => $photo) {
            $result['detected']++;
            $path = "galeria.fotos[{$index}]";
            $url = is_array($photo) ? ($photo['url'] ?? null) : $photo;

            if (! is_string($url) || trim($url) === '') {
                $result['skipped'][] = "{$path}: URL vacía o inválida";
                continue;
            }

            $result['rows'][] = [
                'collection' => InvitationGalleryImage::COLLECTION_GALLERY,
                'url' => $url,
                'media_type' => 'image',
                'alt_text' => is_array($photo)
                    ? $this->stringValue($photo['alt'] ?? null, 255, "{$path}.alt", $result['warnings'])
                    : null,
                'is_cover' => false,
                'status' => 'active',
                'meta' => is_array($photo) ? $this->metaValue($photo, self::GALLERY_KEYS) : null,
                'sort_order' => count($result['rows']),
            ];
        }

        return $result;
    }

    public function pollRows(mixed $module): array
    {
        $result = $this->emptyResult();
        $questions = $this->arrayValue($module)['preguntas'] ?? [];

        if (! is_array($questions)) {
            $result['skipped'][] = 'encuestas.preguntas: se esperaba una lista';

            return $result;
        }

        $seenKeys = [];

        foreach (array_values($questions) as $index => $poll) {
            $result['detected']++;
            $path = "encuestas.preguntas[{$index}]";

            if (! is_array($poll)) {
                $result['skipped'][] = "{$path}: se esperaba un objeto";
                continue;
            }

            $key = is_scalar($poll['id'] ?? null) ? (string) $poll['id'] : '';

            if ($key === '') {
                $key = 'poll-legacy-'.($index + 1);
                $result['warnings'][] = "{$path}: sin id, se asignó '{$key}'";
            }

            if (mb_strlen($key) > 100) {
                $result['skipped'][] = "{$path}: el id supera 100 caracteres";
                continue;
            }

            if (isset($seenKeys[$key])) {
                $result['skipped'][] = "{$path}: id duplicado '{$key}'";
                continue;
            }

            $seenKeys[$key] = true;

            $options = [];
            $rawOptions = $poll['opciones'] ?? [];

            foreach (is_array($rawOptions) ? array_values($rawOptions) : [] as $optionIndex => $option) {
                if ($option !== null && ! is_scalar($option)) {
                    $result['warnings'][] = "{$path}.opciones[{$optionIndex}]: valor no textual descartado";
                    continue;
                }

                $options[] = (string) $option;
            }

            $result['rows'][] = [
                'poll_key' => $key,
                'question' => $this->stringValue($poll['pregunta'] ?? null, null, "{$path}.pregunta", $result['warnings']) ?? '',
                'type' => $this->stringValue($poll['tipo'] ?? null, 20, "{$path}.tipo", $result['warnings']) ?? 'single',
                'is_enabled' => true,
                'meta' => $this->metaValue($poll, self::POLL_KEYS),
                'sort_order' => count($result['rows']),
                'options' => $options,
            ];
        }

        return $result;
    }

    /**
     * Las encuestas se actualizan por poll_key para conservar su id (y los votos enlazados).
     */
    protected function syncPolls(Invitation $invitation, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $poll = InvitationPoll::updateOrCreate(
                ['invitation_id' => $invitation->id, 'poll_key' => $row['poll_key']],
                Arr::except($row, ['poll_key', 'options'])
            );

            InvitationPollOption::where('poll_id', $poll->id)->delete();
            $poll->options()->createMany(array_map(
                fn (string $label, int $index) => ['label' => $label, 'sort_order' => $index],
                $row['options'],
                array_keys($row['options'])
            ));

            PollVote::where('invitation_id', $invitation->id)
                ->where('poll_id', $row['poll_key'])
                ->whereNull('invitation_poll_id')
                ->update(['invitation_poll_id' => $poll->id]);

            $keptIds[] = $poll->id;
        }

        $invitation->polls()->whereNotIn('id', $keptIds)->delete();
    }

    protected function configFromSettings(InvitationSetting $settings): array
    {
        $config = $settings->extra ?? [];

        $columns = [
            'template' => $settings->template,
            'colores' => $settings->colors,
            'tipografias' => $settings->typography,
            'modulos' => $settings->module_visibility,
        ];

        foreach ($columns as $key => $value) {
            if ($value !== null) {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    protected function galleryImageValue(InvitationGalleryImage $image): string|array
    {
        if ($image->alt_text === null && empty($image->meta)) {
            return $image->url;
        }

        return array_filter(
            array_merge($image->meta ?? [], ['url' => $image->url, 'alt' => $image->alt_text]),
            fn ($value) => $value !== null
        );
    }

    protected function reportJsonFallback(Invitation $invitation, array $modules): void
    {
        if (! $invitation->exists
            || $modules === []
            || isset(self::$reportedFallbacks[$invitation->id])
            || ! config('optimizations.structured_modules.log_json_fallback', true)) {
            return;
        }

        self::$reportedFallbacks[$invitation->id] = true;

        Log::info('Módulos leídos desde invitation_data.json_data (fallback). Ejecuta invitations:migrate-json.', [
            'invitation_id' => $invitation->id,
            'slug' => $invitation->slug,
        ]);
    }

    protected function compareRows(array &$differences, string $module, array $expected, array $actual): void
    {
        if (count($expected) !== count($actual)) {
            $differences[] = "{$module}: se esperaban ".count($expected).' filas y se guardaron '.count($actual);

            return;
        }

        foreach ($expected as $index => $row) {
            // Comparación flexible: el orden de claves JSON y los tipos numéricos pueden variar según el motor
            if ($row != $actual[$index]) {
                $differences[] = "{$module}: la fila {$index} no coincide en contenido u orden";
            }
        }
    }

    protected function stringValue(mixed $value, ?int $limit, string $path, array &$warnings): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_scalar($value)) {
            $warnings[] = "{$path}: valor no textual descartado";

            return null;
        }

        $value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        if ($limit !== null && mb_strlen($value) > $limit) {
            $warnings[] = "{$path}: recortado a {$limit} caracteres";
            $value = mb_substr($value, 0, $limit);
        }

        return $value;
    }

    protected function metaValue(array $item, array $knownKeys): ?array
    {
        $meta = Arr::except($item, $knownKeys);

        return $meta === [] ? null : $meta;
    }

    protected function arrayValue(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    protected function emptyResult(): array
    {
        return ['detected' => 0, 'rows' => [], 'skipped' => [], 'warnings' => []];
    }
}
