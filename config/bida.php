<?php

/*
|--------------------------------------------------------------------------
| Datos públicos de Bida Events
|--------------------------------------------------------------------------
|
| Los usan la página de inicio y el login. Los datos de contacto se pueden
| cambiar desde .env sin tocar las vistas; los paquetes, los tipos de evento
| y las fotos se editan aquí.
|
*/

return [

    'brand' => env('BIDA_BRAND', 'Bida Events'),

    'city' => env('BIDA_CITY', 'Cochabamba, Bolivia'),

    // Solo dígitos, con código de país (591 para Bolivia)
    'whatsapp' => env('BIDA_WHATSAPP', '59170000000'),

    'email' => env('BIDA_EMAIL', 'hola@bidaevents.com'),

    // Usuario sin @
    'instagram' => env('BIDA_INSTAGRAM', 'bidaevents'),

    'tiktok' => env('BIDA_TIKTOK', 'bidaevents'),

    // Invitación activa que se muestra como vista previa en la portada (vacío para usar una imagen)
    'demo_slug' => env('BIDA_DEMO_SLUG', 'xv-sofia'),

    /*
    | Eventos que rotan en la portada y en el login. Cada uno usa una foto de
    | la lista "images". Agregar un evento aquí lo suma a la animación.
    */
    'showcase' => [
        ['phrase' => 'tu boda', 'label' => 'bodas', 'image' => 'event-boda'],
        ['phrase' => 'tu bautizo', 'label' => 'bautizos', 'image' => 'event-bautizo'],
        ['phrase' => 'tu cumpleaños', 'label' => 'cumpleaños', 'image' => 'event-cumpleanos'],
        ['phrase' => 'tus XV años', 'label' => 'XV años', 'image' => 'event-xv'],
    ],

    // Franja de tipos de evento (ícono de Phosphor sin prefijo)
    'event_types' => [
        'Bodas' => 'heart',
        'Bautizos' => 'baby',
        'Cumpleaños' => 'cake',
        'XV años' => 'crown-simple',
        'Primera comunión' => 'church',
        'Baby shower' => 'baby-carriage',
        'Graduaciones' => 'graduation-cap',
        'Aniversarios' => 'champagne',
    ],

    /*
    | Fotos del sitio (licencia gratuita de Adobe Stock, ID en "stock"). Si
    | existe el archivo en public/{path} se usa; si no, se muestra un marcador
    | en blanco y negro de picsum. "alt" describe la foto para lectores de pantalla.
    */
    'images' => [
        'event-boda' => [
            'path' => 'images/site/event-boda.webp',
            'size' => [1200, 1500],
            'stock' => 238291944,
            'alt' => 'Novios abrazados frente con frente al atardecer el día de su boda',
        ],
        'event-bautizo' => [
            'path' => 'images/site/event-bautizo.webp',
            'size' => [1200, 1500],
            'stock' => 537001736,
            'alt' => 'Bebé vestido de blanco durante su bautizo en la iglesia',
        ],
        'event-cumpleanos' => [
            'path' => 'images/site/event-cumpleanos.webp',
            'size' => [1200, 1500],
            'stock' => 471100552,
            'alt' => 'Mujer soplando las velas de su pastel de cumpleaños junto a sus amigos',
        ],
        'event-xv' => [
            'path' => 'images/site/event-xv.webp',
            'size' => [1200, 1500],
            'stock' => 469198040,
            'alt' => 'Quinceañera con vestido de gala en un jardín',
        ],
        'nosotros' => [
            'path' => 'images/site/nosotros.webp',
            'size' => [900, 1125],
            'stock' => 476708229,
            'alt' => 'Organizadora de eventos trabajando en su escritorio',
        ],
        'servicio-enlace' => [
            'path' => 'images/site/servicio-enlace.webp',
            'size' => [1200, 840],
            'stock' => 520355428,
            'alt' => 'Mujer sonriendo mientras mira una invitación en su celular',
        ],
        'servicio-fotomural' => [
            'path' => 'images/site/servicio-fotomural.webp',
            'size' => [900, 900],
            'stock' => 625735140,
            'alt' => 'Invitado fotografiando a los novios con su celular durante la fiesta',
        ],
    ],

    'packages' => [
        [
            'key' => 'basico',
            'name' => 'Básico',
            'price' => 200,
            'summary' => 'Lo esencial para invitar con estilo y que nadie se pierda.',
            'features' => [
                'Portada con foto, nombre y mensaje',
                'Cuenta regresiva al gran día',
                'Ubicación con mapa y cómo llegar',
                'Itinerario del evento',
                'Música de fondo',
            ],
        ],
        [
            'key' => 'estandar',
            'name' => 'Estándar',
            'price' => 400,
            'featured' => true,
            'summary' => 'La invitación completa, con confirmación de asistencia por invitado.',
            'features' => [
                'Todo lo del paquete Básico',
                'Galería de fotos y video',
                'Dress code, padrinos y cortejo',
                'Enlace personal para cada invitado',
                'Confirmación con pase QR',
            ],
        ],
        [
            'key' => 'premium',
            'name' => 'Premium',
            'price' => 700,
            'summary' => 'Para que tus invitados participen antes, durante y después del evento.',
            'features' => [
                'Todo lo del paquete Estándar',
                'Encuestas y playlist colaborativa',
                'Fotomural en vivo',
                'Mesa de regalos y datos bancarios',
                'Galería de fotos después del evento',
                'Reporte de invitados en PDF y Excel',
            ],
        ],
    ],

];
