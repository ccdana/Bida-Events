<?php

namespace App\Modules\Invitation;

use App\Models\Invitation;
use App\Models\InvitationGalleryImage;
use App\Modules\Concerns\HasSectionTexts;
use App\Modules\Concerns\ReadsValues;
use App\Modules\Concerns\StoresGalleryPhotos;
use App\Modules\Module;

/** Fotos oficiales después del evento, con título, descripción y enlace a la galería completa. */
class PostEventModule extends Module
{
    use HasSectionTexts, ReadsValues, StoresGalleryPhotos;

    public function code(): string
    {
        return 'post_evento';
    }

    public function label(): string
    {
        return 'Post evento';
    }

    public function defaults(): object
    {
        return (object) [];
    }

    public function relations(): array
    {
        return ['galleryImages', 'sections.feature'];
    }

    public function load(Invitation $invitation): array
    {
        $section = $this->section($invitation);
        $photos = $this->photos($invitation, InvitationGalleryImage::COLLECTION_POST_EVENT);

        return $this->compact([
            'titulo' => $section?->title,
            'descripcion' => $section?->intro,
            'enlace_externo' => $section?->cta_url,
            'fotos' => $photos === [] && ! $section ? null : $photos,
        ]);
    }

    public function save(Invitation $invitation, array $data): void
    {
        $this->savePhotos($invitation, InvitationGalleryImage::COLLECTION_POST_EVENT, $data['fotos'] ?? null);
        $this->saveSection($invitation, [
            'title' => $this->text($data['titulo'] ?? null, 255),
            'intro' => $this->text($data['descripcion'] ?? null),
            'cta_url' => $this->text($data['enlace_externo'] ?? null),
        ]);
    }

    public function hasContent(array $data): bool
    {
        return $this->filled($data['titulo'] ?? null)
            || $this->filled($data['descripcion'] ?? null)
            || $this->filled($data['enlace_externo'] ?? null)
            || $this->filled($data['fotos'] ?? []);
    }
}
