<?php

namespace App\Payments;

use App\Support\Offers;

/**
 * Cuánto cuesta una compra de plan: el precio mensual de hoy (con su descuento, config
 * «bida.reseller_plans» y Ajustes) por los meses que se pagan, menos el descuento por pagar por
 * adelantado (config «payments.prepay»), en la moneda de la pasarela. En bolivianos se redondea a
 * Bs enteros, que es como se lee en el QR y en la app del banco.
 */
final class SubscriptionPrice
{
    /** Meses que se pueden pagar de una vez, con su descuento en %: [1 => 0, 3 => 5, 12 => 10]. */
    public static function prepayOptions(): array
    {
        $options = [];

        foreach ((array) config('payments.prepay', [1 => 0]) as $months => $discount) {
            if ((int) $months >= 1) {
                $options[(int) $months] = max(0, min(90, (int) $discount));
            }
        }

        ksort($options);

        return $options ?: [1 => 0];
    }

    /** Monto con dos decimales («97.00») para la orden. */
    public static function amount(string $plan, int $months, string $currency): string
    {
        $monthly = Offers::planPrice(config("bida.reseller_plans.{$plan}"));
        $discount = self::prepayOptions()[$months] ?? 0;
        $usd = $monthly * $months * (100 - $discount) / 100;

        $amount = strtoupper($currency) === 'BOB'
            ? round($usd * (float) config('bida.currency.bob_rate', 6.96))
            : round($usd, 2);

        return number_format($amount, 2, '.', '');
    }

    /** Tabla para la pantalla de compra: plan => meses => monto. */
    public static function table(string $currency): array
    {
        $table = [];

        foreach (array_keys(config('bida.reseller_plans', [])) as $plan) {
            foreach (array_keys(self::prepayOptions()) as $months) {
                $table[$plan][$months] = self::amount($plan, $months, $currency);
            }
        }

        return $table;
    }
}
