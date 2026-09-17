<?php

namespace App\Support;

use App\Modules\Module;
use Illuminate\Support\Str;

/**
 * Lo que se ve al pegar un enlace en WhatsApp o Facebook: título, descripción e imagen.
 *
 * WhatsApp lee las etiquetas Open Graph; la imagen debe ser pública, de 1200×630 y en
 * JPG o PNG. Las vistas pintan el resultado con layouts/partials/share-meta.
 */
final class ShareMeta
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    /**
     * @return array{title: string, description: string, image: string, url: string, type: string, site: string}
     */
    public static function make(string $title, string $description, string $image, string $url, string $type = 'website'): array
    {
        return [
            'title' => Str::limit(trim($title), 90, '…'),
            'description' => Str::limit(trim(preg_replace('/\s+/', ' ', $description)), 180, '…'),
            'image' => $image,
            'url' => $url,
            'type' => $type,
            'site' => (string) config('bida.brand'),
        ];
    }

    /** Tarjeta de la portada o de una página por evento, ya recortada en public/images/share. */
    public static function siteImage(string $name): string
    {
        $path = "images/share/{$name}.jpg";

        return is_file(public_path($path)) ? asset($path) : asset('images/share/inicio.jpg');
    }

    /**
     * La invitación se presenta con el nombre del festejado, la fecha y el lugar. En el enlace
     * personal el título nombra al invitado, que es quien recibe el mensaje; en la muestra de la
     * home no, porque su invitado es ficticio.
     */
    public static function forInvitation(InvitationPage $page, string $url, bool $personal = true): array
    {
        $eyebrow = ($page->welcome['subtitulo'] ?? null)
            ?: ($page->copy['hero_eyebrow'] ?? null)
            ?: 'Mis XV años';

        $guest = $personal ? $page->guest : null;

        if ($page->profile->kind() === Module::KIND_CARD) {
            return self::forCard($page, $url, $eyebrow, $guest?->name);
        }

        // Neutro en persona: sirve igual para «Mis XV años» que para «Nos casamos»
        $title = $guest
            ? "{$page->displayName} · Invitación para {$guest->name}"
            : "{$page->displayName} · {$eyebrow}";

        $description = collect([$guest ? $eyebrow : null, $page->eventLabel, $page->placeName])->filter()->implode(' · ');

        return self::make(
            $title,
            $description,
            self::invitationImage($page),
            $url,
            'article',
        );
    }

    /**
     * Una tarjeta se presenta como carta: «Para Ana, de Luis» y la frase de la temporada. No lleva
     * fecha ni lugar, y el mensaje queda para quien la abre.
     */
    private static function forCard(InvitationPage $page, string $url, string $phrase, ?string $guestName): array
    {
        $to = trim((string) ($page->dedication['para'] ?? '')) ?: $guestName;
        $from = trim((string) ($page->dedication['de'] ?? ''));

        $title = collect([$to ? "Para {$to}" : null, $from !== '' ? "de {$from}" : null])->filter()->implode(', ')
            ?: $page->displayName;

        return self::make($title, $phrase.' · Abre la carta', self::invitationImage($page), $url, 'article');
    }

    /** La foto de portada recortada por Cloudinary; sin foto, la tarjeta del tipo de evento. */
    private static function invitationImage(InvitationPage $page): string
    {
        $hero = $page->heroImage;

        if (CloudinaryImage::isCloudinary($hero)) {
            return CloudinaryImage::card($hero, self::WIDTH, self::HEIGHT);
        }

        // Fotos en el almacenamiento local: sirven si la dirección es absoluta
        if (is_string($hero) && $hero !== '' && ! str_starts_with($hero, 'blob:') && ! str_starts_with($hero, 'data:')) {
            return url($hero);
        }

        return self::siteImage($page->eventKey);
    }
}
