<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El tipo de evento dice qué producto es (invitación o tarjeta), a qué perfil pertenece
 * (app/Events) y, en las tarjetas, a qué temporada. Así el panel filtra y agrupa sin adivinar
 * por el nombre de la plantilla.
 */
return new class extends Migration
{
    private const CODES = [
        'xv-anos' => 'xv',
        'bodas' => 'boda',
        'bautizos' => 'bautizo',
        'cumpleanos' => 'cumple',
    ];

    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->unique();
            $table->string('kind', 20)->default('invitation')->index();
            $table->string('season', 50)->nullable();
        });

        foreach (self::CODES as $slug => $code) {
            DB::table('event_types')->where('slug', $slug)->update(['code' => $code, 'kind' => 'invitation']);
        }
    }

    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropIndex(['kind']);
            $table->dropColumn(['code', 'kind', 'season']);
        });
    }
};
