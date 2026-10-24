<?php

namespace App\Support;

use App\Modules\ModuleRegistry;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * Idiomas del sitio (config/localization.php): cuál está activo, cómo se llega a la misma página en
 * el otro y qué direcciones tiene cada uno.
 *
 * El español no lleva prefijo (/precios) y sus rutas se llaman como siempre (pricing); el inglés
 * vive bajo /en y sus rutas llevan el prefijo «en.» (en.pricing). Dentro de la aplicación las claves
 * de página son siempre las del español: SetLocale devuelve a esa forma los parámetros de la ruta.
 */
final class Localization
{
    /** Parámetros de ruta que cambian de dirección según el idioma, con el grupo de su tabla. */
    public const SLUG_PARAMETERS = ['landing' => 'landings', 'page' => 'paths'];

    public static function default(): string
    {
        return (string) config('localization.default', 'es');
    }

    /** @return list<string> */
    public static function locales(): array
    {
        return array_keys(config('localization.locales', []));
    }

    public static function supports(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::locales(), true);
    }

    /** El idioma si lo conocemos y, si no, el de la raíz. */
    public static function resolve(?string $locale): string
    {
        return self::supports($locale) ? (string) $locale : self::default();
    }

    public static function current(): string
    {
        return app()->getLocale();
    }

    public static function isDefault(?string $locale = null): bool
    {
        return ($locale ?? self::current()) === self::default();
    }

    /** Idiomas para el selector: código => nombre en su propio idioma. @return array<string, string> */
    public static function names(): array
    {
        return array_map(fn (array $locale) => $locale['name'], config('localization.locales', []));
    }

    public static function short(string $locale): string
    {
        return (string) config("localization.locales.{$locale}.short", strtoupper($locale));
    }

    /** Valor de Open Graph (og:locale) de un idioma: es_BO, en_US. */
    public static function openGraph(?string $locale = null): string
    {
        return (string) config('localization.locales.'.($locale ?? self::current()).'.og', 'es_BO');
    }

    /** Prefijo de URL del idioma, sin barras: «» en español, «en» en inglés. */
    public static function prefix(string $locale): string
    {
        return trim((string) config("localization.locales.{$locale}.prefix", $locale), '/');
    }

    /** Prefijo de los nombres de ruta: «» en español, «en.» en inglés. */
    public static function namePrefix(string $locale): string
    {
        return self::isDefault($locale) ? '' : $locale.'.';
    }

    /**
     * Formato de fecha en el idioma activo, a partir del que se escribió en español
     * («j \d\e F \d\e Y» → «F j, Y» en inglés).
     */
    public static function dateFormat(string $spanishFormat): string
    {
        return (string) config('localization.date_formats.'.self::current().'.'.$spanishFormat, $spanishFormat);
    }

    /** Separadores de miles y decimales del idioma activo: [decimal, miles]. @return array{0: string, 1: string} */
    public static function numberSeparators(): array
    {
        return (array) config('localization.number_separators.'.self::current(), [',', '.']);
    }

    /** Une una lista en prosa: «a, b y c» («a, b and c» en inglés). */
    public static function join(array $items): string
    {
        return Arr::join(array_values($items), ', ', ' '.__('y').' ');
    }

    /** Dirección de una página en un idioma, a partir de su clave en español. */
    public static function path(string $key, ?string $locale = null): string
    {
        $locale ??= self::current();

        return (string) config("localization.paths.{$locale}.{$key}", $key);
    }

    /** Claves de dirección de las páginas por tipo de evento en un idioma (para whereIn de la ruta). */
    public static function landingSlugs(string $locale): array
    {
        $landings = array_keys((array) config('bida.landings', []));

        return array_map(fn (string $slug) => self::slug('landings', $slug, $locale), $landings);
    }

    /** Páginas legales en un idioma: las claves en español pasadas a su dirección. */
    public static function legalSlugs(string $locale): array
    {
        return array_map(fn (string $page) => self::slug('paths', $page, $locale), LegalPages::PAGES);
    }

    /** Dirección de una clave de página en un idioma (la misma clave si ese idioma no la traduce). */
    public static function slug(string $group, string $canonical, string $locale): string
    {
        return (string) config("localization.{$group}.{$locale}.{$canonical}", $canonical);
    }

    /** Clave en español de una dirección recibida en un idioma (la misma si no se tradujo). */
    public static function canonical(string $group, string $slug, string $locale): string
    {
        $table = (array) config("localization.{$group}.{$locale}", []);
        $found = array_search($slug, $table, true);

        return $found === false ? $slug : (string) $found;
    }

    /** Partes de la configuración con texto para el visitante (claves de config): se traducen al activar un idioma. */
    public const TRANSLATED_CONFIG = [
        'bida.showcase', 'bida.images', 'bida.landings', 'bida.services', 'bida.seasons', 'bida.packages',
        'bida.reseller_plans', 'bida.diy', 'bida.launch_promo', 'bida.professionals',
    ];

    /** Lo mismo, pero con el texto en las claves del arreglo (hoy ninguna). */
    public const TRANSLATED_CONFIG_KEYS = [];

    /**
     * Deja un idioma activo: el de la aplicación, las fechas y, si no es el de base, los textos que
     * viven en la configuración (se traducen una vez por petición).
     */
    public static function apply(string $locale): void
    {
        $locale = self::resolve($locale);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if (! self::isDefault($locale)) {
            self::translateConfig();
        }
    }

    /**
     * Ejecuta algo en otro idioma y deja el activo como estaba. Solo cambia el idioma de los textos
     * y las fechas: la configuración no se vuelve a traducir (para eso, apply()).
     *
     * @template T
     *
     * @param  \Closure(): T  $callback
     * @return T
     */
    public static function using(string $locale, \Closure $callback): mixed
    {
        $previous = app()->getLocale();
        $previousCarbon = Carbon::getLocale();

        app()->setLocale(self::resolve($locale));
        Carbon::setLocale(self::resolve($locale));

        try {
            return $callback();
        } finally {
            app()->setLocale($previous);
            Carbon::setLocale($previousCarbon);
        }
    }

    /** Traduce en su lugar los textos de la configuración (idempotente: un texto ya traducido no cambia). */
    public static function translateConfig(): void
    {
        foreach (self::TRANSLATED_CONFIG as $path) {
            if (is_array($tree = config($path))) {
                config([$path => TranslationCatalog::translate($tree)]);
            }
        }

        foreach (self::TRANSLATED_CONFIG_KEYS as $path) {
            if (is_array($tree = config($path))) {
                config([$path => TranslationCatalog::translate($tree, keys: true)]);
            }
        }
    }

    /**
     * Los textos por defecto de las plantillas de invitación (los que no escribió el cliente).
     *
     * @return list<string>
     */
    public static function templateStrings(): array
    {
        $strings = EditableTexts::defaults();

        foreach (InvitationTemplates::all() as $template) {
            foreach (['label', 'tagline', 'description'] as $field) {
                if (is_string($template[$field] ?? null)) {
                    $strings[] = $template[$field];
                }
            }
        }

        array_push($strings, ...array_values(InvitationPage::NAV_LABELS), ...PricingPage::strings(), ...ShowcaseDemos::strings());
        array_push($strings, 'tarjeta', 'invitación');

        foreach (app(ModuleRegistry::class)->all() as $module) {
            $strings[] = $module->label();
        }

        return array_values(array_unique($strings));
    }

    /**
     * Todos los textos de la configuración que se traducen, para comprobar que lang/en.json los tiene.
     *
     * @return list<string>
     */
    public static function configStrings(): array
    {
        $found = [];

        foreach (self::TRANSLATED_CONFIG as $path) {
            if (is_array($tree = config($path))) {
                array_push($found, ...TranslationCatalog::strings($tree));
            }
        }

        foreach (self::TRANSLATED_CONFIG_KEYS as $path) {
            if (is_array($tree = config($path))) {
                array_push($found, ...TranslationCatalog::strings($tree, keys: true));
            }
        }

        return array_values(array_unique($found));
    }

    /**
     * URL de una ruta del sitio en el idioma pedido (el actual por omisión): la ruta «en.xxx» si
     * existe y, si no, la de siempre. Los parámetros de página (landing, page) se pasan con su clave
     * en español.
     */
    public static function route(string $name, mixed $parameters = [], bool $absolute = true, ?string $locale = null): string
    {
        $locale ??= self::current();
        $name = self::unprefixed($name);
        $localized = self::namePrefix($locale).$name;
        $final = Route::has($localized) ? $localized : $name;

        if ($final === $localized && ! self::isDefault($locale)) {
            $names = Route::getRoutes()->getByName($final)?->parameterNames() ?? [];
            $parameters = self::localizeParameters(self::named((array) $parameters, $names), $locale);
        }

        return route($final, $parameters, $absolute);
    }

    /**
     * La misma página que se está viendo, en otro idioma, con su búsqueda; null si la página no
     * tiene versión en ese idioma (las invitaciones, los paneles).
     */
    public static function switchUrl(string $locale, bool $withQuery = true): ?string
    {
        $current = request()->route();
        $name = $current?->getName();

        if ($name === null || ! self::supports($locale)) {
            return null;
        }

        $base = self::unprefixed($name);
        $target = self::namePrefix($locale).$base;

        if (! Route::has($target)) {
            return null;
        }

        $url = self::route($base, $current->parameters(), true, $locale);
        $query = request()->getQueryString();

        return $withQuery && $query ? $url.'?'.$query : $url;
    }

    /**
     * La dirección única de la página abierta, la que los buscadores deben contar: en el idioma actual, sin
     * ?utm_… ni ?ref= y con el dominio oficial (APP_URL), no el que haya escrito quien la visita.
     */
    public static function canonicalUrl(): ?string
    {
        if (request()->route() === null) {
            return null;
        }

        $url = self::switchUrl(app()->getLocale(), false) ?? url()->current();
        $path = rtrim((string) parse_url($url, PHP_URL_PATH), '/');

        return rtrim((string) config('app.url'), '/').$path;
    }

    /**
     * Las versiones de la página actual para los buscadores (hreflang): código => URL, más
     * x-default con la de la raíz. Vacío si la página no está traducida.
     *
     * @return array<string, string>
     */
    public static function alternates(): array
    {
        $urls = [];

        foreach (self::locales() as $locale) {
            if ($url = self::switchUrl($locale, false)) {
                $urls[$locale] = $url;
            }
        }

        if (count($urls) < 2) {
            return [];
        }

        return $urls + ['x-default' => $urls[self::default()] ?? Arr::first($urls)];
    }

    /** Nombre de ruta sin el prefijo de ningún idioma: «en.pricing» → «pricing». */
    public static function unprefixed(string $name): string
    {
        foreach (self::locales() as $locale) {
            if (! self::isDefault($locale) && Str::startsWith($name, $locale.'.')) {
                return Str::after($name, $locale.'.');
            }
        }

        return $name;
    }

    /**
     * Pasa los parámetros posicionales (lroute('legal', 'cookies')) a su nombre en la ruta.
     *
     * @param  list<string>  $names
     * @return array<string|int, mixed>
     */
    private static function named(array $parameters, array $names): array
    {
        $positional = array_values(array_filter($parameters, fn ($key) => is_int($key), ARRAY_FILTER_USE_KEY));
        $named = array_filter($parameters, fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);

        foreach ($positional as $index => $value) {
            if (isset($names[$index]) && ! array_key_exists($names[$index], $named)) {
                $named[$names[$index]] = $value;
            }
        }

        return $named;
    }

    /** @return array<string, mixed> */
    private static function localizeParameters(array $parameters, string $locale): array
    {
        foreach (self::SLUG_PARAMETERS as $parameter => $group) {
            if (isset($parameters[$parameter]) && is_string($parameters[$parameter])) {
                $parameters[$parameter] = self::slug($group, $parameters[$parameter], $locale);
            }
        }

        return $parameters;
    }
}
