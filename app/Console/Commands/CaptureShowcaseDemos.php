<?php

namespace App\Console\Commands;

use App\Support\ShowcaseDemos;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Number;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Captura cómo empieza la apertura de cada invitación de muestra, tal como se ve en un celular
 * (390×844), y la guarda en public/images/muestras/{slug}.webp. Las páginas por evento muestran esa
 * captura y, al tocarla, llevan a la muestra completa (ShowcaseDemos::capture).
 *
 * Usa Chrome o Edge sin ventana contra el sitio que se esté sirviendo (APP_URL o --url), con las
 * hojas compiladas o con Vite corriendo. Sin ventana, el navegador no baja de unos 500 px de ancho: la
 * muestra se carga en un iframe de 390×844 dentro de una página de paso (public/_preview, no se sube). Se corre en el equipo de desarrollo cada vez que cambia la
 * apertura de una plantilla o se suma una muestra; las capturas se suben con el resto de public/.
 */
class CaptureShowcaseDemos extends Command
{
    protected $signature = 'bida:capturas-muestras
        {slugs?* : Muestras a capturar (por defecto, todas las que se pueden abrir en /muestra)}
        {--url= : Dirección del sitio que se está sirviendo (por defecto APP_URL)}
        {--navegador= : Ruta de Chrome o Edge, si no se encuentra solo}';

    protected $description = 'Captura el inicio de la apertura de las invitaciones de muestra para las páginas por evento';

    /** El celular de referencia de las plantillas. */
    private const WIDTH = 390;

    private const HEIGHT = 844;

    /** 585×1266: nítida en el teléfono de la página sin pesar de más. */
    private const SCALE = 1.5;

    /** Tiempo de la página antes de la captura: fuentes cargadas y la apertura ya armada, esperando el toque. */
    private const WAIT_MS = 7000;

    public function handle(): int
    {
        $browser = $this->option('navegador') ?: $this->findBrowser();

        if (! $browser || ! is_file($browser)) {
            $this->components->error('No encontré Chrome ni Edge. Indica la ruta con --navegador.');

            return self::FAILURE;
        }

        $base = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        $slugs = $this->argument('slugs') ?: ShowcaseDemos::allowedSlugs();
        $folder = public_path(ShowcaseDemos::CAPTURES);
        File::ensureDirectoryExists($folder);

        $failed = [];
        File::ensureDirectoryExists(public_path('_preview'));

        foreach ($slugs as $slug) {
            // La página de paso enmarca la muestra al ancho del celular (mismo sitio: la muestra solo se deja enmarcar así)
            $frame = '_preview/captura-'.$slug.'.html';
            File::put(public_path($frame), '<!DOCTYPE html><html><body style="margin:0;overflow:hidden"><iframe src="/muestra/'.e($slug)
                .'" style="display:block;width:'.self::WIDTH.'px;height:'.self::HEIGHT.'px;border:0"></iframe></body></html>');

            $captured = $this->capture($browser, "{$base}/{$frame}", "{$folder}/{$slug}.webp");
            File::delete(public_path($frame));

            if ($captured === null) {
                $failed[] = $slug;
                $this->components->twoColumnDetail($slug, '<fg=red>sin captura</>');

                continue;
            }

            $this->components->twoColumnDetail($slug, Number::fileSize($captured));
        }

        if ($failed) {
            $this->components->warn('Sin captura: '.implode(', ', $failed).'. ¿El sitio responde en '.$base.' y la muestra está sembrada (ShowcaseInvitationsSeeder)?');

            return self::FAILURE;
        }

        $this->components->info(count($slugs).' capturas en public/'.ShowcaseDemos::CAPTURES.'.');

        return self::SUCCESS;
    }

    /** Toma la captura y la guarda en WebP; devuelve su peso en bytes o null si el navegador no la hizo. */
    private function capture(string $browser, string $url, string $target): ?int
    {
        $temp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bida-captura-'.bin2hex(random_bytes(4));
        $png = $temp.'.png';

        (new Process([
            $browser,
            '--headless=new',
            '--disable-gpu',
            '--hide-scrollbars',
            '--mute-audio',
            '--no-first-run',
            '--no-default-browser-check',
            // Un perfil de paso: no toca el del navegador de quien corre el comando
            '--user-data-dir='.$temp,
            '--window-size='.self::WIDTH.','.self::HEIGHT,
            '--force-device-scale-factor='.self::SCALE,
            '--virtual-time-budget='.self::WAIT_MS,
            '--screenshot='.$png,
            $url,
        ]))->setTimeout(120)->run();

        File::deleteDirectory($temp);

        $image = is_file($png) ? @imagecreatefrompng($png) : false;
        @unlink($png);

        if (! $image) {
            return null;
        }

        imagewebp($image, $target, 82);
        imagedestroy($image);
        clearstatcache(true, $target);

        return filesize($target) ?: null;
    }

    private function findBrowser(): ?string
    {
        $candidates = [
            'C:\Program Files\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
            'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        $finder = new ExecutableFinder;

        foreach (['google-chrome', 'google-chrome-stable', 'chromium', 'chromium-browser', 'microsoft-edge'] as $name) {
            if ($path = $finder->find($name)) {
                return $path;
            }
        }

        return null;
    }
}
