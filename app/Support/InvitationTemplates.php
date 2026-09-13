<?php

namespace App\Support;

/**
 * Catálogo de plantillas públicas de invitación.
 *
 * Todas usan los mismos módulos y parciales; cambian el diseño, el orden de las
 * secciones y algunos textos. En "copy" solo van los textos que difieren de la
 * plantilla de XV años: cada parcial conserva ese texto como valor por defecto.
 */
final class InvitationTemplates
{
    public const XV_PREMIUM = 'invitations.templates.xv-premium';

    public const BODA_JARDIN = 'invitations.templates.boda-jardin';

    public const BAUTIZO_CIELO = 'invitations.templates.bautizo-cielo';

    public const DEFAULT = self::XV_PREMIUM;

    public static function all(): array
    {
        return [
            self::XV_PREMIUM => [
                'label' => 'XV Años Premium',
                'description' => 'Portada a pantalla completa con la foto, partículas doradas y estilo editorial.',
                'event' => 'xv',
                'order' => [
                    'cuenta_regresiva', 'video', 'galeria', 'itinerario', 'dress_code', 'destacados', 'ubicacion',
                    'hashtag', 'encuestas', 'playlist', 'regalos', 'rsvp', 'fotomural', 'post_evento',
                ],
                'copy' => [],
            ],
            self::BODA_JARDIN => [
                'label' => 'Boda Jardín',
                'description' => 'Sobre que se abre al entrar, foto en arco con ramas que crecen, pétalos y títulos caligráficos.',
                'event' => 'boda',
                'order' => [
                    'cuenta_regresiva', 'video', 'galeria', 'ubicacion', 'itinerario', 'dress_code', 'destacados',
                    'regalos', 'rsvp', 'hashtag', 'playlist', 'encuestas', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Nos casamos',
                    'menu_heading' => 'La boda de',
                    'cover_eyebrow' => 'Tienes una invitación',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar cada detalle.',
                    'gallery_eyebrow' => 'Momentos juntos',
                    'itinerary_eyebrow' => 'Así será nuestro día',
                    'itinerary_empty' => 'Muy pronto compartiremos el orden de la celebración.',
                    'dress_hint' => 'Tonos sugeridos para la boda',
                    'dress_empty' => 'Viste elegante y cómodo para celebrar con nosotros.',
                    'court_lottie' => 'rings',
                    'court_eyebrow' => 'Quienes nos acompañan',
                    'court_title' => 'Padrinos y cortejo',
                    'court_intro' => 'Personas muy queridas que estarán a nuestro lado en este día.',
                    'court_empty' => 'Pronto presentaremos a quienes nos acompañan.',
                    'court_men' => 'Caballeros de honor',
                    'court_women' => 'Damas de honor',
                    'nav_court' => 'Padrinos',
                    'gifts_intro' => 'Tu presencia es nuestro mejor regalo. Si deseas tener un detalle, aquí tienes algunas opciones.',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbenos para actualizar tu respuesta.',
                ],
            ],
            self::BAUTIZO_CIELO => [
                'label' => 'Bautizo Cielo',
                'description' => 'Nubes que se abren al entrar, foto en medallón con halo y paloma, destellos y secciones separadas por olas.',
                'event' => 'bautizo',
                'order' => [
                    'cuenta_regresiva', 'galeria', 'destacados', 'ubicacion', 'itinerario', 'dress_code', 'video',
                    'regalos', 'rsvp', 'hashtag', 'encuestas', 'playlist', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Mi bautizo',
                    'menu_heading' => 'El bautizo de',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar este día tan especial.',
                    'gallery_eyebrow' => 'Mis primeros momentos',
                    'itinerary_eyebrow' => 'Así será mi día',
                    'itinerary_empty' => 'Muy pronto compartiremos el orden de la celebración.',
                    'dress_hint' => 'Tonos sugeridos para mi bautizo',
                    'dress_empty' => 'Viste cómodo y elegante para acompañarme.',
                    'court_lottie' => 'dove',
                    'court_eyebrow' => 'Quienes me guiarán',
                    'court_title' => 'Mis padrinos',
                    'court_intro' => 'Las personas que me acompañarán en la fe y en la vida.',
                    'court_empty' => 'Pronto presentaremos a mis padrinos.',
                    // Los grupos del cortejo pasan a ser la familia y los padrinos van primero
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Familia',
                    'court_men' => 'Abuelos',
                    'court_women' => 'Tíos',
                    'nav_court' => 'Padrinos',
                    'rsvp_declined_intro' => 'Si cambias de planes, avísale a mis papás para actualizar tu respuesta.',
                ],
            ],
        ];
    }

    /** @return array<string, string> vista => nombre visible */
    public static function labels(): array
    {
        return array_map(fn (array $template) => $template['label'], self::all());
    }

    public static function get(?string $template): array
    {
        $template = (string) $template;

        // Nombres antiguos con prefijo "pages." (ver InvitationDefaults::resolveTemplate)
        if (str_starts_with($template, 'pages.')) {
            $template = substr($template, strlen('pages.'));
        }

        return self::all()[$template] ?? self::all()[self::DEFAULT];
    }

    public static function copy(?string $template): array
    {
        return self::get($template)['copy'];
    }
}
