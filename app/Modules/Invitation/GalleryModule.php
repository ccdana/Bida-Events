<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationGalleryImage;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\StoresGalleryPhotos;
use App\Modules\Module;

/** Galería de fotos que se deslizan. Las fotos del post evento usan la misma tabla (PostEventModule). */
class GalleryModule extends Module
{
    use HasSectionTexts, ReadsValues, StoresGalleryPhotos;

    public function code(): string
    {
        return 'galeria';
    }

    public function label(): string
    {
        return 'Galería';
    }

    public function kinds(): array
    {
        return [self::KIND_INVITATION, self::KIND_CARD];
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
            'fotos' => $this->photos($invitation, InvitationGalleryImage::COLLECTION_GALLERY),
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->savePhotos($invitation, InvitationGalleryImage::COLLECTION_GALLERY, $data['fotos'] ?? null);
        $this->saveSection($invitation, ['title' => $this->text($data['titulo'] ?? null, 255)]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['fotos'] ?? []);
    }
}
