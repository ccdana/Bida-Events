<?php

namespace App\Support;

/**
 * Fotos del sitio público definidas en config('bida.images').
 *
 * Usa el archivo local si ya fue agregado a public/ y, mientras tanto, un
 * marcador en blanco y negro de picsum con las mismas proporciones.
 */
class SiteImage
{
    public static function url(string $key): string
    {
        $image = self::get($key);

        $file = public_path($image['path']);

        // ?v= con la fecha del archivo: al cambiar una foto nadie sigue viendo la vieja por la caché
        if (is_file($file)) {
            return asset($image['path']).'?v='.filemtime($file);
        }

        [$width, $height] = $image['size'];

        return "https://picsum.photos/seed/bida-{$key}/{$width}/{$height}?grayscale";
    }

    /** @return array{0: int, 1: int} */
    public static function size(string $key): array
    {
        return self::get($key)['size'];
    }

    public static function alt(string $key): string
    {
        return self::get($key)['alt'] ?? '';
    }

    /** @return array{path: string, size: array{0: int, 1: int}, stock?: int, alt?: string} */
    private static function get(string $key): array
    {
        $image = config("bida.images.{$key}");

        if (! is_array($image)) {
            throw new \InvalidArgumentException("La imagen [{$key}] no está definida en config/bida.php.");
        }

        return $image;
    }
}
