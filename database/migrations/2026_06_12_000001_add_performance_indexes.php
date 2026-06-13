<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índices para invitations
        Schema::table('invitations', function (Blueprint $table) {
            // Query: where slug + where status
            $table->index(['slug', 'status'])->change();
            // Query: where user_id
            $table->index('user_id');
            // Query: where status + where expires_at
            $table->index(['status', 'expires_at']);
        });

        // Índices para guests
        Schema::table('guests', function (Blueprint $table) {
            // Query: where invitation_id
            $table->index('invitation_id');
        });

        // Índices para guest_contributions
        Schema::table('guest_contributions', function (Blueprint $table) {
            // Query: where invitation_id + where type
            $table->index(['invitation_id', 'type']);
            // Query: where guest_id
            $table->index('guest_id');
        });

        // Índices para invitation_data
        Schema::table('invitation_data', function (Blueprint $table) {
            // Query: where invitation_id + where feature_code
            $table->index(['invitation_id', 'feature_code']);
        });

        // Índices para poll_votes
        Schema::table('poll_votes', function (Blueprint $table) {
            // Query: where invitation_id + where poll_id
            $table->index(['invitation_id', 'poll_id']);
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['slug', 'status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status', 'expires_at']);
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['invitation_id']);
        });

        Schema::table('guest_contributions', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'type']);
            $table->dropIndex(['guest_id']);
        });

        Schema::table('invitation_data', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'feature_code']);
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'poll_id']);
        });
    }
};
