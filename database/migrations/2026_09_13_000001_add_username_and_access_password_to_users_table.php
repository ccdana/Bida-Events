<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * El inicio de sesión pasa a ser con usuario. Los clientes se crean solo con su
 * nombre, así que el correo deja de ser obligatorio, y la contraseña generada se
 * guarda cifrada (access_password) para que el administrador pueda consultarla.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 60)->nullable()->after('name');
            $table->text('access_password')->nullable()->after('password');
            $table->string('email')->nullable()->change();
        });

        // Los usuarios existentes reciben su usuario a partir del correo (admin@test.com -> admin)
        $taken = [];

        DB::table('users')->orderBy('id')->get(['id', 'name', 'email'])->each(function ($user) use (&$taken) {
            $base = Str::slug(Str::before((string) $user->email, '@') ?: (string) $user->name, '.') ?: 'usuario';
            $username = $base;
            $suffix = 2;

            while (in_array($username, $taken, true)) {
                $username = $base.$suffix++;
            }

            $taken[] = $username;
            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'access_password']);
        });
    }
};
