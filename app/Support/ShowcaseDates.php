<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Mantiene vigentes las invitaciones de muestra. Sus archivos (database/seeders/showcase) traen una fecha
 * fija de ejemplo; con el tiempo pasa y la muestra se vería como un evento terminado, sin cuenta regresiva
 * ni confirmación. Aquí se mueve esa fecha, junto con todo lo que depende de ella:
 *
 *  - Muestras normales: la fecha avanza de a semanas completas (así el día de la semana no cambia) hasta
 *    quedar a LEAD_DAYS o más de hoy. Si ya está más lejos, no se toca.
 *  - Muestras de temporada (Halloween, Día del Amor): llevan su fecha de calendario; pasa a la próxima vez
 *    que ocurre, y el día de la semana de los textos se recalcula.
 *
 * Se corrigen también el vencimiento y los textos que dicen la fecha («Sábado 12 de diciembre, 2026»,
 * «confirma antes del 20 de noviembre»), todos corridos la misma cantidad de días. Es idempotente:
 * aplicarlo dos veces el mismo día da lo mismo.
 */
final class ShowcaseDates
{
    /** Días de margen: la fecha de la muestra queda entre LEAD_DAYS y LEAD_DAYS + 6 desde hoy. */
    public const LEAD_DAYS = 45;

    /** El vencimiento nunca queda a menos de estos días de hoy (la muestra deja de abrir al vencer). */
    private const MIN_VALIDITY_DAYS = 120;

    private const MONTHS = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    private const WEEKDAYS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];

    /**
     * @param  array<string, mixed>  $data  datos de una muestra: ['invitation' => [...], 'modules' => [...], …]
     * @return array<string, mixed>
     */
    public static function roll(array $data, ?CarbonInterface $now = null): array
    {
        $today = CarbonImmutable::instance($now ?? now())->startOfDay();
        $attributes = $data['invitation'] ?? [];

        if (empty($attributes['event_date'])) {
            return $data;
        }

        $original = CarbonImmutable::parse($attributes['event_date']);
        $target = ! empty($attributes['event_type']['season'])
            ? self::nextOccurrence($original, $today)
            : self::weeksAhead($original, $today);

        $days = (int) $original->startOfDay()->diffInDays($target->startOfDay(), false);

        $data['invitation']['event_date'] = $target->format('Y-m-d H:i:s');

        if (! empty($attributes['expires_at'])) {
            $validity = (int) $original->startOfDay()->diffInDays(CarbonImmutable::parse($attributes['expires_at'])->startOfDay(), false);
            $expires = $target->startOfDay()->addDays(max($validity, 0));
            $minimum = $today->addDays(self::MIN_VALIDITY_DAYS);

            $data['invitation']['expires_at'] = ($expires->lessThan($minimum) ? $minimum : $expires)->toDateString();
        }

        if ($days !== 0 && isset($data['modules'])) {
            $data['modules'] = self::shiftTexts($data['modules'], $days, $original->year);
        }

        return $data;
    }

    /** La primera fecha, de a semanas completas desde la original, que queda a LEAD_DAYS o más de hoy. */
    private static function weeksAhead(CarbonImmutable $original, CarbonImmutable $today): CarbonImmutable
    {
        $minimum = $today->addDays(self::LEAD_DAYS);

        if ($original->startOfDay()->greaterThanOrEqualTo($minimum)) {
            return $original;
        }

        $weeks = (int) ceil($original->startOfDay()->diffInDays($minimum) / 7);

        return $original->addWeeks($weeks);
    }

    /** El mismo día y mes, en el primer año en que todavía no pasó. */
    private static function nextOccurrence(CarbonImmutable $original, CarbonImmutable $today): CarbonImmutable
    {
        $candidate = $original->setYear($today->year);

        return $candidate->startOfDay()->lessThan($today) ? $candidate->addYear() : $candidate;
    }

    /**
     * Corre los días de todos los textos con una fecha escrita («12 de diciembre», «Sábado 12 de diciembre, 2026»).
     * Un texto sin año se entiende del año del evento original.
     */
    private static function shiftTexts(mixed $value, int $days, int $year): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => self::shiftTexts($item, $days, $year), $value);
        }

        if (! is_string($value)) {
            return $value;
        }

        $months = implode('|', self::MONTHS);
        $weekdays = implode('|', self::WEEKDAYS);

        return preg_replace_callback(
            "/(?:\b({$weekdays})\s+)?\b(\d{1,2}) de ({$months})(?:(,?\s+)(\d{4}))?/iu",
            function (array $match) use ($days, $year) {
                [$all, $weekday, $day, $month] = $match;
                $separator = $match[4] ?? '';
                $writtenYear = $match[5] ?? '';

                $date = CarbonImmutable::create((int) ($writtenYear ?: $year), array_search(mb_strtolower($month), self::MONTHS, true) + 1, (int) $day);
                $moved = $date->addDays($days);

                $text = '';

                if ($weekday !== '') {
                    $name = self::WEEKDAYS[$moved->dayOfWeek];
                    $text = (mb_strtoupper(mb_substr($weekday, 0, 1)) === mb_substr($weekday, 0, 1) ? mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1) : $name).' ';
                }

                $text .= $moved->day.' de '.self::MONTHS[$moved->month - 1];

                return $writtenYear !== '' ? $text.$separator.$moved->year : $text;
            },
            $value,
        ) ?? $value;
    }
}
