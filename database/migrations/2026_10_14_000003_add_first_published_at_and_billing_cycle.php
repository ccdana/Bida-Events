<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * - invitations.first_published_at: cuándo se publicó por primera vez. El cupo mensual de los planes
 *   cuenta las invitaciones publicadas en el mes, no las creadas (los borradores no gastan cupo); ocultar
 *   y volver a publicar no la cuenta dos veces. Las que ya están publicadas toman su fecha de creación.
 * - users.subscription_cycle_months: cada cuántos meses se renueva sola la suscripción con tarjeta
 *   (1, 3 o 12): cada renovación cobrada extiende eso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->timestamp('first_published_at')->nullable()->after('status');
            $table->index(['reseller_id', 'first_published_at']);
        });

        DB::table('invitations')->where('status', 'active')->update(['first_published_at' => DB::raw('created_at')]);

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('subscription_cycle_months')->default(1)->after('subscription_renews_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('subscription_cycle_months');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['reseller_id', 'first_published_at']);
            $table->dropColumn('first_published_at');
        });
    }
};
