<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Illuminate\Database\Seeder;

/**
 * Invitación de ejemplo de la plantilla "Sopla las velas" (slug cumple-valeria).
 *
 * Datos de prueba: los usan los tests (tests/Feature) como invitación completa de esta plantilla.
 * No son las muestras de la portada; esas viven en database/seeders/showcase y ShowcaseInvitationsSeeder.
 *
 * Es idempotente: se puede ejecutar sola sobre una base existente con
 * php artisan db:seed --class=CumpleFiestaDemoSeeder
 */
class CumpleFiestaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $eventType = EventType::firstOrCreate(['slug' => 'cumpleanos'], ['name' => 'Cumpleaños']);

        $invitation = Invitation::updateOrCreate(['slug' => 'cumple-valeria'], [
            'user_id' => User::where('username', 'cliente')->value('id'),
            'event_type_id' => $eventType->id,
            'template' => InvitationTemplates::CUMPLE_FIESTA,
            'title' => 'Cumpleaños de Valeria',
            'event_date' => now()->addMonth()->setTime(20, 0),
            'status' => 'active',
            'expires_at' => now()->addMonths(6),
        ]);

        app(InvitationModuleService::class)->syncAllModules($invitation, self::modules());

        foreach ([['Familia Méndez', 4], ['Sergio Álvarez', 2], ['Paola Rivera', 1]] as [$name, $passes]) {
            $invitation->guests()->firstOrCreate(['name' => $name], ['passes_allocated' => $passes]);
        }
    }

    public static function modules(): array
    {
        return [
            'config' => [
                'template' => InvitationTemplates::CUMPLE_FIESTA,
                'colores' => [
                    'primary' => '#F25C54',
                    'secondary' => '#F7B32B',
                    'accent' => '#9ADBC5',
                    'text' => '#2B2D42',
                    'background' => '#FFF8F0',
                ],
                'tipografias' => [
                    'titulos' => 'Fredoka',
                    'cuerpo' => 'Nunito Sans',
                    'script' => 'Dancing Script',
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
                'nombre_quinceanera' => 'Valeria',
                'subtitulo' => 'Mis 30 años',
                'mensaje' => '¡Treinta vueltas al sol merecen una gran fiesta! Ven a celebrar conmigo una noche de música, risas y buena comida.',
                'mensaje_post_evento' => '¡Gracias por venir y hacer de mis 30 una fiesta inolvidable!',
                'imagen_hero' => '/images/site/event-cumpleanos.webp',
            ],
            'ubicacion' => [
                'nombre_lugar' => 'Terraza Olivo',
                'direccion' => 'Calle Potosí 845, Cochabamba',
                'lat' => -17.3801,
                'lng' => -66.1600,
                'nota' => 'La fiesta es en la terraza del último piso: el ascensor está a la derecha de la entrada.',
            ],
            'itinerario' => [
                'titulo' => 'Programa de la fiesta',
                'eventos' => [
                    ['hora' => '20:00', 'titulo' => 'Bienvenida y cócteles', 'icono' => 'recepcion', 'descripcion' => 'Llega con hambre y ganas de bailar'],
                    ['hora' => '21:00', 'titulo' => 'Cena', 'icono' => 'cena', 'descripcion' => 'Mesa de picadas y platos calientes'],
                    ['hora' => '22:00', 'titulo' => 'Pastel y velas', 'icono' => 'pastel', 'descripcion' => 'Canta conmigo el cumpleaños feliz'],
                    ['hora' => '22:30', 'titulo' => 'A bailar', 'icono' => 'fiesta', 'descripcion' => 'DJ y pista abierta'],
                    ['hora' => '00:00', 'titulo' => 'Hora loca', 'icono' => 'hora-loca', 'descripcion' => 'Disfraces, luces y mucha energía'],
                    ['hora' => '02:00', 'titulo' => 'Despedida', 'icono' => 'despedida', 'descripcion' => 'Un último abrazo antes de irnos'],
                ],
            ],
            'dress_code' => [
                'titulo' => 'Dress code',
                'estilo' => 'Casual festivo',
                'descripcion' => 'Ven con algo que tenga color: ¡queremos una foto grupal llena de vida!',
                'sugerencias' => [
                    [
                        'para' => 'Todos',
                        'titulo' => 'Un toque de color',
                        'descripcion' => 'Una prenda, accesorio o maquillaje con los colores de la fiesta.',
                        'ejemplos' => ['Camisa estampada', 'Vestido colorido', 'Lentes divertidos'],
                    ],
                    [
                        'para' => 'Para bailar',
                        'titulo' => 'Zapatos cómodos',
                        'descripcion' => 'La pista estará abierta hasta tarde: elige calzado con el que aguantes toda la noche.',
                        'ejemplos' => ['Zapatillas limpias', 'Botines bajos', 'Sandalias cómodas'],
                    ],
                ],
                'colores_permitidos' => [
                    ['nombre' => 'Coral', 'hex' => '#F25C54'],
                    ['nombre' => 'Amarillo sol', 'hex' => '#F7B32B'],
                    ['nombre' => 'Menta', 'hex' => '#9ADBC5'],
                    ['nombre' => 'Lila', 'hex' => '#B8A1E3'],
                ],
                'evitar' => [
                    'Todo negro',
                    'Tacones que no aguanten la pista',
                ],
            ],
            'destacados' => [
                // En esta plantilla los chambelanes son amigos, las damitas la familia y los padrinos los anfitriones
                'chambelanes' => [
                    ['nombre' => 'Camila Vargas', 'detalle' => 'Mejor amiga desde el colegio'],
                    ['nombre' => 'Jorge Salinas', 'detalle' => 'Compañero de aventuras'],
                    ['nombre' => 'Andrea Paz', 'detalle' => 'La que siempre organiza el viaje'],
                ],
                'damitas' => [
                    ['nombre' => 'Lucía y Martín', 'detalle' => 'Mis hermanos'],
                    ['nombre' => 'Abuela Carmen', 'detalle' => 'La reina de la pista'],
                ],
                'padrinos' => [
                    ['rol' => 'Anfitriones', 'nombres' => 'Rosa y Andrés, mis papás', 'mensaje' => 'Gracias por abrirnos la casa y el corazón una vez más.'],
                ],
            ],
            'galeria' => [
                'titulo' => 'Galería',
                'fotos' => [
                    '/images/site/event-cumpleanos.webp',
                ],
            ],
            'musica' => [
                'titulo' => 'Mi canción del año',
                'artista' => 'Versión instrumental',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-4.mp3',
                'autoplay' => false,
            ],
            'video' => (object) [],
            'playlist' => [
                'titulo' => 'Playlist de la fiesta',
                'descripcion' => 'Sugiere la canción que no puede faltar en la pista.',
                'placeholder' => 'Nombre de la canción o link de YouTube',
            ],
            'hashtag' => [
                'hashtag' => '#Vale30',
                'plataforma' => 'instagram',
                'texto_boton' => 'Comparte tus fotos',
            ],
            'encuestas' => [
                'titulo' => 'Juegos de la fiesta',
                'preguntas' => [
                    [
                        'id' => 'cancion-pista',
                        'tipo' => 'single',
                        'pregunta' => '¿Qué género abre la pista?',
                        'opciones' => ['Reguetón', 'Cumbia', 'Pop de los 2000', 'Rock'],
                    ],
                    [
                        'id' => 'hora-loca',
                        'tipo' => 'yesno',
                        'pregunta' => '¿Te sumas a la hora loca?',
                        'opciones' => ['¡Obvio!', 'Solo miro'],
                    ],
                ],
            ],
            'regalos' => [
                'titulo' => 'Regalos',
                'tienda_url' => '',
                'tienda_texto' => '',
                'opciones' => [],
                'sobres' => [
                    'titulo' => 'Buzón de sobres',
                    'direccion' => 'Habrá un buzón junto a la mesa del pastel',
                ],
                'banco' => [
                    'banco' => 'Banco Unión',
                    'titular' => 'Valeria Méndez',
                    'ci' => '8765432 CB',
                    'cuenta' => '1000987654',
                    'qr_url' => '',
                ],
            ],
            'post_evento' => [
                'titulo' => 'Fotos de la fiesta',
                'descripcion' => 'Muy pronto subiré aquí las mejores fotos de la noche.',
                'fotos' => [],
                'enlace_externo' => '',
            ],
            'rsvp' => [
                'titulo_confirmacion' => '¿Vienes a la fiesta?',
                'mensaje_personalizado' => 'Confirma para guardarte un lugar en la pista.',
                'texto_confirmado' => '¡Genial! Te espero con el pastel listo.',
                'texto_declinado' => 'Te voy a extrañar. ¡Gracias por avisar!',
            ],
        ];
    }
}
