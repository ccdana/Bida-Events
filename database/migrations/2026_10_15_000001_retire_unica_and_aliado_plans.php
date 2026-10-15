<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los planes «Única» y «Aliado» dejaron de existir (config/bida.php, «reseller_plans»). Una cuenta
 * que todavía los tuviera pasa al Inicial, que incluye lo mismo que el Aliado, y sus precios
 * guardados en Ajustes se descartan. El historial de pagos conserva la clave con la que se cobró.
 */
return new class extends Migration
{
    private const RETIRED = ['unica', 'aliado'];

    public function up(): void
    {
        DB::table('users')->whereIn('reseller_plan', self::RETIRED)->update(['reseller_plan' => 'inicial']);

        $stored = DB::table('site_settings')->where('key', 'reseller_plans')->value('value');
        $plans = is_string($stored) ? json_decode($stored, true) : null;

        if (is_array($plans) && array_intersect(array_keys($plans), self::RETIRED) !== []) {
            DB::table('site_settings')
                ->where('key', 'reseller_plans')
                ->update(['value' => json_encode(array_diff_key($plans, array_flip(self::RETIRED)))]);
        }
    }

    public function down(): void
    {
        // Sin vuelta atrás: los planes ya no están en la configuración
    }
};
