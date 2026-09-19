<?php

namespace App\Modules\Card;

use App\Models\InvitationGalleryImage;

/** Fotos con marco (vintage, sello, polaroid…); la descripción de cada foto va de pie escrito a mano. */
class FramesModule extends PhotoCollectionModule
{
    public function code(): string
    {
        return 'marcos';
    }

    public function label(): string
    {
        return 'Fotos con marco';
    }

    protected function collection(): string
    {
        return InvitationGalleryImage::COLLECTION_FRAMES;
    }

    protected function maxPhotos(): int
    {
        return 12;
    }
}
