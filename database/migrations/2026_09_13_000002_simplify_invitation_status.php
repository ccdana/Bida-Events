<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El estado de publicación queda en dos valores: active (el enlace funciona) e
 * inactive (el enlace no se muestra). Borradores, suspendidas y expiradas pasan a inactive.
 */
return new class extends Migration
{
    public function up(): void
    {
        // El enum de PostgreSQL es un varchar con restricción CHECK: se quita antes de cambiar los valores
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE invitations DROP CONSTRAINT IF EXISTS invitations_status_check');
        }

        Schema::table('invitations', function (Blueprint $table) {
            $table->string('status', 20)->default('inactive')->change();
        });

        DB::table('invitations')->where('status', '!=', 'active')->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        DB::table('invitations')->where('status', 'inactive')->update(['status' => 'draft']);

        Schema::table('invitations', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
        });
    }
};
