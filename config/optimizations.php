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
