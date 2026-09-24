<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tres ajustes a la línea de revendedores:
 * - invitations.reseller_id: el revendedor que arma y administra la invitación. user_id queda para
 *   el cliente final del evento (el que ve sus invitados), que el revendedor crea si quiere.
 * - users.created_by_reseller_id: el cliente que creó un revendedor para uno de sus eventos; solo
 *   ese revendedor puede eliminarlo.
 * - subscription_payments.request_token: cada formulario de pago lleva un código único, así un
 *   doble clic no registra el mismo pago dos veces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->foreignId('reseller_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('created_by_reseller_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('request_token', 64)->nullable()->unique();
        });

        // Lo que ya crearon los revendedores pasa a ser suyo como revendedor, sin cliente asignado
        $resellerIds = DB::table('users')->where('is_reseller', true)->pluck('id');

        if ($resellerIds->isNotEmpty()) {
            DB::table('invitations')
                ->whereIn('user_id', $resellerIds)
                ->update(['reseller_id' => DB::raw('user_id'), 'user_id' => null]);
        }
    }

    public function down(): void
    {
        DB::table('invitations')
            ->whereNotNull('reseller_id')
            ->whereNull('user_id')
            ->update(['user_id' => DB::raw('reseller_id')]);

        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropUnique(['request_token']);
            $table->dropColumn('request_token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_reseller_id');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reseller_id');
        });
    }
};
