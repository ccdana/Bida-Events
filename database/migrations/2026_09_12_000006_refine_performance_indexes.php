<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conteos por estado de confirmación; también cubre las búsquedas solo por invitation_id
        Schema::table('guests', function (Blueprint $table) {
            $table->index(['invitation_id', 'status']);
        });
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['invitation_id']);
        });

        // Playlist y fotomural: where invitation_id + type order by created_at desc limit N
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->index(['invitation_id', 'type', 'created_at']);
        });
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'type']);
        });

        // Duplicados de restricciones únicas existentes
        Schema::table('invitation_data', function (Blueprint $table) {
            // unique(invitation_id, feature_code)
            $table->dropIndex(['invitation_id', 'feature_code']);
        });
        Schema::table('poll_votes', function (Blueprint $table) {
            // prefijo de unique(invitation_id, poll_id, voter_key)
            $table->dropIndex(['invitation_id', 'poll_id']);
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->index(['invitation_id', 'poll_id']);
        });
        Schema::table('invitation_data', function (Blueprint $table) {
            $table->index(['invitation_id', 'feature_code']);
        });

        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->index(['invitation_id', 'type']);
        });
        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'type', 'created_at']);
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->index(['invitation_id']);
        });
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'status']);
        });
    }
};
