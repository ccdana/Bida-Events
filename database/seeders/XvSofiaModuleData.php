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
                'mensaje_post_evento' => 'Gracias por acompañarme en mi noche magica. Tu presencia hizo de este dia algo inolvidable.',
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
                    ['hora' => '18:00', 'titulo' => 'Recepcion de Invitados', 'icono' => 'glass', 'descripcion' => 'Coctel de bienvenida en el salon principal'],
                    ['hora' => '19:00', 'titulo' => 'Ceremonia de Velas', 'icono' => 'candle', 'descripcion' => 'Momento especial con la familia'],
                    ['hora' => '19:30', 'titulo' => 'Vals con Papa', 'icono' => 'dance', 'descripcion' => 'El primer vals de la quinceanera'],
                    ['hora' => '20:00', 'titulo' => 'Cena y Brindis', 'icono' => 'dinner', 'descripcion' => 'Menu gourmet de tres tiempos'],
                    ['hora' => '21:00', 'titulo' => 'Fiesta y DJ', 'icono' => 'music', 'descripcion' => 'A bailar hasta el amanecer'],
                    ['hora' => '00:00', 'titulo' => 'Sorpresa Final', 'icono' => 'star', 'descripcion' => 'Un momento que no olvidaras'],
                ],
            ],
            'dress_code' => [
                'titulo' => 'Codigo de Vestimenta',
                'estilo' => 'Etiqueta Semi-Formal',
                'descripcion' => 'Te pedimos evitar colores que compitan con la quinceanera.',
                'sugerencias' => [
                    [
                        'para' => 'Damas',
                        'titulo' => 'Vestido largo o cocktail',
                        'descripcion' => 'Preferimos vestidos elegantes de longitud media o larga. Telas fluidas en tonos tierra, vino o negro.',
                        'ejemplos' => ['Vestido satinado', 'Jump suit elegante', 'Falda larga + blusa'],
                    ],
                    [
                        'para' => 'Caballeros',
                        'titulo' => 'Traje o guayabera formal',
                        'descripcion' => 'Camisa formal con saco o traje completo. Corbata opcional en tonos sobrios.',
                        'ejemplos' => ['Traje oscuro', 'Camisa blanca + saco', 'Guayabera premium'],
                    ],
                    [
                        'para' => 'Jovenes',
                        'titulo' => 'Semi-formal moderno',
                        'descripcion' => 'Pantalon de vestir o falda elegante. Evitar jeans, tenis deportivos o ropa casual.',
                        'ejemplos' => ['Pantalon de vestir', 'Blazer casual', 'Vestido corto elegante'],
                    ],
                ],
                'colores_permitidos' => [
                    ['nombre' => 'Dorado', 'hex' => '#C9A96E'],
                    ['nombre' => 'Vino', 'hex' => '#722F37'],
                    ['nombre' => 'Negro', 'hex' => '#1A1A1A'],
                    ['nombre' => 'Champagne', 'hex' => '#F7E7CE'],
                ],
                'colores_prohibidos' => [
                    ['nombre' => 'Blanco', 'hex' => '#FFFFFF', 'motivo' => 'Reservado para la quinceanera'],
                    ['nombre' => 'Rosa fuerte', 'hex' => '#FF69B4', 'motivo' => 'Color exclusivo del evento'],
                ],
            ],
            'destacados' => [
                'chambelanes' => [
                    ['nombre' => 'Mateo R.', 'iniciales' => 'MR', 'detalle' => 'Mejor amigo desde kinder'],
                    ['nombre' => 'Diego M.', 'iniciales' => 'DM', 'detalle' => 'Companero de danza'],
                    ['nombre' => 'Sebastian L.', 'iniciales' => 'SL', 'detalle' => 'Primo y confidente'],
                    ['nombre' => 'Andres V.', 'iniciales' => 'AV', 'detalle' => 'Del equipo de basquet'],
                ],
                'damitas' => [
                    ['nombre' => 'Valentina C.', 'iniciales' => 'VC'],
                    ['nombre' => 'Camila P.', 'iniciales' => 'CP'],
                    ['nombre' => 'Isabella G.', 'iniciales' => 'IG'],
                    ['nombre' => 'Lucia F.', 'iniciales' => 'LF'],
                ],
                'padrinos' => [
                    ['rol' => 'Padrinos de Honor', 'nombres' => 'Sr. Roberto y Sra. Elena Quispe', 'mensaje' => 'Gracias por ser nuestro guia y ejemplo de amor en la familia.'],
                    ['rol' => 'Padrinos de Anillo', 'nombres' => 'Sr. Carlos y Sra. Maria Pereyra', 'mensaje' => 'Su carino ha acompanado a Sofia en cada etapa de su vida.'],
                    ['rol' => 'Padrinos de Vals', 'nombres' => 'Sr. Fernando y Sra. Ana Mamani', 'mensaje' => 'Bendicen este vals que queda grabado en nuestros corazones.'],
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
                'titulo' => 'Banda Sonora de la Noche',
                'artista' => 'Vals de Sofia — instrumental',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                'autoplay' => false,
            ],
            'video' => [
                'titulo' => 'Save the Date',
                'video_url' => 'https://res.cloudinary.com/demo/video/upload/v1690000000/xv-sofia/save-the-date.mp4',
                'poster' => 'https://res.cloudinary.com/demo/image/upload/v1690000000/xv-sofia/video-poster.jpg',
            ],
            'playlist' => [
                'titulo' => 'Playlist Colaborativa',
                'descripcion' => 'Sugiere una cancion para la pista de baile',
                'placeholder' => 'Nombre de cancion o link de YouTube',
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
                        'opciones' => ['El tio Juan', 'Mateo (chambelan)', 'Yo mismo', 'Nadie, somos pro'],
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
