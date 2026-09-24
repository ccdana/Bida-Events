<?php

// Invitación de muestra "graduacion-mariana": plantilla «Birrete al aire».
// Sin fotos propias todavía: la portada muestra las iniciales en el arco. Cuando haya fotos de la
// sesión de graduación en Cloudinary, se suman en «imagen_hero» y en la galería.

return [
    'invitation' => [
        'slug' => 'graduacion-mariana',
        'title' => 'Graduación de Mariana',
        'template' => 'invitations.templates.graduacion-birrete',
        'event_type' => [
            'slug' => 'graduaciones',
            'name' => 'Graduaciones',
            'code' => 'graduacion',
            'kind' => 'invitation',
        ],
        'event_date' => '2026-12-12 19:00:00',
        'status' => 'active',
        'expires_at' => '2027-06-12',
    ],
    'modules' => [
        'config' => [
            'colores' => [
                'primary' => '#9C7A2E',
                'secondary' => '#1F2A44',
                'accent' => '#E9E2D0',
                'text' => '#1B2233',
                'background' => '#FBF9F4',
            ],
            'tipografias' => [
                'titulos' => 'Cinzel',
                'cuerpo' => 'Montserrat',
                'script' => 'Great Vibes',
            ],
            'modulos' => [
                'bienvenida' => true,
                'video' => false,
                'musica' => false,
                'galeria' => false,
                'itinerario' => true,
                'dress_code' => true,
                'destacados' => true,
                'ubicacion' => true,
                'hashtag' => true,
                'encuestas' => true,
                'playlist' => true,
                'regalos' => true,
                'rsvp' => true,
                'fotomural' => true,
                'cuenta_regresiva' => true,
                'agendar' => true,
                'post_evento' => false,
            ],
            'template' => 'invitations.templates.graduacion-birrete',
        ],
        'bienvenida' => [
            'nombre_quinceanera' => 'Mariana Rojas',
            'subtitulo' => 'Licenciatura en Arquitectura',
            'mensaje' => 'Cinco años de maquetas, noches sin dormir y mucho apoyo. Quiero celebrar este logro con quienes me acompañaron en el camino.',
            'fecha_texto' => 'Sábado 12 de diciembre',
            'mensaje_post_evento' => '¡Gracias por celebrar conmigo este logro!',
            'imagen_hero' => null,
        ],
        'ubicacion' => [
            'lat' => -17.3897,
            'lng' => -66.1568,
            'nombre_lugar' => 'Salón Los Portales',
            'direccion' => 'Av. Potosí 1450, Cochabamba',
            'maps_url' => 'https://maps.google.com/?q=-17.3897,-66.1568',
            'nota' => 'El acto de colación es a las 16:00 en el paraninfo; la fiesta empieza a las 19:00 en el salón.',
            'imagen_lugar' => null,
        ],
        'itinerario' => [
            'titulo' => 'Programa del día',
            'eventos' => [
                ['hora' => '16:00', 'titulo' => 'Acto de colación', 'icono' => 'ceremonia', 'descripcion' => 'Entrega de títulos en el paraninfo'],
                ['hora' => '17:30', 'titulo' => 'Fotos con la familia', 'icono' => 'fotos', 'descripcion' => 'Con toga, birrete y diploma'],
                ['hora' => '19:00', 'titulo' => 'Recepción', 'icono' => 'recepcion', 'descripcion' => 'Bienvenida en el salón'],
                ['hora' => '20:00', 'titulo' => 'Brindis', 'icono' => 'brindis', 'descripcion' => 'Unas palabras de agradecimiento'],
                ['hora' => '20:30', 'titulo' => 'Cena', 'icono' => 'cena', 'descripcion' => 'Menú de tres tiempos'],
                ['hora' => '22:00', 'titulo' => 'Fiesta', 'icono' => 'fiesta', 'descripcion' => 'DJ y pista abierta'],
            ],
        ],
        'dress_code' => [
            'sugerencias' => [
                [
                    'para' => 'Todos',
                    'titulo' => 'Formal',
                    'descripcion' => 'Es una noche de gala: traje, vestido largo o de cóctel.',
                    'ejemplos' => ['Traje oscuro', 'Vestido de cóctel', 'Vestido largo'],
                    'imagen' => null,
                ],
            ],
            'colores_permitidos' => [
                ['nombre' => 'Azul noche', 'hex' => '#1F2A44'],
                ['nombre' => 'Dorado', 'hex' => '#9C7A2E'],
                ['nombre' => 'Marfil', 'hex' => '#EDE6D6'],
            ],
            'evitar' => ['Blanco total'],
            'titulo' => 'Dress code',
            'estilo' => 'Formal',
            'descripcion' => 'Ven elegante: habrá fotos con la promoción.',
        ],
        'destacados' => [
            'chambelanes' => [
                ['nombre' => 'Carmen y Luis', 'detalle' => 'Mis papás'],
                ['nombre' => 'Andrea Rojas', 'detalle' => 'Mi hermana'],
            ],
            'damitas' => [
                ['nombre' => 'Diego Salinas', 'detalle' => 'Compañero de taller'],
                ['nombre' => 'Valeria Quiroga', 'detalle' => 'Compañera de tesis'],
            ],
            'padrinos' => [
                [
                    'rol' => 'Padrinos de promoción',
                    'nombres' => 'Arq. Jorge Méndez y Sra. Lucía Paz',
                    'mensaje' => 'Gracias por guiarnos hasta el último día.',
                ],
            ],
        ],
        'galeria' => ['fotos' => [], 'titulo' => 'El camino hasta aquí'],
        'musica' => ['titulo' => '', 'artista' => '', 'audio_url' => '', 'autoplay' => false],
        'video' => ['titulo' => '', 'video_url' => '', 'poster' => ''],
        'playlist' => [
            'titulo' => 'La música de la fiesta',
            'descripcion' => 'Sugiere la canción que no puede faltar.',
            'placeholder' => 'Nombre de la canción o link de YouTube',
        ],
        'hashtag' => [
            'hashtag' => '#MarianaArquitecta',
            'plataforma' => 'instagram',
            'texto_boton' => 'Comparte tus fotos',
        ],
        'encuestas' => [
            'preguntas' => [
                [
                    'id' => 'discurso',
                    'tipo' => 'single',
                    'pregunta' => '¿Cuánto durará el discurso?',
                    'opciones' => ['Menos de 2 minutos', 'Unos 5 minutos', 'Traigan almohada'],
                ],
                [
                    'id' => 'birrete',
                    'tipo' => 'yesno',
                    'pregunta' => '¿Lanzamos los birretes en la fiesta?',
                    'opciones' => ['¡Sí!', 'Mejor no'],
                ],
            ],
            'titulo' => 'Antes de la fiesta',
        ],
        'regalos' => [
            'sobres' => [
                'titulo' => 'Buzón de sobres',
                'direccion' => 'Habrá un buzón en la entrada del salón',
            ],
            'banco' => [],
            'titulo' => 'Regalos',
            'tienda_url' => '',
            'tienda_texto' => '',
            'opciones' => [],
        ],
        'post_evento' => ['titulo' => '', 'descripcion' => '', 'fotos' => [], 'enlace_externo' => ''],
        'rsvp' => [
            'titulo_confirmacion' => '¿Me acompañas?',
            'mensaje_personalizado' => 'Confirma antes del 1 de diciembre para reservar tu lugar.',
            'texto_confirmado' => '¡Gracias! Nos vemos en la celebración.',
            'texto_declinado' => 'Te voy a extrañar. ¡Gracias por avisar!',
        ],
        'cuenta_regresiva' => [],
        'agendar' => [],
        'fotomural' => [],
    ],
    'guests' => [
        [
            'name' => 'Familia Rojas Paz',
            'phone' => '71234501',
            'passes_allocated' => 4,
            'passes_confirmed' => 4,
            'status' => 'confirmed',
            'table_number' => null,
            'dietary_restrictions' => null,
            'qr_code_token' => 'Gr4dM4r1anaR0jasFam1l1aPaz000001',
            'confirmed_at' => '2026-09-20 18:10:00',
        ],
        [
            'name' => 'Diego Salinas',
            'phone' => '76543202',
            'passes_allocated' => 2,
            'passes_confirmed' => 0,
            'status' => 'pending',
            'table_number' => null,
            'dietary_restrictions' => null,
            'qr_code_token' => 'Gr4dM4r1anaD1egoSal1nas000000002',
            'confirmed_at' => null,
        ],
        [
            'name' => 'Valeria Quiroga',
            'phone' => '70011203',
            'passes_allocated' => 1,
            'passes_confirmed' => 1,
            'status' => 'confirmed',
            'table_number' => null,
            'dietary_restrictions' => 'Vegetariana',
            'qr_code_token' => 'Gr4dM4r1anaVal3r1aQu1roga0000003',
            'confirmed_at' => '2026-09-21 12:40:00',
        ],
    ],
    'contributions' => [],
    'poll_votes' => [
        ['poll' => 'discurso', 'option' => 1, 'guest' => 'Gr4dM4r1anaR0jasFam1l1aPaz000001', 'voter_key' => 'grad-vote-000000000000000000000000000001', 'created_at' => '2026-09-20 18:12:00'],
        ['poll' => 'birrete', 'option' => 0, 'guest' => 'Gr4dM4r1anaVal3r1aQu1roga0000003', 'voter_key' => 'grad-vote-000000000000000000000000000002', 'created_at' => '2026-09-21 12:41:00'],
    ],
];
