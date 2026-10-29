<?php

namespace App\Support;

/** Cuánto falta para un evento, dicho corto: «hoy», «mañana», «faltan 23 días» o «faltan 3 meses». */
final class EventCountdown
{
    public static function label(?int $days): string
    {
        return match (true) {
            $days === null => '',
            $days < 0 => 'ya pasó',
            $days === 0 => 'hoy',
            $days === 1 => 'mañana',
            $days <= 60 => "faltan {$days} días",
            default => 'faltan '.(int) round($days / 30).' meses',
        };
    }

    /** Días enteros desde hoy hasta la fecha del evento (negativo si ya pasó); null sin fecha. */
    public static function days(?\Carbon\CarbonInterface $date): ?int
    {
        return $date ? (int) now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false) : null;
    }
}
