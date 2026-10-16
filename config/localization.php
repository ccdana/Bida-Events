<?php

/*
|--------------------------------------------------------------------------
| Idiomas del sitio
|--------------------------------------------------------------------------
|
| El sitio es nativo en español e inglés; cualquier otro idioma lo resuelve el traductor del
| navegador, que respeta el atributo lang de cada página. App\Support\Localization usa esto.
|
| - Español en la raíz (/precios) e inglés bajo /en (/en/pricing): cada versión se indexa por separado
|   y se enlazan entre sí con hreflang.
| - Los textos viven en lang/en.json con el español como clave: __('Crea tu invitación'). Lo que no
|   tiene traducción se muestra en español.
| - Una invitación tiene su propio idioma (columna invitations.locale), el que eligió quien la creó:
|   no depende de la dirección ni del navegador.
|
*/

return [

    // Idioma de la raíz y de todo lo que no se traduce todavía (paneles y editor)
    'default' => 'es',

    'locales' => [
        'es' => ['name' => 'Español', 'short' => 'ES', 'og' => 'es_BO', 'prefix' => ''],
        'en' => ['name' => 'English', 'short' => 'EN', 'og' => 'en_US', 'prefix' => 'en'],
    ],

    /*
    | Direcciones del sitio en cada idioma, por la clave que tienen en español. Lo que no está aquí
    | se escribe igual en ambos (login, cookies…).
    */
    'paths' => [
        'en' => [
            'unete' => 'join',
            'precios' => 'pricing',
            'guia-invitaciones-digitales' => 'digital-invitations-guide',
            'registro' => 'sign-up',
            'contrasena/olvide' => 'password/forgot',
            'contrasena/nueva' => 'password/reset',
            'privacidad' => 'privacy',
            'terminos' => 'terms',
        ],
    ],

    /*
    | Formatos de fecha: los del español («15 de octubre de 2026») por su equivalente en cada idioma.
    | Localization::dateFormat() los usa; la fecha misma la traduce Carbon según el idioma activo.
    */
    'date_formats' => [
        'en' => [
            'j \d\e F \d\e Y' => 'F j, Y',
            'j \d\e F' => 'F j',
            'l j \d\e F \d\e Y' => 'l, F j, Y',
            'l j \d\e F' => 'l, F j',
            'l j \d\e F, Y' => 'l, F j, Y',
            'l j \d\e F · H:i \h' => 'l, F j · g:i A',
            'l j \d\e F · H:i' => 'l, F j · g:i A',
            'j \d\e F · H:i' => 'F j · g:i A',
            'j \d\e F, H:i' => 'F j, g:i A',
            'j \d\e F \d\e Y, H:i' => 'F j, Y, g:i A',
            'l j \d\e F \d\e Y, H:i' => 'l, F j, Y, g:i A',
            'F \d\e Y' => 'F Y',
            'H:i' => 'g:i A',
        ],
    ],

    // Separadores de los números: [decimal, miles]
    'number_separators' => [
        'es' => [',', '.'],
        'en' => ['.', ','],
    ],

    // Páginas por tipo de evento (config bida.landings): clave en español => dirección en cada idioma
    'landings' => [
        'en' => [
            'invitaciones-de-boda' => 'wedding-invitations',
            'invitaciones-xv-anos' => 'quinceanera-invitations',
            'invitaciones-de-bautizo' => 'baptism-invitations',
            'invitaciones-de-cumpleanos' => 'birthday-invitations',
            'invitaciones-de-graduacion' => 'graduation-invitations',
            'invitaciones-de-baby-shower' => 'baby-shower-invitations',
            'invitaciones-de-halloween' => 'halloween-invitations',
            'tarjetas-dia-del-amor' => 'love-day-cards',
        ],
    ],

];
