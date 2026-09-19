<?php

// Tarjeta de muestra del Día del Amor con la plantilla «Libro de aventuras».
// Reusa fotos y canción de la muestra de boda, que ya están en Cloudinary.

$photo = fn (string $path) => 'https://res.cloudinary.com/dwm7mniny/image/upload/'.$path;

$hero = $photo('v1789364856/bida-events/boda-camila-andres/hero/238291937_sjvdll.jpg');
$gallery = [
    $photo('v1789364872/bida-events/boda-camila-andres/gallery/258108791_jqgwpb.jpg'),
    $photo('v1789364874/bida-events/boda-camila-andres/gallery/532227184_rrvjtq.jpg'),
    $photo('v1789364877/bida-events/boda-camila-andres/gallery/192858832_eqojih.jpg'),
    $photo('v1789364879/bida-events/boda-camila-andres/gallery/296715644_n7kcrz.jpg'),
    $photo('v1789364882/bida-events/boda-camila-andres/gallery/271978218_tkel3r.jpg'),
];
$extra = [
    $photo('v1789364903/bida-events/boda-camila-andres/post-evento/300029166_hwqnuv.jpg'),
    $photo('v1789364906/bida-events/boda-camila-andres/post-evento/488429246_gpwvnc.jpg'),
    $photo('v1789364908/bida-events/boda-camila-andres/post-evento/381855881_y7i47o.jpg'),
    $photo('v1789364899/bida-events/boda-camila-andres/video-poster/398765982-frame_wwhndk.jpg'),
];

return [
    'invitation' => [
        'slug' => 'tarjeta-libro-aventuras',
        'title' => 'El libro de aventuras de Ana y Luis',
        'template' => 'invitations.templates.tarjeta-aventura',
        'event_type' => [
            'slug' => 'libro-de-aventuras',
            'name' => 'Libro de aventuras (Día del Amor)',
            'code' => 'aventura',
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
                'primary' => '#8A4B1F',
                'secondary' => '#5A3214',
                'accent' => '#F2C230',
                'text' => '#2B1D12',
                'background' => '#F7EEDC',
            ],
            'tipografias' => [
                'titulos' => 'Lora',
                'cuerpo' => 'Lora',
                'script' => 'Dancing Script',
            ],
            'modulos' => [
                'bienvenida' => true,
                'juntos_desde' => true,
                'dedicatoria' => true,
                'historia' => true,
                'recuerdos' => true,
                'collage' => true,
                'marcos' => true,
                'memoria' => true,
                'musica' => true,
                'respuesta' => true,
                'aventuras' => true,
            ],
            'template' => 'invitations.templates.tarjeta-aventura',
        ],
        'bienvenida' => [
            'subtitulo' => 'Nuestro libro de aventuras',
            'imagen_hero' => $hero,
            'imagen_hero_alt' => 'Ana y Luis abrazados al atardecer',
        ],
        'juntos_desde' => [
            'titulo' => 'Llevamos juntos',
            'fecha' => '2019-02-14',
        ],
        'dedicatoria' => [
            'de' => 'Luis',
            'para' => 'Ana',
            'mensaje' => "Te hice este libro porque las mejores aventuras de mi vida empezaron el día que te conocí, y quería tenerlas todas en un solo lugar.\n\nGracias por las mañanas de café, por reírte de mis chistes malos y por quedarte incluso en los días difíciles. Contigo aprendí que querer es elegirse todos los días, también cuando llueve y se nos olvida el paraguas.\n\nHoy te regalo flores amarillas, como manda la tradición, pero sobre todo te regalo todas las páginas que todavía nos faltan escribir. Feliz Día del Amor.",
            'firma' => 'Tu Luis',
        ],
        'historia' => [
            'titulo' => 'Nuestra historia',
            'capitulos' => [
                [
                    'titulo' => 'Cómo nos conocimos',
                    'fecha' => '2018-11-03',
                    'texto' => "Era una tarde de lluvia y los dos buscábamos refugio en la misma librería. Tú querías el último ejemplar de un libro de viajes y yo, por pura casualidad, lo tenía en la mano.\n\nTe lo dejé con una condición: que me contaras qué lugar del libro visitarías primero. Me dijiste que el mar, y desde entonces cada vez que llueve me acuerdo de tu risa entre los estantes.",
                    'foto' => $gallery[0],
                    'alt' => 'Ana y Luis sonriendo',
                ],
                [
                    'titulo' => 'Nuestra primera cita',
                    'fecha' => '2019-02-14',
                    'texto' => "Llegué veinte minutos antes y cambié de mesa tres veces. Tú llegaste con un girasol en la mano, porque según tú las rosas eran demasiado obvias.\n\nPedimos dos cafés y terminamos hablando hasta que cerraron el lugar. Caminamos por la plaza sin rumbo, contando historias de la infancia, planes imposibles y canciones que nos gustaban a los dos.\n\nCuando nos despedimos supe que algo había empezado. No sabía qué nombre ponerle, pero sabía que quería volver a verte al día siguiente, y al otro, y al otro.\n\nEsa noche escribí en una servilleta la fecha, por si algún día necesitaba acordarme. Todavía la tengo guardada en la billetera, un poco borrosa, pero con la tinta suficiente para recordarme que ahí comenzó nuestra aventura más grande.\n\nDesde entonces cada 14 celebramos con un girasol, aunque sea uno pequeñito comprado a la salida del trabajo.",
                ],
                [
                    'titulo' => 'El viaje que no olvidamos',
                    'fecha' => '2022-07-20',
                    'texto' => 'Se nos pinchó una llanta a mitad de camino y terminamos viendo el atardecer más lindo sentados sobre la maleta. A veces los mejores planes son los que salen mal.',
                    'foto' => $gallery[2],
                    'alt' => 'Atardecer en el camino',
                ],
            ],
        ],
        'recuerdos' => [
            'titulo' => 'Recuerdos especiales',
            'recuerdos' => [
                ['titulo' => 'El primer girasol', 'fecha' => '2019-02-14', 'texto' => 'Todavía está guardado entre las hojas de mi libro favorito.', 'foto' => $gallery[1], 'alt' => 'Ana con un girasol'],
                ['titulo' => 'Bailando bajo la lluvia', 'fecha' => '2020-03-08', 'texto' => 'Nos empapamos y fue perfecto.', 'foto' => $gallery[3], 'alt' => 'Ana y Luis bailando'],
                ['titulo' => 'Domingo de picnic', 'fecha' => '2021-10-17', 'texto' => 'Sándwiches aplastados y el mejor día del año.', 'foto' => $gallery[4], 'alt' => 'Picnic en el parque'],
            ],
        ],
        'collage' => [
            'titulo' => 'Nuestro collage',
            'fotos' => [$hero, ...$gallery, ...$extra],
        ],
        'marcos' => [
            'titulo' => 'Enmarcados para siempre',
            'fotos' => [
                ['url' => $gallery[0], 'alt' => 'La foto que más me gusta'],
                ['url' => $extra[0], 'alt' => 'Nuestro primer viaje'],
                ['url' => $gallery[4], 'alt' => 'Tu sonrisa de domingo'],
                ['url' => $extra[2], 'alt' => 'Los dos contra el mundo'],
            ],
        ],
        'memoria' => [
            'titulo' => 'Encuentra los pares',
            'mensaje_final' => '¡Los encontraste todos! Así de bien nos complementamos. Te quiero, Ana.',
            'fotos' => [$gallery[0], $gallery[1], $gallery[2], $gallery[3], $extra[0], $extra[1]],
        ],
        'musica' => [
            'titulo' => 'Nuestra canción',
            'artista' => 'Lady Gaga, Bruno Mars - Die With A Smile',
            'audio_url' => 'https://res.cloudinary.com/dwm7mniny/video/upload/v1789366356/bida-events/boda-camila-andres/musica/php4393_c6yqnh.mp3',
            'autoplay' => true,
        ],
        'respuesta' => [
            'titulo' => 'Escríbele a Luis',
            'descripcion' => 'Agrega tu propia hoja a este libro: solo él la va a leer.',
        ],
        'aventuras' => [
            'titulo' => 'Aventuras por vivir',
            'lista' => [
                ['titulo' => 'Ver el amanecer juntos en el lago Titicaca'],
                ['titulo' => 'Aprender a bailar salsa'],
                ['titulo' => 'Adoptar un perrito'],
                ['titulo' => 'Acampar bajo las estrellas en el salar de Uyuni'],
                ['titulo' => 'Cocinar juntos la receta de la abuela'],
                ['titulo' => 'Escribir la siguiente página de este libro'],
            ],
        ],
    ],
    'guests' => [],
    'contributions' => [],
    'poll_votes' => [],
];
