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

    // Lo que va después de facebook.com/
    'facebook' => env('BIDA_FACEBOOK', 'bidaevents'),

    // Invitación activa que se muestra como vista previa en la portada (vacío para usar una imagen)
    'demo_slug' => env('BIDA_DEMO_SLUG', 'xv-isabella'),

    // Invitaciones de muestra (una por plantilla): se prueban en "Plantillas" sin guardar nada y el teléfono de la portada recorre sus aperturas
    'demo_invitations' => ['xv-isabella', 'boda-camila-andres', 'bautizo-emilia', 'cumple-daniela-30', 'tarjeta-ana-luis', 'tarjeta-libro-aventuras'],

    /*
    | Eventos que rotan en la portada y en el login. Cada uno usa una foto de
    | la lista "images" y "event" lo une con su plantilla de muestra, para que en
    | la portada cambien junto con el teléfono. Agregar un evento lo suma a la animación.
    */
    'showcase' => [
        ['phrase' => 'tu boda', 'label' => 'bodas', 'image' => 'event-boda', 'event' => 'boda'],
        ['phrase' => 'tu bautizo', 'label' => 'bautizos', 'image' => 'event-bautizo', 'event' => 'bautizo'],
        ['phrase' => 'tu cumpleaños', 'label' => 'cumpleaños', 'image' => 'event-cumpleanos', 'event' => 'cumple'],
        ['phrase' => 'tus XV años', 'label' => 'XV años', 'image' => 'event-xv', 'event' => 'xv'],
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

    /*
    | Tarjetas de 1200×630 que se ven al compartir un enlace en WhatsApp o Facebook.
    | Se recortan de las fotos de "images" con `php artisan bida:imagenes-compartir`
    | y quedan en public/images/share/{nombre}.jpg. "focus" es el punto vertical del
    | recorte: 0 arriba, 1 abajo. Las invitaciones sin foto de portada usan la de su evento.
    */
    'share_images' => [
        'inicio' => ['image' => 'servicio-enlace', 'focus' => 0.45],
        'boda' => ['image' => 'event-boda', 'focus' => 0.35],
        'xv' => ['image' => 'event-xv', 'focus' => 0.25],
        'bautizo' => ['image' => 'event-bautizo', 'focus' => 0.8],
        'cumple' => ['image' => 'event-cumpleanos', 'focus' => 0.15],
        // Tarjeta del Día del Amor: sin foto propia todavía, usa la de la pareja
        'amor' => ['image' => 'event-boda', 'focus' => 0.35],
    ],

    /*
    | Páginas por tipo de evento (/invitaciones-de-boda, etc.). Cada una tiene su
    | muestra embebida, sus preguntas y un código propio en el mensaje de WhatsApp,
    | para saber desde qué página escribió el cliente.
    */
    'landings' => [
        'invitaciones-de-boda' => [
            'event' => 'boda',
            'link' => 'Invitaciones de boda',
            'code' => 'BODA',
            'demo' => 'boda-camila-andres',
            'image' => 'event-boda',
            'label' => 'Bodas',
            'title' => 'Invitaciones digitales de boda en Bolivia',
            'description' => 'Invitación web para tu boda con sobre que se abre, confirmación de asistencia por invitado, padrinos, mesa de regalos y mapa. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de boda que tus invitados abren desde WhatsApp',
            'intro' => 'Un sobre que se abre al entrar, su foto en arco y todo lo que sus invitados necesitan saber: cuándo, dónde, qué ponerse y cómo confirmar.',
            'highlights' => [
                ['icon' => 'envelope-open', 'title' => 'Un sobre que se abre', 'text' => 'La invitación empieza como un sobre sellado que el invitado toca para abrir.'],
                ['icon' => 'users-three', 'title' => 'Padrinos y cortejo', 'text' => 'Presenten a quienes los acompañan, con nombre y el papel de cada uno.'],
                ['icon' => 'gift', 'title' => 'Mesa de regalos', 'text' => 'Datos de la cuenta con QR, lluvia de sobres o el enlace a su lista de regalos.'],
                ['icon' => 'qr-code', 'title' => 'Confirmación con pase', 'text' => 'Cada invitado confirma cuántas personas van y recibe un pase QR para la entrada.'],
            ],
            'faqs' => [
                ['¿Podemos poner la ceremonia y la recepción en lugares distintos?', 'Sí. El itinerario muestra cada momento con su hora y la ubicación lleva mapa y el botón para llegar.'],
                ['¿Cómo evitamos que vengan más personas de las invitadas?', 'Cada invitado recibe su propio enlace con los lugares reservados; al confirmar no puede pasar de ese número.'],
                ['¿Podemos compartir las fotos de la boda después?', 'Sí. Después de la fiesta la invitación pasa a mostrar las fotos oficiales y las que subieron sus invitados.'],
            ],
            'whatsapp' => 'Hola {brand}, nos casamos y queremos una invitación digital para nuestra boda.',
        ],
        'invitaciones-xv-anos' => [
            'event' => 'xv',
            'link' => 'Invitaciones de XV años',
            'code' => 'XV',
            'demo' => 'xv-isabella',
            'image' => 'event-xv',
            'label' => 'XV años',
            'title' => 'Invitaciones digitales de XV años en Bolivia',
            'description' => 'Invitación web para quince años con telón que se abre, chambelanes y damas, playlist, encuestas y confirmación con pase QR. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de XV años con telón, música y pase de entrada',
            'intro' => 'Un telón que se abre al entrar, su foto a pantalla completa y una invitación donde los invitados también participan: votan, sugieren canciones y suben fotos.',
            'highlights' => [
                ['icon' => 'crown-simple', 'title' => 'Un telón que se abre', 'text' => 'La invitación empieza con un telón que el invitado toca para descubrir a la quinceañera.'],
                ['icon' => 'users-three', 'title' => 'Chambelanes y padrinos', 'text' => 'Presenta a tu corte y a tus padrinos, cada uno con su papel en la fiesta.'],
                ['icon' => 'music-notes', 'title' => 'Playlist y encuestas', 'text' => 'Tus invitados sugieren las canciones del baile y votan en juegos antes de la fiesta.'],
                ['icon' => 'camera', 'title' => 'Fotomural en vivo', 'text' => 'Durante la fiesta suben fotos desde el celular y todos las ven al instante.'],
            ],
            'faqs' => [
                ['¿Se puede poner el vals y la ceremonia de velas en el itinerario?', 'Sí. Cada momento lleva su hora, un ícono y una descripción corta.'],
                ['¿Mis papás pueden ver quién confirmó?', 'Sí. Tienen un panel con la lista de invitados, cuántos confirmaron y el reporte en PDF o Excel.'],
                ['¿Puede sonar mi canción al abrir la invitación?', 'Sí. La música empieza cuando el invitado abre el telón y puede pausarla cuando quiera.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una invitación digital para unos XV años.',
        ],
        'invitaciones-de-bautizo' => [
            'event' => 'bautizo',
            'link' => 'Invitaciones de bautizo',
            'code' => 'BAUT',
            'demo' => 'bautizo-emilia',
            'image' => 'event-bautizo',
            'label' => 'Bautizos',
            'title' => 'Invitaciones digitales de bautizo en Bolivia',
            'description' => 'Invitación web para bautizo con nubes que se abren, padrinos, horario de la misa y la recepción, mapa y confirmación de asistencia. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de bautizo tranquilas, claras y fáciles de compartir',
            'intro' => 'Una pila bautismal que se llena al entrar, la foto del bebé en un medallón y los datos que la familia necesita: la misa, la recepción, los padrinos y cómo llegar.',
            'highlights' => [
                ['icon' => 'drop', 'title' => 'Una apertura con agua', 'text' => 'La invitación empieza con una jarra que vierte agua sobre la pila al tocarla.'],
                ['icon' => 'hands-praying', 'title' => 'Padrinos primero', 'text' => 'Los padrinos encabezan la sección de familia, con su nombre y un mensaje.'],
                ['icon' => 'map-pin', 'title' => 'Misa y recepción', 'text' => 'Horario de la iglesia y de la recepción, con mapa y botón para llegar a cada lugar.'],
                ['icon' => 'images', 'title' => 'Fotos para la familia', 'text' => 'Después del bautizo, la misma invitación reúne las fotos del día.'],
            ],
            'faqs' => [
                ['¿Sirve también para primera comunión o presentación?', 'Sí. Cambiamos los textos y las secciones a la celebración que tengan.'],
                ['¿Los abuelos van a poder abrirla?', 'Sí. Se abre en el navegador del celular desde el enlace, sin instalar nada ni crear cuentas.'],
                ['¿Podemos poner a los padrinos y a la familia?', 'Sí. Hay una sección para padrinos y otra para la familia, cada una con su nombre y su papel.'],
            ],
            'whatsapp' => 'Hola {brand}, queremos una invitación digital para un bautizo.',
        ],
        'invitaciones-de-cumpleanos' => [
            'event' => 'cumple',
            'link' => 'Invitaciones de cumpleaños',
            'code' => 'CUMP',
            'demo' => 'cumple-daniela-30',
            'image' => 'event-cumpleanos',
            'label' => 'Cumpleaños',
            'title' => 'Invitaciones digitales de cumpleaños en Bolivia',
            'description' => 'Invitación web de cumpleaños con pastel y velas que se soplan, confeti, playlist, juegos y confirmación de asistencia. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de cumpleaños para que la fiesta empiece antes',
            'intro' => 'Un pastel con velas que el invitado sopla al entrar, confeti y una invitación con juegos, playlist y todo lo necesario para llegar a la fiesta.',
            'highlights' => [
                ['icon' => 'cake', 'title' => 'Velas que se soplan', 'text' => 'La invitación empieza con un pastel: el invitado lo toca y se apagan las velas.'],
                ['icon' => 'chart-bar', 'title' => 'Juegos antes de la fiesta', 'text' => 'Encuestas divertidas que tus invitados responden y ven los resultados al instante.'],
                ['icon' => 'music-notes', 'title' => 'Playlist entre todos', 'text' => 'Cada invitado sugiere la canción que no puede faltar en la pista.'],
                ['icon' => 't-shirt', 'title' => 'Dress code con ejemplos', 'text' => 'Colores de la fiesta y sugerencias con foto, para que todos combinen.'],
            ],
            'faqs' => [
                ['¿Sirve para cumpleaños infantiles?', 'Sí. Adaptamos colores, textos y secciones a la edad y al tema de la fiesta.'],
                ['¿Puede mostrar la edad que cumplo?', 'Sí. La plantilla pone la edad en grande sobre el pastel.'],
                ['¿Qué pasa si cambia la hora o el lugar?', 'Se actualiza en el mismo enlace y tus invitados ven siempre la versión correcta.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una invitación digital para un cumpleaños.',
        ],

        // ── Tarjetas estacionales: sin paquetes de invitación, el precio se consulta por WhatsApp
        'tarjetas-dia-del-amor' => [
            'event' => 'amor',
            'kind' => 'card',
            'link' => 'Tarjetas del Día del Amor',
            'code' => 'AMOR',
            'demo' => 'tarjeta-ana-luis',
            'image' => 'event-boda',
            'label' => 'Día del Amor',
            'for' => 'decir lo que sientes',
            'title' => 'Tarjetas digitales para el Día del Amor en Bolivia',
            'description' => 'Una carta digital para tu pareja este 21 de septiembre: se abre desatando una cinta, lleva su foto, tu mensaje escrito a mano, el tiempo que llevan juntos y su canción. Lista para enviar por WhatsApp.',
            'heading' => 'Este 21 de septiembre, mándale una carta que se abre',
            'intro' => 'Una carta atada con una cinta que tu pareja desata con el dedo. Adentro, su foto, lo que le quieres decir, cuánto tiempo llevan juntos y su canción.',
            'features_note' => 'Y si quiere, te responde desde la misma carta.',
            'demo_note' => 'Ábrela dentro del teléfono y escribe una respuesta. Es una muestra: nada de lo que hagas se guarda.',
            'price_note' => 'Precio especial de temporada: escríbenos y te lo pasamos al momento.',
            'highlights' => [
                ['icon' => 'envelope-open', 'title' => 'Una carta que se desata', 'text' => 'Tu pareja toca la cinta, se suelta el moño y la carta se despliega.'],
                ['icon' => 'heart-straight', 'title' => 'Tu mensaje, escrito a mano', 'text' => 'La dedicatoria aparece con letra manuscrita, con tu firma al final.'],
                ['icon' => 'hourglass-medium', 'title' => 'Juntos desde', 'text' => 'Los años, meses y días que llevan juntos, contados desde su fecha.'],
                ['icon' => 'chat-circle-text', 'title' => 'Te puede responder', 'text' => 'Su respuesta te llega a tu panel y solo tú la lees.'],
            ],
            'faqs' => [
                ['¿Cuánto tarda en estar lista?', 'Nos mandas la foto, el mensaje y la fecha, y la tenemos el mismo día. Si la pides el 21, escríbenos temprano.'],
                ['¿Se puede mandar a una amiga o a la familia?', 'Sí. Cambiamos los textos para el Día de la Amistad o para quien quieras.'],
                ['¿Quién más puede ver la carta?', 'Solo quien tenga el enlace. No aparece en buscadores y la respuesta la lees solo tú.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una tarjeta digital para el Día del Amor.',
        ],
    ],

    /*
    | Origen de cada contacto. Los enlaces de campaña llevan utm_source, utm_medium y
    | utm_campaign (o ?ref= en material impreso). El sitio recuerda el origen 30 días y
    | agrega un código corto al mensaje de WhatsApp, por ejemplo «Ref. BODA-FB-MAYO».
    | Aquí se abrevian las fuentes conocidas; las demás usan sus primeras letras.
    | Para armar un enlace: php artisan bida:enlace-campana
    */
    'lead_sources' => [
        'facebook' => 'FB',
        'fb' => 'FB',
        'instagram' => 'IG',
        'ig' => 'IG',
        'tiktok' => 'TT',
        'google' => 'GO',
        'whatsapp' => 'WA',
        'youtube' => 'YT',
        'qr' => 'QR',
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
            'premium' => true,
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
