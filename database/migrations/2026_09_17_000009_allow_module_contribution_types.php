<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * guest_contributions.type era un enum (canción, foto, libro de firmas) y la base rechazaba
 * cualquier otro valor, como las respuestas de tarjeta (card_reply). Cada módulo declara su
 * propio tipo de aporte, así que la columna pasa a texto y el tipo lo valida la aplicación.
 */
return new class extends Migration
{
    private const LEGACY_TYPES = ['song_request', 'live_photo', 'guestbook_message'];

    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE guest_contributions DROP CONSTRAINT IF EXISTS guest_contributions_type_check');
        }

        // En SQLite change() rearma la tabla sin el CHECK del enum
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->string('type', 40)->change();
        });
    }

    public function down(): void
    {
        // Los aportes de módulos nuevos no caben en el enum anterior
        DB::table('guest_contributions')->whereNotIn('type', self::LEGACY_TYPES)->delete();

        // PostgreSQL no acepta el CHECK dentro de ALTER COLUMN: se vuelve a poner como restricción
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE guest_contributions ALTER COLUMN type TYPE varchar(255)');
            DB::statement("ALTER TABLE guest_contributions ADD CONSTRAINT guest_contributions_type_check CHECK (type IN ('".implode("', '", self::LEGACY_TYPES)."'))");

            return;
        }

        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->enum('type', self::LEGACY_TYPES)->change();
        });
    }
};
