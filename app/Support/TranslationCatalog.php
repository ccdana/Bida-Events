<?php

namespace App\Support;

/**
 * Textos del sitio que viven en datos (config/bida.php, las plantillas) y no en una vista: se
 * traducen recorriendo el arreglo y pasando cada texto por __(), cuya clave es el español.
 *
 * Es seguro por construcción: un valor que no es clave de lang/en.json vuelve igual, así que un
 * identificador (una ruta, un código, un color) no cambia aunque pase por aquí. Los campos que se
 * sabe que no son texto se saltan igual, para no depender de esa coincidencia.
 */
final class TranslationCatalog
{
    /** Campos cuyo valor nunca es un texto para el visitante. */
    private const SKIP_FIELDS = [
        'key', 'code', 'slug', 'image', 'path', 'href', 'url', 'icon', 'event', 'kind', 'price', 'landing', 'product',
        'templates', 'demos', 'size', 'stock', 'collections', 'rsvp', 'collection', 'theme', 'module', 'feature',
        'ends_at', 'starts_at', 'cta_url', 'lottie', 'class', 'color', 'colors', 'font', 'fonts', 'pdf', 'partials',
    ];

    /** @param  array<mixed>  $tree */
    public static function translate(array $tree, bool $keys = false, ?string $field = null): array
    {
        $out = [];

        foreach ($tree as $key => $value) {
            $name = is_string($key) ? $key : $field;
            $newKey = $keys && is_string($key) ? __($key) : $key;

            $out[$newKey] = match (true) {
                is_array($value) => self::translate($value, $keys, $name),
                is_string($value) && ! $keys && ! in_array($name, self::SKIP_FIELDS, true) => __($value),
                default => $value,
            };
        }

        return $out;
    }

    /**
     * Los textos de un arreglo, sin repetir (para comprobar que lang/en.json los tiene).
     *
     * @param  array<mixed>  $tree
     * @return list<string>
     */
    public static function strings(array $tree, bool $keys = false, ?string $field = null): array
    {
        $found = [];

        foreach ($tree as $key => $value) {
            $name = is_string($key) ? $key : $field;

            if ($keys && is_string($key) && self::isText($key)) {
                $found[] = $key;
            }

            if (is_array($value)) {
                array_push($found, ...self::strings($value, $keys, $name));
            } elseif (is_string($value) && ! $keys && ! in_array($name, self::SKIP_FIELDS, true) && self::isText($value)) {
                $found[] = $value;
            }
        }

        return array_values(array_unique($found));
    }

    /** ¿Es una frase o una palabra para leer, y no una dirección, un archivo o un número? */
    public static function isText(string $value): bool
    {
        $value = trim($value);

        if ($value === '' || ! preg_match('/\p{L}{2,}/u', $value)) {
            return false;
        }

        if (preg_match('~^(https?:|mailto:|tel:|/|#)|\.(webp|jpe?g|png|svg|css|js)$~i', $value)) {
            return false;
        }

        return true;
    }
}
