<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Illuminate\Database\Seeder;

/**
 * Invitación de ejemplo de la plantilla "Boda Jardín" (slug boda-ana-luis).
 *
 * Datos de prueba: los usan los tests (tests/Feature) como invitación completa de esta plantilla.
 * No son las muestras de la portada; esas viven en database/seeders/showcase y ShowcaseInvitationsSeeder.
 *
 * Es idempotente: se puede ejecutar sola sobre una base existente con
 * php artisan db:seed --class=BodaJardinDemoSeeder
 */
class BodaJardinDemoSeeder extends Seeder
{
    public function run(): void
    {
        $eventType = EventType::firstOrCreate(['slug' => 'bodas'], ['name' => 'Bodas']);

        $invitation = Invitation::updateOrCreate(['slug' => 'boda-ana-luis'], [
            'user_id' => User::where('username', 'cliente')->value('id'),
            'event_type_id' => $eventType->id,
            'template' => InvitationTemplates::BODA_JARDIN,
            'title' => 'Boda de Ana y Luis',
            'event_date' => now()->addMonths(4)->setTime(16, 30),
            'status' => 'active',
            'expires_at' => now()->addMonths(10),
        ]);

        app(InvitationModuleService::class)->syncAllModules($invitation, self::modules());

        foreach ([['Familia Rojas', 4], ['Carla Méndez', 2], ['Jorge Villarroel', 1]] as [$name, $passes]) {
            $invitation->guests()->firstOrCreate(['name' => $name], ['passes_allocated' => $passes]);
        }
    }

    public static function modules(): array
    {
        return [
            'config' => [
                'template' => InvitationTemplates::BODA_JARDIN,
                'colores' => [
                    'primary' => '#A8875A',
                    'secondary' => '#5E6B55',
                    'accent' => '#EAD9CF',
                    'text' => '#3A3530',
                    'background' => '#FCF9F4',
                ],
                'tipografias' => [
                    'titulos' => 'Cormorant Garamond',
                    'cuerpo' => 'Lato',
                    'script' => 'Parisienne',
                ],
                'modulos' => [
                    'cuenta_regresiva' => true,
                    'agendar' => true,
                    'ubicacion' => true,
                    'itinerario' => true,
                    'dress_code' => true,
                    'destacados' => true,
                    'galeria' => true,
                    'musica' => true,
                    'video' => false,
                    'playlist' => true,
                    'hashtag' => true,
                    'encuestas' => true,
                    'rsvp' => true,
                    'regalos' => true,
                    'fotomural' => true,
                    'post_evento' => true,
                ],
            ],
            'bienvenida' => [
                'nombre_quinceanera' => 'Ana & Luis',
                'subtitulo' => 'Nos casamos',
                'mensaje' => 'Con la bendición de Dios y el cariño de nuestras familias, queremos compartir contigo el día en que uniremos nuestras vidas.',
                'mensaje_post_evento' => 'Gracias por ser parte del día más feliz de nuestras vidas.',
                'imagen_hero' => '/images/site/event-boda.webp',
            ],
            'ubicacion' => [
                'nombre_lugar' => 'Hacienda Los Álamos',
                'direccion' => 'Km 8 camino a Tiquipaya, Cochabamba',
                'lat' => -17.3380,
                'lng' => -66.2150,
                'nota' => 'La ceremonia es en el jardín: te recomendamos calzado cómodo para césped.',
            ],
            'itinerario' => [
                'titulo' => 'Nuestro día',
                'eventos' => [
                    ['hora' => '16:30', 'titulo' => 'Ceremonia religiosa', 'icono' => 'ceremonia', 'descripcion' => 'En la capilla del jardín'],
                    ['hora' => '17:30', 'titulo' => 'Cóctel de bienvenida', 'icono' => 'recepcion', 'descripcion' => 'Música en vivo junto a la fuente'],
                    ['hora' => '18:30', 'titulo' => 'Entrada de los novios', 'icono' => 'entrada', 'descripcion' => 'Te esperamos en el salón principal'],
                    ['hora' => '19:00', 'titulo' => 'Primer baile', 'icono' => 'vals', 'descripcion' => 'Nuestro primer baile como esposos'],
                    ['hora' => '19:30', 'titulo' => 'Brindis y cena', 'icono' => 'cena', 'descripcion' => 'Menú de tres tiempos'],
                    ['hora' => '21:30', 'titulo' => 'Corte del pastel', 'icono' => 'pastel', 'descripcion' => 'Un momento dulce para compartir'],
                    ['hora' => '22:00', 'titulo' => 'Fiesta', 'icono' => 'fiesta', 'descripcion' => 'A bailar hasta que el cuerpo aguante'],
                ],
            ],
            'dress_code' => [
                'titulo' => 'Dress code',
                'estilo' => 'Formal de jardín',
                'descripcion' => 'Te pedimos reservar el blanco y el marfil para la novia.',
                'sugerencias' => [
                    [
                        'para' => 'Damas',
                        'titulo' => 'Vestido largo o midi',
                        'descripcion' => 'Telas livianas y tonos suaves. Tacón ancho o plataforma para caminar sobre el césped.',
                        'ejemplos' => ['Vestido fluido', 'Conjunto de dos piezas', 'Tacón ancho'],
                    ],
                    [
                        'para' => 'Caballeros',
                        'titulo' => 'Traje de tonos claros',
                        'descripcion' => 'Traje completo en beige, gris claro o azul. La corbata es opcional.',
                        'ejemplos' => ['Traje de lino', 'Camisa clara', 'Mocasines'],
                    ],
                ],
                'colores_permitidos' => [
                    ['nombre' => 'Salvia', 'hex' => '#9CAF88'],
                    ['nombre' => 'Terracota', 'hex' => '#C4876B'],
                    ['nombre' => 'Azul polvo', 'hex' => '#9FB4C7'],
                    ['nombre' => 'Arena', 'hex' => '#D8C3A5'],
                ],
                'evitar' => [
                    'Blanco, marfil o crema',
                    'Tacones de aguja',
                    'Jeans o ropa deportiva',
                ],
            ],
            'destacados' => [
                'chambelanes' => [
                    ['nombre' => 'Diego Arce', 'detalle' => 'Hermano del novio'],
                    ['nombre' => 'Martín Salazar', 'detalle' => 'Amigo de la infancia'],
                    ['nombre' => 'Pablo Rivero', 'detalle' => 'Compañero de universidad'],
                ],
                'damitas' => [
                    ['nombre' => 'Lucía Terán', 'detalle' => 'Hermana de la novia'],
                    ['nombre' => 'Valeria Soria', 'detalle' => 'Mejor amiga'],
                    ['nombre' => 'Camila Ortiz', 'detalle' => 'Prima y confidente'],
                ],
                'padrinos' => [
                    ['rol' => 'Padrinos de velación', 'nombres' => 'Sr. Roberto y Sra. Elena Quiroga', 'mensaje' => 'Gracias por guiarnos con su ejemplo de amor y paciencia.'],
                    ['rol' => 'Padrinos de anillos', 'nombres' => 'Sr. Carlos y Sra. Patricia Rocha', 'mensaje' => 'Por acompañarnos desde el primer día de esta historia.'],
                    ['rol' => 'Padrinos de lazo', 'nombres' => 'Sr. Fernando y Sra. Ana Mercado'],
                ],
            ],
            'galeria' => [
                'titulo' => 'Nuestra historia',
                'fotos' => [
                    '/images/site/event-boda.webp',
                    '/images/site/servicio-fotomural.webp',
                ],
            ],
            'musica' => [
                'titulo' => 'Nuestra canción',
                'artista' => 'Versión instrumental',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
                'autoplay' => true,
            ],
            'video' => (object) [],
            'playlist' => [
                'titulo' => 'Canciones para la fiesta',
                'descripcion' => 'Sugiere la canción con la que quieres vernos bailar.',
                'placeholder' => 'Nombre de la canción o link de YouTube',
            ],
            'hashtag' => [
                'hashtag' => '#AnaYLuis',
                'plataforma' => 'instagram',
                'texto_boton' => 'Comparte tus fotos',
            ],
            'encuestas' => [
                'titulo' => 'Juguemos un poco',
                'preguntas' => [
                    [
                        'id' => 'primero-en-llorar',
                        'tipo' => 'single',
                        'pregunta' => '¿Quién llorará primero en la ceremonia?',
                        'opciones' => ['El novio', 'La novia', 'Las mamás', 'Todos'],
                    ],
                    [
                        'id' => 'hora-loca',
                        'tipo' => 'yesno',
                        'pregunta' => '¿Te animas a la hora loca?',
                        'opciones' => ['¡Claro que sí!', 'Mejor miro'],
                    ],
                ],
            ],
            'regalos' => [
                'titulo' => 'Mesa de regalos',
                'tienda_url' => 'https://example.com/mesa-ana-luis',
                'tienda_texto' => 'Ver mesa de regalos',
                'opciones' => [],
                'sobres' => [
                    'titulo' => 'Lluvia de sobres',
                    'direccion' => 'Habrá un buzón en la recepción',
                ],
                'banco' => [
                    'banco' => 'Banco Unión',
                    'titular' => 'Ana Terán',
                    'ci' => '7654321 CB',
                    'cuenta' => '1000123456',
                    'qr_url' => '',
                ],
            ],
            'post_evento' => [
                'titulo' => 'Fotos oficiales',
                'descripcion' => 'Muy pronto compartiremos aquí las fotos del fotógrafo.',
                'fotos' => [],
                'enlace_externo' => '',
            ],
            'rsvp' => [
                'titulo_confirmacion' => '¿Nos acompañas?',
                'mensaje_personalizado' => 'Confirma tu asistencia para reservar tu lugar.',
                'texto_confirmado' => '¡Gracias por confirmar! Presenta este pase en la entrada.',
                'texto_declinado' => 'Te extrañaremos. Gracias por avisarnos.',
            ],
        ];
    }
}
