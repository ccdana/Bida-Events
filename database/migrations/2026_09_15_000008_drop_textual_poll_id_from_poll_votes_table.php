<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El voto pasa a apuntar a la encuesta por su fila (invitation_poll_id) y deja de guardar el
 * identificador textual. Antes de borrar la columna se completa la relación con poll_key; los votos
 * de encuestas que ya no existen no se pueden relacionar y se eliminan (no se podían contar).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('poll_votes', 'poll_id')) {
            return;
        }

        DB::table('poll_votes')
            ->whereNull('invitation_poll_id')
            ->orderBy('id')
            ->chunkById(500, function ($votes) {
                foreach ($votes as $vote) {
                    $pollId = DB::table('invitation_polls')
                        ->where('invitation_id', $vote->invitation_id)
                        ->where('poll_key', $vote->poll_id)
                        ->value('id');

                    if ($pollId) {
                        DB::table('poll_votes')->where('id', $vote->id)->update(['invitation_poll_id' => $pollId]);
                    }
                }
            });

        DB::table('poll_votes')->whereNull('invitation_poll_id')->delete();

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique(['invitation_id', 'poll_id', 'voter_key']);
        });

        // Este índice ya lo quitó 2026_09_12_000006 en instalaciones nuevas
        if (Schema::hasIndex('poll_votes', 'poll_votes_invitation_id_poll_id_index')) {
            Schema::table('poll_votes', function (Blueprint $table) {
                $table->dropIndex('poll_votes_invitation_id_poll_id_index');
            });
        }

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropColumn('poll_id');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            // Un voto por encuesta y por votante, ahora contra la fila real de la encuesta
            $table->unique(['invitation_poll_id', 'voter_key'], 'poll_votes_poll_voter_unique');
        });
    }

    public function down(): void
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropUnique('poll_votes_poll_voter_unique');
            $table->string('poll_id', 100)->nullable()->after('invitation_id');
        });

        DB::statement('UPDATE poll_votes SET poll_id = (
            SELECT poll_key FROM invitation_polls WHERE invitation_polls.id = poll_votes.invitation_poll_id
        )');

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->index(['invitation_id', 'poll_id']);
            $table->unique(['invitation_id', 'poll_id', 'voter_key']);
        });
    }
};
