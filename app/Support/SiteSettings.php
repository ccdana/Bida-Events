<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Lo que el administrador maneja desde el panel sin tocar el código: los precios de los paquetes,
 * la promoción y hasta cuándo dura, cada temporada (encendida o no, precio y fecha, por separado),
 * qué plantillas de temporada se ofrecen hoy y el precio y el cupo de cada plan de revendedor.
 *
 * Lo guardado se aplica sobre config/bida.php al arrancar (AppServiceProvider), así que el resto
 * del código sigue leyendo config('bida.…') y no se entera de nada. Lo que no se haya tocado
 * desde el panel conserva el valor del archivo de configuración.
 */
final class SiteSettings
{
    private const CACHE_KEY = 'bida.site-settings';

    /** Grupos que se guardan; cualquier otro nombre se ignora. */
    public const GROUPS = ['promo', 'packages', 'season', 'templates', 'reseller_plans'];

    /** Aplica lo guardado sobre la configuración. Si la base no responde, queda la config del archivo. */
    public static function apply(): void
    {
        $stored = self::stored();

        if ($stored === []) {
            return;
        }

        self::applyPackages($stored['packages'] ?? []);
        self::applyPromo($stored['promo'] ?? []);
        self::applySeasons($stored['season'] ?? []);
        self::applyResellerPlans($stored['reseller_plans'] ?? []);

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
            'seasons' => collect(config('bida.seasons', []))
                ->map(fn (array $season, string $key) => [
                    'key' => $key,
                    'name' => $season['name'],
                    'product' => $season['product'] ?? 'tarjeta',
                    'active' => (bool) ($season['active'] ?? true),
                    'price' => (int) ($season['price'] ?? 0),
                    'promo_price' => isset($season['promo_price']) ? (int) $season['promo_price'] : null,
                    'ends_at' => $season['ends_at'] ?? null,
                ])
                ->values()
                ->all(),
            'templates' => ['disabled' => (array) config('bida.templates_disabled', [])],
            'reseller_plans' => collect(config('bida.reseller_plans', []))
                ->map(fn (array $plan, string $key) => [
                    'key' => $key,
                    'name' => $plan['name'],
                    'summary' => $plan['summary'] ?? '',
                    'price' => (int) $plan['price'],
                    'promo_price' => isset($plan['promo_price']) ? (int) $plan['promo_price'] : null,
                    'quota_per_month' => isset($plan['quota_per_month']) ? (int) $plan['quota_per_month'] : null,
                ])
                ->values()
                ->all(),
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

    /** Precio y cupo mensual de cada plan de revendedor, por su clave (un cupo vacío es sin tope). */
    private static function applyResellerPlans(array $plans): void
    {
        foreach ($plans as $key => $values) {
            if (! is_array($values) || ! is_array(config("bida.reseller_plans.{$key}"))) {
                continue;
            }

            if (array_key_exists('price', $values)) {
                config(["bida.reseller_plans.{$key}.price" => (int) $values['price']]);
            }

            if (array_key_exists('promo_price', $values)) {
                $promo = $values['promo_price'];
                config(["bida.reseller_plans.{$key}.promo_price" => $promo === null || $promo === '' ? null : (int) $promo]);
            }

            if (array_key_exists('quota_per_month', $values)) {
                $quota = $values['quota_per_month'];
                config(["bida.reseller_plans.{$key}.quota_per_month" => $quota === null || $quota === '' ? null : (int) $quota]);
            }
        }
    }

    /**
     * Cada temporada por su clave: encendida o apagada, precio y fecha de término. Son
     * independientes: apagar el Día del Amor no toca a Halloween. Lo guardado antes de que hubiera
     * varias temporadas (un solo grupo con precio y fecha) era del Día del Amor, o de la temporada
     * que dice su «key».
     */
    private static function applySeasons(array $stored): void
    {
        if (array_intersect(['price', 'promo_price', 'ends_at'], array_keys($stored))) {
            $stored = [($stored['key'] ?? 'amor') => $stored];
        }

        foreach ($stored as $key => $values) {
            if (! is_array($values) || ! is_array(config("bida.seasons.{$key}"))) {
                continue;
            }

            if (array_key_exists('active', $values)) {
                config(["bida.seasons.{$key}.active" => (bool) $values['active']]);
            }

            foreach (['price', 'promo_price'] as $field) {
                if (array_key_exists($field, $values)) {
                    config(["bida.seasons.{$key}.{$field}" => $values[$field] === null || $values[$field] === '' ? null : (int) $values[$field]]);
                }
            }

            if (array_key_exists('ends_at', $values)) {
                config(["bida.seasons.{$key}.ends_at" => $values['ends_at'] ?: null]);
            }
        }
    }
}
