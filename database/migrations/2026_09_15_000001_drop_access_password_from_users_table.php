<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La contraseña del cliente ya no se guarda de forma descifrable: se muestra una sola vez al crearla
 * y, si se pierde, el administrador genera una nueva desde el editor. Solo queda el hash.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'access_password')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('access_password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('access_password')->nullable()->after('password');
        });
    }
};
