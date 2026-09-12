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

    'structured_modules' => [
        // Registra en el log las invitaciones que todavía se leen desde invitation_data.json_data
        'log_json_fallback' => env('LOG_JSON_MODULE_FALLBACK', true),
    ],

    'rate_limits' => [
        // Peticiones por minuto; los endpoints públicos se limitan por IP e invitación
        'login' => env('RATE_LIMIT_LOGIN', 5),
        'rsvp' => env('RATE_LIMIT_RSVP', 10),
        'songs' => env('RATE_LIMIT_SONGS', 10),
        'photos' => env('RATE_LIMIT_PHOTOS', 10),
        'votes' => env('RATE_LIMIT_VOTES', 30),
    ],

    'database' => [
        // Lazy loading: cargar relaciones solo cuando sea necesario
        'lazy_loading' => env('DB_LAZY_LOADING', false),
        
        // Query limit: útil para debug en desarrollo
        'query_limit' => env('DB_QUERY_LIMIT', 100),
    ],

    'blade' => [
        // Compilar vistas con caché
        'cache_compiled' => true,
        
        // Usar componentes en lugar de includes cuando sea posible
        'use_components' => true,
    ],

    'http' => [
        // Cache headers para invitaciones públicas (desactivado por defecto para evitar datos obsoletos)
        'public_invitation_cache' => env('HTTP_CACHE_ENABLED', false),
        'public_invitation_ttl' => 300,
        'guest_invitation_ttl' => 120,
    ],

    'cdn' => [
        // Usar CDN para assets estáticos (si está configurado)
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL', ''),
    ],
];
