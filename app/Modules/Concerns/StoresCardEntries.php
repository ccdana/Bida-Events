<?php

namespace App\Modules\Concerns;

use App\Models\CardEntry;
use App\Models\Invitation;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Entradas escritas de una tarjeta tipo cuaderno (card_entries), separadas por sección.
 * Cada una viaja como {titulo, fecha, texto, foto, alt}; se omiten las claves vacías.
 */
trait StoresCardEntries
{
    use ReadsValues, ReplacesOrderedRows;

    /** @return list<array<string, string>> */
    protected function entries(Invitation $invitation, string $section): array
    {
        return $invitation->cardEntries
            ->where('section', $section)
            ->map(fn (CardEntry $entry) => $this->compact([
                'titulo' => $entry->title,
                'fecha' => $entry->happened_on?->toDateString(),
                'texto' => $entry->body,
                'foto' => $entry->image_url,
                'alt' => $entry->image_alt,
            ]))
            ->values()
            ->all();
    }

    protected function saveEntries(Invitation $invitation, string $section, mixed $entries, int $bodyLimit): void
    {
        $rows = [];

        foreach ($this->list($entries) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $row = [
                'title' => $this->text($entry['titulo'] ?? null, 255),
                'happened_on' => $this->entryDate($entry['fecha'] ?? null),
                'body' => $this->text($entry['texto'] ?? null, $bodyLimit),
                'image_url' => $this->text($entry['foto'] ?? null, 2048),
                'image_alt' => $this->text($entry['alt'] ?? null, 255),
            ];

            // Una entrada sin título, texto ni foto es una fila que quedó vacía en el editor
            if ($row['title'] === null && $row['body'] === null && $row['image_url'] === null) {
                continue;
            }

            $rows[] = $row;
        }

        $this->replaceOrdered(
            $invitation->cardEntries()->where('section', $section),
            $rows,
            ['section' => $section],
        );
    }

    /** Reglas de una lista de entradas bajo "{$prefix}.{code}.{$key}". */
    protected function entryRules(string $prefix, string $key, int $max, int $bodyLimit, array $urlRules): array
    {
        $field = "{$prefix}.{$this->code()}.{$key}";

        return [
            $field => ['nullable', 'array', "max:{$max}"],
            "{$field}.*" => ['array'],
            "{$field}.*.titulo" => ['nullable', 'string', 'max:255'],
            "{$field}.*.fecha" => ['nullable', 'date'],
            "{$field}.*.texto" => ['nullable', 'string', "max:{$bodyLimit}"],
            "{$field}.*.foto" => $urlRules,
            "{$field}.*.alt" => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function entryDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
