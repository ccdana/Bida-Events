<?php

namespace App\Modules\Card;

use App\Models\CardEntry;
use App\Models\Invitation;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\StoresCardEntries;
use App\Modules\Module;

/** «Aventuras por vivir»: la lista de lo que la pareja todavía quiere hacer junta. */
class AdventuresModule extends Module
{
    use HasSectionTexts, StoresCardEntries;

    public const MAX_ITEMS = 15;

    public const ITEM_LIMIT = 120;

    public function code(): string
    {
        return 'aventuras';
    }

    public function label(): string
    {
        return 'Aventuras por vivir';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): array
    {
        return ['lista' => []];
    }

    public function relations(): array
    {
        return ['cardEntries', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        return $this->compact([
            'titulo' => $this->section($invitation)?->title,
            // Cada aventura es solo su texto
            'lista' => array_map(
                fn (array $entry) => $this->compact(['titulo' => $entry['titulo'] ?? null]),
                $this->entries($invitation, CardEntry::SECTION_ADVENTURES),
            ),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $items = array_map(
            fn ($item) => ['titulo' => is_array($item) ? $this->text($item['titulo'] ?? null, self::ITEM_LIMIT) : null],
            $this->list($data['lista'] ?? null),
        );

        $this->saveEntries($invitation, CardEntry::SECTION_ADVENTURES, $items, self::ITEM_LIMIT);
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['lista'] ?? []);
    }

    public function rules(string $prefix): array
    {
        return [
            "{$prefix}.aventuras.titulo" => ['nullable', 'string', 'max:255'],
            "{$prefix}.aventuras.lista" => ['nullable', 'array', 'max:'.self::MAX_ITEMS],
            "{$prefix}.aventuras.lista.*" => ['array'],
            "{$prefix}.aventuras.lista.*.titulo" => ['nullable', 'string', 'max:'.self::ITEM_LIMIT],
        ];
    }

    public function attributes(string $prefix): array
    {
        return [
            "{$prefix}.aventuras.lista" => 'aventuras por vivir',
            "{$prefix}.aventuras.lista.*.titulo" => 'aventura',
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.aventuras';
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.aventuras';
    }
}
