<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Paquete de cada invitación (basico, estandar, premium; ver App\Support\Packages) y el WhatsApp
 * que recibe las confirmaciones del paquete Estándar. Las invitaciones que ya existen quedan sin
 * paquete, es decir con todo incluido, igual que hasta hoy.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('package', 16)->nullable()->after('template');
        });

        Schema::table('invitation_rsvp_settings', function (Blueprint $table) {
            $table->string('whatsapp', 20)->nullable()->after('declined_text');
        });
    }

    public function down(): void
    {
        Schema::table('invitation_rsvp_settings', function (Blueprint $table) {
            $table->dropColumn('whatsapp');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('package');
        });
    }
};
