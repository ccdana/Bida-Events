<?php

namespace App\Support;

use App\EventProfiles\EventProfiles;
use Illuminate\Support\Carbon;

/**
 * Qué tipos de evento y plantillas se pueden elegir hoy en el editor. Lo decide el administrador
 * desde Ajustes: una plantilla apagada, o una temporada apagada o terminada, deja de ofrecerse
 * (con el motivo a la vista). Las de todo el año siempre están.
 *
 * También agrupa los tipos de evento para el editor: los de todo el año y, aparte, cada temporada
 * con su ánimo («primaveral y romántico», «tenebroso»; clave «category» de config('bida.seasons')).
 */
final class TemplateAvailability
{
    public const YEAR_ROUND = 'Eventos de todo el año';

    /** Por qué no se puede elegir esta plantilla hoy; null si se puede. */
    public static function templateReason(string $template): ?string
    {
        if (in_array($template, (array) config('bida.templates_disabled', []), true)) {
            return 'Apagada en Ajustes';
        }

        return self::seasonReason(self::seasonOf($template));
    }

    /** Por qué no se ofrece la temporada de un tipo de evento; null si está a la venta o es de todo el año. */
    public static function seasonReason(?string $season): ?string
    {
        if ($season === null) {
            return null;
        }

        return match (Offers::seasonStatus($season)) {
            'off' => 'Temporada apagada en Ajustes',
            'ended' => 'La temporada terminó el '.self::endDate($season),
            default => null,
        };
    }

    /** Grupo del tipo de evento en el editor. */
    public static function category(?string $season): string
    {
        if ($season === null) {
            return self::YEAR_ROUND;
        }

        return (string) (config("bida.seasons.{$season}.category") ?: config("bida.seasons.{$season}.name", $season));
    }

    /** Orden de los grupos: primero los de todo el año y después las temporadas, como están en la config. */
    public static function categoryOrder(): array
    {
        return array_values(array_unique([
            self::YEAR_ROUND,
            ...array_map(fn (string $season) => self::category($season), array_keys((array) config('bida.seasons', []))),
        ]));
    }

    public static function seasonOf(string $template): ?string
    {
        return rescue(fn () => app(EventProfiles::class)->forTemplate($template)->season(), null, report: false);
    }

    private static function endDate(string $season): string
    {
        $endsAt = Offers::date(config("bida.seasons.{$season}.ends_at"));

        return $endsAt ? Carbon::parse($endsAt)->locale('es')->translatedFormat('j \d\e F') : '';
    }
}
