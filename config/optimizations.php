<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Optimizations Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración de optimizaciones de rendimiento para Laravel 12
    |
    */

    'cache' => [
        'enabled' => env('CACHE_OPTIMIZATIONS_ENABLED', false),

        'invitations' => [
            'ttl' => env('INVITATION_CACHE_TTL', 3600), // 1 hora
            'playlist_ttl' => 300, // 5 minutos
            'fotomural_ttl' => 300, // 5 minutos
            'polls_ttl' => 300, // 5 minutos
        ],
    ],

    'retention' => [
        // Días después del evento que se conservan las fotos del fotomural (comando invitations:purge-contributions)
        'photos_days' => env('RETENTION_PHOTOS_DAYS', 180),

        // Días que se conservan los Excel y PDF que pidió el cliente
        'exports_days' => env('RETENTION_EXPORTS_DAYS', 7),
    ],

    'rate_limits' => [
        // Peticiones por minuto; los endpoints públicos se limitan por IP e invitación
        'login' => env('RATE_LIMIT_LOGIN', 5),
        'rsvp' => env('RATE_LIMIT_RSVP', 10),
        'songs' => env('RATE_LIMIT_SONGS', 10),
        'photos' => env('RATE_LIMIT_PHOTOS', 10),
        'votes' => env('RATE_LIMIT_VOTES', 30),
        'replies' => env('RATE_LIMIT_REPLIES', 5),
        // Puerta del evento: un teléfono escanea muchos pases seguidos, pero no más de uno por segundo
        'door' => env('RATE_LIMIT_DOOR', 90),
    ],

    'http' => [
        // Cache headers para invitaciones públicas (desactivado por defecto para evitar datos obsoletos)
        'public_invitation_cache' => env('HTTP_CACHE_ENABLED', false),
        'public_invitation_ttl' => 300,
        'guest_invitation_ttl' => 120,
    ],
];
