<?php

namespace App\Support;

/**
 * Genera variantes responsivas (f_auto, q_auto, ancho) de imágenes alojadas en Cloudinary.
 * Cualquier otra URL (almacenamiento local, data URLs, otros CDN) se devuelve sin cambios.
 */
class CloudinaryImage
{
    public const WIDTHS = [480, 768, 1200];

    // Parámetros de transformación de Cloudinary; evita confundir carpetas con transformaciones
    private const TRANSFORMATION_SEGMENT = '/^(?:w|h|c|q|f|g|e|ar|dpr|fl|t|b|o|r|a|x|y|z|bo|co|l|u|d)_[^,\/]+(?:,(?:w|h|c|q|f|g|e|ar|dpr|fl|t|b|o|r|a|x|y|z|bo|co|l|u|d)_[^,\/]+)*$/';

    public static function isCloudinary(mixed $url): bool
    {
        return is_string($url)
            && str_contains($url, 'res.cloudinary.com/')
            && str_contains($url, '/image/upload/');
    }

    public static function url(mixed $url, int $width): mixed
    {
        if (! self::isCloudinary($url)) {
            return $url;
        }

        [$base, $path] = explode('/image/upload/', $url, 2);
        $segments = explode('/', $path);

        // Sustituye las transformaciones aplicadas al subir en lugar de encadenarlas
        while (count($segments) > 1 && preg_match(self::TRANSFORMATION_SEGMENT, $segments[0])) {
            array_shift($segments);
        }

        return "{$base}/image/upload/f_auto,q_auto,c_limit,w_{$width}/".implode('/', $segments);
    }

    /**
     * Recorte exacto para compartir (Open Graph): llena el tamaño con el recorte inteligente
     * de Cloudinary y fuerza JPG, porque WhatsApp no siempre muestra WebP ni AVIF.
     */
    public static function card(string $url, int $width, int $height): string
    {
        if (! self::isCloudinary($url)) {
            return $url;
        }

        [$base, $path] = explode('/image/upload/', $url, 2);
        $segments = explode('/', $path);

        while (count($segments) > 1 && preg_match(self::TRANSFORMATION_SEGMENT, $segments[0])) {
            array_shift($segments);
        }

        return "{$base}/image/upload/c_fill,g_auto,w_{$width},h_{$height},q_auto,f_jpg/".implode('/', $segments);
    }

    public static function srcset(mixed $url, array $widths = self::WIDTHS): ?string
    {
        if (! self::isCloudinary($url)) {
            return null;
        }

        return implode(', ', array_map(fn (int $width) => self::url($url, $width)." {$width}w", $widths));
    }
}
