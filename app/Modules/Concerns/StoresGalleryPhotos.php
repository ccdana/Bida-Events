<?php

namespace App\Modules\Concerns;

use App\Models\Invitation;
use App\Models\InvitationGalleryImage;

/**
 * Fotos en invitation_gallery_images, separadas por colección (galería, post evento…).
 * Cada foto viaja como su URL o como {url, alt} cuando tiene descripción.
 */
trait StoresGalleryPhotos
{
    use ReplacesOrderedRows;

    /** @return list<string|array{url: string, alt: string}> */
    protected function photos(Invitation $invitation, string $collection): array
    {
        return $invitation->galleryImages
            ->where('collection', $collection)
            ->map(fn (InvitationGalleryImage $image) => $image->alt_text === null
                ? $image->url
                : ['url' => $image->url, 'alt' => $image->alt_text])
            ->values()
            ->all();
    }

    protected function savePhotos(Invitation $invitation, string $collection, mixed $photos): void
    {
        $rows = [];

        foreach (is_array($photos) ? array_values($photos) : [] as $photo) {
            $url = is_array($photo) ? ($photo['url'] ?? null) : $photo;

            if (! is_string($url) || trim($url) === '') {
                continue;
            }

            $alt = is_array($photo) && is_string($photo['alt'] ?? null) && trim($photo['alt']) !== ''
                ? mb_substr(trim($photo['alt']), 0, 255)
                : null;

            $rows[] = ['url' => $url, 'media_type' => 'image', 'alt_text' => $alt, 'is_cover' => false, 'status' => 'active'];
        }

        $this->replaceOrdered(
            $invitation->galleryImages()->where('collection', $collection),
            $rows,
            ['collection' => $collection],
        );
    }
}
