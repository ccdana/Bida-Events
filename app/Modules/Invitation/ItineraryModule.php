<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationItineraryItem;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\ReplacesOrderedRows;
use App\Modules\Module;

class ItineraryModule extends Module
{
    use HasSectionTexts, ReadsValues, ReplacesOrderedRows;

    public function code(): string
    {
        return 'itinerario';
    }

    public function label(): string
    {
        return 'Itinerario';
    }

    public function defaults(): array
    {
        return ['titulo' => 'Itinerario', 'eventos' => []];
    }

    public function relations(): array
    {
        return ['itineraryItems', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        return $this->compact([
            'titulo' => $this->section($invitation)?->title,
            'eventos' => $invitation->itineraryItems
                ->map(fn (InvitationItineraryItem $item) => [
                    'hora' => $item->time ?? '',
                    'titulo' => $item->title,
                    'icono' => $item->icon ?? 'star',
                    'descripcion' => $item->description ?? '',
                ])
                ->all(),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $rows = [];

        foreach ($this->list($data['eventos'] ?? null) as $event) {
            if (! is_array($event)) {
                continue;
            }

            $rows[] = [
                'time' => $this->text($event['hora'] ?? null, 50),
                'title' => $this->text($event['titulo'] ?? null, 255) ?? '',
                'icon' => $this->text($event['icono'] ?? null, 50),
                'description' => $this->text($event['descripcion'] ?? null),
            ];
        }

        $this->replaceOrdered($invitation->itineraryItems(), $rows);
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['eventos'] ?? []);
    }
}
