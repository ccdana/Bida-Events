<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idiomas (config/localization.php):
 * - invitations.locale: el idioma de la invitación, el que eligió quien la creó. Define los textos
 *   por defecto y los botones que ve el invitado; lo que escribió el cliente no se traduce.
 * - users.locale: el idioma en que la cuenta se abrió; es el de sus correos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->string('locale', 5)->default('es')->after('template');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
