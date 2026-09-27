<?php

// Invitación de muestra "babyshower-valentina": el baby shower de Valentina con «Tendedero», el
// diseño clásico del evento. Toma las fotos, la música y la actividad de invitados de la muestra
// del bautizo (ya están en Cloudinary) y cambia todo lo que es propio del baby shower.

use App\Support\TrendTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('bautizo-emilia', 'babyshower-valentina', 'Baby shower de Valentina', TrendTemplates::key('tendedero', 'babyshower'), [
    'invitation.event_type' => ['slug' => 'baby-shower', 'name' => 'Baby shower', 'code' => 'babyshower', 'kind' => 'invitation'],
    'invitation.event_date' => '2026-11-14 16:00:00',
    'invitation.expires_at' => '2027-05-14',
    'modules.bienvenida' => [
        'nombre' => 'Valentina',
        'nombre_quinceanera' => 'Valentina',
        'subtitulo' => 'Baby shower',
        'mensaje' => 'Gabriela y Rodrigo esperan a su primera hija con muchísima ilusión. Acompáñanos a celebrar su llegada con una tarde de juegos, dulces y mucho cariño.',
        'fecha_texto' => 'Sábado 14 de noviembre, 2026',
        'mensaje_post_evento' => 'Gracias por celebrar con nosotros la llegada de Valentina.',
        'imagen_hero' => 'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364935/bida-events/bautizo-emilia/gallery/221855171_c1m3ws.jpg',
    ],
    // Solo las fotos de la muestra del bautizo que no son del sacramento (sin pila, vela ni cruz)
    'modules.galeria.fotos' => [
        'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364935/bida-events/bautizo-emilia/gallery/221855171_c1m3ws.jpg',
        'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364938/bida-events/bautizo-emilia/gallery/1195042200_mkchov.jpg',
        'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364972/bida-events/bautizo-emilia/post-evento/466309417_pixqra.jpg',
    ],
    // La tercera foto que subieron los invitados al bautizo es de la iglesia: aquí, la de la bebé
    'contributions.6.file_path' => 'https://res.cloudinary.com/dwm7mniny/image/upload/w_500,h_500,c_fill,q_auto,f_auto/v1789364938/bida-events/bautizo-emilia/gallery/1195042200_mkchov.jpg',
    'modules.post_evento.fotos' => [
        'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364938/bida-events/bautizo-emilia/gallery/1195042200_mkchov.jpg',
        'https://res.cloudinary.com/dwm7mniny/image/upload/v1789364972/bida-events/bautizo-emilia/post-evento/466309417_pixqra.jpg',
    ],
    'modules.ubicacion.nombre_lugar' => 'Casa Jardín Los Molles',
    'modules.ubicacion.direccion' => 'Calle Los Molles 214, zona Queru Queru, Cochabamba',
    'modules.ubicacion.nota' => 'Hay parqueo en la puerta. Toca el timbre del portón verde.',
    'modules.itinerario' => [
        'titulo' => 'La tarde',
        'eventos' => [
            ['hora' => '16:00', 'titulo' => 'Bienvenida', 'icono' => 'recepcion', 'descripcion' => 'Te recibimos con limonada y bocaditos'],
            ['hora' => '16:30', 'titulo' => 'Juegos', 'icono' => 'sorpresa', 'descripcion' => 'Adivina la fecha, el peso y a quién se parecerá'],
            ['hora' => '17:30', 'titulo' => 'Mensajes para Valentina', 'icono' => 'fotos', 'descripcion' => 'Escribe un deseo para su primer año'],
            ['hora' => '18:00', 'titulo' => 'Apertura de regalos', 'icono' => 'pastel', 'descripcion' => 'Con torta y café para todos'],
        ],
    ],
    'modules.dress_code.titulo' => 'Vestimenta',
    'modules.dress_code.estilo' => 'Casual, en tonos suaves',
    'modules.dress_code.descripcion' => 'Ven cómodo: la tarde es en el jardín. Si quieres sumarte, usa algo en tonos pastel.',
    'modules.destacados' => [
        'padrinos' => [
            ['rol' => 'Anfitrionas', 'nombres' => 'Andrea Peña y Natalia Soliz', 'mensaje' => 'Las tías que prepararon cada detalle de la tarde.'],
            ['rol' => 'Abuela que ya teje escarpines', 'nombres' => 'Sra. Rosario Peña'],
        ],
        'chambelanes' => [
            ['nombre' => 'Rodrigo y Martha Vargas', 'detalle' => 'Abuelos paternos'],
            ['nombre' => 'Fernando y Rosario Peña', 'detalle' => 'Abuelos maternos'],
        ],
        'damitas' => [
            ['nombre' => 'Andrea Peña', 'detalle' => 'Tía'],
            ['nombre' => 'Carla Méndez', 'detalle' => 'Amiga de mamá'],
        ],
    ],
    'modules.galeria.titulo' => 'Esperándote',
    'modules.musica.titulo' => 'La canción de la espera',
    'modules.video.titulo' => 'Nueve meses',
    'modules.playlist.titulo' => 'Música para la tarde',
    'modules.playlist.descripcion' => 'Sugiere una canción para la tarde en el jardín.',
    'modules.hashtag.hashtag' => '#LlegaValentina',
    'modules.encuestas' => [
        'titulo' => 'Adivina adivinador',
        'preguntas' => [
            ['id' => 'parecido', 'tipo' => 'single', 'pregunta' => '¿A quién se parecerá Valentina?', 'opciones' => ['A mamá', 'A papá', 'A los dos', 'A la abuela']],
            ['id' => 'agua-bendita', 'tipo' => 'yesno', 'pregunta' => '¿Llegará antes de la fecha?', 'opciones' => ['Sí, apurada', 'No, puntual']],
            ['id' => 'primera-palabra', 'tipo' => 'single', 'pregunta' => '¿Cuál será su primera palabra?', 'opciones' => ['Mamá', 'Papá', 'Agua', 'Tata']],
        ],
    ],
    'modules.regalos.titulo' => 'Lista de regalos',
    'modules.regalos.sobres' => ['titulo' => 'Lluvia de sobres', 'direccion' => 'Habrá un buzón en la entrada para quien prefiera un sobre.'],
    'modules.regalos.opciones' => [
        ['titulo' => 'Pañales etapa 1 y 2', 'descripcion' => 'Siempre hacen falta: cualquier marca está bien.'],
        ['titulo' => 'Ropita de 3 a 6 meses', 'descripcion' => 'Enteritos de algodón y medias.'],
        ['titulo' => 'Libros de tela', 'descripcion' => 'Para sus primeras lecturas.'],
    ],
    'modules.post_evento.descripcion' => 'Así celebramos la llegada de Valentina. ¡Gracias por venir!',
    'modules.rsvp' => [
        'titulo_confirmacion' => '¿Nos acompañas?',
        'mensaje_personalizado' => 'Confirma antes del 7 de noviembre para preparar tu lugar.',
        'texto_confirmado' => '¡Gracias! Te esperamos con mucho cariño.',
        'texto_declinado' => 'Te vamos a extrañar. Gracias por avisar.',
    ],
]);
