<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La flor (u otra reacción de temporada) con la que el destinatario responde una tarjeta.
 * Los códigos válidos los declara cada plantilla (InvitationTemplates, clave «reactions»).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->string('reaction', 30)->nullable()->after('content_text');
        });
    }

    public function down(): void
    {
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->dropColumn('reaction');
        });
    }
};
