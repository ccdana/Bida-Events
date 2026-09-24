<?php

use App\Support\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Los precios pasan de bolivianos a dólares.
 *
 * - Lo guardado en Ajustes (paquetes, temporadas y planes de revendedor) se convierte al tipo de
 *   cambio oficial (6,96 Bs = 1 USD) y se redondea a entero.
 * - Los pagos de revendedores registrados hasta hoy se cobraron en bolivianos: no se convierten,
 *   se marcan con su moneda para que el historial siga diciendo lo que de verdad se pagó.
 */
return new class extends Migration
{
    private const PRICE_FIELDS = ['price', 'promo_price'];

    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('amount');
        });

        DB::table('subscription_payments')->update(['currency' => 'BOB']);

        if (! Schema::hasTable('site_settings')) {
            return;
        }

        foreach (['packages', 'season', 'reseller_plans'] as $group) {
            $row = DB::table('site_settings')->where('key', $group)->first();

            if (! $row) {
                continue;
            }

            $value = json_decode((string) $row->value, true);

            if (! is_array($value) || ($value['currency'] ?? null) === 'USD') {
                continue;
            }

            $value = $this->convert($value);
            $value['currency'] = 'USD';

            DB::table('site_settings')->where('key', $group)->update(['value' => json_encode($value)]);
        }

        Cache::forget('bida.site-settings');
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }

    /** Convierte cada price/promo_price, en cualquier nivel (temporadas y planes van por clave). */
    private function convert(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->convert($item);
            } elseif (in_array($key, self::PRICE_FIELDS, true) && is_numeric($item)) {
                $value[$key] = Money::fromBolivianos((float) $item);
            }
        }

        return $value;
    }
};
