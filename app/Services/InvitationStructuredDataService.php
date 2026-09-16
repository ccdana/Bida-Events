<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationDressCodeItem;
use App\Models\InvitationFeaturedPerson;
use App\Models\InvitationGalleryImage;
use App\Models\InvitationGiftOption;
use App\Models\InvitationItineraryItem;
use App\Models\InvitationLocation;
use App\Models\InvitationMedia;
use App\Models\InvitationPoll;
use App\Models\InvitationSetting;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Módulos que ya viven en tablas normalizadas: configuración, itinerario, galería (incluidas las
 * fotos post evento), encuestas, ubicación, personas destacadas, código de vestimenta, opciones de
 * regalo y medios (audio y video).
 *
 * En el JSON del módulo solo queda su configuración: títulos, textos y bloques que no son listas
 * (por ejemplo los datos bancarios de regalos). invitation_data.json_data sigue siendo el respaldo
 * mientras una invitación no tenga fila en invitation_settings.
 */
class InvitationStructuredDataService
{
    public const RELATIONS = [
        'settings',
        'itineraryItems',
        'galleryImages',
        'polls.options',
        'locations',
        'featuredPeople',
        'dressCodeItems',
        'giftOptions',
        'media',
    ];

    protected const CONFIG_KEYS = ['template', 'colores', 'tipografias', 'modulos'];

    protected const ITINERARY_KEYS = ['hora', 'titulo', 'icono', 'descripcion'];

    protected const GALLERY_KEYS = ['url', 'alt'];

    protected const POLL_KEYS = ['id', 'tipo', 'pregunta', 'opciones'];

    protected const LOCATION_KEYS = ['lat', 'lng', 'nombre_lugar', 'direccion', 'maps_url', 'nota', 'imagen_lugar'];

    protected const PERSON_KEYS = ['nombre', 'nombres', 'iniciales', 'rol', 'detalle', 'mensaje'];

    protected const DRESS_SUGGESTION_KEYS = ['para', 'titulo', 'descripcion', 'ejemplos', 'imagen'];

    protected const DRESS_COLOR_KEYS = ['nombre', 'hex'];

    protected const GIFT_KEYS = ['titulo', 'descripcion', 'enlace', 'imagen'];

    protected const AUDIO_KEYS = ['titulo', 'audio_url', 'autoplay'];

    protected const VIDEO_KEYS = ['titulo', 'video_url', 'poster'];

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

        if ($location = $this->locationValue($invitation)) {
            $modules['ubicacion'] = $location;
        }

        // Las claves que no son listas (títulos, textos) se conservan del JSON del módulo
        $modules['destacados'] = array_merge(
            array_filter($this->arrayValue($modules['destacados'] ?? null), fn ($value) => ! is_array($value)),
            $this->featuredPeopleValues($invitation)
        );

        $modules['dress_code'] = array_merge(
            $this->arrayValue($modules['dress_code'] ?? null),
            $this->dressCodeValues($invitation)
        );

        $modules['regalos'] = array_merge(
            $this->arrayValue($modules['regalos'] ?? null),
            ['opciones' => $this->giftOptionValues($invitation)]
        );

        foreach ($this->mediaValues($invitation) as $code => $value) {
            $modules[$code] = $value;
        }

        $postEvent = $invitation->galleryImages->where('collection', InvitationGalleryImage::COLLECTION_POST_EVENT);

        if ($postEvent->isNotEmpty() || array_key_exists('fotos', $this->arrayValue($modules['post_evento'] ?? null))) {
            $modules['post_evento'] = [
                ...$this->arrayValue($modules['post_evento'] ?? null),
                'fotos' => $postEvent->map(fn (InvitationGalleryImage $image) => $this->galleryImageValue($image))->values()->all(),
            ];
        }

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
        $locations = $this->locationRows($modules['ubicacion'] ?? null);
        $people = $this->featuredPeopleRows($modules['destacados'] ?? null);
        $dress = $this->dressCodeRows($modules['dress_code'] ?? null);
        $gifts = $this->giftOptionRows($modules['regalos'] ?? null);
        $media = $this->mediaRows($modules);
        $postEvent = $this->postEventGalleryRows($modules['post_evento'] ?? null);

        DB::transaction(function () use ($invitation, $modules, $itinerary, $gallery, $polls, $locations, $people, $dress, $gifts, $media, $postEvent) {
            InvitationSetting::updateOrCreate(
                ['invitation_id' => $invitation->id],
                $this->settingsAttributes($modules['config'] ?? null)
            );

            $this->replaceOrdered($invitation->itineraryItems(), $itinerary['rows']);

            $this->replaceOrdered(
                $invitation->galleryImages()->where('collection', InvitationGalleryImage::COLLECTION_GALLERY),
                $gallery['rows'],
                ['collection' => InvitationGalleryImage::COLLECTION_GALLERY]
            );

            $this->replaceOrdered(
                $invitation->galleryImages()->where('collection', InvitationGalleryImage::COLLECTION_POST_EVENT),
                $postEvent['rows'],
                ['collection' => InvitationGalleryImage::COLLECTION_POST_EVENT]
            );

            $this->replaceOrdered($invitation->locations(), $locations['rows']);
            $this->replaceOrdered($invitation->featuredPeople(), $people['rows']);
            $this->replaceOrdered($invitation->dressCodeItems(), $dress['rows']);
            $this->replaceOrdered($invitation->giftOptions(), $gifts['rows']);
            $this->replaceOrdered($invitation->media(), $media['rows']);

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
            'ubicacion' => ['detected' => $locations['detected'], 'created' => count($locations['rows'])],
            'destacados' => ['detected' => $people['detected'], 'created' => count($people['rows'])],
            'dress_code' => ['detected' => $dress['detected'], 'created' => count($dress['rows'])],
            'regalos' => ['detected' => $gifts['detected'], 'created' => count($gifts['rows'])],
            'media' => ['detected' => $media['detected'], 'created' => count($media['rows'])],
            'post_evento' => ['detected' => $postEvent['detected'], 'created' => count($postEvent['rows'])],
            'skipped' => [
                ...$itinerary['skipped'], ...$gallery['skipped'], ...$polls['skipped'],
                ...$locations['skipped'], ...$people['skipped'], ...$dress['skipped'],
                ...$gifts['skipped'], ...$media['skipped'], ...$postEvent['skipped'],
            ],
            'warnings' => [
                ...$itinerary['warnings'], ...$gallery['warnings'], ...$polls['warnings'],
                ...$locations['warnings'], ...$people['warnings'], ...$dress['warnings'],
                ...$gifts['warnings'], ...$media['warnings'], ...$postEvent['warnings'],
            ],
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

        $this->compareRows(
            $differences,
            'ubicacion',
            $this->locationRows($modules['ubicacion'] ?? null)['rows'],
            $invitation->locations->map->only(['name', 'address', 'latitude', 'longitude', 'map_url', 'image_url', 'note', 'meta', 'sort_order'])->all()
        );

        $this->compareRows(
            $differences,
            'destacados',
            $this->featuredPeopleRows($modules['destacados'] ?? null)['rows'],
            $invitation->featuredPeople->map->only(['group', 'name_key', 'name', 'initials', 'role', 'detail', 'message', 'meta', 'sort_order'])->all()
        );

        $this->compareRows(
            $differences,
            'dress_code',
            $this->dressCodeRows($modules['dress_code'] ?? null)['rows'],
            $invitation->dressCodeItems->map->only(['kind', 'audience', 'title', 'description', 'image_url', 'color_hex', 'examples', 'meta', 'sort_order'])->all()
        );

        $this->compareRows(
            $differences,
            'regalos',
            $this->giftOptionRows($modules['regalos'] ?? null)['rows'],
            $invitation->giftOptions->map->only(['title', 'description', 'url', 'image_url', 'meta', 'sort_order'])->all()
        );

        $this->compareRows(
            $differences,
            'media',
            $this->mediaRows($modules)['rows'],
            $invitation->media->map->only(['type', 'title', 'url', 'poster_url', 'autoplay', 'status', 'meta', 'sort_order'])->all()
        );

        $this->compareRows(
            $differences,
            'post_evento',
            $this->postEventGalleryRows($modules['post_evento'] ?? null)['rows'],
            $invitation->galleryImages
                ->where('collection', InvitationGalleryImage::COLLECTION_POST_EVENT)
                ->map->only(['collection', 'url', 'media_type', 'alt_text', 'is_cover', 'status', 'meta', 'sort_order'])
                ->values()
                ->all()
        );

        return $differences;
    }

    /** La ubicación es una fila: así mañana pueden ser dos (ceremonia y fiesta) sin tocar el JSON. */
    public function locationRows(mixed $module): array
    {
        $result = $this->emptyResult();
        $location = $this->arrayValue($module);

        if ($location === []) {
            return $result;
        }

        $result['detected']++;

        $result['rows'][] = [
            'name' => $this->stringValue($location['nombre_lugar'] ?? null, 255, 'ubicacion.nombre_lugar', $result['warnings']),
            'address' => $this->stringValue($location['direccion'] ?? null, 500, 'ubicacion.direccion', $result['warnings']),
            'latitude' => $this->floatValue($location['lat'] ?? null),
            'longitude' => $this->floatValue($location['lng'] ?? null),
            'map_url' => $this->stringValue($location['maps_url'] ?? null, null, 'ubicacion.maps_url', $result['warnings']),
            'image_url' => $this->stringValue($location['imagen_lugar'] ?? null, null, 'ubicacion.imagen_lugar', $result['warnings']),
            'note' => $this->stringValue($location['nota'] ?? null, null, 'ubicacion.nota', $result['warnings']),
            'meta' => $this->metaValue($location, self::LOCATION_KEYS),
            'sort_order' => 0,
        ];

        return $result;
    }

    /** Cada grupo (chambelanes, damitas, padrinos, cortejo…) es una lista ordenada de personas. */
    public function featuredPeopleRows(mixed $module): array
    {
        $result = $this->emptyResult();

        foreach ($this->arrayValue($module) as $group => $people) {
            if (! is_array($people) || ! array_is_list($people)) {
                continue;
            }

            foreach ($people as $index => $person) {
                $result['detected']++;
                $path = "destacados.{$group}[{$index}]";

                if (! is_array($person)) {
                    $result['skipped'][] = "{$path}: se esperaba un objeto";
                    continue;
                }

                // Los padrinos llegan con "nombres" y el resto con "nombre": se recuerda cuál era
                $nameKey = array_key_exists('nombres', $person) ? 'nombres' : 'nombre';

                $result['rows'][] = [
                    'group' => mb_substr((string) $group, 0, 50),
                    'name_key' => $nameKey,
                    'name' => $this->stringValue($person[$nameKey] ?? null, 255, "{$path}.{$nameKey}", $result['warnings']),
                    'initials' => $this->stringValue($person['iniciales'] ?? null, 10, "{$path}.iniciales", $result['warnings']),
                    'role' => $this->stringValue($person['rol'] ?? null, 255, "{$path}.rol", $result['warnings']),
                    'detail' => $this->stringValue($person['detalle'] ?? null, null, "{$path}.detalle", $result['warnings']),
                    'message' => $this->stringValue($person['mensaje'] ?? null, null, "{$path}.mensaje", $result['warnings']),
                    'meta' => $this->metaValue($person, self::PERSON_KEYS),
                    'sort_order' => count($result['rows']),
                ];
            }
        }

        return $result;
    }

    /** Tres listas en una tabla: sugerencias, colores permitidos y qué evitar. */
    public function dressCodeRows(mixed $module): array
    {
        $result = $this->emptyResult();
        $module = $this->arrayValue($module);

        foreach (array_values($this->listValue($module['sugerencias'] ?? null)) as $index => $item) {
            $result['detected']++;
            $path = "dress_code.sugerencias[{$index}]";

            if (! is_array($item)) {
                $result['skipped'][] = "{$path}: se esperaba un objeto";
                continue;
            }

            $examples = $this->listValue($item['ejemplos'] ?? null);

            $result['rows'][] = [
                'kind' => InvitationDressCodeItem::KIND_SUGGESTION,
                'audience' => $this->stringValue($item['para'] ?? null, 100, "{$path}.para", $result['warnings']),
                'title' => $this->stringValue($item['titulo'] ?? null, 255, "{$path}.titulo", $result['warnings']),
                'description' => $this->stringValue($item['descripcion'] ?? null, null, "{$path}.descripcion", $result['warnings']),
                'image_url' => $this->stringValue($item['imagen'] ?? null, null, "{$path}.imagen", $result['warnings']),
                'color_hex' => null,
                'examples' => $examples === [] ? null : array_map('strval', $examples),
                'meta' => $this->metaValue($item, self::DRESS_SUGGESTION_KEYS),
                'sort_order' => count($result['rows']),
            ];
        }

        foreach (array_values($this->listValue($module['colores_permitidos'] ?? null)) as $index => $color) {
            $result['detected']++;
            $path = "dress_code.colores_permitidos[{$index}]";
            $color = is_array($color) ? $color : ['nombre' => $color];

            $result['rows'][] = [
                'kind' => InvitationDressCodeItem::KIND_COLOR,
                'audience' => null,
                'title' => $this->stringValue($color['nombre'] ?? null, 255, "{$path}.nombre", $result['warnings']),
                'description' => null,
                'image_url' => null,
                'color_hex' => $this->stringValue($color['hex'] ?? null, 20, "{$path}.hex", $result['warnings']),
                'examples' => null,
                'meta' => $this->metaValue($color, self::DRESS_COLOR_KEYS),
                'sort_order' => count($result['rows']),
            ];
        }

        foreach (array_values($this->listValue($module['evitar'] ?? null)) as $index => $avoid) {
            $result['detected']++;
            $path = "dress_code.evitar[{$index}]";

            $result['rows'][] = [
                'kind' => InvitationDressCodeItem::KIND_AVOID,
                'audience' => null,
                'title' => $this->stringValue(is_array($avoid) ? ($avoid['titulo'] ?? null) : $avoid, 255, $path, $result['warnings']),
                'description' => null,
                'image_url' => null,
                'color_hex' => null,
                'examples' => null,
                'meta' => is_array($avoid) ? $this->metaValue($avoid, ['titulo']) : null,
                'sort_order' => count($result['rows']),
            ];
        }

        return $result;
    }

    /** Solo la lista pública de regalos: los datos bancarios siguen en el JSON del módulo. */
    public function giftOptionRows(mixed $module): array
    {
        $result = $this->emptyResult();
        $options = $this->listValue($this->arrayValue($module)['opciones'] ?? null);

        foreach (array_values($options) as $index => $option) {
            $result['detected']++;
            $path = "regalos.opciones[{$index}]";

            if (! is_array($option)) {
                $result['skipped'][] = "{$path}: se esperaba un objeto";
                continue;
            }

            $result['rows'][] = [
                'title' => $this->stringValue($option['titulo'] ?? null, 255, "{$path}.titulo", $result['warnings']),
                'description' => $this->stringValue($option['descripcion'] ?? null, null, "{$path}.descripcion", $result['warnings']),
                'url' => $this->stringValue($option['enlace'] ?? null, null, "{$path}.enlace", $result['warnings']),
                'image_url' => $this->stringValue($option['imagen'] ?? null, null, "{$path}.imagen", $result['warnings']),
                'meta' => $this->metaValue($option, self::GIFT_KEYS),
                'sort_order' => count($result['rows']),
            ];
        }

        return $result;
    }

    /** Catálogo de medios: la canción de fondo y el video, uno por tipo. */
    public function mediaRows(array $modules): array
    {
        $result = $this->emptyResult();
        $audio = $this->arrayValue($modules['musica'] ?? null);
        $video = $this->arrayValue($modules['video'] ?? null);

        if (($audio['audio_url'] ?? '') !== '' || ($audio['titulo'] ?? '') !== '') {
            $result['detected']++;
            $result['rows'][] = [
                'type' => InvitationMedia::TYPE_AUDIO,
                'title' => $this->stringValue($audio['titulo'] ?? null, 255, 'musica.titulo', $result['warnings']),
                'url' => $this->stringValue($audio['audio_url'] ?? null, null, 'musica.audio_url', $result['warnings']),
                'poster_url' => null,
                'autoplay' => (bool) ($audio['autoplay'] ?? false),
                'status' => 'active',
                'meta' => $this->metaValue($audio, self::AUDIO_KEYS),
                'sort_order' => count($result['rows']),
            ];
        }

        if (($video['video_url'] ?? '') !== '' || ($video['titulo'] ?? '') !== '') {
            $result['detected']++;
            $result['rows'][] = [
                'type' => InvitationMedia::TYPE_VIDEO,
                'title' => $this->stringValue($video['titulo'] ?? null, 255, 'video.titulo', $result['warnings']),
                'url' => $this->stringValue($video['video_url'] ?? null, null, 'video.video_url', $result['warnings']),
                'poster_url' => $this->stringValue($video['poster'] ?? null, null, 'video.poster', $result['warnings']),
                'autoplay' => false,
                'status' => 'active',
                'meta' => $this->metaValue($video, self::VIDEO_KEYS),
                'sort_order' => count($result['rows']),
            ];
        }

        return $result;
    }

    /** Las fotos posteriores al evento usan la misma tabla de galería, en otra colección. */
    public function postEventGalleryRows(mixed $module): array
    {
        $result = $this->galleryRows(['fotos' => $this->arrayValue($module)['fotos'] ?? []]);

        foreach ($result['rows'] as $index => $row) {
            $result['rows'][$index]['collection'] = InvitationGalleryImage::COLLECTION_POST_EVENT;
        }

        $result['skipped'] = array_map(
            fn (string $message) => str_replace('galeria.fotos', 'post_evento.fotos', $message),
            $result['skipped']
        );

        return $result;
    }

    protected function locationValue(Invitation $invitation): ?array
    {
        $location = $invitation->locations->first();

        if (! $location instanceof InvitationLocation) {
            return null;
        }

        return array_filter(array_merge($location->meta ?? [], [
            'lat' => $location->latitude,
            'lng' => $location->longitude,
            'nombre_lugar' => $location->name,
            'direccion' => $location->address,
            'maps_url' => $location->map_url,
            'nota' => $location->note,
            'imagen_lugar' => $location->image_url,
        ]), fn ($value) => $value !== null);
    }

    /** @return array<string, list<array<string, mixed>>> */
    protected function featuredPeopleValues(Invitation $invitation): array
    {
        $groups = [];

        foreach ($invitation->featuredPeople as $person) {
            $groups[$person->group][] = array_filter(array_merge($person->meta ?? [], [
                $person->name_key => $person->name,
                'iniciales' => $person->initials,
                'rol' => $person->role,
                'detalle' => $person->detail,
                'mensaje' => $person->message,
            ]), fn ($value) => $value !== null);
        }

        return $groups;
    }

    protected function dressCodeValues(Invitation $invitation): array
    {
        $items = $invitation->dressCodeItems;

        return [
            'sugerencias' => $items
                ->where('kind', InvitationDressCodeItem::KIND_SUGGESTION)
                ->map(fn (InvitationDressCodeItem $item) => array_filter(array_merge($item->meta ?? [], [
                    'para' => $item->audience,
                    'titulo' => $item->title,
                    'descripcion' => $item->description,
                    'ejemplos' => $item->examples,
                    'imagen' => $item->image_url,
                ]), fn ($value) => $value !== null))
                ->values()
                ->all(),
            'colores_permitidos' => $items
                ->where('kind', InvitationDressCodeItem::KIND_COLOR)
                ->map(fn (InvitationDressCodeItem $item) => array_filter(array_merge($item->meta ?? [], [
                    'nombre' => $item->title,
                    'hex' => $item->color_hex,
                ]), fn ($value) => $value !== null))
                ->values()
                ->all(),
            'evitar' => $items
                ->where('kind', InvitationDressCodeItem::KIND_AVOID)
                ->map(fn (InvitationDressCodeItem $item) => $item->title)
                ->values()
                ->all(),
        ];
    }

    /** @return list<array<string, mixed>> */
    protected function giftOptionValues(Invitation $invitation): array
    {
        return $invitation->giftOptions
            ->map(fn (InvitationGiftOption $option) => array_filter(array_merge($option->meta ?? [], [
                'titulo' => $option->title,
                'descripcion' => $option->description,
                'enlace' => $option->url,
                'imagen' => $option->image_url,
            ]), fn ($value) => $value !== null))
            ->values()
            ->all();
    }

    /** @return array<string, array<string, mixed>> */
    protected function mediaValues(Invitation $invitation): array
    {
        $values = [];

        foreach ($invitation->media as $media) {
            if ($media->type === InvitationMedia::TYPE_AUDIO) {
                $values['musica'] = array_filter(array_merge($media->meta ?? [], [
                    'titulo' => $media->title,
                    'audio_url' => $media->url,
                    'autoplay' => $media->autoplay,
                ]), fn ($value) => $value !== null);
                continue;
            }

            if ($media->type === InvitationMedia::TYPE_VIDEO) {
                $values['video'] = array_filter(array_merge($media->meta ?? [], [
                    'titulo' => $media->title,
                    'video_url' => $media->url,
                    'poster' => $media->poster_url,
                ]), fn ($value) => $value !== null);
            }
        }

        return $values;
    }

    protected function floatValue(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    /** @return array<int|string, mixed> */
    protected function listValue(mixed $value): array
    {
        return is_array($value) ? $value : [];
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

            $this->replaceOrdered($poll->options(), array_map(
                fn (string $label, int $index) => ['label' => $label, 'sort_order' => $index],
                $row['options'],
                array_keys($row['options'])
            ));

            $keptIds[] = $poll->id;
        }

        $invitation->polls()->whereNotIn('id', $keptIds)->delete();
    }

    /**
     * Guarda una lista ordenada reutilizando las filas que ya existen: actualiza las primeras,
     * crea las que faltan y borra las que sobran. Así no cambian los ids (nada que dependa de
     * ellos se rompe) ni se llenan los timestamps de cambios que no ocurrieron.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\HasMany<\Illuminate\Database\Eloquent\Model>  $relation
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, mixed>  $defaults  Valores que identifican al grupo (por ejemplo, la colección)
     */
    protected function replaceOrdered($relation, array $rows, array $defaults = []): void
    {
        $existing = (clone $relation)->orderBy('sort_order')->orderBy('id')->get();
        $rows = array_values($rows);

        foreach ($rows as $index => $row) {
            $current = $existing[$index] ?? null;

            if ($current) {
                $current->fill($row + $defaults)->save();
                continue;
            }

            $relation->create($row + $defaults);
        }

        $extra = $existing->slice(count($rows))->pluck('id');

        if ($extra->isNotEmpty()) {
            (clone $relation)->whereIn($existing->first()->getTable().'.id', $extra->all())->delete();
        }
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
