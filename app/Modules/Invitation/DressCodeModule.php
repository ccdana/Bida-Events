<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationDressCodeItem;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;

/**
 * Código de vestimenta: sugerencias (con sus ejemplos en otra tabla), colores permitidos y qué
 * evitar. El título, el estilo y la descripción van en la sección.
 */
class DressCodeModule extends Module
{
    use HasSectionTexts, ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'dress_code';
    }

    public function label(): string
    {
        return 'Dress code';
    }

    public function defaults(): array
    {
        return ['sugerencias' => [], 'colores_permitidos' => [], 'evitar' => []];
    }

    public function relations(): array
    {
        return ['dressCodeItems.examples', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        $items = $invitation->dressCodeItems;
        $section = $this->section($invitation);

        return $this->compact([
            'titulo' => $section?->title,
            'estilo' => $section?->subtitle,
            'descripcion' => $section?->intro,
            'sugerencias' => $items
                ->where('kind', InvitationDressCodeItem::KIND_SUGGESTION)
                ->map(fn (InvitationDressCodeItem $item) => $this->compact([
                    'para' => $item->audience,
                    'titulo' => $item->title,
                    'descripcion' => $item->description,
                    'ejemplos' => $item->examples->isEmpty() ? null : $item->examples->pluck('text')->all(),
                    'imagen' => $item->image_url,
                ]))
                ->values()
                ->all(),
            'colores_permitidos' => $items
                ->where('kind', InvitationDressCodeItem::KIND_COLOR)
                ->map(fn (InvitationDressCodeItem $item) => $this->compact(['nombre' => $item->title, 'hex' => $item->color_hex]))
                ->values()
                ->all(),
            'evitar' => $items
                ->where('kind', InvitationDressCodeItem::KIND_AVOID)
                ->map(fn (InvitationDressCodeItem $item) => $item->title)
                ->values()
                ->all(),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $rows = [];
        $examples = [];

        foreach ($this->list($data['sugerencias'] ?? null) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $examples[count($rows)] = array_values(array_filter(
                array_map(fn ($example) => $this->text($example, 255), $this->list($item['ejemplos'] ?? null)),
            ));

            $rows[] = $this->row(InvitationDressCodeItem::KIND_SUGGESTION, [
                'audience' => $this->text($item['para'] ?? null, 100),
                'title' => $this->text($item['titulo'] ?? null, 255),
                'description' => $this->text($item['descripcion'] ?? null),
                'image_url' => $this->text($item['imagen'] ?? null),
            ]);
        }

        foreach ($this->list($data['colores_permitidos'] ?? null) as $color) {
            $color = is_array($color) ? $color : ['nombre' => $color];

            $rows[] = $this->row(InvitationDressCodeItem::KIND_COLOR, [
                'title' => $this->text($color['nombre'] ?? null, 255),
                'color_hex' => $this->text($color['hex'] ?? null, 20),
            ]);
        }

        foreach ($this->list($data['evitar'] ?? null) as $avoid) {
            $rows[] = $this->row(InvitationDressCodeItem::KIND_AVOID, [
                'title' => $this->text(is_array($avoid) ? ($avoid['titulo'] ?? null) : $avoid, 255),
            ]);
        }

        $saved = $this->replaceOrdered($invitation->dressCodeItems(), $rows);

        foreach ($saved as $index => $item) {
            $this->replaceOrdered(
                $item->examples(),
                array_map(fn (string $text) => ['text' => $text], $examples[$index] ?? []),
            );
        }

        $this->saveSection($invitation, [
            'title' => $this->text($data['titulo'] ?? null, 255),
            'subtitle' => $this->text($data['estilo'] ?? null, 255),
            'intro' => $this->text($data['descripcion'] ?? null),
        ]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['titulo'] ?? null)
            || $this->filled($data['estilo'] ?? null)
            || $this->filled($data['descripcion'] ?? null)
            || $this->filled($data['sugerencias'] ?? []);
    }

    /** Todas las filas llevan todas las columnas: al reutilizar una fila no quedan restos de otro tipo. */
    protected function row(string $kind, array $values): array
    {
        return array_merge([
            'kind' => $kind,
            'audience' => null,
            'title' => null,
            'description' => null,
            'image_url' => null,
            'color_hex' => null,
        ], $values);
    }
}
