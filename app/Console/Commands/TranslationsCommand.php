<?php

namespace App\Console\Commands;

use App\Support\Localization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Audita lang/en.json contra los textos que usa el sitio: las llamadas a __() de las vistas y del
 * código, y los textos que viven en la configuración y en las plantillas.
 *
 *   php artisan bida:traducciones              resumen
 *   php artisan bida:traducciones --faltan     lista las claves sin traducción
 *   php artisan bida:traducciones --json       las que faltan, como esqueleto de lang/en.json
 *   php artisan bida:traducciones --sobran     las que ya nadie usa
 */
class TranslationsCommand extends Command
{
    protected $signature = 'bida:traducciones {--faltan : Lista las claves sin traducción} {--json : Las que faltan, como JSON} {--sobran : Las claves de lang/en.json que ya no se usan}';

    protected $description = 'Compara lang/en.json con los textos que usa el sitio';

    public function handle(): int
    {
        $used = self::usedKeys();
        $translated = self::translatedKeys();
        $missing = array_values(array_diff($used, $translated));
        $unused = array_values(array_diff($translated, $used));

        if ($this->option('json')) {
            $this->line(json_encode(array_fill_keys($missing, ''), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->components->info(sprintf('%d textos en uso · %d traducidos · %d sin traducir · %d sin uso', count($used), count($used) - count($missing), count($missing), count($unused)));

        if ($this->option('faltan')) {
            foreach ($missing as $key) {
                $this->line('  '.$key);
            }
        }

        if ($this->option('sobran')) {
            foreach ($unused as $key) {
                $this->line('  '.$key);
            }
        }

        return $missing === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Claves de lang/en.json.
     *
     * @return list<string>
     */
    public static function translatedKeys(): array
    {
        $path = lang_path('en.json');

        return File::exists($path) ? array_keys((array) json_decode(File::get($path), true)) : [];
    }

    /**
     * Textos en uso: las llamadas a __() del código y de las vistas, más los de la configuración y
     * las plantillas (Localization::configStrings, plantillas de invitación).
     *
     * @return list<string>
     */
    public static function usedKeys(): array
    {
        $keys = [];

        foreach ([resource_path('views'), app_path(), base_path('routes')] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                if (! str_ends_with($file->getFilename(), '.php')) {
                    continue;
                }

                array_push($keys, ...self::keysIn(File::get($file->getPathname())));
            }
        }

        array_push($keys, ...Localization::configStrings());
        array_push($keys, ...Localization::templateStrings());

        $keys = array_values(array_unique(array_filter($keys, fn (string $key) => trim($key) !== '')));
        sort($keys);

        return $keys;
    }

    /**
     * Las cadenas literales de un archivo que van dentro de __(), trans() o @lang().
     *
     * @return list<string>
     */
    public static function keysIn(string $source): array
    {
        $found = [];
        preg_match_all('/(?:\b__|\btrans|@lang|\bLang::get)\(\s*(?:\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/s', $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $found[] = isset($match[2]) && $match[2] !== ''
                ? stripcslashes($match[2])
                : str_replace(["\\'", '\\\\'], ["'", '\\'], $match[1]);
        }

        return $found;
    }
}
