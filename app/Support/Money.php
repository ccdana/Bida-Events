<?php

namespace App\Support;

/**
 * Cómo se muestra el dinero en todo el sitio. La moneda sale de config('bida.currency'), así que
 * ninguna vista escribe la moneda a mano: cambiarla es tocar una sola línea.
 *
 * Los montos se guardan como enteros en la moneda vigente (dólares). Los pagos de revendedores
 * guardan además su propia moneda, porque los primeros se cobraron en bolivianos.
 */
final class Money
{
    /** «US$ 29» (o «US$ 29,50» si tiene centavos). */
    public static function format(int|float|string|null $amount, ?string $currency = null): string
    {
        return self::symbol($currency).' '.self::number($amount);
    }

    /** Solo el número, con coma decimal cuando hace falta: 29, 1.200, 29,50. */
    public static function number(int|float|string|null $amount): string
    {
        $value = (float) $amount;
        $decimals = floor($value) === $value ? 0 : 2;

        return number_format($value, $decimals, ',', '.');
    }

    public static function symbol(?string $currency = null): string
    {
        $currency ??= self::code();

        return match ($currency) {
            'BOB' => 'Bs',
            default => (string) config('bida.currency.symbol', 'US$'),
        };
    }

    public static function code(): string
    {
        return (string) config('bida.currency.code', 'USD');
    }

    /**
     * Convierte un precio en bolivianos a la moneda vigente, redondeado a un número entero
     * (se usa una sola vez, al pasar los precios guardados de bolivianos a dólares).
     */
    public static function fromBolivianos(int|float|null $amount): ?int
    {
        if ($amount === null) {
            return null;
        }

        return max(0, (int) round($amount / (float) config('bida.currency.bob_rate', 6.96)));
    }
}
