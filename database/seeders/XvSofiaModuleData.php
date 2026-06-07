<?php

namespace Database\Seeders;

/**
 * Payloads JSON de ejemplo para la invitación demo 'xv-sofia'.
 *
 * Estructura de módulos (feature_code => json_data):
 * - config: colores, tipografías, toggles de módulos, plantilla
 * - bienvenida: textos del hero
 * - ubicacion: maps, transporte, dirección
 * - itinerario: cronograma vertical
 * - dress_code: vestimenta y paletas
 * - destacados: chambelanes, damitas, padrinos
 * - galeria: URLs Cloudinary simuladas
 * - musica: audio de fondo
 * - video: save the date vertical
 * - playlist: YouTube colaborativo
 * - hashtag: red social oficial
 * - encuestas: preguntas interactivas
 * - regalos: tienda, sobres, datos bancarios
 * - post_evento: galería del fotógrafo post-fiesta
 * - rsvp: mensajes de confirmación
 */
class XvSofiaModuleData
{
    public static function all(): array
    {
        return [
            'config' => [
                'template' => 'invitations.templates.xv-premium',
                'colores' => [
                    'primary' => '#C9A96E',
                    'secondary' => '#2C1810',
                    'accent' => '#F5E6D3',
                    'text' => '#1A1A1A',
                    'background' => '#FFFAF5',
                ],
                'tipografias' => [
                    'titulos' => 'Playfair Display',
                    'cuerpo' => 'Montserrat',
                    'script' => 'Great Vibes',
                ],
                'modulos' => [
                    'cuenta_regresiva' => true,
                    'ubicacion' => true,
                    'itinerario' => true,
                    'dress_code' => true,
                    'destacados' => true,
                    'agendar' => true,
                    'galeria' => true,
                    'musica' => true,
                    'video' => true,
                    'playlist' => true,
                    'hashtag' => true,
                    'encuestas' => true,
                    'rsvp' => true,
                    'regalos' => true,
                    'transporte' => true,
                    'fotomural' => true,
                    'post_evento' => true,
                ],
            ],
            'bienvenida' => [
                'nombre_quinceanera' => 'Sofía Valentina',
                'subtitulo' => 'Celebrando mis XV Años',
                'mensaje' => 'Con inmensa alegría, te invito a ser parte de esta noche mágica llena de amor, música y momentos inolvidables.',
                'fecha_texto' => 'Sábado 15 de Noviembre, 2026',
                'mensaje_post_evento' => '¡Gracias por acompañarme en mi noche mágica! Tu presencia hizo de este día algo inolvidable. 💫',
                'imagen_hero' => 'https://res.cloudinary.com/demo/image/upload/v1690000000/xv-sofia/hero.jpg',
            ],
            'ubicacion' => [
                'nombre_lugar' => 'Salón Imperial La Paz',
                'direccion' => 'Av. Costanera 1234, Zona Sur, La Paz, Bolivia',
                'maps_url' => 'https://maps.google.com/?q=Salon+Imperial+La+Paz',
                'lat' => -16.5000,
                'lng' => -68.1500,
                'nota' => 'Estacionamiento disponible en el subsuelo del salón.',
            ],
            'itinerario' => [
                'titulo' => 'Itinerario de la Noche',
                'eventos' => [
                    ['hora' => '18:00', 'titulo' => 'Recepción de Invitados', 'icono' => '🥂', 'descripcion' => 'Cóctel de bienvenida'],
                    ['hora' => '19:00', 'titulo' => 'Ceremonia de Velas', 'icono' => '🕯️', 'descripcion' => 'Momento especial con la familia'],
                    ['hora' => '19:30', 'titulo' => 'Vals con Papá', 'icono' => '💃', 'descripcion' => 'El primer vals de la quinceañera'],
                    ['hora' => '20:00', 'titulo' => 'Cena & Brindis', 'icono' => '🍽️', 'descripcion' => 'Menú gourmet de tres tiempos'],
                    ['hora' => '21:00', 'titulo' => 'Fiesta & DJ', 'icono' => '🎵', 'descripcion' => '¡A bailar hasta el amanecer!'],
                    ['hora' => '00:00', 'titulo' => 'Sorpresa Final', 'icono' => '✨', 'descripcion' => 'Un momento que no olvidarás'],
                ],
            ],
            'dress_code' => [
                'titulo' => 'Código de Vestimenta',
                'estilo' => 'Etiqueta Semi-Formal',
                'descripcion' => 'Te pedimos evitar colores que compitan con la quinceañera.',
                'colores_permitidos' => [
                    ['nombre' => 'Dorado', 'hex' => '#C9A96E'],
                    ['nombre' => 'Vino', 'hex' => '#722F37'],
                    ['nombre' => 'Negro', 'hex' => '#1A1A1A'],
                    ['nombre' => 'Champagne', 'hex' => '#F7E7CE'],
                ],
                'colores_prohibidos' => [
                    ['nombre' => 'Blanco', 'hex' => '#FFFFFF', 'motivo' => 'Reservado para la quinceañera'],
                    ['nombre' => 'Rosa fuerte', 'hex' => '#FF69B4', 'motivo' => 'Color exclusivo del evento'],
                ],
            ],
            'destacados' => [
                'chambelanes' => ['Mateo R.', 'Diego M.', 'Sebastián L.', 'Andrés V.'],
                'damitas' => ['Valentina C.', 'Camila P.', 'Isabella G.', 'Lucía F.'],
                'padrinos' => [
                    ['rol' => 'Padrinos de Honor', 'nombres' => 'Sr. Roberto & Sra. Elena Quispe'],
                    ['rol' => 'Padrinos de Anillo', 'nombres' => 'Sr. Carlos & Sra. María Pereyra'],
                    ['rol' => 'Padrinos de Vals', 'nombres' => 'Sr. Fernando & Sra. Ana Mamani'],
                ],
            ],
            'galeria' => [
                'titulo' => 'Momentos Especiales',
                'fotos' => [
                    'https://res.cloudinary.com/demo/image/upload/v1690000001/xv-sofia/gallery-1.jpg',
                    'https://res.cloudinary.com/demo/image/upload/v1690000002/xv-sofia/gallery-2.jpg',
                    'https://res.cloudinary.com/demo/image/upload/v1690000003/xv-sofia/gallery-3.jpg',
                    'https://res.cloudinary.com/demo/image/upload/v1690000004/xv-sofia/gallery-4.jpg',
                    'https://res.cloudinary.com/demo/image/upload/v1690000005/xv-sofia/gallery-5.jpg',
                ],
            ],
            'musica' => [
                'titulo' => 'Mi Canción Especial',
                'artista' => 'Shakira - Waka Waka',
                'audio_url' => 'https://res.cloudinary.com/demo/video/upload/v1690000000/xv-sofia/musica.mp3',
                'autoplay' => false,
            ],
            'video' => [
                'titulo' => 'Save the Date',
                'video_url' => 'https://res.cloudinary.com/demo/video/upload/v1690000000/xv-sofia/save-the-date.mp4',
                'poster' => 'https://res.cloudinary.com/demo/image/upload/v1690000000/xv-sofia/video-poster.jpg',
            ],
            'playlist' => [
                'titulo' => 'Playlist Colaborativa',
                'descripcion' => 'Sugiere una canción para la pista de baile 🎶',
                'placeholder' => 'Nombre de canción o link de YouTube',
            ],
            'hashtag' => [
                'hashtag' => '#SofiaXV2026',
                'plataforma' => 'instagram',
                'texto_boton' => 'Comparte tus fotos',
            ],
            'encuestas' => [
                'titulo' => 'Encuestas de la Fiesta',
                'preguntas' => [
                    [
                        'id' => 'color-vestido',
                        'pregunta' => '¿De qué color será el vestido?',
                        'opciones' => ['Rosa palo', 'Champagne', 'Azul cielo', 'Sorpresa total'],
                    ],
                    [
                        'id' => 'caida-pista',
                        'pregunta' => '¿Quién se caerá primero en la pista?',
                        'opciones' => ['El tío Juan', 'Mateo (chambelán)', 'Yo mismo 😅', 'Nadie, somos pro'],
                    ],
                ],
            ],
            'regalos' => [
                'titulo' => 'Detalles & Regalos',
                'tienda_url' => 'https://example.com/lista-regalos-sofia',
                'tienda_texto' => 'Ver lista de regalos',
                'sobres' => [
                    'titulo' => 'Lluvia de Sobres',
                    'direccion' => 'Av. 6 de Agosto #789, Edificio Mirador, La Paz',
                ],
                'banco' => [
                    'banco' => 'Banco Nacional de Bolivia',
                    'titular' => 'María Elena Valenzuela',
                    'ci' => '1234567 LP',
                    'cuenta' => '1500123456789',
                    'qr_url' => 'https://res.cloudinary.com/demo/image/upload/v1690000000/xv-sofia/qr-banco.png',
                ],
            ],
            'post_evento' => [
                'titulo' => 'Galería del Fotógrafo',
                'descripcion' => 'Las fotos oficiales estarán disponibles aquí próximamente.',
                'fotos' => [],
                'enlace_externo' => '',
            ],
            'rsvp' => [
                'titulo_confirmacion' => 'Confirma tu Asistencia',
                'mensaje_personalizado' => 'Estamos emocionados de celebrar contigo esta noche inolvidable.',
                'texto_confirmado' => '¡Gracias por confirmar! Presenta este pase VIP en la entrada.',
                'texto_declinado' => 'Lamentamos que no puedas acompañarnos. ¡Te extrañaremos!',
            ],
        ];
    }
}
