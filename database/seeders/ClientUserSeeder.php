<?php

namespace Database\Seeders;

use App\Models\Invitation;
use App\Models\User;
use App\Support\ClientCredentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cliente de prueba para recorrer el panel del cliente (/client) con una invitación de muestra.
 *
 * La contraseña no está escrita en el código: se toma de SEED_CLIENT_PASSWORD o se genera y se
 * muestra una sola vez en la consola, igual que cuando el administrador crea un cliente.
 * Es idempotente y no toca un usuario que ya existe ni una invitación que ya tiene dueño.
 *
 *   php artisan db:seed --class=ClientUserSeeder
 */
class ClientUserSeeder extends Seeder
{
    public const USERNAME = 'cliente.prueba';

    /** Invitación de muestra que se le asigna si nadie más la tiene (SEED_CLIENT_INVITATION). */
    public const INVITATION = 'cumple-daniela-30';

    public function run(ClientCredentials $credentials): void
    {
        // Un usuario con clave conocida no debe aparecer solo en producción
        if (app()->isProduction() && ! env('SEED_CLIENT_IN_PRODUCTION', false)) {
            $this->command?->warn('ClientUserSeeder no corre en producción (SEED_CLIENT_IN_PRODUCTION=true para forzarlo).');

            return;
        }

        $client = User::where('username', self::USERNAME)->first();

        if ($client) {
            $this->command?->info('El cliente «'.self::USERNAME.'» ya existe; su contraseña no se cambia (se regenera desde el editor).');
        } else {
            $password = (string) (env('SEED_CLIENT_PASSWORD') ?: $credentials->password());

            $client = User::create([
                'name' => 'Cliente de prueba',
                'username' => self::USERNAME,
                'password' => Hash::make($password),
                'is_admin' => false,
            ]);

            $this->command?->info('Cliente creado. Usuario: '.self::USERNAME.' · Contraseña: '.$password);
            $this->command?->warn('Guárdala ahora: no se vuelve a mostrar.');
        }

        $slug = (string) (env('SEED_CLIENT_INVITATION') ?: self::INVITATION);

        // Solo una invitación sin dueño: nunca se le quita la suya a un cliente real
        $assigned = Invitation::where('slug', $slug)
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $client->id))
            ->update(['user_id' => $client->id]);

        $this->command?->info($assigned
            ? "Invitación «{$slug}» asignada al cliente de prueba."
            : "No se asignó «{$slug}»: no existe o ya tiene dueño.");
    }
}
