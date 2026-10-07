<?php

namespace App\Support;

class MapsLinkParser
{
    /**
     * @return array{lat: float, lng: float}|null
     */
    public static function parse(?string $link): ?array
    {
        if (blank($link)) {
            return null;
        }

        $haystack = trim(html_entity_decode($link));

        // 1. Coordenadas exactas del pin del lugar en Google Maps (!3d<lat>!4d<lng>)
        if (preg_match('/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/', $haystack, $matches)) {
            $coords = self::normalize((float) $matches[1], (float) $matches[2]);
            if ($coords) {
                return $coords;
            }
        }

        // 2. Parámetros de consulta explícitos: q=lat,lng o query=lat,lng o ll=lat,lng (soporta espacios opcionales)
        $queryPatterns = [
            '/[?&]q=(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)/',
            '/[?&]query=(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)/',
            '/[?&]ll=(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)/',
        ];

        foreach ($queryPatterns as $pattern) {
            if (preg_match($pattern, $haystack, $matches)) {
                $coords = self::normalize((float) $matches[1], (float) $matches[2]);
                if ($coords) {
                    return $coords;
                }
            }
        }

        // 3. Coordenadas directas en texto plano: "-17.3955837, -66.1638894"
        if (preg_match('/^(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)$/', $haystack, $matches)) {
            $coords = self::normalize((float) $matches[1], (float) $matches[2]);
            if ($coords) {
                return $coords;
            }
        }

        // 4. Último recurso: centro de cámara de pantalla @lat,lng
        $viewportPatterns = [
            '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/place\/[^\/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/\/maps\/(?:search|place)\/[^\/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/\/(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)\/?(?:\?|$)/',
        ];

        foreach ($viewportPatterns as $pattern) {
            if (preg_match($pattern, $haystack, $matches)) {
                $coords = self::normalize((float) $matches[1], (float) $matches[2]);
                if ($coords) {
                    return $coords;
                }
            }
        }

        return null;
    }

    public static function buildUrl(float $lat, float $lng): string
    {
        return sprintf('https://www.google.com/maps?q=%s,%s', $lat, $lng);
    }

    public static function navigationUrl(float $lat, float $lng, ?string $label = null): string
    {
        $query = $label ? urlencode($label) : sprintf('%s,%s', $lat, $lng);

        return "https://www.google.com/maps/dir/?api=1&destination={$query}";
    }

    /**
     * Enlace http(s) listo para un href: agrega https:// si el usuario lo pegó sin esquema y descarta
     * cualquier otra cosa (texto suelto, coordenadas, javascript:) para que nunca termine como ruta relativa.
     */
    public static function webUrl(?string $link): ?string
    {
        $link = trim((string) $link);

        if ($link === '' || preg_match('/\s/', $link)) {
            return null;
        }

        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $link)) {
            if (! preg_match('#^(?:[a-z0-9-]+\.)+[a-z]{2,}(?:[/?\#]|$)#i', $link)) {
                return null;
            }

            $link = "https://{$link}";
        }

        $parts = parse_url($link);

        if (! $parts || empty($parts['host']) || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }

        return $link;
    }

    /** Si el enlace abre de verdad un mapa (Google Maps, Waze, Apple Maps, OpenStreetMap), no cualquier página. */
    public static function isMapsUrl(?string $link): bool
    {
        $link = self::webUrl($link);

        if (! $link) {
            return false;
        }

        $parts = parse_url($link);
        $host = strtolower(preg_replace('/^www\./i', '', $parts['host']));
        $path = strtolower($parts['path'] ?? '');

        return match (true) {
            in_array($host, ['maps.app.goo.gl', 'g.page', 'waze.com', 'ul.waze.com', 'maps.apple.com', 'openstreetmap.org'], true) => true,
            $host === 'goo.gl' => str_starts_with($path, '/maps'),
            (bool) preg_match('/^google\.[a-z.]+$/', $host) => str_starts_with($path, '/maps'),
            (bool) preg_match('/^maps\.google\.[a-z.]+$/', $host) => true,
            default => false,
        };
    }

    /**
     * Coordenadas de una ubicación guardada. Lo que el usuario escribió a mano en el campo del enlace
     * («-17.39,-66.16») manda sobre lat/lng, que pueden seguir en el valor por defecto del editor.
     *
     * @param  array<string, mixed>  $location
     * @return array{lat: float, lng: float}|null
     */
    public static function coordinates(array $location): ?array
    {
        $link = trim((string) ($location['maps_url'] ?? ''));

        if ($link !== '' && ! self::webUrl($link) && ($typed = self::parse($link))) {
            return $typed;
        }

        $lat = $location['lat'] ?? null;
        $lng = $location['lng'] ?? null;

        if (is_numeric($lat) && is_numeric($lng) && ($saved = self::normalize((float) $lat, (float) $lng))) {
            return $saved;
        }

        return $link !== '' ? self::parse($link) : null;
    }

    /**
     * Enlace del botón «Cómo llegar»: el del usuario si de verdad es de un mapa; si no, la ruta hacia las
     * coordenadas y, como último recurso, una búsqueda por nombre y dirección.
     *
     * @param  array<string, mixed>  $location
     */
    public static function directionsUrl(array $location): ?string
    {
        $link = self::webUrl($location['maps_url'] ?? null);

        if ($link && self::isMapsUrl($link)) {
            return $link;
        }

        if ($coords = self::coordinates($location)) {
            return self::navigationUrl($coords['lat'], $coords['lng']);
        }

        $query = self::placeQuery($location);

        return $query ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($query) : null;
    }

    /**
     * Mapa incrustado de la invitación (iframe de Google Maps).
     *
     * @param  array<string, mixed>  $location
     */
    public static function embedUrl(array $location): ?string
    {
        if ($coords = self::coordinates($location)) {
            return "https://maps.google.com/maps?q={$coords['lat']},{$coords['lng']}&z=15&output=embed";
        }

        $query = self::placeQuery($location);

        return $query ? 'https://maps.google.com/maps?q='.urlencode($query).'&z=15&output=embed' : null;
    }

    /** @param  array<string, mixed>  $location */
    protected static function placeQuery(array $location): ?string
    {
        $query = trim(implode(', ', array_filter([
            is_string($location['nombre_lugar'] ?? null) ? trim($location['nombre_lugar']) : null,
            is_string($location['direccion'] ?? null) ? trim($location['direccion']) : null,
        ])));

        return $query !== '' ? $query : null;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    protected static function normalize(float $lat, float $lng): ?array
    {
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        if ($lat === 0.0 && $lng === 0.0) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }
}
