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

        $patterns = [
            '/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/[?&]query=(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/',
            '/place\/[^\/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/\/maps\/(?:search|place)\/[^\/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/',
            '/\/(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)\/?(?:\?|$)/',
        ];

        foreach ($patterns as $pattern) {
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
