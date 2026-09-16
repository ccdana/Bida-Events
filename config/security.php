<?php

/*
|--------------------------------------------------------------------------
| Seguridad del sitio
|--------------------------------------------------------------------------
|
| Ajustes que endurecen las respuestas públicas. La política de contenido se
| envía en modo "solo reporte" hasta que CSP_ENFORCE sea true: primero se
| observa qué bloquearía y recién después se exige.
|
*/

return [

    'csp' => [
        'enabled' => env('CSP_ENABLED', true),
        'enforce' => env('CSP_ENFORCE', false),
    ],

    /*
    | Proxies en los que se confía para leer la IP real del visitante
    | (X-Forwarded-For). Vacío = no confiar en ninguno. Si el hosting o el CDN
    | está delante, hay que declararlo aquí, porque de eso dependen los límites
    | por IP de login, RSVP, votos y fotos.
    */
    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),

];
