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

    public const LIENZO = 'invitations.templates.lienzo';

    public const GRADUACION_BIRRETE = 'invitations.templates.graduacion-birrete';

    public const HALLOWEEN_CALABAZAS = 'invitations.templates.halloween-calabazas';

    public const TARJETA_AMOR = 'invitations.templates.tarjeta-amor';

    public const TARJETA_AVENTURA = 'invitations.templates.tarjeta-aventura';

    public const WE_STORY_TOGETHER = 'invitations.templates.we-story-together';

    // Colección «nueva»: una por evento, cada una con su propia idea (ver «theme» en cada entrada)
    public const XV_CARTA_DE_BAILE = 'invitations.templates.xv-carta-de-baile';

    public const BODA_DOS_CAMINOS = 'invitations.templates.boda-dos-caminos';

    public const GRADUACION_PROXIMA_SALIDA = 'invitations.templates.graduacion-proxima-salida';

    public const BAUTIZO_LA_GOTA = 'invitations.templates.bautizo-la-gota';

    public const CUMPLE_STICKERS = 'invitations.templates.cumple-stickers';

    public const HALLOWEEN_EXPEDIENTE = 'invitations.templates.halloween-expediente';

    public const DEFAULT = self::XV_PREMIUM;

    /**
     * Paleta por defecto de una plantilla. La usan el editor y las pruebas de contraste
     * para medir que cada tema se lea.
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
                'label' => 'Noche de gala',
                'tagline' => 'Se abre con un telón, entre destellos dorados',
                'description' => 'Portada a pantalla completa con la foto, partículas doradas y estilo editorial.',
                'event' => 'xv',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'clasica',
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
                'copy' => [
                    'intro_hint' => 'Toca para abrir los telones',
                ],
            ],
            self::BODA_JARDIN => [
                'label' => 'Promesa en el jardín',
                'tagline' => 'Un sobre lacrado entre ramas y pétalos',
                'description' => 'Sobre que se abre al entrar, foto en arco con ramas que crecen, pétalos y títulos caligráficos.',
                'event' => 'boda',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'clasica',
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
                    'intro_hint' => 'Toca el sello para abrir',
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
                'label' => 'Entre nubes',
                'tagline' => 'Agua que cae sobre la pila, nubes y palomas',
                'description' => 'Nubes que se abren al entrar, foto en medallón con halo y paloma, destellos y secciones separadas por olas.',
                'event' => 'bautizo',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'clasica',
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
                    'intro_hint' => 'Toca la jarra para verter el agua',
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
                'label' => 'Sopla las velas',
                'tagline' => 'Un pastel con velas, confeti y globos',
                'description' => 'Pastel con velas que se soplan al entrar, confeti, globos, banderines y la edad en grande.',
                'event' => 'cumple',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'clasica',
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
                    'hero_ticket_label' => 'Fiesta',
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
            self::GRADUACION_BIRRETE => [
                'label' => 'Birrete al aire',
                'tagline' => 'Un diploma con cinta que se desata al entrar',
                'description' => 'Diploma enrollado que se abre con un toque, birretes que vuelan, foto en marco de arco y detalles dorados de ceremonia.',
                'event' => 'graduacion',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'clasica',
                'palette' => [
                    'primary' => '#9C7A2E',
                    'secondary' => '#1F2A44',
                    'accent' => '#E9E2D0',
                    'text' => '#1B2233',
                    'background' => '#FBF9F4',
                ],
                // Tipografías con las que nace (las demás plantillas usan las del editor)
                'fonts' => ['titulos' => 'Cinzel', 'cuerpo' => 'Montserrat', 'script' => 'Great Vibes'],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'destacados', 'galeria', 'dress_code',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Me gradúo',
                    'hero_day_label' => 'Día',
                    'hero_time_label' => 'Hora',
                    'hero_class_label' => 'Promoción',
                    'menu_heading' => 'La graduación de',
                    'intro_eyebrow' => 'Tienes una invitación',
                    'intro_hint' => 'Toca la cinta para abrir el diploma',
                    'intro_cheer' => '¡Lo logramos!',
                    'countdown_lottie' => 'calendar',
                    'countdown_eyebrow' => 'La ceremonia se acerca',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar la celebración.',
                    'gallery_eyebrow' => 'El camino hasta aquí',
                    'itinerary_eyebrow' => 'Así será el día',
                    'itinerary_empty' => 'Muy pronto compartiré el horario del acto y de la fiesta.',
                    'dress_hint' => 'Tonos sugeridos para la celebración',
                    'dress_empty' => 'Viste formal: es una noche de gala.',
                    'court_lottie' => 'invitation',
                    'court_eyebrow' => 'Quienes me acompañaron',
                    'court_title' => 'Gracias a ustedes',
                    'court_intro' => 'Las personas que hicieron posible este logro.',
                    'court_empty' => 'Pronto presentaré a quienes me acompañaron.',
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Familia y amigos',
                    'court_sponsors_tab' => 'Padrinos',
                    'court_men' => 'Familia',
                    'court_women' => 'Compañeros',
                    'nav_court' => 'Gracias',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbeme para actualizar tu respuesta.',
                ],
            ],
            // ── Plantilla en blanco para cualquier evento ─────────────────────
            self::LIENZO => [
                'label' => 'Lienzo',
                'tagline' => 'En blanco, para diseñarla a tu manera',
                'description' => 'Fondo blanco, letra negra y nada de adornos: cada color, tipografía y texto se cambia desde el editor.',
                'event' => 'lienzo',
                'collection' => 'lienzo',
                'palette' => [
                    'primary' => '#111111',
                    'secondary' => '#333333',
                    'accent' => '#F1F1F1',
                    'text' => '#111111',
                    'background' => '#FFFFFF',
                ],
                'fonts' => ['titulos' => 'Inter', 'cuerpo' => 'Inter', 'script' => 'Inter'],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'galeria', 'video',
                    'destacados', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                // Textos neutros: sirven para cualquier evento y se cambian todos desde el editor
                'copy' => [
                    'hero_eyebrow' => 'Estás invitado',
                    'menu_heading' => 'Invitación',
                    'countdown_eyebrow' => 'Falta poco',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a organizarnos.',
                    'gallery_eyebrow' => 'Fotos',
                    'itinerary_eyebrow' => 'Programa',
                    'itinerary_empty' => 'Muy pronto compartiremos el programa.',
                    'dress_hint' => 'Colores sugeridos',
                    'dress_empty' => 'Ven como te sientas cómodo.',
                    'court_eyebrow' => 'Personas especiales',
                    'court_title' => 'Quienes nos acompañan',
                    'court_intro' => 'Las personas que hacen posible este día.',
                    'court_empty' => 'Pronto presentaremos a quienes nos acompañan.',
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Invitados',
                    'court_sponsors_tab' => 'Anfitriones',
                    'court_men' => 'Invitados especiales',
                    'court_women' => 'Equipo',
                    'nav_court' => 'Personas',
                    'gifts_intro' => 'Tu presencia es lo más importante. Si deseas tener un detalle, aquí tienes algunas opciones.',
                    'rsvp_declined_intro' => 'Si cambias de planes, avísanos para actualizar tu respuesta.',
                ],
            ],
            // ── Invitaciones de temporada ─────────────────────────────────────
            self::HALLOWEEN_CALABAZAS => [
                'label' => 'Noche de calabazas',
                'tagline' => 'Una calabaza que se enciende al tocarla',
                'description' => 'Calabaza que se ilumina para entrar, luna llena con murciélagos, niebla y velas: una fiesta de disfraces con confirmación y playlist.',
                'event' => 'halloween',
                'collection' => 'temporada',
                'palette' => [
                    'primary' => '#F08A24',
                    'secondary' => '#7B4BB7',
                    'accent' => '#2B2238',
                    'text' => '#F4EEE6',
                    'background' => '#130F1A',
                ],
                'fonts' => ['titulos' => 'Fredoka', 'cuerpo' => 'Nunito Sans', 'script' => 'Creepster'],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'rsvp', 'dress_code', 'itinerario', 'playlist', 'encuestas',
                    'galeria', 'video', 'destacados', 'hashtag', 'regalos', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Fiesta de Halloween',
                    'hero_ticket_label' => 'La noche del',
                    'menu_heading' => 'Fiesta de',
                    'intro_eyebrow' => 'Te espera una noche de miedo',
                    'intro_hint' => 'Toca la calabaza para encenderla',
                    'intro_cheer' => '¡Que empiece la fiesta!',
                    'countdown_eyebrow' => 'La noche se acerca',
                    'countdown_done_title' => '¡Hoy es la fiesta!',
                    'location_eyebrow' => '¿Dónde es la fiesta?',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar la fiesta.',
                    'gallery_eyebrow' => 'Fiestas pasadas',
                    'itinerary_eyebrow' => 'Lo que pasará esa noche',
                    'itinerary_empty' => 'Muy pronto compartiremos el programa de la noche.',
                    'dress_eyebrow' => 'Disfraz',
                    'dress_hint' => 'Colores de la noche',
                    'dress_empty' => 'Ven disfrazado: habrá premio al mejor disfraz.',
                    'court_lottie' => 'music',
                    'court_eyebrow' => 'Quiénes arman la fiesta',
                    'court_title' => 'La tripulación',
                    'court_intro' => 'Anfitriones, música y el jurado del concurso de disfraces.',
                    'court_empty' => 'Pronto presentaremos a quienes arman la fiesta.',
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Show y jurado',
                    'court_sponsors_tab' => 'Anfitriones',
                    'court_men' => 'DJ y show',
                    'court_women' => 'Jurado de disfraces',
                    'nav_court' => 'Anfitriones',
                    'playlist_eyebrow' => 'La música de la noche',
                    'polls_eyebrow' => 'Vota antes de la fiesta',
                    'rsvp_yes' => 'Sí, ahí estaré',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbenos para actualizar tu respuesta.',
                ],
            ],
            // ── Tarjetas estacionales ─────────────────────────────────────────
            self::TARJETA_AMOR => [
                'label' => 'Carta que florece',
                'tagline' => 'Se abre regando una flor y responde con otra',
                'description' => 'Un jardín que florece: un capullo que se riega para abrirlo, la foto que se revela, la carta lacrada, una margarita que se deshoja, recuerdos en un tendedero, una flor de respuesta y un diente de león para pedir un deseo.',
                'event' => 'amor',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'temporada',
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
                'tagline' => 'Un cuaderno de recortes que se hojea, con juego de memoria',
                'description' => 'Un cuaderno de recortes que se hojea: el mes del aniversario, la carta, su historia por capítulos, recuerdos, collages con flores amarillas, fotos con marco y un juego de memoria.',
                'event' => 'aventura',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'temporada',
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
            // Perfil propio (StoryCardProfile). Cuatro actos fijos en partials/historia: la vista no
            // recorre «order», que solo arma el menú. Cada texto *_fallback cubre un dato vacío con
            // algo propio de su acto, para que la cadena de sentido no se rompa.
            self::WE_STORY_TOGETHER => [
                'label' => 'Bajo la misma luna',
                'tagline' => 'Su historia en cuatro actos, de la luna en el agua a un cielo de estrellas',
                'description' => 'La historia de una pareja en cuatro actos: la luna reflejada en el agua, la marea que sube con cada recuerdo, la luna de frente con la anécdota que lo cambió todo y un cielo estrellado a lo Van Gogh.',
                'event' => 'historia',
                // Familia de plantillas: qué plan de revendedor la incluye (config «reseller_plans»)
                'collection' => 'temporada',
                // Noche fija: el tema solo toma del cliente la luz de la luna (primary)
                'palette' => [
                    'primary' => '#E8C872',
                    'secondary' => '#1C2B5A',
                    'accent' => '#3F6FA8',
                    'text' => '#F3ECDA',
                    'background' => '#0A1230',
                ],
                'order' => ['juntos_desde', 'relato', 'dedicatoria', 'galeria', 'respuesta'],
                'copy' => [
                    'menu_heading' => 'La historia de',
                    'footer_pitch' => '¿Te gustó esta historia? Escribe la tuya',
                    'cover_hint' => 'Toca el agua para empezar',
                    'cover_skip' => 'Entrar sin esperar',
                    'act1_label' => 'El reflejo',
                    'act2_label' => 'La marea',
                    'act3_label' => 'De frente',
                    'act4_label' => 'La constelación',
                    'act1_title' => 'The story we write together',
                    'act1_intro_fallback' => 'Toda historia empieza lejos, como la luna en el agua: se la ve antes de entenderla.',
                    'act1_met_prefix' => 'Nos conocimos el',
                    'act1_met_fallback' => 'No recordamos el día exacto. Recordamos que después de ese día, todo fue distinto.',
                    'act1_first_fallback' => 'Al principio fueron cosas pequeñas: una mirada que duró un segundo más, una conversación que no queríamos terminar. No sabíamos que ya estaba empezando.',
                    'act2_title' => 'Y la marea empezó a subir',
                    'act2_moments_fallback' => 'Hubo una primera caminata sin rumbo, una primera canción compartida, una primera vez en que el silencio no incomodó. Cada una subió la marea un poco más, hasta que ya no hubo forma de volver a la orilla.',
                    'act3_title_fallback' => 'Ese momento',
                    'act3_anecdote_fallback' => 'No hizo falta nada grande. Fue un instante cualquiera en que nos miramos y supimos, sin decirlo, que ya no queríamos mirar hacia otro lado.',
                    'act3_song_label' => 'La canción que suena cuando pensamos en esto',
                    'act3_dedication_fallback' => 'Te deseo lo mejor que tenga la vida, y quiero estar cerca para verlo.',
                    'act3_photos_label' => 'Así nos vemos ahora',
                    'act4_title' => 'Lo que cambió en nosotros',
                    'act4_reflection_fallback' => 'Desde que estás, el cielo tiene más estrellas. No cambiaron las noches: cambió la forma en que las miramos.',
                    'act4_promise_fallback' => 'Seguiremos escribiendo esta historia, una noche a la vez.',
                    'act4_together' => 'Escribiendo juntos desde hace',
                    'reply_title' => 'Deja tu estrella',
                    'reply_intro' => 'Escribe unas palabras para esta historia. Solo las leen quienes la escribieron.',
                ],
            ],

            // ── Colección «nueva» ─────────────────────────────────────────────
            // Cada una tiene su propia clase en la página («theme»: inv-carta, inv-caminos…) y su hoja
            // en resources/css/invitation/themes, así no hereda nada de la clásica de su evento. Todo
            // se dibuja con los cinco colores y las tres letras del editor: «color_usage» le cuenta al
            // editor para qué usa cada color esta plantilla.
            self::XV_CARTA_DE_BAILE => [
                'label' => 'Carta de baile',
                'tagline' => 'Una carta de baile atada con un cordón',
                'description' => 'La invitación es la carta de baile de la noche: se desata el cordón, se abre la tapa y adentro está el programa del vals, la entrada y el brindis, con un lugar reservado a nombre del invitado.',
                'event' => 'xv',
                'collection' => 'nueva',
                'theme' => 'carta',
                'palette' => [
                    'primary' => '#94732C',
                    'secondary' => '#5B2146',
                    'accent' => '#B9B3D6',
                    'text' => '#1F2A5C',
                    'background' => '#F2EFF7',
                ],
                'fonts' => ['titulos' => 'Bodoni Moda', 'cuerpo' => 'Instrument Sans', 'script' => 'Bodoni Moda'],
                'color_usage' => [
                    'background' => 'La carta: el papel sobre el que va todo.',
                    'text' => 'La tinta de la carta y el terciopelo que la rodea.',
                    'primary' => 'El cordón, los filetes dobles y los números del programa.',
                    'accent' => 'Los recuadros de la carta y el brillo del terciopelo.',
                    'secondary' => 'La borla del cordón y el sello de «Reservado».',
                ],
                // El programa va primero: es lo que se lee en una carta de baile
                'order' => [
                    'itinerario', 'cuenta_regresiva', 'ubicacion', 'rsvp', 'dress_code', 'destacados', 'galeria',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Mis XV años',
                    'card_title' => 'Carta de baile',
                    'card_reserved' => 'Reservado para',
                    'card_reserved_any' => 'Un lugar reservado para ti',
                    'hero_day_label' => 'Fecha',
                    'hero_time_label' => 'Hora',
                    'hero_place_label' => 'Salón',
                    'intro_eyebrow' => 'Tienes un lugar en el baile',
                    'intro_hint' => 'Toca la borla para desatar el cordón',
                    'itinerary_eyebrow' => 'El programa de la noche',
                    'itinerary_empty' => 'Muy pronto escribiremos el programa del baile.',
                    'rsvp_eyebrow' => 'Tu lugar en el baile',
                    'rsvp_submit' => 'Firmar mi carta',
                    'guest_cta' => 'Firmar mi carta',
                    'stories_hint' => 'Toca para pasar a la siguiente pieza',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.carta.program',
                ],
            ],
            self::BODA_DOS_CAMINOS => [
                'label' => 'Dos caminos',
                'tagline' => 'Un mapa donde dos caminos se encuentran',
                'description' => 'Un mapa de curvas de nivel que se despliega: cada nombre empieza su camino en un extremo y los dos se juntan en el lugar de la boda. El recorrido del día son paradas y la leyenda del mapa guarda la vestimenta y los regalos.',
                'event' => 'boda',
                'collection' => 'nueva',
                'theme' => 'caminos',
                'palette' => [
                    'primary' => '#2F6282',
                    'secondary' => '#94762F',
                    'accent' => '#B9C7B4',
                    'text' => '#22302A',
                    'background' => '#E6E8E1',
                ],
                'fonts' => ['titulos' => 'Libre Caslon Display', 'cuerpo' => 'Figtree', 'script' => 'Libre Caslon Display'],
                'color_usage' => [
                    'background' => 'El papel del mapa.',
                    'text' => 'Los nombres, los textos y las curvas de nivel.',
                    'primary' => 'El camino del primer nombre, los botones y las paradas.',
                    'secondary' => 'El camino del segundo nombre y el aro donde se encuentran.',
                    'accent' => 'El relleno de la leyenda y de los recuadros.',
                ],
                // Primero el camino y después el destino, como se lee un mapa
                'order' => [
                    'itinerario', 'ubicacion', 'cuenta_regresiva', 'rsvp', 'dress_code', 'regalos', 'destacados',
                    'galeria', 'video', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Nos casamos',
                    'hero_meet' => 'Nuestros caminos se juntan el',
                    'menu_heading' => 'La boda de',
                    'intro_eyebrow' => 'Te mandamos un mapa',
                    'intro_hint' => 'Toca el mapa para desplegarlo',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar cada detalle.',
                    'countdown_eyebrow' => 'Lo que falta de camino',
                    'location_eyebrow' => 'El punto de encuentro',
                    'location_button' => 'Trazar la ruta',
                    'gallery_eyebrow' => 'Lo que recorrimos',
                    'itinerary_eyebrow' => 'Las paradas del día',
                    'itinerary_empty' => 'Muy pronto marcaremos las paradas del día.',
                    'route_end' => 'Llegada',
                    'dress_eyebrow' => 'Leyenda: vestimenta',
                    'dress_hint' => 'Tonos sugeridos para la boda',
                    'dress_empty' => 'Viste elegante y cómodo para celebrar con nosotros.',
                    'court_lottie' => 'rings',
                    'court_eyebrow' => 'Quienes caminan con nosotros',
                    'court_title' => 'Padrinos y cortejo',
                    'court_intro' => 'Personas muy queridas que estarán a nuestro lado en este día.',
                    'court_empty' => 'Pronto presentaremos a quienes nos acompañan.',
                    'court_men' => 'Caballeros de honor',
                    'court_women' => 'Damas de honor',
                    'nav_court' => 'Padrinos',
                    'gifts_intro' => 'Tu presencia es nuestro mejor regalo. Si deseas tener un detalle, aquí tienes algunas opciones.',
                    'rsvp_eyebrow' => '¿Llegas al encuentro?',
                    'rsvp_yes' => 'Llegaré',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbenos para actualizar tu respuesta.',
                    'stories_hint' => 'Toca para seguir el camino',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.caminos.route',
                ],
            ],
            self::GRADUACION_PROXIMA_SALIDA => [
                'label' => 'Próxima salida',
                'tagline' => 'Un panel de salidas con el nombre en letras que giran',
                'description' => 'El panel de salidas de una terminal: el nombre aparece letra por letra, la carrera es el destino y el lugar es la puerta. Se entra con un pase de abordar y el horario se lee como un tablero.',
                'event' => 'graduacion',
                'collection' => 'nueva',
                'theme' => 'salidas',
                'palette' => [
                    'primary' => '#F2B134',
                    'secondary' => '#D2505C',
                    'accent' => '#2C3A50',
                    'text' => '#EDEFF2',
                    'background' => '#1B2433',
                ],
                'fonts' => ['titulos' => 'Barlow Condensed', 'cuerpo' => 'Source Serif 4', 'script' => 'Barlow Condensed'],
                'color_usage' => [
                    'background' => 'La terminal: el fondo de toda la invitación.',
                    'text' => 'Las letras del panel y los textos.',
                    'primary' => 'La luz del panel: horas, estado y botones.',
                    'secondary' => 'La franja de la carrera y el sello del pase.',
                    'accent' => 'Las casillas de las letras y los recuadros.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'dress_code', 'destacados', 'galeria',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Me gradúo',
                    'board_title' => 'Próxima salida',
                    'board_destination' => 'Destino',
                    'board_date' => 'Fecha',
                    'board_time' => 'Hora',
                    'board_gate' => 'Puerta',
                    'board_status' => 'Estado',
                    'board_on_time' => 'A tiempo',
                    'board_today' => 'Embarcando hoy',
                    'pass_title' => 'Pase de abordar',
                    'pass_passenger' => 'Pasajero',
                    'pass_guest' => 'Invitado especial',
                    'menu_heading' => 'La graduación de',
                    'intro_eyebrow' => 'Tienes un asiento reservado',
                    'intro_hint' => 'Toca el pase para abordar',
                    'countdown_eyebrow' => 'La salida se acerca',
                    'countdown_title' => 'Salimos en',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar la celebración.',
                    'gallery_eyebrow' => 'El camino hasta aquí',
                    'itinerary_eyebrow' => 'Horario del día',
                    'itinerary_empty' => 'Muy pronto publicaremos el horario del acto y de la fiesta.',
                    'location_eyebrow' => 'Puerta de embarque',
                    'dress_eyebrow' => 'Equipaje: vestimenta',
                    'dress_hint' => 'Tonos sugeridos para la celebración',
                    'dress_empty' => 'Viste formal: es una noche de gala.',
                    'court_lottie' => 'invitation',
                    'court_eyebrow' => 'Tripulación',
                    'court_title' => 'Gracias a ustedes',
                    'court_intro' => 'Las personas que hicieron posible este logro.',
                    'court_empty' => 'Pronto presentaré a quienes me acompañaron.',
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Familia y amigos',
                    'court_sponsors_tab' => 'Padrinos',
                    'court_men' => 'Familia',
                    'court_women' => 'Compañeros',
                    'nav_court' => 'Gracias',
                    'rsvp_eyebrow' => 'Reserva tu asiento',
                    'rsvp_submit' => 'Reservar asiento',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbeme para actualizar tu respuesta.',
                    'stories_hint' => 'Toca para ver la siguiente salida',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.salidas.timetable',
                ],
            ],
            self::BAUTIZO_LA_GOTA => [
                'label' => 'La gota',
                'tagline' => 'Una gota cae sobre el agua y abre ondas',
                'description' => 'La pila vista desde arriba: cae una gota y abre ondas. El nombre queda en el centro, los padrinos rodean el primer anillo y cada onda que se aleja trae la fecha, el lugar y la celebración.',
                'event' => 'bautizo',
                'collection' => 'nueva',
                'theme' => 'gota',
                'palette' => [
                    'primary' => '#3F7483',
                    'secondary' => '#B39B5C',
                    'accent' => '#CFE3E6',
                    'text' => '#1C343D',
                    'background' => '#FAFBF9',
                ],
                'fonts' => ['titulos' => 'Cormorant Infant', 'cuerpo' => 'Nunito Sans', 'script' => 'Cormorant Infant'],
                'color_usage' => [
                    'background' => 'La luz sobre el agua: el fondo de la invitación.',
                    'text' => 'El nombre y los textos.',
                    'primary' => 'Las ondas, la gota y los botones.',
                    'secondary' => 'El primer anillo, el de los padrinos.',
                    'accent' => 'El agua de la portada y los recuadros.',
                ],
                // Los padrinos van cerca del principio: en esta plantilla son el primer anillo
                'order' => [
                    'cuenta_regresiva', 'destacados', 'ubicacion', 'itinerario', 'rsvp', 'galeria', 'dress_code',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Mi bautizo',
                    'ring_label' => 'Mis padrinos',
                    'menu_heading' => 'El bautizo de',
                    'intro_eyebrow' => 'Tienes una invitación',
                    'intro_hint' => 'Toca el agua',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar este día tan especial.',
                    'countdown_eyebrow' => 'Ya falta poco',
                    'gallery_eyebrow' => 'Mis primeros momentos',
                    'itinerary_eyebrow' => 'Así será mi día',
                    'itinerary_empty' => 'Muy pronto compartiremos el orden de la celebración.',
                    'dress_hint' => 'Tonos sugeridos para mi bautizo',
                    'dress_empty' => 'Viste cómodo y elegante para acompañarme.',
                    'court_lottie' => 'dove',
                    'court_eyebrow' => 'Quienes me acompañan',
                    'court_title' => 'Mis padrinos',
                    'court_intro' => 'Las personas que estarán cerca de mí mientras crezco.',
                    'court_empty' => 'Pronto presentaremos a mis padrinos.',
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Familia',
                    'court_men' => 'Abuelos',
                    'court_women' => 'Tíos',
                    'nav_court' => 'Padrinos',
                    'rsvp_declined_intro' => 'Si cambias de planes, avísale a mis papás para actualizar tu respuesta.',
                    'stories_hint' => 'Toca para que caiga otra gota',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.gota.drops',
                ],
            ],
            self::CUMPLE_STICKERS => [
                'label' => 'Álbum de stickers',
                'tagline' => 'Un sobre de stickers para abrir y un álbum por llenar',
                'description' => 'Una página de álbum de stickers: se entra abriendo el sobre, quien cumple es el sticker brillante con su edad como número y cada dato va pegado en su casilla; la casilla vacía espera la confirmación. Con 18 años o más, los stickers van derechos; los colores salen de la paleta.',
                'event' => 'cumple',
                'collection' => 'nueva',
                'theme' => 'stickers',
                'palette' => [
                    'primary' => '#2A3FD6',
                    'secondary' => '#F4C430',
                    'accent' => '#F59AC0',
                    'text' => '#1E2461',
                    'background' => '#FBFBF8',
                ],
                'fonts' => ['titulos' => 'Bagel Fat One', 'cuerpo' => 'Outfit', 'script' => 'Bagel Fat One'],
                'color_usage' => [
                    'background' => 'La página del álbum y el borde blanco de cada sticker.',
                    'text' => 'Los textos y las casillas impresas.',
                    'primary' => 'El nombre, el sobre, los botones y el primer color de los stickers.',
                    'secondary' => 'La franja con el nombre de cada sticker y las cintas de los títulos.',
                    'accent' => 'El brillo del sticker principal y el tercer color de los stickers.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'playlist', 'dress_code', 'encuestas',
                    'galeria', 'destacados', 'regalos', 'hashtag', 'video', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => '¡Celebremos juntos!',
                    'sticker_age' => 'años',
                    'sticker_going' => '¡Voy!',
                    'sticker_missing' => 'Falta la tuya',
                    'sticker_time' => 'La hora',
                    'sticker_place' => 'El lugar',
                    'menu_heading' => 'El cumpleaños de',
                    'intro_eyebrow' => '¡Estás invitado!',
                    'intro_pack' => 'Stickers de',
                    'intro_hint' => 'Arranca la tira para abrir el sobre',
                    'guest_help' => 'Te toma menos de un minuto y me ayuda a preparar la fiesta.',
                    'countdown_eyebrow' => 'Cuenta regresiva',
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
                    'court_group_tab' => 'Mi gente',
                    'court_sponsors_tab' => 'Anfitriones',
                    'court_men' => 'Amigos',
                    'court_women' => 'Familia',
                    'nav_court' => 'Mi gente',
                    'rsvp_yes' => '¡Voy!',
                    'rsvp_no' => 'Esta vez no puedo',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbeme para actualizar tu respuesta.',
                    'stories_hint' => 'Toca para despegar el siguiente',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.stickers.schedule',
                ],
            ],
            self::HALLOWEEN_EXPEDIENTE => [
                'label' => 'Expediente abierto',
                'tagline' => 'Un expediente que se lee con linterna',
                'description' => 'El archivo de un caso: la carpeta se abre, la página está a oscuras y el dedo del invitado es una linterna que revela los datos. Un botón enciende las luces para leerla completa.',
                'event' => 'halloween',
                'collection' => 'nueva',
                'theme' => 'expediente',
                'palette' => [
                    'primary' => '#A08BF5',
                    'secondary' => '#D0554A',
                    'accent' => '#CDB891',
                    'text' => '#E9E4D8',
                    'background' => '#0F2620',
                ],
                'fonts' => ['titulos' => 'Special Elite', 'cuerpo' => 'Public Sans', 'script' => 'Special Elite'],
                'color_usage' => [
                    'background' => 'La oscuridad de la oficina.',
                    'text' => 'Los textos del expediente.',
                    'primary' => 'La luz de la linterna, los botones y los detalles.',
                    'secondary' => 'Los sellos y las marcas del caso.',
                    'accent' => 'La carpeta de cartulina y los recuadros.',
                ],
                'order' => [
                    'ubicacion', 'cuenta_regresiva', 'dress_code', 'rsvp', 'itinerario', 'encuestas', 'playlist',
                    'galeria', 'video', 'destacados', 'hashtag', 'regalos', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'hero_eyebrow' => 'Fiesta de Halloween',
                    'case_label' => 'Expediente',
                    'case_open' => 'Caso abierto',
                    'case_lead' => 'Investigador a cargo',
                    'case_seen' => 'Cita',
                    'case_place' => 'Lugar',
                    'case_notes' => 'Notas del caso',
                    'lights_on' => 'Encender las luces',
                    'lights_off' => 'Usar la linterna',
                    'menu_heading' => 'Fiesta de',
                    'intro_eyebrow' => 'Tienes un caso asignado',
                    'intro_hint' => 'Toca la carpeta para abrirla',
                    'countdown_eyebrow' => 'Tiempo para resolverlo',
                    'countdown_done_title' => '¡Hoy es la fiesta!',
                    'location_eyebrow' => 'Lugar de la cita',
                    'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar la fiesta.',
                    'gallery_eyebrow' => 'Pruebas fotográficas',
                    'itinerary_eyebrow' => 'Bitácora de la noche',
                    'itinerary_empty' => 'Muy pronto anotaremos lo que pasará esa noche.',
                    'dress_eyebrow' => 'Descripción del disfraz',
                    'dress_hint' => 'Colores de la noche',
                    'dress_empty' => 'Ven disfrazado: habrá premio al mejor disfraz.',
                    'court_lottie' => 'music',
                    'court_eyebrow' => 'El equipo del caso',
                    'court_title' => 'Quiénes arman la fiesta',
                    'court_intro' => 'Anfitriones, música y el jurado del concurso de disfraces.',
                    'court_empty' => 'Pronto presentaremos a quienes arman la fiesta.',
                    'court_first' => 'padrinos',
                    'court_group_tab' => 'Show y jurado',
                    'court_sponsors_tab' => 'Anfitriones',
                    'court_men' => 'DJ y show',
                    'court_women' => 'Jurado de disfraces',
                    'nav_court' => 'Equipo',
                    'playlist_eyebrow' => 'La música de la noche',
                    'polls_eyebrow' => 'Vota antes de la fiesta',
                    'rsvp_eyebrow' => 'Únete al caso',
                    'rsvp_yes' => 'Me uno al caso',
                    'rsvp_declined_intro' => 'Si cambias de planes, escríbenos para actualizar tu respuesta.',
                    'stories_hint' => 'Toca para pasar la hoja',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.expediente.log',
                ],
            ],
        ];
    }

    /** Clase propia de la página (inv-{tema}); las clásicas usan la de su evento (inv-xv, inv-boda…). */
    public static function theme(?string $template): string
    {
        $meta = self::get($template);

        return $meta['theme'] ?? $meta['event'];
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

    /**
     * Familia de la plantilla: lienzo (en blanco), clasica (una por evento), temporada (Halloween y
     * las tarjetas) y nueva (las que se suman durante el año para cada evento). La usan los planes de
     * revendedor: Inicial lienzo y clásicas, Aliado suma temporada y desde Emprendedor las nuevas.
     */
    public static function collection(?string $template): string
    {
        return self::get($template)['collection'] ?? 'clasica';
    }

    public static function copy(?string $template): array
    {
        return self::get($template)['copy'];
    }
}
