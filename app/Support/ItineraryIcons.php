<?php

namespace App\Support;

/**
 * Catálogo de íconos para los momentos del itinerario de XV años.
 *
 * Cada clave tiene su dibujo en resources/views/invitations/partials/itinerary-icon.blade.php.
 * Las claves genéricas que se usaban antes (users, candle, dance…) siguen funcionando
 * mediante alias, así los itinerarios ya guardados no pierden su ícono.
 */
final class ItineraryIcons
{
    public const DEFAULT = 'especial';

    private const GROUPS = [
        'Llegada y ceremonia' => [
            'traslado' => 'Traslado',
            'recepcion' => 'Recepción de invitados',
            'ceremonia' => 'Misa o ceremonia',
            'entrada' => 'Entrada de la quinceañera',
        ],
        'Tradiciones' => [
            'vals' => 'Vals',
            'velas' => 'Ceremonia de velas',
            'zapatilla' => 'Cambio de zapatilla',
            'muneca' => 'Última muñeca',
            'baile' => 'Baile sorpresa',
        ],
        'Celebración' => [
            'brindis' => 'Brindis',
            'cena' => 'Cena',
            'pastel' => 'Pastel',
            'musica' => 'Música en vivo',
            'fiesta' => 'Fiesta y DJ',
            'hora-loca' => 'Hora loca',
            'fotos' => 'Sesión de fotos',
        ],
        'Cierre' => [
            'sorpresa' => 'Sorpresa',
            'despedida' => 'Despedida',
            'especial' => 'Momento especial',
        ],
    ];

    private const ALIASES = [
        'users' => 'recepcion',
        'people' => 'recepcion',
        'map-pin' => 'traslado',
        'crown' => 'entrada',
        'dance' => 'vals',
        'candle' => 'velas',
        'sparkle' => 'baile',
        'glass' => 'brindis',
        'dinner' => 'cena',
        'music' => 'fiesta',
        'camera' => 'fotos',
        'gift' => 'sorpresa',
        'star' => 'especial',
    ];

    /** @return array<string, array<string, string>> etapa => [clave => nombre] */
    public static function groups(): array
    {
        return self::GROUPS;
    }

    /** @return array<string, string> clave => nombre, en el orden del catálogo */
    public static function all(): array
    {
        return array_merge(...array_values(self::GROUPS));
    }

    /** @return array<string, string> clave antigua => clave del catálogo */
    public static function aliases(): array
    {
        return self::ALIASES;
    }

    public static function resolve(?string $key): string
    {
        $key = self::ALIASES[$key ?? ''] ?? $key;

        return array_key_exists((string) $key, self::all()) ? $key : self::DEFAULT;
    }

    public static function label(?string $key): string
    {
        return self::all()[self::resolve($key)];
    }
}
