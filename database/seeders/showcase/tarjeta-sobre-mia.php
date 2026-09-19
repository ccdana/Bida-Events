<?php

// Tarjeta de muestra «Sobre lacrado» (Día del Amor y la Amistad, 21 de septiembre).
// Reusa fotos y canción de la muestra de boda, que ya están en Cloudinary.

$photos = [
    'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364872/bida-events/boda-camila-andres/gallery/258108791_jqgwpb.jpg',
    'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364874/bida-events/boda-camila-andres/gallery/532227184_rrvjtq.jpg',
    'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364877/bida-events/boda-camila-andres/gallery/192858832_eqojih.jpg',
    'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364879/bida-events/boda-camila-andres/gallery/296715644_n7kcrz.jpg',
    'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364882/bida-events/boda-camila-andres/gallery/271978218_tkel3r.jpg',
];

return [
    'invitation' => [
        'slug' => 'tarjeta-sobre-mia',
        'title' => 'Sobre de Luis para Mía',
        'template' => 'invitations.templates.tarjeta-sobre',
        'event_type' => [
            'slug' => 'dia-del-amor',
            'name' => 'Día del Amor',
            'code' => 'amor',
            'kind' => 'card',
            'season' => 'amor',
        ],
        'event_date' => '2026-09-21 00:00:00',
        'status' => 'active',
        'expires_at' => '2027-03-21',
    ],
    'modules' => [
        'config' => [
            'colores' => [
                'primary' => '#F2A5B6',
                'secondary' => '#2E060C',
                'accent' => '#F2D7DB',
                'text' => '#FDF3EE',
                'background' => '#4A0D14',
            ],
            'tipografias' => [
                'titulos' => 'Dancing Script',
                'cuerpo' => 'Lato',
                'script' => 'Great Vibes',
            ],
            'modulos' => [
                'bienvenida' => true,
                'dedicatoria' => true,
                'galeria' => true,
                'video' => true,
                'musica' => true,
                'respuesta' => true,
            ],
            'template' => 'invitations.templates.tarjeta-sobre',
        ],
        'bienvenida' => [
            'subtitulo' => 'Feliz Día del Amor',
            'mensaje' => "Es increíble cómo pasa el tiempo a tu lado. Cada momento, hasta el más simple, significa mucho para mí porque lo viví contigo.",
            'imagen_hero' => $photos[0],
            'imagen_hero_alt' => 'Mía y Luis juntos',
        ],
        'dedicatoria' => [
            'de' => 'Luis',
            'para' => 'Mía',
            'mensaje' => "Gracias por tener paciencia conmigo, por entenderme incluso cuando soy difícil de entender y por quedarte a mi lado en todo.\n\nAprecio cada detalle tuyo, hasta los que crees que no noto, porque sí los noto.\n\nEstoy muy agradecido de tenerte en mi vida. Ojalá sigamos creciendo juntos y creando más recuerdos.",
            'firma' => 'Tu Luis',
        ],
        'galeria' => [
            'titulo' => 'Todo lo que llevamos juntos',
            'fotos' => $photos,
        ],
        'video' => [
            'titulo' => 'Mi persona favorita',
        ],
        'musica' => [
            'titulo' => 'Nuestra canción',
            'artista' => 'Lady Gaga, Bruno Mars - Die With A Smile',
            'audio_url' => 'https://res.cloudinary.com/dwm7mniny/video/upload/v1789366356/bida-events/boda-camila-andres/musica/php4393_c6yqnh.mp3',
            'autoplay' => false,
        ],
        'respuesta' => [
            'titulo' => 'Respóndele a Luis',
            'descripcion' => 'Elige una flor y escríbele unas palabras: solo él las va a leer.',
        ],
    ],
    'guests' => [],
    'contributions' => [],
    'poll_votes' => [],
];
