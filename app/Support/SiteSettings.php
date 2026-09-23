<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Lo que el administrador maneja desde el panel sin tocar el código: los precios de los paquetes,
 * la promoción y hasta cuándo dura, la temporada de tarjetas y qué plantillas de temporada se
 * ofrecen hoy.
 *
 * Lo guardado se aplica sobre config/bida.php al arrancar (AppServiceProvider), así que el resto
 * del código sigue leyendo config('bida.…') y no se entera de nada. Lo que no se haya tocado
 * desde el panel conserva el valor del archivo de configuración.
 */
final class SiteSettings
{
    private const CACHE_KEY = 'bida.site-settings';

    /** Grupos que se guardan; cualquier otro nombre se ignora. */
    public const GROUPS = ['promo', 'packages', 'season', 'templates'];

    /** Aplica lo guardado sobre la configuración. Si la base no responde, queda la config del archivo. */
    public static function apply(): void
    {
        $stored = self::stored();

        if ($stored === []) {
            return;
        }

        self::applyPackages($stored['packages'] ?? []);
        self::applyPromo($stored['promo'] ?? []);
        self::applySeason($stored['season'] ?? []);

        config(['bida.templates_disabled' => $stored['templates']['disabled'] ?? []]);
    }

    /** Lo que muestra el formulario del panel: los valores vigentes, vengan de donde vengan. */
    public static function current(): array
    {
        return [
            'promo' => [
                'active' => (bool) config('bida.launch_promo.active', false),
                'ends_at' => config('bida.launch_promo.ends_at'),
                'label' => config('bida.launch_promo.label'),
            ],
            'packages' => collect(config('bida.packages', []))
                ->map(fn (array $package) => [
                    'key' => $package['key'],
                    'name' => $package['name'],
                    'summary' => $package['summary'] ?? '',
                    'price' => (int) $package['price'],
                    'promo_price' => isset($package['promo_price']) ? (int) $package['promo_price'] : null,
                ])
                ->values()
                ->all(),
            'season' => [
                'name' => config('bida.season.name'),
                'price' => (int) config('bida.season.price', 0),
                'promo_price' => config('bida.season.promo_price') !== null ? (int) config('bida.season.promo_price') : null,
                'ends_at' => config('bida.season.ends_at'),
            ],
            'templates' => ['disabled' => (array) config('bida.templates_disabled', [])],
        ];
    }

    /** Guarda un grupo y borra la caché para que el cambio se vea en la siguiente petición. */
    public static function put(string $group, array $value): void
    {
        abort_unless(in_array($group, self::GROUPS, true), 500, "Ajuste desconocido: {$group}");

        SiteSetting::updateOrCreate(['key' => $group], ['value' => $value]);

        self::forget();
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Lo guardado, cacheado. En una instalación nueva todavía no existen ni la tabla de ajustes
     * ni la de caché: el sitio arranca igual con lo que dice config/bida.php.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function stored(): array
    {
        return rescue(
            fn () => Cache::rememberForever(self::CACHE_KEY, fn () => SiteSetting::pluck('value', 'key')->all()),
            [],
            report: false
        );
    }

    /** Precio normal y rebajado de cada paquete, por su clave. */
    private static function applyPackages(array $prices): void
    {
        if ($prices === []) {
            return;
        }

        $packages = collect(config('bida.packages', []))
            ->map(function (array $package) use ($prices): array {
                $stored = $prices[$package['key']] ?? null;

                if (! is_array($stored)) {
                    return $package;
                }

                $package['price'] = (int) ($stored['price'] ?? $package['price']);
                $promo = $stored['promo_price'] ?? null;

                // Sin rebaja se quita promo_price: así Offers cobra el precio normal
                if ($promo === null || $promo === '') {
                    unset($package['promo_price']);
                } else {
                    $package['promo_price'] = (int) $promo;
                }

                return $package;
            })
            ->all();

        config(['bida.packages' => $packages]);
    }

    private static function applyPromo(array $promo): void
    {
        if (array_key_exists('active', $promo)) {
            config(['bida.launch_promo.active' => (bool) $promo['active']]);
        }

        if (array_key_exists('ends_at', $promo)) {
            config(['bida.launch_promo.ends_at' => $promo['ends_at'] ?: null]);
        }
    }

    private static function applySeason(array $season): void
    {
        foreach (['price', 'promo_price'] as $field) {
            if (array_key_exists($field, $season)) {
                config(["bida.season.{$field}" => $season[$field] === null || $season[$field] === '' ? null : (int) $season[$field]]);
            }
        }

        if (array_key_exists('ends_at', $season)) {
            config(['bida.season.ends_at' => $season['ends_at'] ?: null]);
        }
    }
}
