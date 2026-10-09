<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cuentas abiertas desde el sitio: además del usuario que entrega el equipo, se entra con el correo
 * o con Google.
 * - users.google_id: la cuenta de Google vinculada (el «sub» que devuelve Google, nunca cambia).
 * - users.avatar_url: la foto de esa cuenta.
 * Los correos pasan a minúsculas, que es como se buscan al iniciar sesión (salvo los que chocarían
 * con otro igual en distinta caja: esos se dejan como están y se resuelven a mano).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 64)->nullable()->unique()->after('email_verified_at');
            $table->string('avatar_url', 500)->nullable()->after('google_id');
        });

        DB::statement(<<<'SQL'
            UPDATE users SET email = LOWER(email)
            WHERE email IS NOT NULL
              AND email <> LOWER(email)
              AND NOT EXISTS (
                  SELECT 1 FROM users AS other
                  WHERE LOWER(other.email) = LOWER(users.email) AND other.id <> users.id
              )
        SQL);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['google_id', 'avatar_url']);
        });
    }
};
