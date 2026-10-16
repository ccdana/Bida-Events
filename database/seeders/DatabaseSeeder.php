<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ClientCredentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // En local el administrador entra con «password» (lo recuerda el login). Fuera de local, una
        // contraseña de SEED_ADMIN_PASSWORD o una generada que se muestra una sola vez en la consola.
        $password = app()->environment('local', 'testing')
            ? 'password'
            : (string) (env('SEED_ADMIN_PASSWORD') ?: app(ClientCredentials::class)->password());

        User::create([
            'name' => 'Administrador Bida',
            'username' => 'admin',
            'email' => 'admin@test.com',
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        if (! app()->environment('local', 'testing') && ! env('SEED_ADMIN_PASSWORD')) {
            $this->command?->warn('Administrador creado. Usuario: admin · Contraseña: '.$password);
        }

        $this->call([
            EventTypeSeeder::class,
            ShowcaseInvitationsSeeder::class,
            // Después de las muestras: se le asigna una de ellas
            ClientUserSeeder::class,
        ]);
    }
}
