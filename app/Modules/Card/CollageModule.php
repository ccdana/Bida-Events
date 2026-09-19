<?php

namespace App\Modules\Card;

use App\Models\InvitationGalleryImage;

/** Collage de fotos con formas (corazón, girasol, fotomatón…) y flores amarillas. */
class CollageModule extends PhotoCollectionModule
{
    public function code(): string
    {
        return 'collage';
    }

    public function label(): string
    {
        return 'Collage de fotos';
    }

    protected function collection(): string
    {
        return InvitationGalleryImage::COLLECTION_COLLAGE;
    }

    protected function maxPhotos(): int
    {
        return 30;
    }
}
