<?php

namespace App\Console\Commands;

use App\Support\ShareMeta;
use Illuminate\Console\Command;

/**
 * Revisa la imagen que se ve al compartir un enlace (WhatsApp, Facebook).
 *
 * Ya no se recortan tarjetas de las fotos del sitio: todo el sitio comparte el logo de Bida
 * (public/images/share/bida.jpg, 1200×630, se sube con el resto de public/) y cada invitación o
 * tarjeta, la foto de su portada. El comando se mantiene porque el arranque de producción lo
 * llama; solo avisa si falta el logo o si no tiene la medida que piden WhatsApp y Facebook.
 */
class GenerateShareImages extends Command
{
    protected $signature = 'bida:imagenes-compartir';

    protected $description = 'Revisa la imagen de 1200×630 con el logo que se ve al compartir el sitio';

    public function handle(): int
    {
        $path = public_path(ShareMeta::DEFAULT_IMAGE);

        if (! is_file($path)) {
            $this->components->error('Falta '.ShareMeta::DEFAULT_IMAGE.': es la imagen con el logo que se ve al compartir.');

            return self::FAILURE;
        }

        [$width, $height] = getimagesize($path) ?: [0, 0];

        if ([$width, $height] !== [ShareMeta::WIDTH, ShareMeta::HEIGHT]) {
            $this->components->warn(ShareMeta::DEFAULT_IMAGE." mide {$width}×{$height}: WhatsApp y Facebook la esperan de 1200×630.");

            return self::FAILURE;
        }

        $this->components->info('Al compartir se ve el logo ('.ShareMeta::DEFAULT_IMAGE.') y, en cada invitación, la foto de su portada.');

        return self::SUCCESS;
    }
}
