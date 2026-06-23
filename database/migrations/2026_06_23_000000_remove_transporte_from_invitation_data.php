<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar cualquier fila de datos independiente del módulo transporte
        DB::table('invitation_data')->where('feature_code', 'transporte')->delete();

        // 2. Limpiar el toggle "transporte" dentro de los módulos de configuración
        $configs = DB::table('invitation_data')->where('feature_code', 'config')->get();

        foreach ($configs as $config) {
            $data = json_decode($config->json_data, true);
            
            if (isset($data['modulos']['transporte'])) {
                unset($data['modulos']['transporte']);
                
                DB::table('invitation_data')
                    ->where('id', $config->id)
                    ->update(['json_data' => json_encode($data)]);
            }
        }
    }

    public function down(): void
    {
        // No hay método de reversión exacto porque eliminamos datos obsoletos.
    }
};
