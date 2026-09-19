<?php

namespace App\Modules\Card;

use App\Models\Invitation;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\StoresGalleryPhotos;
use App\Modules\Module;
use App\Support\InvitationModuleRules;

/**
 * Páginas de fotos de la tarjeta tipo cuaderno (collage, marcos, juego de memoria): un título y
 * una lista de fotos en invitation_gallery_images, cada módulo con su colección.
 */
abstract class PhotoCollectionModule extends Module
{
    use HasSectionTexts, ReadsValues, StoresGalleryPhotos;

    /** Colección de invitation_gallery_images donde viven sus fotos. */
    abstract protected function collection(): string;

    /** Cuántas fotos admite. */
    abstract protected function maxPhotos(): int;

    public function kinds(): array
    {
        return [self::KIND_CARD];
    }

    public function defaults(): array
    {
        return ['fotos' => []];
    }

    public function relations(): array
    {
        return ['galleryImages', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        return $this->compact([
            'titulo' => $this->section($invitation)?->title,
            'fotos' => $this->photos($invitation, $this->collection()),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->savePhotos($invitation, $this->collection(), $data['fotos'] ?? null);
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['fotos'] ?? []);
    }

    public function rules(string $prefix): array
    {
        $field = "{$prefix}.{$this->code()}";

        return [
            "{$field}.titulo" => ['nullable', 'string', 'max:255'],
            "{$field}.fotos" => ['nullable', 'array', 'max:'.$this->maxPhotos()],
            "{$field}.fotos.*" => InvitationModuleRules::photoEntry(InvitationModuleRules::urlRules()),
            "{$field}.fotos.*.alt" => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(string $prefix): array
    {
        return [
            "{$prefix}.{$this->code()}.fotos" => 'fotos',
            "{$prefix}.{$this->code()}.fotos.*.alt" => 'descripción de la foto',
        ];
    }

    public function partial(): string
    {
        return 'invitations.partials.modules.'.$this->code();
    }

    public function panel(): string
    {
        return 'admin.invitations.panels.modules.'.$this->code();
    }
}
