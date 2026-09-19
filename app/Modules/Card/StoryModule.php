<?php

namespace App\Modules\Card;

use App\Models\CardEntry;
use App\Models\Invitation;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\StoresCardEntries;
use App\Modules\Module;
use App\Support\InvitationModuleRules;

/** «Nuestra historia»: capítulos con fecha, texto y foto. Un capítulo largo ocupa varias páginas. */
class StoryModule extends Module
{
    use HasSectionTexts, StoresCardEntries;

    public const BODY_LIMIT = 5000;

    public function code(): string
    {
        return 'historia';
    }

    public function label(): string
    {
        return 'Nuestra historia';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): array
    {
        return ['capitulos' => []];
    }

    public function relations(): array
    {
        return ['cardEntries', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        return $this->compact([
            'titulo' => $this->section($invitation)?->title,
            'capitulos' => $this->entries($invitation, CardEntry::SECTION_STORY),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->saveEntries($invitation, CardEntry::SECTION_STORY, $data['capitulos'] ?? null, self::BODY_LIMIT);
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['capitulos'] ?? []);
    }

    public function rules(string $prefix): array
    {
        return ["{$prefix}.historia.titulo" => ['nullable', 'string', 'max:255']]
            + $this->entryRules($prefix, 'capitulos', 12, self::BODY_LIMIT, InvitationModuleRules::urlRules());
    }

    public function attributes(string $prefix): array
    {
        return [
            "{$prefix}.historia.capitulos" => 'capítulos',
            "{$prefix}.historia.capitulos.*.titulo" => 'título del capítulo',
            "{$prefix}.historia.capitulos.*.texto" => 'texto del capítulo',
            "{$prefix}.historia.capitulos.*.foto" => 'foto del capítulo',
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.historia';
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.historia';
    }
}
