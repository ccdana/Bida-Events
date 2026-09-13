<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Credenciales de los clientes creados desde el editor: usuario a partir del
 * nombre y una contraseña fácil de dictar por teléfono o WhatsApp.
 */
class ClientCredentials
{
    // Sin caracteres que se confunden al leerlos (l, 1, i, o, 0)
    private const PASSWORD_ALPHABET = 'abcdefghjkmnpqrstuvwxyz23456789';

    /** "María José Valenzuela" -> "maria.valenzuela"; si ya existe, "maria.valenzuela2". */
    public function username(string $name): string
    {
        $words = collect(preg_split('/\s+/', Str::lower(Str::ascii(trim($name)))) ?: [])
            ->map(fn (string $word) => preg_replace('/[^a-z0-9]/', '', $word))
            ->filter()
            ->values();

        $base = match ($words->count()) {
            0 => 'cliente',
            1 => $words->first(),
            default => $words->first().'.'.$words->last(),
        };
        $base = substr($base, 0, 50);

        $username = $base;
        $suffix = 2;

        while (User::where('username', $username)->exists()) {
            $username = $base.$suffix++;
        }

        return $username;
    }

    /** Contraseña de 8 caracteres en dos grupos, por ejemplo "k7mq-2hxa". */
    public function password(): string
    {
        $group = fn () => collect(range(1, 4))
            ->map(fn () => self::PASSWORD_ALPHABET[random_int(0, strlen(self::PASSWORD_ALPHABET) - 1)])
            ->implode('');

        return $group().'-'.$group();
    }
}
