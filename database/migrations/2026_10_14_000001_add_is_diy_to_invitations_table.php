<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «Hazlo tú»: invitaciones que arma su propio dueño desde el sitio (App\Support\DiyInvitations).
 * Mientras no se publica no lleva paquete y tiene todo desbloqueado; al publicarla queda con el
 * nivel que pagó (gratis, basico, estandar o premium) en la misma columna «package».
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->boolean('is_diy')->default(false)->after('reseller_id');
            $table->index(['user_id', 'is_diy']);
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_diy']);
            $table->dropColumn('is_diy');
        });
    }
};
