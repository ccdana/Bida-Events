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

    // Moneda de todos los precios (App\Support\Money). Los precios se guardan en esta moneda;
    // «bob_rate» es el tipo de cambio oficial con el que se pasaron los precios desde bolivianos.
    'currency' => [
        'code' => 'USD',
        'symbol' => 'US$',
        'bob_rate' => 6.96,
    ],

    // Datos de la empresa para las páginas legales (App\Support\LegalPages). (Supuesto) Sin razón
    // social ni NIT cargados todavía: se muestra la marca hasta completarlos en .env.
    'legal' => [
        'name' => env('BIDA_LEGAL_NAME'),
        'nit' => env('BIDA_LEGAL_NIT'),
        'updated_at' => '2026-09-23',
    ],

    // Solo dígitos, con código de país (591 para Bolivia)
    'whatsapp' => env('BIDA_WHATSAPP', '59170000000'),

    'email' => env('BIDA_EMAIL', 'hola@bida-events.com'),

    // Usuario sin @
    'instagram' => env('BIDA_INSTAGRAM', 'bidaevents'),

    'tiktok' => env('BIDA_TIKTOK', 'bidaevents'),

    // Lo que va después de facebook.com/
    'facebook' => env('BIDA_FACEBOOK', 'bidaevents'),

    // Invitación activa que se muestra como vista previa en la portada (vacío para usar una imagen)
    'demo_slug' => env('BIDA_DEMO_SLUG', 'xv-isabella'),

    // Invitaciones de muestra (una por plantilla): se prueban en "Plantillas" sin guardar nada y el teléfono de la portada recorre sus aperturas.
    // Las tarjetas de temporada no van aquí: tienen su sección propia (clave «season»).
    'demo_invitations' => ['xv-isabella', 'boda-camila-andres', 'bautizo-emilia', 'cumple-daniela-30', 'graduacion-mariana'],

    // Página «Hazlo tú» (/hazlo-tu, planes mensuales): las muestras que se prueban en su teléfono
    'professionals' => [
        'demos' => ['lienzo-casa-molina', 'graduacion-mariana', 'halloween-noche-diego'],
    ],

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
        ['phrase' => 'tu graduación', 'label' => 'graduaciones', 'image' => 'event-graduacion', 'event' => 'graduacion'],
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
            'stock' => 383518502,
            'alt' => 'Bebé envuelto en su manta blanca el día de su bautizo',
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
            'stock' => 624942002,
            'alt' => 'Quinceañera con vestido de gala rosa al aire libre',
        ],
        'event-graduacion' => [
            'path' => 'images/site/event-graduacion.webp',
            'size' => [1200, 1500],
            'stock' => 427428198,
            'alt' => 'Graduada con toga, birrete y su diploma el día de su graduación',
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
        'historia' => ['image' => 'event-boda', 'focus' => 0.35],
        // Sin foto propia todavía: la de la invitación en el celular
        'graduacion' => ['image' => 'event-graduacion', 'focus' => 0.3],
        'halloween' => ['image' => 'servicio-enlace', 'focus' => 0.45],
        'lienzo' => ['image' => 'servicio-enlace', 'focus' => 0.45],
        'hazlo' => ['image' => 'servicio-enlace', 'focus' => 0.45],
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
            'demos' => ['boda-camila-andres'],
            'image' => 'event-boda',
            'label' => 'Bodas',
            'title' => 'Invitaciones digitales de boda en Bolivia',
            'description' => 'Invitación web para tu boda con confirmación de asistencia por invitado, padrinos, mesa de regalos y mapa. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de boda que tus invitados abren desde WhatsApp',
            'intro' => 'Todo lo que sus invitados necesitan saber en un enlace: cuándo, dónde, qué ponerse y cómo confirmar. Eligen el diseño y lo armamos con sus fotos y sus colores.',
            'highlights' => [
                ['icon' => 'map-pin', 'title' => 'Ceremonia y recepción', 'text' => 'Cada momento con su hora y cada lugar con su mapa y el botón para llegar.'],
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
            'demos' => ['xv-isabella'],
            'image' => 'event-xv',
            'label' => 'XV años',
            'title' => 'Invitaciones digitales de XV años en Bolivia',
            'description' => 'Invitación web para quince años con chambelanes y padrinos, playlist, encuestas, fotomural y confirmación con pase QR. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de XV años con música, juegos y pase de entrada',
            'intro' => 'Una invitación donde los invitados también participan: votan, sugieren canciones y suben fotos. Eliges el diseño y lo armamos con tus fotos y tus colores.',
            'highlights' => [
                ['icon' => 'users-three', 'title' => 'Chambelanes y padrinos', 'text' => 'Presenta a tu corte y a tus padrinos, cada uno con su papel en la fiesta.'],
                ['icon' => 'music-notes', 'title' => 'Playlist y encuestas', 'text' => 'Tus invitados sugieren las canciones del baile y votan en juegos antes de la fiesta.'],
                ['icon' => 'camera', 'title' => 'Fotomural en vivo', 'text' => 'Durante la fiesta suben fotos desde el celular y todos las ven al instante.'],
                ['icon' => 'qr-code', 'title' => 'Pase de entrada', 'text' => 'Cada invitado confirma desde su enlace y recibe un pase QR para la puerta.'],
            ],
            'faqs' => [
                ['¿Se puede poner el vals y la ceremonia de velas en el itinerario?', 'Sí. Cada momento lleva su hora, un ícono y una descripción corta.'],
                ['¿Mis papás pueden ver quién confirmó?', 'Sí. Tienen un panel con la lista de invitados, cuántos confirmaron y el reporte en PDF o Excel.'],
                ['¿Puede sonar mi canción al abrir la invitación?', 'Sí. La música empieza cuando el invitado abre la invitación y puede pausarla cuando quiera.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una invitación digital para unos XV años.',
        ],
        'invitaciones-de-bautizo' => [
            'event' => 'bautizo',
            'link' => 'Invitaciones de bautizo',
            'code' => 'BAUT',
            'demos' => ['bautizo-emilia'],
            'image' => 'event-bautizo',
            'label' => 'Bautizos',
            'title' => 'Invitaciones digitales de bautizo en Bolivia',
            'description' => 'Invitación web para bautizo con padrinos, horario de la misa y la recepción, mapa y confirmación de asistencia. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de bautizo tranquilas, claras y fáciles de compartir',
            'intro' => 'Los datos que la familia necesita, claros y en un solo enlace: la misa, la recepción, los padrinos y cómo llegar. Eligen el diseño y lo armamos con sus fotos.',
            'highlights' => [
                ['icon' => 'hands-praying', 'title' => 'Padrinos primero', 'text' => 'Los padrinos encabezan la sección de familia, con su nombre y un mensaje.'],
                ['icon' => 'map-pin', 'title' => 'Misa y recepción', 'text' => 'Horario de la iglesia y de la recepción, con mapa y botón para llegar a cada lugar.'],
                ['icon' => 'check-circle', 'title' => 'Confirmación sencilla', 'text' => 'Los invitados confirman desde el celular, sin instalar nada ni crear cuentas.'],
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
            'demos' => ['cumple-daniela-30'],
            'image' => 'event-cumpleanos',
            'label' => 'Cumpleaños',
            'title' => 'Invitaciones digitales de cumpleaños en Bolivia',
            'description' => 'Invitación web de cumpleaños con playlist, juegos, dress code y confirmación de asistencia. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de cumpleaños para que la fiesta empiece antes',
            'intro' => 'Una invitación con juegos, playlist y todo lo necesario para llegar a la fiesta. Eliges el diseño y lo armamos con tus fotos, tus colores y el tema de tu cumple.',
            'highlights' => [
                ['icon' => 'chart-bar', 'title' => 'Juegos antes de la fiesta', 'text' => 'Encuestas divertidas que tus invitados responden y ven los resultados al instante.'],
                ['icon' => 'music-notes', 'title' => 'Playlist entre todos', 'text' => 'Cada invitado sugiere la canción que no puede faltar en la pista.'],
                ['icon' => 't-shirt', 'title' => 'Dress code con ejemplos', 'text' => 'Colores de la fiesta y sugerencias con foto, para que todos combinen.'],
                ['icon' => 'qr-code', 'title' => 'Confirmación con pase', 'text' => 'Sabes quién va y cuántas personas; cada invitado recibe su pase QR.'],
            ],
            'faqs' => [
                ['¿Sirve para cumpleaños infantiles?', 'Sí. Adaptamos colores, textos y secciones a la edad y al tema de la fiesta.'],
                ['¿Puede mostrar la edad que cumplo?', 'Sí. La edad puede ir en grande en la portada, junto a tu nombre.'],
                ['¿Qué pasa si cambia la hora o el lugar?', 'Se actualiza en el mismo enlace y tus invitados ven siempre la versión correcta.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una invitación digital para un cumpleaños.',
        ],

        'invitaciones-de-graduacion' => [
            'event' => 'graduacion',
            'link' => 'Invitaciones de graduación',
            'code' => 'GRAD',
            'demos' => ['graduacion-mariana'],
            'image' => 'event-graduacion',
            'label' => 'Graduación',
            'for' => 'tu graduación',
            'title' => 'Invitaciones digitales de graduación en Bolivia',
            'description' => 'Invitación web para tu graduación o promoción: horario del acto y de la fiesta, mapa, padrinos de promoción y confirmación de asistencia. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de graduación para celebrar el logro en grande',
            'intro' => 'Un diploma que se abre al tocar la cinta y todo lo que tus invitados necesitan: la hora del acto, la fiesta, el lugar y quiénes te acompañaron. Con tus fotos, tus colores y tu carrera.',
            'highlights' => [
                ['icon' => 'graduation-cap', 'title' => 'Acto y fiesta en un enlace', 'text' => 'El horario de la colación y el de la celebración, con el mapa de cada lugar.'],
                ['icon' => 'users-three', 'title' => 'Padrinos y compañeros', 'text' => 'Un espacio para agradecer a tus padrinos de promoción, tu familia y tus compañeros.'],
                ['icon' => 'qr-code', 'title' => 'Confirmación con pase', 'text' => 'Sabes quién va y cuántas personas; cada invitado recibe su pase QR.'],
                ['icon' => 'camera', 'title' => 'Las fotos del día', 'text' => 'Tus invitados suben sus fotos al fotomural y después quedan todas en el mismo enlace.'],
            ],
            'faqs' => [
                ['¿Sirve para una promoción entera?', 'Sí. Puede llevar el nombre de la promoción en lugar de una persona, y los padrinos de promoción en su propio espacio.'],
                ['¿Puedo poner el acto y la fiesta en lugares distintos?', 'Sí. El itinerario lleva cada momento con su hora y la ubicación explica cómo llegar.'],
                ['¿Qué pasa si cambia la hora del acto?', 'Se actualiza en el mismo enlace y tus invitados ven siempre la versión correcta.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una invitación digital para una graduación.',
        ],

        // ── Invitaciones de temporada: se venden a su precio de temporada mientras dure (config «season»)
        'invitaciones-de-halloween' => [
            'event' => 'halloween',
            'kind' => 'season',
            'link' => 'Invitaciones de Halloween',
            'code' => 'HALLO',
            'demos' => ['halloween-noche-diego'],
            'image' => 'servicio-enlace',
            'label' => 'Halloween',
            'for' => 'tu fiesta de Halloween',
            'title' => 'Invitaciones digitales para fiestas de Halloween en Bolivia',
            'description' => 'Invitación web para tu fiesta de disfraces: se abre encendiendo una calabaza, tus invitados confirman asistencia, votan y sugieren canciones. Lista para enviar por WhatsApp.',
            'heading' => 'Invitaciones de Halloween para tu fiesta de disfraces',
            'intro' => 'Una invitación que se enciende al tocar la calabaza, con la cuenta regresiva a la noche, el lugar, el disfraz que esperas y la confirmación de cada invitado.',
            'features_note' => 'Además de la cuenta regresiva y el mapa, lo que hace más divertida la previa.',
            'highlights' => [
                ['icon' => 'ghost', 'title' => 'Se enciende al abrirla', 'text' => 'Una calabaza que se ilumina al tocarla, murciélagos y la luna llena en la portada.'],
                ['icon' => 'mask-happy', 'title' => 'El disfraz, explicado', 'text' => 'Qué disfraces esperas, los colores de la noche y el premio al mejor disfraz.'],
                ['icon' => 'music-notes', 'title' => 'Playlist y votaciones', 'text' => 'Tus invitados sugieren canciones y votan la película o el concurso antes de la fiesta.'],
                ['icon' => 'qr-code', 'title' => 'Confirmación con pase', 'text' => 'Sabes quién va y cuántas personas; cada invitado recibe su pase QR.'],
            ],
            'faqs' => [
                ['¿Cuánto tarda en estar lista?', 'Nos mandas los datos de la fiesta y la tenemos en uno o dos días. Si la pides cerca del 31, escríbenos temprano.'],
                ['¿Sirve para una fiesta infantil?', 'Sí. Cambiamos los textos y los colores para que sea divertida y no dé miedo.'],
                ['¿Puedo usarla para una fiesta en un local?', 'Sí. En lugar de una persona puede llevar el nombre del local o del evento.'],
            ],
            'whatsapp' => 'Hola {brand}, quiero una invitación para mi fiesta de Halloween.',
        ],

        // ── Tarjetas estacionales: sin paquetes de invitación, el precio se consulta por WhatsApp
        'tarjetas-dia-del-amor' => [
            'event' => 'amor',
            'kind' => 'card',
            'link' => 'Tarjetas del Día del Amor',
            'code' => 'AMOR',
            'image' => 'event-boda',
            'label' => 'Día del Amor',
            'for' => 'decir lo que sientes',
            'title' => 'Tarjetas digitales para el Día del Amor en Bolivia',
            'description' => 'Tarjetas digitales para el 21 de septiembre: tu foto y tu mensaje en una tarjeta que se abre con un gesto y te puede responder. Para tu pareja, tus amigos o tu familia. Lista para enviar por WhatsApp.',
            'heading' => 'Tarjetas digitales para el Día del Amor y la Primavera',
            'intro' => 'Para tu pareja, tu mejor amiga o tu familia. Nos mandas tu foto y tu mensaje, eliges el diseño y la tarjeta llega por WhatsApp el mismo día.',
            'features_note' => 'Cada diseño se abre a su manera; esto lo tienen todos.',
            'demo_note' => 'Ábrela dentro del teléfono y escribe una respuesta. Es una muestra: nada de lo que hagas se guarda.',
            'highlights' => [
                ['icon' => 'image', 'title' => 'Tu foto y tu mensaje', 'text' => 'La tarjeta lleva su foto, tu carta y los recuerdos que quieras compartir.'],
                ['icon' => 'hand-tap', 'title' => 'Se abre con un gesto', 'text' => 'Cada diseño tiene su sorpresa: tocar, mantener presionado, deslizar o soplar.'],
                ['icon' => 'chat-circle-text', 'title' => 'Te puede responder', 'text' => 'Quien la recibe te deja una respuesta desde la misma tarjeta. Solo tú la lees.'],
                ['icon' => 'lock-simple', 'title' => 'Solo para quien la recibe', 'text' => 'Se abre con su enlace y no aparece en buscadores.'],
            ],
            'faqs' => [
                ['¿Cuánto tarda en estar lista?', 'Nos mandas la foto, el mensaje y la fecha, y la tenemos el mismo día. Si la pides el 21, escríbenos temprano.'],
                ['¿Se puede mandar a una amiga o a la familia?', 'Sí. Cambiamos los textos para el Día de la Amistad o para quien quieras.'],
                ['¿Quién más puede ver la tarjeta?', 'Solo quien tenga el enlace. No aparece en buscadores y la respuesta la lees solo tú.'],
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

    /*
    | Promoción de inauguración: los paquetes muestran su precio normal tachado y
    | cobran promo_price. Se apaga con BIDA_LAUNCH_PROMO=false, y «ends_at» le pone fecha de
    | término (al pasar, los precios vuelven solos a los normales). Se maneja desde el panel:
    | Ajustes (App\Support\SiteSettings) pisa estos valores.
    */
    'launch_promo' => [
        'active' => (bool) env('BIDA_LAUNCH_PROMO', true),
        'ends_at' => env('BIDA_LAUNCH_PROMO_ENDS_AT'),
        'label' => 'Promoción de inauguración',
    ],

    // Plantillas que hoy no se ofrecen (se apagan desde Ajustes); las invitaciones ya creadas siguen igual
    'templates_disabled' => [],

    /*
    | Lo que ofrece la marca, en la sección «Servicios» de la portada. «price» toma el
    | precio de hoy: «packages» (el paquete más barato), «season» (la temporada vigente) o
    | «reseller» (el plan mensual más barato para profesionales).
    | «href» acepta un ancla, «season» (la temporada o su página) o una URL.
    */
    'services' => [
        [
            'key' => 'invitaciones',
            'name' => 'Invitaciones digitales',
            'text' => 'La invitación web de tu evento: se abre con una animación, tus invitados confirman asistencia desde su enlace y tú sigues la lista desde tu panel.',
            'occasions' => ['Bodas', 'XV años', 'Bautizos', 'Cumpleaños', 'Y cualquier celebración'],
            'price' => 'packages',
            'href' => '#plantillas',
            'cta' => 'Ver las plantillas',
        ],
        [
            'key' => 'temporada',
            'name' => 'Diseños de temporada',
            'text' => 'Invitaciones y tarjetas que salen solo por unos días para una fecha especial, con un precio único mientras dure la temporada.',
            'occasions' => ['Halloween', 'Día del Amor y la Primavera', 'Próximas fechas especiales'],
            'price' => 'season',
            'href' => 'season',
            'cta' => 'Ver la temporada',
        ],
        [
            'key' => 'hazlo',
            'name' => 'Hazlo tú',
            'text' => 'Tu propio panel para armar invitaciones con nuestras plantillas, cuando quieras y para quien quieras: tu familia, tus clientes o tu negocio. Un plan al mes, sin comisión por invitación.',
            'occasions' => ['Si organizas varios eventos', 'Fotógrafos y salones', 'Emprendedores de eventos'],
            'price' => 'reseller',
            'href' => '/hazlo-tu',
            'cta' => 'Ver los planes',
        ],
    ],

    /*
    | Temporadas: diseños que se venden solo por unos días. Cada una es independiente: tiene su
    | interruptor («active»), su precio, su fecha de término (hora de La Paz) y sus diseños, y se
    | enciende o se apaga desde Ajustes sin tocar a la otra. En la portada cada temporada vigente
    | tiene su botón flotante (abajo a la derecha) que abre su panel; al pasar su fecha el botón
    | desaparece solo. «product» dice qué se vende (invitación o tarjeta) para los textos y
    | «landing» es su página de campaña. Para sumar diseños, agrega su muestra a «templates».
    |
    | Precios en dólares (config «currency»). (Supuesto) Halloween: US$ 26, US$ 20 de promoción.
    */
    'seasons' => [
        'amor' => [
            'code' => 'AMOR',
            'name' => 'Día del Amor y la Primavera',
            'product' => 'tarjeta',
            'date' => '21 de septiembre',
            'title' => 'Día del Amor y la Primavera',
            'text' => 'Tarjetas digitales para decirle lo que sientes a tu pareja, a tu mejor amiga o a tu familia. Llevan tu foto y tu mensaje, se abren con un gesto y llegan por WhatsApp.',
            'more_note' => 'Durante la temporada vamos sumando diseños.',
            'active' => true,
            // BIDA_SEASON_ENDS_AT era la fecha de la única temporada que había (esta)
            'ends_at' => env('BIDA_SEASON_AMOR_ENDS_AT', env('BIDA_SEASON_ENDS_AT', '2026-09-21 23:59:59')),
            'price' => 14,
            'promo_price' => 11,
            'promo_label' => 'Promoción de temporada',
            'templates' => ['tarjeta-ana-luis', 'tarjeta-libro-aventuras', 'historia-ana-luis'],
            'landing' => 'tarjetas-dia-del-amor',
            'whatsapp' => 'Hola {brand}, quiero una tarjeta del Día del Amor ({price}).',
        ],
        'halloween' => [
            'code' => 'HALLO',
            'name' => 'Halloween',
            'product' => 'invitación',
            'date' => '31 de octubre',
            'title' => 'Tu fiesta de Halloween empieza en la invitación',
            'text' => 'Invitaciones para tu fiesta de disfraces: se abren encendiendo una calabaza, tus invitados confirman asistencia, votan la película de la noche y sugieren las canciones.',
            'more_note' => 'Durante la temporada vamos sumando diseños.',
            'active' => true,
            'ends_at' => env('BIDA_SEASON_HALLOWEEN_ENDS_AT', '2026-10-31 23:59:59'),
            'price' => 26,
            'promo_price' => 20,
            'promo_label' => 'Promoción de temporada',
            'templates' => ['halloween-noche-diego'],
            'landing' => 'invitaciones-de-halloween',
            'whatsapp' => 'Hola {brand}, quiero una invitación para mi fiesta de Halloween ({price}).',
        ],
    ],

    'packages' => [
        [
            'key' => 'basico',
            'name' => 'Básico',
            'price' => 29,
            'promo_price' => 22,
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
            'price' => 57,
            'promo_price' => 43,
            'featured' => true,
            'summary' => 'La invitación completa, con confirmación de asistencia por invitado.',
            'features' => [
                'Todo lo del paquete Básico',
                'Galería de fotos y video',
                'Dress code, padrinos y cortejo',
                'Enlace personal para cada invitado',
                'Confirmación con pase QR',
                'Control de entrada: el portero escanea el pase',
            ],
        ],
        [
            'key' => 'premium',
            'name' => 'Premium',
            'price' => 99,
            'promo_price' => 72,
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

    /*
    | Planes de suscripción mensual para revendedores (fotógrafos, wedding planners, decoradores):
    | arman sus propias invitaciones dentro de «quota_per_month» por mes calendario (null = sin
    | tope). Cada plan desbloquea más cosas, y eso es lo que justifica pagar más:
    |  - «collections»: qué familias de plantillas puede usar (ver «collection» en
    |    App\Support\InvitationTemplates): lienzo (la genérica en blanco), clasica (XV, boda,
    |    bautizo, cumpleaños, graduación) y tematica (Halloween, tarjetas de temporada).
    | Todos los planes crean accesos para sus clientes (uno por evento), pero no más por mes que
    | las invitaciones que el plan permite crear (ResellerSubscription::canCreateClients).
    |  - «white_label»: el pie de sus invitaciones lleva su nombre comercial en vez de Bida Events.
    | «cycle_months» es cuánto extiende la suscripción cada pago; «templates» (opcional) recorta
    | todavía más el catálogo con una lista de plantillas. «promo_price» es el precio con descuento
    | (null = sin descuento): se muestra el normal tachado. Precio, descuento y cupo se cambian en Ajustes.
    | No se cobra automáticamente: el administrador registra cada pago desde Revendedores.
    |
    | (Supuesto) Precios, cupos y qué incluye cada plan, pendientes de confirmar con el dueño.
    */
    'reseller_plans' => [
        'inicial' => [
            'name' => 'Inicial',
            'price' => 9,
            'promo_price' => null,
            'quota_per_month' => 3,
            'collections' => ['lienzo'],
            'white_label' => false,
            'cycle_months' => 1,
            'templates' => null,
            'summary' => 'Para empezar: la plantilla en blanco, para diseñar a tu manera.',
            'features' => ['3 invitaciones al mes', 'Plantilla en blanco con todo editable', 'Acceso para tu cliente en cada evento', 'Confirmación de asistencia y reportes'],
        ],
        'aliado' => [
            'name' => 'Aliado',
            'price' => 17,
            'promo_price' => 14,
            'quota_per_month' => 8,
            'collections' => ['lienzo', 'clasica'],
            'white_label' => false,
            'cycle_months' => 1,
            'templates' => null,
            'summary' => 'Las plantillas de bodas, XV, bautizos, cumpleaños y graduaciones.',
            'features' => ['8 invitaciones al mes', 'Plantillas clásicas y la plantilla en blanco', 'Acceso para tu cliente en cada evento'],
        ],
        'emprendedor' => [
            'name' => 'Emprendedor',
            'price' => 36,
            'promo_price' => 29,
            'quota_per_month' => 20,
            'collections' => ['lienzo', 'clasica', 'tematica'],
            'white_label' => true,
            'cycle_months' => 1,
            'templates' => null,
            'summary' => 'Con tu marca y las temáticas de temporada: Halloween y las tarjetas.',
            'features' => ['20 invitaciones al mes', 'Todo el catálogo, con las temáticas y de temporada', 'Tu marca al pie, sin la de Bida Events', 'Acceso para tu cliente en cada evento'],
        ],
        'agencia' => [
            'name' => 'Agencia',
            'price' => 72,
            'promo_price' => 59,
            'quota_per_month' => null,
            'collections' => ['lienzo', 'clasica', 'tematica'],
            'white_label' => true,
            'cycle_months' => 1,
            'templates' => null,
            'summary' => 'Sin tope: para estudios que entregan invitaciones todas las semanas.',
            'features' => ['Invitaciones sin tope', 'Todo el catálogo, con las temáticas y de temporada', 'Tu marca al pie, sin la de Bida Events', 'Acceso para tu cliente en cada evento'],
        ],
    ],
];
