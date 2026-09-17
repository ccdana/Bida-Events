<?php

namespace Database\Seeders;

use App\Models\EventType;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationModuleService;
use App\Support\InvitationTemplates;
use Illuminate\Database\Seeder;

/**
 * Invitación de ejemplo de la plantilla "Bautizo Cielo" (slug bautizo-mateo).
 *
 * Datos de prueba: los usan los tests (tests/Feature) como invitación completa de esta plantilla.
 * No son las muestras de la portada; esas viven en database/seeders/showcase y ShowcaseInvitationsSeeder.
 *
 * Es idempotente: se puede ejecutar sola sobre una base existente con
 * php artisan db:seed --class=BautizoCieloDemoSeeder
 */
class BautizoCieloDemoSeeder extends Seeder
{
    public function run(): void
    {
        $eventType = EventType::firstOrCreate(['slug' => 'bautizos'], ['name' => 'Bautizos']);

        $invitation = Invitation::updateOrCreate(['slug' => 'bautizo-mateo'], [
            'user_id' => User::where('username', 'cliente')->value('id'),
            'event_type_id' => $eventType->id,
            'template' => InvitationTemplates::BAUTIZO_CIELO,
            'title' => 'Bautizo de Mateo Andrés',
            'event_date' => now()->addMonths(2)->setTime(11, 0),
            'status' => 'active',
            'expires_at' => now()->addMonths(8),
        ]);

        app(InvitationModuleService::class)->syncAllModules($invitation, self::modules());

        foreach ([['Familia Paz', 4], ['Andrea Gutiérrez', 2], ['Familia Soria', 3]] as [$name, $passes]) {
            $invitation->guests()->firstOrCreate(['name' => $name], ['passes_allocated' => $passes]);
        }
    }

    public static function modules(): array
    {
        return [
            'config' => [
                'template' => InvitationTemplates::BAUTIZO_CIELO,
                'colores' => [
                    'primary' => '#6B9AC4',
                    'secondary' => '#C9A96E',
                    'accent' => '#DCEBF5',
                    'text' => '#2E3A46',
                    'background' => '#F7FBFE',
                ],
                'tipografias' => [
                    'titulos' => 'Lora',
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
                    'playlist' => false,
                    'hashtag' => true,
                    'encuestas' => true,
                    'rsvp' => true,
                    'regalos' => true,
                    'fotomural' => true,
                    'post_evento' => true,
                ],
            ],
            'bienvenida' => [
                'nombre_quinceanera' => 'Mateo Andrés',
                'subtitulo' => 'Mi bautizo',
                'mensaje' => 'Con la bendición de Dios y la alegría de mis papás, Carla y Javier, te invito a acompañarme en el día de mi bautizo.',
                'mensaje_post_evento' => 'Gracias por acompañarme en un día tan especial para mi familia.',
                'imagen_hero' => '/images/site/event-bautizo.webp',
            ],
            'ubicacion' => [
                'nombre_lugar' => 'Parroquia San Martín',
                'direccion' => 'Av. América esquina Pando, Cochabamba',
                'lat' => -17.3782,
                'lng' => -66.1566,
                'nota' => 'Después de la misa nos vemos en el Salón Los Pinos, a cinco cuadras de la parroquia.',
            ],
            'itinerario' => [
                'titulo' => 'Mi día',
                'eventos' => [
                    ['hora' => '11:00', 'titulo' => 'Misa de bautizo', 'icono' => 'ceremonia', 'descripcion' => 'En la Parroquia San Martín'],
                    ['hora' => '12:00', 'titulo' => 'Fotos en familia', 'icono' => 'fotos', 'descripcion' => 'En los jardines de la parroquia'],
                    ['hora' => '12:30', 'titulo' => 'Recepción', 'icono' => 'recepcion', 'descripcion' => 'Salón Los Pinos'],
                    ['hora' => '13:30', 'titulo' => 'Almuerzo', 'icono' => 'cena', 'descripcion' => 'Compartimos la mesa en familia'],
                    ['hora' => '15:00', 'titulo' => 'Pastel', 'icono' => 'pastel', 'descripcion' => 'Soplamos juntos la primera velita'],
                    ['hora' => '15:30', 'titulo' => 'Juegos y sorpresas', 'icono' => 'sorpresa', 'descripcion' => 'Para los más pequeños de la familia'],
                ],
            ],
            'dress_code' => [
                'titulo' => 'Vestimenta',
                'estilo' => 'Casual elegante',
                'descripcion' => 'Colores claros y ropa cómoda para compartir un día en familia.',
                'sugerencias' => [
                    [
                        'para' => 'Damas',
                        'titulo' => 'Vestido o conjunto claro',
                        'descripcion' => 'Telas livianas en tonos pastel. Zapatos cómodos para la parroquia y el jardín.',
                        'ejemplos' => ['Vestido midi', 'Blusa y pantalón de lino', 'Sandalias bajas'],
                    ],
                    [
                        'para' => 'Caballeros',
                        'titulo' => 'Camisa clara y pantalón de vestir',
                        'descripcion' => 'No hace falta traje completo: un blazer liviano queda perfecto.',
                        'ejemplos' => ['Camisa celeste', 'Blazer beige', 'Mocasines'],
                    ],
                    [
                        'para' => 'Niños',
                        'titulo' => 'Cómodos y listos para jugar',
                        'descripcion' => 'Ropa clara que les permita moverse con libertad.',
                        'ejemplos' => ['Vestido de algodón', 'Camisa y bermuda', 'Zapatillas blancas'],
                    ],
                ],
                'colores_permitidos' => [
                    ['nombre' => 'Celeste', 'hex' => '#BFD7EA'],
                    ['nombre' => 'Blanco', 'hex' => '#FFFFFF'],
                    ['nombre' => 'Beige', 'hex' => '#E8DCC8'],
                    ['nombre' => 'Gris perla', 'hex' => '#D5D8DC'],
                ],
                'evitar' => [
                    'Negro total',
                    'Ropa deportiva',
                ],
            ],
            'destacados' => [
                // En esta plantilla los chambelanes se muestran como abuelos y las damitas como tíos
                'chambelanes' => [
                    ['nombre' => 'Hugo y Rosa Paz', 'detalle' => 'Abuelos paternos'],
                    ['nombre' => 'Ramiro y Elena Soria', 'detalle' => 'Abuelos maternos'],
                ],
                'damitas' => [
                    ['nombre' => 'Lucía Soria', 'detalle' => 'Hermana de mamá'],
                    ['nombre' => 'Diego Paz', 'detalle' => 'Hermano de papá'],
                ],
                'padrinos' => [
                    ['rol' => 'Padrinos de bautizo', 'nombres' => 'Andrea Gutiérrez y Rodrigo Paz', 'mensaje' => 'Gracias por aceptar guiarme en la fe y acompañarme toda la vida.'],
                    ['rol' => 'Padrinos de vela', 'nombres' => 'Sr. Óscar y Sra. Marcela Rojas', 'mensaje' => 'Por encender la luz que me acompañará siempre.'],
                    ['rol' => 'Padrinos de ropón', 'nombres' => 'Sra. Teresa Villca'],
                ],
            ],
            'galeria' => [
                'titulo' => 'Galería',
                'fotos' => [
                    '/images/site/event-bautizo.webp',
                ],
            ],
            'musica' => [
                'titulo' => 'Canción de cuna',
                'artista' => 'Versión instrumental',
                'audio_url' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
                'autoplay' => false,
            ],
            'video' => (object) [],
            'playlist' => (object) [],
            'hashtag' => [
                'hashtag' => '#BautizoDeMateo',
                'plataforma' => 'instagram',
                'texto_boton' => 'Comparte tus fotos',
            ],
            'encuestas' => [
                'titulo' => 'Adivina adivinador',
                'preguntas' => [
                    [
                        'id' => 'parecido',
                        'tipo' => 'single',
                        'pregunta' => '¿A quién se parece más Mateo?',
                        'opciones' => ['A mamá', 'A papá', 'A los dos', 'Al abuelo'],
                    ],
                    [
                        'id' => 'agua-bendita',
                        'tipo' => 'yesno',
                        'pregunta' => '¿Llorará con el agua bendita?',
                        'opciones' => ['Sí, seguro', 'No, es un campeón'],
                    ],
                ],
            ],
            'regalos' => [
                'titulo' => 'Regalos',
                'tienda_url' => '',
                'tienda_texto' => '',
                'opciones' => [],
                'sobres' => [
                    'titulo' => 'Alcancía',
                    'direccion' => 'Habrá una alcancía en la recepción para mis primeros ahorros',
                ],
                'banco' => [
                    'banco' => 'Banco Unión',
                    'titular' => 'Carla Soria',
                    'ci' => '6543210 CB',
                    'cuenta' => '1000765432',
                    'qr_url' => '',
                ],
            ],
            'post_evento' => [
                'titulo' => 'Fotos oficiales',
                'descripcion' => 'Muy pronto compartiremos aquí las fotos del bautizo.',
                'fotos' => [],
                'enlace_externo' => '',
            ],
            'rsvp' => [
                'titulo_confirmacion' => '¿Me acompañas?',
                'mensaje_personalizado' => 'Confirma tu asistencia para que mis papás reserven tu lugar.',
                'texto_confirmado' => '¡Gracias por confirmar! Te espero con mucho cariño.',
                'texto_declinado' => 'Te extrañaré. Gracias por avisar.',
            ],
        ];
    }
}
