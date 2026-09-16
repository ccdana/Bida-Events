<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moderación del fotomural y la playlist: el cliente puede ocultar una foto o una canción sin
 * borrarla, así el historial se conserva y la invitación deja de mostrarla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->string('moderation_status', 20)->default('visible')->after('type');
            $table->index(['invitation_id', 'type', 'moderation_status'], 'guest_contributions_moderation_idx');
        });
    }

    public function down(): void
    {
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->dropIndex('guest_contributions_moderation_idx');
            $table->dropColumn('moderation_status');
        });
    }
};
