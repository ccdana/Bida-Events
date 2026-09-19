<?php

// Muestra de «The Story We Write Together» (perfil historia).
// Reusa fotos y canción de la muestra de boda, que ya están en Cloudinary.

$photo = fn (string $path) => 'https://res.cloudinary.com/dwm7mniny/image/upload/'.$path;

return [
    'invitation' => [
        'slug' => 'historia-ana-luis',
        'title' => 'La historia de Ana y Luis',
        'template' => 'invitations.templates.we-story-together',
        'event_type' => [
            'slug' => 'nuestra-historia',
            'name' => 'Nuestra historia',
            'code' => 'historia',
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
                'primary' => '#E8C872',
                'secondary' => '#1C2B5A',
                'accent' => '#3F6FA8',
                'text' => '#F3ECDA',
                'background' => '#0A1230',
            ],
            'tipografias' => [
                'titulos' => 'Cormorant Garamond',
                'cuerpo' => 'Lora',
                'script' => 'Cormorant Garamond',
            ],
            'modulos' => [
                'bienvenida' => true,
                'historia' => true,
                'juntos_desde' => true,
                'galeria' => true,
                'musica' => true,
                'dedicatoria' => true,
                'respuesta' => true,
            ],
            'template' => 'invitations.templates.we-story-together',
        ],
        'bienvenida' => [
            'nombre' => 'Ana',
            'nombre_pareja' => 'Luis',
            'nombre_quinceanera' => 'Ana & Luis',
            'subtitulo' => 'Lo vimos primero de lejos, como se ve la luna en el agua.',
            'imagen_hero' => $photo('v1789364856/bida-events/boda-camila-andres/hero/238291937_sjvdll.jpg'),
            'imagen_hero_alt' => 'Ana y Luis abrazados al atardecer',
        ],
        'juntos_desde' => [
            'titulo' => 'Juntos desde',
            'fecha' => '2019-02-14',
        ],
        'historia' => [
            'primeras_impresiones' => "Ella pensó que él hablaba demasiado. Él pensó que ella nunca se reía de sus chistes.\n\nLos dos se equivocaron, y tardaron un verano entero en admitirlo.",
            'cita' => 'cortazar',
            'momentos' => [
                [
                    'cuando' => 'Marzo 2019',
                    'titulo' => 'El primer café que duró cuatro horas',
                    'descripcion' => 'Pedimos un café y nos echaron cuando cerraban.',
                    'foto' => $photo('v1789364872/bida-events/boda-camila-andres/gallery/258108791_jqgwpb.jpg'),
                ],
                [
                    'cuando' => 'Julio 2020',
                    'titulo' => 'Nuestro primer viaje',
                    'descripcion' => 'Un bus nocturno a Copacabana y un amanecer sobre el lago que todavía nos debe una foto decente.',
                ],
                [
                    'cuando' => 'Enero 2023',
                    'titulo' => 'Las llaves de la misma puerta',
                    'descripcion' => 'Elegimos un departamento pequeño con una ventana grande.',
                    'foto' => $photo('v1789364874/bida-events/boda-camila-andres/gallery/532227184_rrvjtq.jpg'),
                ],
            ],
            'anecdota_titulo' => 'La noche del paraguas',
            'anecdota' => "Llovía en El Prado y teníamos un solo paraguas, roto. Luis lo sostuvo todo el camino sobre mi cabeza y llegó empapado, feliz, diciendo que así se secaba más rápido.\n\nNo fue un gran gesto. Fue ese: mojarse sin pensarlo. Ahí supe.",
            'reflexion' => "Antes cada uno miraba su propio cielo. Ahora miramos el mismo, y tiene más estrellas.\n\nAprendimos que querer es elegirse todos los días, incluso los días grises.",
            'promesa' => 'Seguir escribiendo esta historia, una noche a la vez.',
        ],
        'galeria' => [
            'titulo' => 'Así nos vemos ahora',
            'fotos' => [
                $photo('v1789364877/bida-events/boda-camila-andres/gallery/192858832_eqojih.jpg'),
                $photo('v1789364872/bida-events/boda-camila-andres/gallery/258108791_jqgwpb.jpg'),
                $photo('v1789364874/bida-events/boda-camila-andres/gallery/532227184_rrvjtq.jpg'),
            ],
        ],
        'musica' => [
            'titulo' => 'Die With A Smile',
            'artista' => 'Lady Gaga, Bruno Mars',
            'audio_url' => 'https://res.cloudinary.com/dwm7mniny/video/upload/v1789366356/bida-events/boda-camila-andres/musica/php4393_c6yqnh.mp3',
            'autoplay' => true,
        ],
        'dedicatoria' => [
            'de' => 'Luis',
            'para' => 'Ana',
            'mensaje' => "Te deseo lo mejor que tenga la vida, y quiero estar cerca para verlo.\n\nGracias por dejarme escribir contigo.",
            'firma' => 'Tu Luis',
        ],
        'respuesta' => [
            'titulo' => 'Deja tu estrella',
            'descripcion' => 'Escríbenos unas palabras: solo nosotros las vamos a leer.',
        ],
    ],
    'guests' => [],
    'contributions' => [],
    'poll_votes' => [],
];
