<?php

// Tarjeta de muestra del Día del Amor (plantilla "Carta de amor").
// Reusa fotos y canción de la muestra de boda, que ya están en Cloudinary.

return [
    'invitation' => [
        'slug' => 'tarjeta-ana-luis',
        'title' => 'Carta de Luis para Ana',
        'template' => 'invitations.templates.tarjeta-amor',
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
                'primary' => '#A63A50',
                'secondary' => '#6B2433',
                'accent' => '#F2D7DB',
                'text' => '#2E1A1F',
                'background' => '#FFF8F5',
            ],
            'tipografias' => [
                'titulos' => 'Libre Baskerville',
                'cuerpo' => 'Lato',
                'script' => 'Great Vibes',
            ],
            'modulos' => [
                'bienvenida' => true,
                'dedicatoria' => true,
                'juntos_desde' => true,
                'galeria' => true,
                'musica' => true,
                'respuesta' => true,
            ],
            'template' => 'invitations.templates.tarjeta-amor',
        ],
        'bienvenida' => [
            'subtitulo' => 'Feliz Día del Amor',
            'imagen_hero' => 'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364856/bida-events/boda-camila-andres/hero/238291937_sjvdll.jpg',
            'imagen_hero_alt' => 'Ana y Luis abrazados al atardecer',
        ],
        'dedicatoria' => [
            'de' => 'Luis',
            'para' => 'Ana',
            'mensaje' => "Hoy quería escribirte algo que no se perdiera entre mensajes.\n\nGracias por las mañanas de café, por reírte de mis chistes malos y por quedarte incluso en los días difíciles. Contigo aprendí que querer es elegirse todos los días.\n\nFeliz Día del Amor. Que nos sigan sobrando los motivos para celebrarlo.",
            'firma' => 'Tu Luis',
        ],
        'juntos_desde' => [
            'titulo' => 'Llevamos juntos',
            'fecha' => '2019-02-14',
        ],
        'galeria' => [
            'titulo' => 'Nuestros momentos',
            'fotos' => [
                'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364872/bida-events/boda-camila-andres/gallery/258108791_jqgwpb.jpg',
                'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364874/bida-events/boda-camila-andres/gallery/532227184_rrvjtq.jpg',
                'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364877/bida-events/boda-camila-andres/gallery/192858832_eqojih.jpg',
            ],
        ],
        'musica' => [
            'titulo' => 'Nuestra canción',
            'artista' => 'Lady Gaga, Bruno Mars - Die With A Smile',
            'audio_url' => 'https://res.cloudinary.com/dwm7mniny/video/upload/v1789366356/bida-events/boda-camila-andres/musica/php4393_c6yqnh.mp3',
            'autoplay' => true,
        ],
        'respuesta' => [
            'titulo' => 'Respóndele a Luis',
            'descripcion' => 'Escríbele unas palabras: solo él las va a leer.',
        ],
    ],
    'guests' => [],
    'contributions' => [],
    'poll_votes' => [],
];
