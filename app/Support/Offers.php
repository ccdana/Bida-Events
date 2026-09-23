<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Precios con promoción y temporada vigente, para la portada y las páginas por evento.
 * - Paquetes: con la promoción de inauguración encendida (config «bida.launch_promo») se cobra
 *   promo_price y el precio normal se muestra tachado.
 * - Temporada (config «bida.season»): tarjetas que se venden hasta ends_at; después no se ofrecen.
 */
final class Offers
{
    /** La promoción vale mientras esté encendida y no se haya pasado su fecha de término. */
    public static function launchPromoActive(?CarbonInterface $now = null): bool
    {
        if (! config('bida.launch_promo.active', false)) {
            return false;
        }

        $endsAt = self::date(config('bida.launch_promo.ends_at'));

        return $endsAt === null || ($now ?? now())->lessThan($endsAt);
    }

    /** Fecha de la configuración (o del panel) en la hora de Bolivia; null si está vacía o mal escrita. */
    public static function date(mixed $value): ?CarbonInterface
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return rescue(fn () => Carbon::parse($value, config('app.timezone')), null, report: false);
    }

    /**
     * Lo que se cobra hoy y el precio normal para tacharlo (null si no hay rebaja).
     *
     * @return array{final: int, regular: int|null}
     */
    public static function price(array $item, bool $promo): array
    {
        $regular = (int) $item['price'];
        $promoPrice = isset($item['promo_price']) ? (int) $item['promo_price'] : null;
        $onSale = $promo && $promoPrice !== null && $promoPrice < $regular;

        return ['final' => $onSale ? $promoPrice : $regular, 'regular' => $onSale ? $regular : null];
    }

    /** Paquetes con su precio de hoy (final_price) y el normal tachado (old_price) si hay promoción. */
    public static function packages(?array $packages = null): array
    {
        $promo = self::launchPromoActive();

        return array_map(function (array $package) use ($promo): array {
            $price = self::price($package, $promo);

            return $package + [
                'final_price' => $price['final'],
                'old_price' => $price['regular'],
                'promo_label' => $price['regular'] ? config('bida.launch_promo.label') : null,
            ];
        }, $packages ?? config('bida.packages', []));
    }

    /** El paquete más barato al precio de hoy («Paquetes desde 150 Bs»). */
    public static function lowestPackagePrice(): int
    {
        return (int) collect(self::packages())->min('final_price');
    }

    /**
     * La temporada vigente con su precio y sus plantillas de muestra, o null si ya terminó,
     * si falta la fecha o si no hay ninguna muestra activa.
     */
    public static function season(?CarbonInterface $now = null): ?array
    {
        $season = config('bida.season');
        $endsAt = is_array($season) ? self::date($season['ends_at'] ?? null) : null;

        if ($endsAt === null || ($now ?? now())->greaterThanOrEqualTo($endsAt)) {
            return null;
        }

        $demos = ShowcaseDemos::find($season['templates'] ?? []);

        if (! $demos) {
            return null;
        }

        $price = self::price($season, true);

        return $season + [
            'endsAt' => $endsAt,
            'final_price' => $price['final'],
            'old_price' => $price['regular'],
            'demos' => $demos,
        ];
    }
}
