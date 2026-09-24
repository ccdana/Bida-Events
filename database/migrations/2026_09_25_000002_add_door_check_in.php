<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Control de entrada el día del evento.
 *
 * - invitations.door_token: el enlace secreto que el organizador le pasa al personal de la
 *   puerta. Quien lo abre queda habilitado para registrar ingresos de esa invitación; generar uno
 *   nuevo deja sin acceso al anterior.
 * - guests.checked_in_passes / checked_in_at: cuántas personas del pase ya entraron y cuándo
 *   entró la primera. Un pase de 4 puede entrar en dos tandas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('door_token', 64)->nullable()->unique();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->unsignedSmallInteger('checked_in_passes')->default(0);
            $table->timestamp('checked_in_at')->nullable();

            $table->index(['invitation_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex(['invitation_id', 'checked_in_at']);
            $table->dropColumn(['checked_in_passes', 'checked_in_at']);
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropUnique(['door_token']);
            $table->dropColumn('door_token');
        });
    }
};
