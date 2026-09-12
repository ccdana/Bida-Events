<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            // Se mantiene poll_id textual durante el periodo de fallback; nullOnDelete conserva los votos
            $table->foreignId('invitation_poll_id')
                ->nullable()
                ->after('poll_id')
                ->constrained('invitation_polls')
                ->nullOnDelete();

            $table->index(['invitation_poll_id', 'option_index']);
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropForeign(['invitation_poll_id']);
            $table->dropIndex(['invitation_poll_id', 'option_index']);
            $table->dropColumn('invitation_poll_id');
        });
    }
};
