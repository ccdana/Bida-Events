<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Tarjetas de 1200×630 para la vista previa al compartir un enlace (WhatsApp, Facebook).
 *
 * Se recortan de las fotos del sitio (config bida.images) y se guardan en JPG, porque
 * WhatsApp no siempre muestra WebP. Se generan una vez y se suben con el resto de public/.
 */
class GenerateShareImages extends Command
{
    protected $signature = 'bida:imagenes-compartir';

    protected $description = 'Genera las imágenes de 1200×630 que se ven al compartir la portada y las páginas por evento';

    private const WIDTH = 1200;

    private const HEIGHT = 630;

    public function handle(): int
    {
        if (! function_exists('imagecreatefromwebp')) {
            $this->components->error('PHP necesita la extensión GD con soporte WebP.');

            return self::FAILURE;
        }

        $directory = public_path('images/share');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        foreach (config('bida.share_images', []) as $name => $card) {
            $source = public_path(config("bida.images.{$card['image']}.path", ''));

            if (! is_file($source)) {
                $this->components->warn("{$name}: falta la foto {$source}");

                continue;
            }

            $this->crop($source, "{$directory}/{$name}.jpg", (float) ($card['focus'] ?? 0.5));
            $this->components->info("{$name}.jpg");
        }

        return self::SUCCESS;
    }

    /** Recorte tipo «cover»: llena 1200×630 y centra en el punto vertical indicado (0 arriba, 1 abajo). */
    private function crop(string $source, string $target, float $focus): void
    {
        $image = imagecreatefromwebp($source);
        $width = imagesx($image);
        $height = imagesy($image);

        $scale = max(self::WIDTH / $width, self::HEIGHT / $height);
        $cropWidth = (int) round(self::WIDTH / $scale);
        $cropHeight = (int) round(self::HEIGHT / $scale);
        $x = (int) round(($width - $cropWidth) / 2);
        $y = (int) round(($height - $cropHeight) * min(1, max(0, $focus)));

        $card = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled($card, $image, 0, 0, $x, $y, self::WIDTH, self::HEIGHT, $cropWidth, $cropHeight);
        imagejpeg($card, $target, 84);

        imagedestroy($image);
        imagedestroy($card);
    }
}
