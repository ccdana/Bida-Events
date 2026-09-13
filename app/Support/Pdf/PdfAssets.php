<?php

namespace App\Support\Pdf;

use App\Support\CloudinaryImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

/**
 * Recursos embebidos para los PDF. DomPDF no descarga archivos remotos, así que
 * las fotos se recortan a JPEG, los QR se generan en SVG y las fuentes de la
 * plantilla se guardan en storage/app/pdf-cache para no repetir descargas.
 */
class PdfAssets
{
    private const TIMEOUT = 6;

    private readonly string $cacheRoot;

    public function __construct(?string $cacheRoot = null)
    {
        $this->cacheRoot = $cacheRoot ?? storage_path('app/pdf-cache');
    }

    /** Foto recortada (o encajada con $contain) al tamaño pedido, como data URI JPEG. */
    public function photo(?string $url, int $width, int $height, float $focusY = 0.4, bool $contain = false): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $cached = $this->cachePath('img', implode('|', [$url, $width, $height, $focusY, (int) $contain]), 'jpg');

        if (! is_file($cached)) {
            $source = CloudinaryImage::isCloudinary($url) ? CloudinaryImage::url($url, max($width, 1200)) : $url;
            $bytes = $this->read($source);

            if ($bytes === null || ! $this->writeJpeg($bytes, $cached, $width, $height, $focusY, $contain)) {
                return null;
            }
        }

        return 'data:image/jpeg;base64,'.base64_encode((string) file_get_contents($cached));
    }

    /** Código QR en SVG (vectorial, se imprime nítido). */
    public function qr(?string $content, string $color = '#1d1e20'): ?string
    {
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        [$red, $green, $blue] = sscanf(ltrim($color, '#'), '%02x%02x%02x');

        $svg = (string) QrCode::format('svg')
            ->size(300)
            ->margin(0)
            ->errorCorrection('M')
            ->color($red, $green, $blue)
            ->generate($content);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Ruta local de una fuente de Google Fonts en formato estático (TTF).
     * Devuelve null si no existe en ese formato o no se pudo descargar; el PDF usa entonces una serif.
     */
    public function googleFont(?string $family): ?string
    {
        if (! is_string($family) || ! preg_match('/^[A-Za-z0-9 ]{2,60}$/', $family)) {
            return null;
        }

        $path = $this->cachePath('fonts', $family, 'ttf');
        $missing = "{$path}.missing";

        // DomPDF registra las fuentes externas en esta carpeta y no la crea por su cuenta
        File::ensureDirectoryExists(config('dompdf.options.font_dir', storage_path('fonts')));

        if (is_file($path)) {
            return $path;
        }

        if (is_file($missing) && filemtime($missing) > now()->subDays(7)->getTimestamp()) {
            return null;
        }

        $compact = str_replace(' ', '', $family);
        $bytes = $this->read('https://raw.githubusercontent.com/google/fonts/main/ofl/'.strtolower($compact)."/{$compact}-Regular.ttf");
        $isTrueType = $bytes !== null && (str_starts_with($bytes, "\x00\x01\x00\x00") || str_starts_with($bytes, 'true'));

        File::ensureDirectoryExists(dirname($path));

        if (! $isTrueType) {
            File::put($missing, '');

            return null;
        }

        File::put($path, $bytes);

        return $path;
    }

    /**
     * Isotipo de Bida Events para PDF. En lugar de una máscara (no soportada por DomPDF),
     * la separación entre la solapa y el cuerpo se dibuja con un trazo del color del papel.
     */
    public function logo(string $color = '#b8902e', string $paper = '#ffffff'): string
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="355 128 544 966" width="544" height="966">
<g fill="none" stroke="{$color}" stroke-width="44" stroke-linecap="round" stroke-linejoin="round">
<path d="M419 357 L419 992 Q419 1070 497 1070 L757 1070 Q835 1070 835 992 L835 357"/>
</g>
<path d="M380 322.5 L627 541 L874 322.5" fill="none" stroke="{$paper}" stroke-width="92"/>
<g fill="none" stroke="{$color}" stroke-width="44" stroke-linecap="round" stroke-linejoin="round">
<path d="M419 357 L419 230 Q419 152 497 152 L757 152 Q835 152 835 230 L835 357 L627 541 Z"/>
<path d="M581 216 L674 216"/>
<path d="M377 460 L377 614 M877 460 L877 544"/>
</g>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    private function read(string $url): ?string
    {
        try {
            if (str_starts_with($url, 'data:')) {
                $decoded = base64_decode(substr($url, (int) strpos($url, ',') + 1), true);

                return $decoded === false ? null : $decoded;
            }

            // Archivos subidos al almacenamiento local del propio sitio
            $appUrl = rtrim((string) config('app.url'), '/');
            $isLocal = ! preg_match('#^https?://#i', $url) || ($appUrl !== '' && str_starts_with($url, "{$appUrl}/"));

            if ($isLocal) {
                $path = public_path(ltrim((string) parse_url($url, PHP_URL_PATH), '/'));

                return is_file($path) ? (string) file_get_contents($path) : null;
            }

            $response = Http::timeout(self::TIMEOUT)->get($url);

            return $response->successful() ? $response->body() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function writeJpeg(string $bytes, string $target, int $width, int $height, float $focusY, bool $contain): bool
    {
        $source = @imagecreatefromstring($bytes);

        if ($source === false) {
            return false;
        }

        [$sourceWidth, $sourceHeight] = [imagesx($source), imagesy($source)];
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));

        if ($contain) {
            $scale = min($width / $sourceWidth, $height / $sourceHeight);
            $targetWidth = (int) round($sourceWidth * $scale);
            $targetHeight = (int) round($sourceHeight * $scale);
            imagecopyresampled($canvas, $source, (int) (($width - $targetWidth) / 2), (int) (($height - $targetHeight) / 2), 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        } else {
            $ratio = $width / $height;
            $cropWidth = min($sourceWidth, (int) round($sourceHeight * $ratio));
            $cropHeight = min($sourceHeight, (int) round($cropWidth / $ratio));
            $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
            $cropY = (int) max(0, min($sourceHeight - $cropHeight, round($sourceHeight * $focusY - $cropHeight / 2)));
            imagecopyresampled($canvas, $source, 0, 0, $cropX, $cropY, $width, $height, $cropWidth, $cropHeight);
        }

        File::ensureDirectoryExists(dirname($target));
        $written = imagejpeg($canvas, $target, 82);
        imagedestroy($source);
        imagedestroy($canvas);

        return $written;
    }

    private function cachePath(string $type, string $key, string $extension): string
    {
        return "{$this->cacheRoot}/{$type}/".md5($key).".{$extension}";
    }
}
