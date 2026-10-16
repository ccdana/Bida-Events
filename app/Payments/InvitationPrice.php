<?php

namespace App\Payments;

use App\EventProfiles\EventProfiles;
use App\Models\Invitation;
use App\Support\Offers;
use App\Support\Packages;

/**
 * Cuánto cuesta publicar una invitación de «Hazlo tú» con un nivel, o subirla de nivel: un pago
 * único, en dólares enteros como el resto de los precios (config «bida.diy.prices», Ajustes).
 *
 * - Publicarla con un nivel cuesta el precio de hoy de ese nivel (con su descuento, si tiene).
 * - Subir de nivel cobra la diferencia entre los dos precios de hoy (la gratuita no cuenta como pagada).
 * - Un diseño de una temporada con precio propio («diy_price» de la temporada) se publica con todo
 *   por ese precio único, y ya publicado no sube de nivel (ya tiene todo).
 *
 * En bolivianos (el QR) se redondea a Bs enteros, como los planes (SubscriptionPrice).
 */
final class InvitationPrice
{
    /** Precio de hoy de un nivel (0 el gratuito o uno sin precio). */
    public static function tierPrice(string $tier): int
    {
        $item = config("bida.diy.prices.{$tier}");

        return is_array($item) && isset($item['price']) ? Offers::price($item, true)['final'] : 0;
    }

    /**
     * Los niveles pagados con su precio de hoy y el normal tachado (null sin descuento), para mostrarlos.
     *
     * @return array<string, array{final: int, regular: int|null}>
     */
    public static function tierPrices(): array
    {
        return collect((array) config('bida.diy.prices', []))
            ->filter(fn ($item) => is_array($item) && isset($item['price']))
            ->map(fn (array $item) => Offers::price($item, true))
            ->all();
    }

    /** Precio único de la temporada del diseño, si su temporada sigue a la venta y lo tiene; si no, null. */
    public static function seasonPrice(Invitation $invitation): ?int
    {
        $key = app(EventProfiles::class)->forTemplate($invitation->template)->season();
        $season = $key ? config("bida.seasons.{$key}") : null;

        if (! is_array($season) || ! isset($season['diy_price']) || ! ($season['active'] ?? true)) {
            return null;
        }

        $endsAt = Offers::date($season['ends_at'] ?? null);

        return $endsAt !== null && now()->lessThan($endsAt) ? (int) $season['diy_price'] : null;
    }

    /** ¿Ya pagó un nivel? La gratuita (o un borrador) no cuenta. */
    public static function hasPaid(Invitation $invitation): bool
    {
        return Packages::isTier($invitation->package) && $invitation->package !== Packages::FREE;
    }

    /** Lo que cuesta llegar a $tier desde el nivel que ya tiene, en dólares (0: no hay nada que cobrar). */
    public static function due(Invitation $invitation, string $tier): int
    {
        $season = self::seasonPrice($invitation);

        if ($season !== null) {
            return self::hasPaid($invitation) ? 0 : $season;
        }

        $already = self::hasPaid($invitation) ? self::tierPrice((string) $invitation->package) : 0;

        return max(0, self::tierPrice($tier) - $already);
    }

    /** Un monto en dólares en la moneda de la pasarela, con dos decimales («132.00»). */
    public static function amount(int|float $usd, string $currency): string
    {
        $amount = strtoupper($currency) === 'BOB'
            ? round($usd * (float) config('bida.currency.bob_rate', 6.96))
            : round($usd, 2);

        return number_format($amount, 2, '.', '');
    }
}
