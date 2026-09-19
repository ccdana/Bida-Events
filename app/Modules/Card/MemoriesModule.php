<?php

namespace App\Modules\Card;

use App\Models\CardEntry;
use App\Models\Invitation;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\StoresCardEntries;
use App\Modules\Module;
use App\Support\InvitationModuleRules;

/** «Recuerdos especiales»: fotos con su título, fecha y una nota corta. */
class MemoriesModule extends Module
{
    use HasSectionTexts, StoresCardEntries;

    /** La nota va al lado de la foto, en media hoja. */
    public const BODY_LIMIT = 220;

    public function code(): string
    {
        return 'recuerdos';
    }

    public function label(): string
    {
        return 'Recuerdos especiales';
    }

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): array
    {
        return ['recuerdos' => []];
    }

    public function relations(): array
    {
        return ['cardEntries', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        return $this->compact([
            'titulo' => $this->section($invitation)?->title,
            'recuerdos' => $this->entries($invitation, CardEntry::SECTION_MEMORIES),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->saveEntries($invitation, CardEntry::SECTION_MEMORIES, $data['recuerdos'] ?? null, self::BODY_LIMIT);
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['recuerdos'] ?? []);
    }

    public function rules(string $prefix): array
    {
        return ["{$prefix}.recuerdos.titulo" => ['nullable', 'string', 'max:255']]
            + $this->entryRules($prefix, 'recuerdos', 12, self::BODY_LIMIT, InvitationModuleRules::urlRules());
    }

    public function attributes(string $prefix): array
    {
        return [
            "{$prefix}.recuerdos.recuerdos" => 'recuerdos',
            "{$prefix}.recuerdos.recuerdos.*.titulo" => 'título del recuerdo',
            "{$prefix}.recuerdos.recuerdos.*.texto" => 'nota del recuerdo',
            "{$prefix}.recuerdos.recuerdos.*.foto" => 'foto del recuerdo',
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.recuerdos';
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.recuerdos';
    }
}
