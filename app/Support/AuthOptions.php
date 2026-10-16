<?php

namespace App\Support;

/**
 * Qué formas de entrar ofrece hoy el sitio: abrir una cuenta nueva (config «auth.registration») y
 * entrar con Google (solo si hay credenciales de Google en .env). Las usan las vistas y los
 * controladores de acceso.
 */
final class AuthOptions
{
    public static function registrationOpen(): bool
    {
        return (bool) config('auth.registration', true);
    }

    public static function googleEnabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
