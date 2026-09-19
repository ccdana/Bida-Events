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

    public const CUMPLE_FIESTA = 'invitations.templates.cumple-fiesta';

    public const TARJETA_AMOR = 'invitations.templates.tarjeta-amor';

    public const TARJETA_AVENTURA = 'invitations.templates.tarjeta-aventura';

    public const DEFAULT = self::XV_PREMIUM;

    /**
     * Paleta por defecto de una plantilla. La usa la página del sistema visual
     * (/admin/sistema-visual) para medir el contraste de cada tema.
     *
     * @return array<string, string>
     */
    public static function palette(string $template): array
    {
        return self::all()[$template]['palette'] ?? self::all()[self::DEFAULT]['palette'];
    }

    public static function all(): array
    {
        return [
            self::XV_PREMIUM => [
                'label' => 'XV Años Elegante',
                'description' => 'Portada a pantalla completa con la foto, partículas doradas y estilo editorial.',
                'event' => 'xv',
                // Paleta con la que nace una invitación de este tipo (la misma de la muestra)
                'palette' => [
                    'primary' => '#C9A96E',
                    'secondary' => '#2C1810',
                    'accent' => '#F5E6D3',
                    'text' => '#1A1A1A',
                    'background' => '#FFFAF5',
                ],
                // Orden por prioridad del invitado: cuándo y dónde, confirmar, lo emocional, regalos y participación
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'video', 'galeria',
                    'destacados', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [],
            ],
            self::BODA_JARDIN => [
                'label' => 'Boda Jardín',
                'description' => 'Sobre que se abre al entrar, foto en arco con ramas que crecen, pétalos y títulos caligráficos.',
                'event' => 'boda',
                // Paleta con la que nace una invitación de este tipo (la misma de la muestra)
                'palette' => [
                    'primary' => '#A8875A',
                    'secondary' => '#5E6B55',
                    'accent' => '#EAD9CF',
                    'text' => '#3A3530',
                    'background' => '#FCF9F4',
                ],
                'order' => [
                    'cuenta_regresiva', 'galeria', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'video',
                    'destacados', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
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
                // Paleta con la que nace una invitación de este tipo (la misma de la muestra)
                'palette' => [
                    'primary' => '#6B9AC4',
                    'secondary' => '#C9A96E',
                    'accent' => '#DCEBF5',
                    'text' => '#2E3A46',
                    'background' => '#F7FBFE',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'destacados', 'galeria', 'dress_code',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
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
            self::CUMPLE_FIESTA => [
                'label' => 'Cumpleaños Fiesta',
                'description' => 'Pastel con velas que se soplan al entrar, confeti, globos, banderines y la edad en grande.',
                'event' => 'cumple',
                // Paleta con la que nace una invitación de este tipo (la misma de la muestra)
                'palette' => [
                    'primary' => '#F25C54',
                    'secondary' => '#F7B32B',
                    'accent' => '#9ADBC5',
                    'text' => '#2B2D42',
                    'background' => '#FFF8F0',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'playlist', 'encuestas',
                    'galeria', 'destacados', 'regalos', 'hashtag', 'video', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => '¡Celebremos juntos!',
                    'menu_heading' => 'El cumpleaños de',
                    'intro_eyebrow' => '¡Estás invitado!',
                    'intro_hint' => 'Toca el pastel para soplar las velas',
                    'intro_cheer' => '¡A celebrar!',
                    'guest_help' => 'Te toma menos de un minuto y me ayuda a preparar la fiesta.',
                    'countdown_lottie' => 'cake',
                    'gallery_eyebrow' => 'Recuerdos favoritos',
                    'itinerary_eyebrow' => 'Así será la fiesta',
                    'itinerary_empty' => 'Muy pronto compartiré el programa de la fiesta.',
                    'dress_hint' => 'Colores sugeridos para la fiesta',
                    'dress_empty' => 'Ven cómodo y listo para bailar.',
                    'court_lottie' => 'balloon',
                    'court_eyebrow' => 'Gente especial',
                    'court_title' => 'Mi gente favorita',
                    'court_intro' => 'Las personas que hacen cada año más especial.',
                    'court_empty' => 'Pronto presentaré a mi gente favorita.',
                    // Los grupos del cortejo son amigos y familia; los padrinos pasan a ser anfitriones
                    'court_group_tab' => 'Mi gente',
                    'court_sponsors_tab' => 'Anfitriones',
                    'court_men' => 'Amigos',
                    'court_women' => 'Familia',
                    'nav_court' => 'Mi gente',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbeme para actualizar tu respuesta.',
                ],
            ],
            // ── Tarjetas estacionales ─────────────────────────────────────────
            self::TARJETA_AMOR => [
                'label' => 'Carta de amor',
                'tagline' => 'Se abre regando una flor y responde con otra',
                'description' => 'Un jardín que florece: un capullo que se riega para abrirlo, la foto que se revela, la carta lacrada, una margarita que se deshoja, recuerdos en un tendedero, una flor de respuesta y un diente de león para pedir un deseo.',
                'event' => 'amor',
                'palette' => [
                    'primary' => '#A63A50',
                    'secondary' => '#6B2433',
                    'accent' => '#F2D7DB',
                    'text' => '#2E1A1F',
                    'background' => '#FFF8F5',
                ],
                'order' => ['dedicatoria', 'juntos_desde', 'galeria', 'video', 'respuesta'],
                'copy' => [
                    'hero_eyebrow' => 'Feliz Día del Amor',
                    'menu_heading' => 'Una carta para',
                    'intro_hint' => 'Riégalo con tres toques',
                    'gallery_eyebrow' => 'Nuestros momentos',
                    'footer_pitch' => '¿Te gustó esta carta? Manda la tuya',
                    'daisy_title' => '¿Me quiere?',
                    'daisy_hint' => 'Deshoja la margarita, pétalo por pétalo',
                    'daisy_yes' => 'Me quiere',
                    'daisy_no' => 'No me quiere',
                    'daisy_answer' => '¡Me quiere!',
                    'wish_eyebrow' => 'Antes de irte',
                    'wish_title' => 'Pide un deseo',
                    'wish_hint' => 'Desliza hacia arriba para soplar',
                    'wish_message' => 'Que esta primavera nos encuentre juntos, y todas las que vengan.',
                    'butterflies_found' => 'Encontraste las tres mariposas: la primavera es toda tuya.',
                ],
                // Vistas propias que reemplazan a las comunes solo en esta tarjeta
                'partials' => [
                    'juntos_desde' => 'invitations.partials.amor.daisy-milestone',
                    'galeria' => 'invitations.partials.amor.clothesline',
                    'respuesta' => 'invitations.partials.amor.flower-reply',
                ],
                // Flores para responder: se guardan en guest_contributions.reaction
                'reactions' => [
                    'rosa' => [
                        'label' => 'Una rosa',
                        'meaning' => 'Te amo',
                        'phrase' => 'La flor de los enamorados: te quiero con todo el corazón.',
                    ],
                    'girasol' => [
                        'label' => 'Un girasol',
                        'meaning' => 'Me haces feliz',
                        'phrase' => 'Siempre busca el sol, como yo te busco a ti: contigo todo brilla.',
                    ],
                    'tulipan' => [
                        'label' => 'Un tulipán',
                        'meaning' => 'Amor sincero',
                        'phrase' => 'Es una declaración: lo que siento por ti es de verdad.',
                    ],
                    'margarita' => [
                        'label' => 'Una margarita',
                        'meaning' => 'Ternura',
                        'phrase' => 'Sencilla y dulce, como un «te quiero» dicho al oído.',
                    ],
                ],
            ],
            self::TARJETA_AVENTURA => [
                'label' => 'Libro de aventuras',
                'description' => 'Un cuaderno de recortes que se hojea: el mes del aniversario, la carta, su historia por capítulos, recuerdos, collages con flores amarillas, fotos con marco y un juego de memoria.',
                'event' => 'aventura',
                'palette' => [
                    'primary' => '#8A4B1F',
                    'secondary' => '#5A3214',
                    'accent' => '#F2C230',
                    'text' => '#2B1D12',
                    'background' => '#F7EEDC',
                ],
                // Cada módulo es una o varias páginas del cuaderno, en este orden
                'order' => ['juntos_desde', 'dedicatoria', 'historia', 'recuerdos', 'collage', 'marcos', 'memoria', 'respuesta', 'aventuras'],
                'copy' => [
                    'hero_eyebrow' => 'Nuestro libro de aventuras',
                    'menu_heading' => 'Un libro para',
                    'intro_hint' => 'Desliza la tapa para abrir el libro',
                    'footer_pitch' => '¿Te gustó este libro? Arma el tuyo',
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
