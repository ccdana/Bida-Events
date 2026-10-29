<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Quien tiene un plan ya no paga nivel por invitación: las de «Hazlo tú» que armó antes de comprarlo
 * pasan a ser del plan (SubscriptionActivator::absorbDiyInvitations lo hace con cada pago nuevo; esto
 * alcanza a las cuentas que ya lo habían pagado). Siguen a su nombre y lo pagado antes no se toca.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('invitations')
            ->where('is_diy', true)
            ->whereIn('user_id', DB::table('users')->where('is_reseller', true)->select('id'))
            ->update([
                'reseller_id' => DB::raw('user_id'),
                'is_diy' => false,
                'package' => null,
            ]);
    }

    public function down(): void
    {
        // Sin vuelta atrás: no se sabe cuáles eran de «Hazlo tú» ni con qué nivel
    }
};
