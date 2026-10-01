<?php

namespace App\Support;

/**
 * Colección «tendencias»: plantillas temáticas, cada una con su historia
 * (la inauguración de una galería para los XV, un concierto para dos voces para la boda…). La
 * temática recorre toda la invitación: la apertura, la portada, el nombre de cada sección y las
 * vistas propias de algunos módulos.
 *
 * Se arman igual que la colección «nueva», pero no tienen un archivo por plantilla: todas se
 * dibujan con la vista VIEW (InvitationTemplates::view) y lo propio de cada una vive en
 * partials/tendencias/{tema} y en css/invitation/tendencias/{tema}.css, que se carga solo en ella.
 *
 * Ninguna depende de una paleta ni de una tipografía: la firma está en la composición, las formas y
 * el movimiento. La paleta y las letras de cada entrada son solo con las que nace la invitación.
 *
 * Los textos parten del vocabulario de su evento (el de su plantilla clásica: «Nos casamos», «Mis
 * padrinos»…) y encima van los de la temática.
 */
final class TrendTemplates
{
    /** Vista que dibuja todas las plantillas de la colección. */
    public const VIEW = 'invitations.templates.tendencia';

    /** Plantilla clásica de cada evento: de su «copy» sale el vocabulario del evento. */
    private const VOCABULARY_FROM = [
        'boda' => InvitationTemplates::BODA_JARDIN,
        'bautizo' => InvitationTemplates::BAUTIZO_CIELO,
        'cumple' => InvitationTemplates::CUMPLE_FIESTA,
        'graduacion' => InvitationTemplates::GRADUACION_BIRRETE,
        'halloween' => InvitationTemplates::HALLOWEEN_CALABAZAS,
    ];

    /** Textos que dependen del evento y no de la temática (la apertura y los rótulos propios no). */
    private const VOCABULARY_KEYS = [
        'hero_eyebrow', 'menu_heading', 'guest_help', 'countdown_lottie', 'countdown_eyebrow', 'countdown_done_title',
        'location_eyebrow', 'gallery_eyebrow', 'itinerary_eyebrow', 'itinerary_empty', 'dress_eyebrow', 'dress_hint',
        'dress_empty', 'court_lottie', 'court_eyebrow', 'court_title', 'court_intro', 'court_empty', 'court_first',
        'court_group_tab', 'court_sponsors_tab', 'court_men', 'court_women', 'nav_court', 'gifts_intro',
        'playlist_eyebrow', 'polls_eyebrow', 'rsvp_eyebrow', 'rsvp_yes', 'rsvp_declined_intro', 'hero_class_label',
    ];

    /**
     * XV tampoco la toma de su clásica: «Atelier» (XV_PREMIUM) habla del desfile en todos sus textos,
     * y las demás plantillas de XV parten de este vocabulario neutro de la fiesta.
     */
    private const XV_VOCABULARY = [
        'hero_eyebrow' => 'Mis XV años',
        'itinerary_eyebrow' => 'Así será mi noche',
        'court_eyebrow' => 'Quienes me acompañan',
        'court_title' => 'Mi corte de honor',
        'court_men' => 'Chambelanes',
        'court_women' => 'Damas',
    ];

    /** Baby shower no tiene clásica de la que tomar el vocabulario: va aquí. */
    private const BABY_SHOWER_VOCABULARY = [
        'hero_eyebrow' => 'Baby shower',
        'menu_heading' => 'El baby shower de',
        'guest_help' => 'Te toma menos de un minuto y nos ayuda a preparar la celebración.',
        'countdown_lottie' => 'heart',
        'countdown_eyebrow' => 'Ya casi llega',
        'countdown_done_title' => '¡Hoy celebramos!',
        'gallery_eyebrow' => 'Esperando con amor',
        'itinerary_eyebrow' => 'Así será la celebración',
        'itinerary_empty' => 'Muy pronto compartiremos el programa.',
        'dress_hint' => 'Colores sugeridos para la celebración',
        'dress_empty' => 'Ven cómodo y con ganas de celebrar.',
        'court_lottie' => 'gift',
        'court_eyebrow' => 'Quienes lo organizan',
        'court_title' => 'Con mucho cariño',
        'court_intro' => 'Las personas que preparan esta celebración para la mamá y el bebé.',
        'court_empty' => 'Pronto presentaremos a quienes organizan la celebración.',
        'court_first' => 'padrinos',
        'court_sponsors_tab' => 'Anfitrionas',
        'court_group_tab' => 'Familia',
        'court_men' => 'Abuelos',
        'court_women' => 'Tías y amigas',
        'nav_court' => 'Anfitrionas',
        'gifts_intro' => 'Tu compañía es lo más importante. Si deseas tener un detalle, aquí está la lista de regalos para el bebé.',
        'rsvp_declined_intro' => 'Si cambias de planes, escríbenos para actualizar tu respuesta.',
    ];

    /**
     * Muestra de cada evento (database/seeders/showcase) sobre la que se arma la de su plantilla
     * temática: mismo contenido, otro diseño, para que se note que solo cambia la apariencia.
     */
    public const DEMO_BASES = [
        'xv' => 'xv-isabella',
        'boda' => 'boda-camila-andres',
        'bautizo' => 'bautizo-emilia',
        'cumple' => 'cumple-daniela-30',
        'graduacion' => 'graduacion-mariana',
        'halloween' => 'halloween-noche-diego',
        'babyshower' => 'babyshower-valentina',
    ];

    /**
     * Las entradas del catálogo, a partir de las plantillas que ya existen (de ahí sale el
     * vocabulario de cada evento).
     *
     * @param  array<string, array<string, mixed>>  $catalog
     * @return array<string, array<string, mixed>>
     */
    public static function entries(array $catalog): array
    {
        $entries = [];

        foreach (self::themes() as $theme => $meta) {
            $entries[self::key($theme, $meta['event'])] = [
                'label' => $meta['label'],
                'tagline' => $meta['tagline'],
                'description' => $meta['description'],
                'event' => $meta['event'],
                'collection' => $meta['collection'] ?? 'tendencias',
                'theme' => $theme,
                'view' => self::VIEW,
                'palette' => $meta['palette'],
                'fonts' => $meta['fonts'],
                'color_usage' => $meta['color_usage'],
                'order' => $meta['order'],
                'copy' => array_merge(self::vocabulary($meta['event'], $catalog), $meta['copy']),
                'partials' => $meta['partials'] ?? [],
                'frame' => $meta['frame'],
                'pdf' => $meta['pdf'],
            ];
        }

        return $entries;
    }

    public static function key(string $theme, string $event): string
    {
        return "invitations.templates.{$event}-{$theme}";
    }

    /**
     * Muestras de la colección ({muestra del evento}-{tema}). La de baby shower ya es la de
     * «Tendedero» (es la muestra base del evento): no suma otra.
     *
     * @return list<string>
     */
    public static function demoSlugs(): array
    {
        return array_keys(self::demos());
    }

    /**
     * Cómo se arma una muestra de la colección: la muestra base y la plantilla; null si el slug no es
     * de la colección.
     *
     * @return array{base: string, template: string, event: string}|null
     */
    public static function demo(string $slug): ?array
    {
        return self::demos()[$slug] ?? null;
    }

    /** Muestras de un evento: la de su plantilla temática (o nada, si la base ya lo es). */
    public static function demoSlugsFor(string $event): array
    {
        return array_keys(array_filter(self::demos(), fn (array $demo) => $demo['event'] === $event));
    }

    /** @return array<string, array{base: string, template: string, event: string}> */
    private static function demos(): array
    {
        $demos = [];

        foreach (self::themes() as $theme => $meta) {
            $base = self::DEMO_BASES[$meta['event']];

            if (! empty($meta['is_base_demo'])) {
                continue;
            }

            $demos["{$base}-{$theme}"] = ['base' => $base, 'template' => self::key($theme, $meta['event']), 'event' => $meta['event']];
        }

        return $demos;
    }

    /**
     * Las plantillas de la colección: tema => su evento, su historia, con qué nace y los textos de
     * la temática.
     */
    public static function themes(): array
    {
        return [
            // ── XV años: un libro desplegable ─────────────────────────────────────────
            // Reemplaza a «Carta de baile»: sigue en la familia «nueva» (mismos planes de revendedor)
            'cuento' => [
                'event' => 'xv',
                'collection' => 'nueva',
                'label' => 'Cuento desplegable',
                'tagline' => 'Sus quince años en un libro que se despliega',
                'description' => 'Sus quince años contados como un libro pop-up: se abre la tapa y del pliegue se levantan el salón y su nombre en papel recortado. Cada sección es un capítulo, el programa son las páginas de la noche que se levantan una a una y los padrinos, los personajes del cuento en sus medallones.',
                'palette' => ['primary' => '#6F4FA0', 'secondary' => '#2F5D50', 'accent' => '#F2C6D0', 'text' => '#26212B', 'background' => '#FBF8F3'],
                'fonts' => ['titulos' => 'Fraunces', 'cuerpo' => 'Figtree', 'script' => 'Tangerine'],
                'color_usage' => [
                    'background' => 'Las páginas del libro.',
                    'text' => 'La tinta de imprenta.',
                    'primary' => 'La tapa de tela, las capitulares, las pestañas y los botones.',
                    'secondary' => 'Los planos de fondo del desplegable y el lomo.',
                    'accent' => 'Las capas de papel y las guardas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'dress_code', 'destacados', 'galeria',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'menu_heading' => 'El cuento de',
                    'intro_eyebrow' => 'Érase una vez',
                    'intro_hint' => 'Toca el libro para abrirlo',
                    'book_title' => 'El cuento de',
                    'book_opening' => 'Érase una vez…',
                    'book_chapter' => 'Capítulo',
                    'book_plate' => 'Lámina',
                    'guest_banner_eyebrow' => 'Este cuento es para',
                    'countdown_eyebrow' => 'Faltan pocas páginas',
                    'countdown_done_title' => '¡Hoy empieza el cuento!',
                    'location_eyebrow' => 'Dónde sucede la historia',
                    'itinerary_eyebrow' => 'Las páginas de la noche',
                    'itinerary_empty' => 'Muy pronto escribiremos las páginas de la noche.',
                    'dress_eyebrow' => 'Cómo vestir para el cuento',
                    'gallery_eyebrow' => 'Láminas del cuento',
                    'court_eyebrow' => 'Los personajes',
                    'court_title' => 'Quienes están en mi historia',
                    'court_intro' => 'Las personas que hacen posible este capítulo de mi vida.',
                    'nav_court' => 'Personajes',
                    'rsvp_eyebrow' => '¿Vendrás a mi historia?',
                    'rsvp_yes' => 'Sí, ahí estaré',
                    'rsvp_no' => 'No podré ir',
                    'rsvp_submit' => 'Guardar mi lugar',
                    'rsvp_confirmed_eyebrow' => 'Tu lugar en la historia',
                    'guest_cta' => 'Guardar mi lugar',
                    'post_eyebrow' => '…y la fiesta siguió',
                    'stories_hint' => 'Toca para pasar la página',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.cuento.pages',
                    'destacados' => 'invitations.partials.tendencias.cuento.characters',
                ],
                'frame' => [1080, 1350, 'arch', 'Foto de la lámina'],
                'pdf' => ['cover' => 'cuento', 'motif' => 'flor', 'frame' => 'double', 'kicker' => 'Érase una vez'],
            ],

            // ── XV años: sus primeros tacones, en la caja de la zapatería ─────────────
            // Reemplaza a «Caleidoscopio» (y antes a «Galería Quince»)
            'zapatos' => [
                'event' => 'xv',
                'label' => 'El cambio de zapatos',
                'tagline' => 'Sus primeros tacones, en la caja de la zapatería',
                'description' => 'La tradición más tierna de los quince hecha invitación: se entra abriendo la caja de la zapatería, se aparta el papel de seda y aparecen sus primeros tacones, con su nombre grabado en la plantilla. La portada es la caja abierta (la foto entre las dos zapatillas) con su etiqueta de modelo y talla; cada sección es otra caja con su referencia, el programa son los pasos de la noche como huellas de tacón y los padrinos, las cajas apiladas de la vitrina con su etiqueta.',
                'palette' => ['primary' => '#B5476B', 'secondary' => '#221C20', 'accent' => '#EFD5CC', 'text' => '#241E21', 'background' => '#FBF6F1'],
                'fonts' => ['titulos' => 'Cormorant Garamond', 'cuerpo' => 'Raleway', 'script' => 'Great Vibes'],
                'color_usage' => [
                    'background' => 'Las paredes de la zapatería.',
                    'text' => 'Los textos.',
                    'primary' => 'Las zapatillas de satén, el moño y los botones.',
                    'secondary' => 'Las cajas de la zapatería y sus letras.',
                    'accent' => 'El papel de seda y las etiquetas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'galeria', 'dress_code', 'destacados',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'menu_heading' => 'Los quince de',
                    'intro_eyebrow' => 'Una caja muy especial',
                    'intro_hint' => 'Toca la caja para abrirla',
                    'guest_banner_eyebrow' => 'Hecho a medida para',
                    'shoe_line' => 'Colección XV',
                    'shoe_made' => 'Hecho a mano para',
                    'shoe_model' => 'Modelo',
                    'shoe_model_name' => 'Mis primeros tacones',
                    'shoe_size' => 'Talla',
                    'shoe_date' => 'Fecha',
                    'shoe_time' => 'Hora',
                    'shoe_place' => 'Lugar',
                    'shoe_ref' => 'Ref.',
                    'shoe_moment' => 'El cambio de zapatos',
                    'countdown_eyebrow' => 'Falta muy poco',
                    'countdown_done_title' => '¡Hoy estreno mis tacones!',
                    'itinerary_eyebrow' => 'Los pasos de la noche',
                    'itinerary_empty' => 'Muy pronto compartiré los pasos de la noche.',
                    'gallery_eyebrow' => 'Para el recuerdo',
                    'dress_eyebrow' => 'Los colores de la noche',
                    'court_eyebrow' => 'Quienes me acompañan',
                    'court_title' => 'Mi corte de honor',
                    'rsvp_eyebrow' => 'Reserva tu lugar',
                    'stories_hint' => 'Toca para dar el siguiente paso',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.zapatos.steps',
                    'destacados' => 'invitations.partials.tendencias.zapatos.boxes',
                ],
                'frame' => [1080, 1350, 'rect', 'Foto de la caja'],
                'pdf' => ['cover' => 'zapatos', 'motif' => 'diamante', 'frame' => 'double', 'kicker' => 'Mis XV años'],
            ],
            // ── XV años: el joyero que pasa de madre a hija ──────────────────────────
            'joyero' => [
                'event' => 'xv',
                'label' => 'Joyero musical',
                'tagline' => 'El joyero que pasa de madre a hija, con el vals adentro',
                'description' => 'Se entra girando la llave del joyero: la tapa se levanta y gira la figura de la quinceañera. La foto va en un relicario sobre el terciopelo capitoné, el programa es una cadena de dijes, cada sección es una bandeja del joyero y cada padrino aparece con la joya que entrega en la ceremonia.',
                'palette' => ['primary' => '#1F5E4E', 'secondary' => '#5A3A2A', 'accent' => '#D8B46A', 'text' => '#231C1F', 'background' => '#FAF6F0'],
                'fonts' => ['titulos' => 'Libre Caslon Display', 'cuerpo' => 'Lato', 'script' => 'Pinyon Script'],
                'color_usage' => [
                    'background' => 'La seda del forro.',
                    'text' => 'Los textos.',
                    'primary' => 'El terciopelo del joyero y los botones.',
                    'secondary' => 'La madera lacada del joyero.',
                    'accent' => 'El oro de las bisagras, la cadena y los filetes.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'destacados', 'dress_code', 'galeria',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'menu_heading' => 'El joyero de',
                    'intro_eyebrow' => 'Un recuerdo para siempre',
                    'intro_hint' => 'Toca la llave para abrir el joyero',
                    'guest_banner_eyebrow' => 'Guardado con cariño para',
                    'jewel_card' => 'Mis XV años',
                    'countdown_eyebrow' => 'Falta muy poco',
                    'itinerary_eyebrow' => 'Los dijes de mi noche',
                    'itinerary_empty' => 'Muy pronto sumaremos cada dije de la noche.',
                    'court_eyebrow' => 'Las joyas de la ceremonia',
                    'court_title' => 'Mis padrinos y mi corte',
                    'court_intro' => 'Cada padrino me entrega un símbolo que voy a guardar siempre.',
                    'dress_eyebrow' => 'Las piedras de la noche',
                    'rsvp_eyebrow' => 'Guarda tu lugar',
                    'stories_hint' => 'Toca para abrir la siguiente bandeja',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.joyero.charms',
                    'destacados' => 'invitations.partials.tendencias.joyero.jewels',
                ],
                'frame' => [1000, 1300, 'oval', 'Foto del relicario'],
                'pdf' => ['cover' => 'joyero', 'motif' => 'diamante', 'frame' => 'double', 'kicker' => 'Mis XV años'],
            ],

            // ── XV años: su nombre es una fragancia ──────────────────────────────────
            'esencia' => [
                'event' => 'xv',
                'label' => 'Esencia XV',
                'tagline' => 'Su nombre es la fragancia: la campaña de lanzamiento',
                'description' => 'La quinceañera lanza su propia fragancia: se entra desatando la cinta de la caja, sube el frasco y en la bruma aparece su nombre. La portada es el afiche de la campaña, el programa es la pirámide de notas (salida, corazón y fondo), los padrinos van como los créditos del reverso de la caja y cada sección es una página de la campaña.',
                'palette' => ['primary' => '#B8456E', 'secondary' => '#151417', 'accent' => '#EADCF0', 'text' => '#1C1A1E', 'background' => '#F7F4F1'],
                'fonts' => ['titulos' => 'Italiana', 'cuerpo' => 'DM Sans', 'script' => 'Instrument Serif'],
                'color_usage' => [
                    'background' => 'La porcelana de la campaña.',
                    'text' => 'Los textos.',
                    'primary' => 'La fragancia dentro del frasco, la cinta y los detalles.',
                    'secondary' => 'La caja, la tapa del frasco y los botones.',
                    'accent' => 'El nácar del cristal y los brillos.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'galeria', 'dress_code', 'destacados',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'menu_heading' => 'El lanzamiento de',
                    'intro_eyebrow' => 'Lanzamiento exclusivo',
                    'intro_hint' => 'Toca la cinta para abrir la caja',
                    'guest_banner_eyebrow' => 'Muestra exclusiva para',
                    'scent_line' => 'Eau de Quince',
                    'scent_edition' => 'Edición',
                    'scent_launch' => 'Lanzamiento',
                    'scent_top' => 'Notas de salida',
                    'scent_heart' => 'Notas de corazón',
                    'scent_base' => 'Notas de fondo',
                    'countdown_eyebrow' => 'Disponible en',
                    'countdown_done_title' => '¡Hoy es el lanzamiento!',
                    'location_eyebrow' => 'Punto de lanzamiento',
                    'itinerary_eyebrow' => 'La pirámide de la noche',
                    'dress_eyebrow' => 'Tonos de la colección',
                    'gallery_eyebrow' => 'La campaña',
                    'court_eyebrow' => 'La casa detrás de la fragancia',
                    'court_title' => 'Créditos',
                    'court_intro' => 'Las personas que crearon esta noche conmigo.',
                    'nav_court' => 'Créditos',
                    'rsvp_eyebrow' => 'Tu lugar en el lanzamiento',
                    'post_eyebrow' => 'Gracias por ser parte del lanzamiento',
                    'stories_hint' => 'Toca para ver la siguiente página',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.esencia.pyramid',
                    'destacados' => 'invitations.partials.tendencias.esencia.credits',
                ],
                'frame' => [1080, 1350, 'rect', 'Foto de la campaña'],
                'pdf' => ['cover' => 'esencia', 'motif' => 'linea', 'frame' => 'thin', 'kicker' => 'Lanzamiento'],
            ],

            // ── Boda: dos melodías que se vuelven una ────────────────────────────────
            'partitura' => [
                'event' => 'boda',
                'label' => 'Partitura a dos voces',
                'tagline' => 'Un concierto donde dos voces se vuelven una',
                'description' => 'La invitación es el programa de un concierto: cada nombre empieza en su pentagrama y los dos se juntan en uno solo. El día se lee en movimientos (Andante, Allegro…), los padrinos forman el ensamble y para confirmar se reserva una butaca.',
                'palette' => ['primary' => '#3D5A73', 'secondary' => '#B5654E', 'accent' => '#EDE6D8', 'text' => '#1E2433', 'background' => '#F7F3EA'],
                'fonts' => ['titulos' => 'Instrument Serif', 'cuerpo' => 'Instrument Sans', 'script' => 'Instrument Serif'],
                'color_usage' => [
                    'background' => 'El papel del programa.',
                    'text' => 'La tinta de las notas y los textos.',
                    'primary' => 'La primera voz, los números de los movimientos y los botones.',
                    'secondary' => 'La segunda voz.',
                    'accent' => 'Los recuadros y la butaca.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'galeria', 'destacados', 'dress_code',
                    'playlist', 'video', 'regalos', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Tienes una invitación',
                    'intro_hint' => 'Toca la batuta para empezar',
                    'score_program' => 'Programa',
                    'score_title' => 'Concierto para dos voces',
                    'score_opus' => 'Op. 1',
                    'score_date' => 'Función',
                    'score_hall' => 'Sala',
                    'guest_banner_eyebrow' => 'Butaca reservada para',
                    'countdown_eyebrow' => 'La orquesta se prepara',
                    'countdown_title' => 'Faltan',
                    'location_eyebrow' => 'Sala del concierto',
                    'itinerary_eyebrow' => 'Programa del concierto',
                    'itinerary_empty' => 'Muy pronto publicaremos el programa del concierto.',
                    'nav_itinerario' => 'Programa',
                    'gallery_eyebrow' => 'Nuestros ensayos',
                    'court_eyebrow' => 'El ensamble',
                    'court_title' => 'Quienes tocan con nosotros',
                    'nav_court' => 'Ensamble',
                    'dress_eyebrow' => 'Etiqueta del concierto',
                    'playlist_eyebrow' => 'Pide tu canción',
                    'playlist_label' => 'Tu canción',
                    'playlist_list_title' => 'Canciones pedidas por el público',
                    'nav_playlist' => 'Pide tu canción',
                    'video_eyebrow' => 'Obertura',
                    'gifts_eyebrow' => 'Con gratitud',
                    'polls_eyebrow' => 'La voz del público',
                    'hashtag_eyebrow' => 'Comparte el concierto',
                    'mural_eyebrow' => 'Desde el público',
                    'post_eyebrow' => 'Así sonó el concierto',
                    'rsvp_eyebrow' => 'Reserva tu butaca',
                    'rsvp_yes' => 'Reservo mi butaca',
                    'rsvp_no' => 'No podré ir',
                    'rsvp_submit' => 'Reservar mi butaca',
                    'rsvp_confirmed_eyebrow' => 'Tu butaca',
                    'rsvp_pass_people' => 'Butacas',
                    'guest_cta' => 'Reservar mi butaca',
                    'nav_rsvp' => 'Butaca',
                    'stories_hint' => 'Toca para pasar al siguiente compás',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.partitura.movements',
                    'destacados' => 'invitations.partials.tendencias.partitura.ensemble',
                ],
                'frame' => [1080, 1440, 'arch', 'Foto del programa'],
                'pdf' => ['cover' => 'partitura', 'motif' => 'linea', 'frame' => 'thin', 'kicker' => 'Concierto para dos voces'],
            ],

            // ── Boda: su lugar en la mesa del banquete ───────────────────────────────
            'mesa' => [
                'event' => 'boda',
                'label' => 'Mesa de honor',
                'tagline' => 'Un lugar reservado en la mesa del banquete',
                'description' => 'La invitación es su lugar en la mesa: se entra quitando el servilletero, la servilleta se abre y en el plato aparece el monograma de los novios. La portada es el cubierto puesto (la foto pintada en el plato de porcelana, la tarjeta con los nombres y el menú con la fecha y el salón); el programa es el menú del día por tiempos, los padrinos se sientan en la mesa de honor con su tarjeta y cada sección es una tarjeta sobre el mantel.',
                'palette' => ['primary' => '#4E6B5A', 'secondary' => '#A88645', 'accent' => '#EADFD6', 'text' => '#2A2724', 'background' => '#F4F1EA'],
                'fonts' => ['titulos' => 'Cormorant Infant', 'cuerpo' => 'Nunito Sans', 'script' => 'Pinyon Script'],
                'color_usage' => [
                    'background' => 'El mantel de lino.',
                    'text' => 'Los textos y la caligrafía.',
                    'primary' => 'La servilleta, las tarjetas de honor y los botones.',
                    'secondary' => 'El oro de los platos, los cubiertos y los filetes.',
                    'accent' => 'La porcelana y las tarjetas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'destacados', 'galeria',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Tienes un lugar en nuestra mesa',
                    'intro_hint' => 'Toca el servilletero para abrir',
                    'table_seat' => 'Su lugar en nuestra mesa',
                    'table_menu' => 'Menú de la celebración',
                    'table_course' => 'tiempo',
                    'guest_banner_eyebrow' => 'Mesa reservada para',
                    'countdown_eyebrow' => 'La mesa se está poniendo',
                    'location_eyebrow' => 'Dónde está la mesa',
                    'itinerary_eyebrow' => 'El menú del día',
                    'itinerary_empty' => 'Muy pronto compartiremos el menú del día.',
                    'nav_itinerario' => 'Menú',
                    'gallery_eyebrow' => 'Sobre la mesa',
                    'dress_eyebrow' => 'Etiqueta de la cena',
                    'court_eyebrow' => 'Mesa de honor',
                    'court_title' => 'Quienes se sientan a nuestro lado',
                    'nav_court' => 'Mesa de honor',
                    'gifts_eyebrow' => 'Un detalle en la mesa',
                    'playlist_eyebrow' => 'La música de la cena',
                    'rsvp_eyebrow' => 'Reserva tu silla',
                    'rsvp_yes' => 'Guárdenme la silla',
                    'rsvp_no' => 'No podré ir',
                    'rsvp_submit' => 'Reservar mi lugar',
                    'rsvp_confirmed_eyebrow' => 'Tu lugar en la mesa',
                    'guest_cta' => 'Reservar mi lugar',
                    'post_eyebrow' => 'La sobremesa',
                    'stories_hint' => 'Toca para servir el siguiente tiempo',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.mesa.menu',
                    'destacados' => 'invitations.partials.tendencias.mesa.seating',
                ],
                'frame' => [1200, 1200, 'circle', 'Foto del plato'],
                'pdf' => ['cover' => 'mesa', 'motif' => 'ramas', 'frame' => 'double', 'kicker' => 'Nos casamos'],
            ],

            // ── Boda: dos relojes que desde ese día marcan la misma hora ─────────────
            'reloj' => [
                'event' => 'boda',
                'label' => 'A la misma hora',
                'tagline' => 'Dos relojes que se sincronizan a la hora de la boda',
                'description' => 'Cada uno llega con su reloj marcando su propia hora; al darles cuerda las agujas giran hasta la hora de la boda y las dos esferas se vuelven una. La foto es la esfera del reloj con las agujas en la hora de la ceremonia, cada sección lleva su hora en números romanos, el programa muestra un relojito por momento con su hora exacta y los padrinos son los engranajes que hacen que todo marche.',
                'palette' => ['primary' => '#2F4A6D', 'secondary' => '#9C7A3E', 'accent' => '#D8B4A0', 'text' => '#1D2433', 'background' => '#F5F0E6'],
                'fonts' => ['titulos' => 'Cinzel', 'cuerpo' => 'Source Sans 3', 'script' => 'Instrument Serif'],
                'color_usage' => [
                    'background' => 'La esfera de marfil.',
                    'text' => 'Los números, las agujas finas y los textos.',
                    'primary' => 'Las agujas, los botones y el acero.',
                    'secondary' => 'El latón de la caja y los engranajes.',
                    'accent' => 'Las subesferas y el segundero.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'rsvp', 'dress_code', 'destacados', 'galeria',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Desde ese día, un mismo tiempo',
                    'intro_hint' => 'Toca la corona para darles cuerda',
                    'watch_brand' => 'A la misma hora',
                    'watch_date' => 'Día',
                    'watch_time' => 'Hora',
                    'watch_place' => 'Lugar',
                    'guest_banner_eyebrow' => 'Con tiempo reservado para',
                    'countdown_eyebrow' => 'Cuenta atrás',
                    'countdown_done_title' => '¡Llegó la hora!',
                    'location_eyebrow' => 'Dónde nos vemos',
                    'itinerary_eyebrow' => 'Hora por hora',
                    'itinerary_empty' => 'Muy pronto compartiremos cada hora del día.',
                    'gallery_eyebrow' => 'Nuestro tiempo juntos',
                    'court_eyebrow' => 'El mecanismo',
                    'court_title' => 'Quienes hacen que todo marche',
                    'court_intro' => 'Sin ellos nada se mueve: nuestros padrinos y el cortejo.',
                    'nav_court' => 'Padrinos',
                    'rsvp_eyebrow' => 'Sincroniza tu reloj',
                    'rsvp_yes' => 'Llegaré a tiempo',
                    'rsvp_no' => 'No podré ir',
                    'rsvp_confirmed_eyebrow' => 'Tu hora con nosotros',
                    'post_eyebrow' => 'El tiempo que compartimos',
                    'stories_hint' => 'Toca para adelantar el reloj',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.reloj.hours',
                    'destacados' => 'invitations.partials.tendencias.reloj.gears',
                ],
                'frame' => [1200, 1200, 'circle', 'Foto de la esfera'],
                'pdf' => ['cover' => 'reloj', 'motif' => 'diamante', 'frame' => 'thin', 'kicker' => 'Nos casamos'],
            ],

            // ── Bautizo: el móvil que cuelga sobre la cuna ───────────────────────────
            'movil' => [
                'event' => 'bautizo',
                'label' => 'Móvil de cuna',
                'tagline' => 'Un móvil de fieltro que gira sobre la cuna',
                'description' => 'La portada es el móvil de la cuna, con la vela del bautismo, una estrella, la luna, una casita y un corderito de fieltro que giran despacio. Un hilo baja por la invitación y de él cuelga cada sección; los padrinos cuelgan juntos de la misma varilla.',
                'palette' => ['primary' => '#557559', 'secondary' => '#C98E72', 'accent' => '#E6DCCB', 'text' => '#26314A', 'background' => '#F7F3EC'],
                'fonts' => ['titulos' => 'Fraunces', 'cuerpo' => 'Figtree', 'script' => 'Fraunces'],
                'color_usage' => [
                    'background' => 'La pared del cuarto.',
                    'text' => 'Los textos, los hilos y las puntadas.',
                    'primary' => 'Las figuras de fieltro verdes, la vela y los botones.',
                    'secondary' => 'Las figuras de fieltro cálidas: la casita y la estrella.',
                    'accent' => 'La varilla de madera y el corderito.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'destacados', 'rsvp', 'galeria', 'dress_code',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Un día muy especial',
                    'intro_hint' => 'Toca el móvil para que gire',
                    'guest_banner_eyebrow' => 'Esta invitación es para',
                    'countdown_eyebrow' => 'Ya casi es el día',
                    'location_eyebrow' => 'Dónde será',
                    'gallery_eyebrow' => 'Mis primeros días',
                    'dress_eyebrow' => 'Qué ponerse',
                    'gifts_eyebrow' => 'Un detalle para mí',
                    'playlist_eyebrow' => 'Música para la reunión',
                    'polls_eyebrow' => 'Adivina, adivinador',
                    'hashtag_eyebrow' => 'Comparte mis fotos',
                    'video_eyebrow' => 'Un pequeño video',
                    'mural_eyebrow' => 'Fotos de mi día',
                    'post_eyebrow' => 'Recuerdos de mi bautizo',
                    'rsvp_eyebrow' => '¿Me acompañas?',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Te espero',
                    'stories_hint' => 'Toca para seguir',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.movil.steps',
                    'destacados' => 'invitations.partials.tendencias.movil.godparents',
                ],
                'frame' => [1080, 1080, 'circle', 'Foto en el marco de fieltro'],
                'pdf' => ['cover' => 'movil', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Mi bautizo'],
            ],

            // ── Bautizo: el cielo del día del bautizo, con su constelación ───────────
            'estrellas' => [
                'event' => 'bautizo',
                'label' => 'Mapa de estrellas',
                'tagline' => 'El cielo del día de su bautizo, con su propia constelación',
                'description' => 'Se entra uniendo las estrellas de su constelación. La portada es el mapa del cielo del día del bautizo, con la foto como la luna y las coordenadas del lugar; el día se recorre estrella por estrella y los padrinos son las estrellas que lo guían. Al fondo el cielo gira despacio y pasan estrellas fugaces.',
                'palette' => ['primary' => '#E8C77E', 'secondary' => '#1E2A56', 'accent' => '#9DB2EA', 'text' => '#EFE9DC', 'background' => '#0E1533'],
                'fonts' => ['titulos' => 'Cormorant Garamond', 'cuerpo' => 'Nunito Sans', 'script' => 'Cormorant Garamond'],
                'color_usage' => [
                    'background' => 'El cielo de noche.',
                    'text' => 'Los textos y las estrellas chicas.',
                    'primary' => 'Las estrellas que brillan, la luna y los botones.',
                    'secondary' => 'Los recuadros: un cielo un poco más claro.',
                    'accent' => 'Las líneas de las constelaciones.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'destacados', 'rsvp', 'galeria', 'dress_code',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Mira el cielo',
                    'intro_hint' => 'Toca la estrella más brillante para unir la constelación',
                    'sky_label' => 'El cielo del día de mi bautizo',
                    'constellation_label' => 'La constelación de',
                    'guest_banner_eyebrow' => 'Una estrella para',
                    'countdown_eyebrow' => 'Faltan pocas noches',
                    'location_eyebrow' => 'Bajo este cielo',
                    'itinerary_eyebrow' => 'Mi día, estrella por estrella',
                    'court_eyebrow' => 'Las estrellas que me guían',
                    'gallery_eyebrow' => 'Mis primeras noches',
                    'dress_eyebrow' => 'Qué ponerse',
                    'gifts_eyebrow' => 'Un deseo para mí',
                    'playlist_eyebrow' => 'Música para la reunión',
                    'polls_eyebrow' => 'Adivina, adivinador',
                    'hashtag_eyebrow' => 'Comparte mi cielo',
                    'video_eyebrow' => 'Un pequeño video',
                    'mural_eyebrow' => 'Fotos de mi día',
                    'post_eyebrow' => 'Recuerdos de mi bautizo',
                    'rsvp_eyebrow' => '¿Me acompañas?',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Te espero',
                    'stories_hint' => 'Toca para seguir la constelación',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.estrellas.path',
                    'destacados' => 'invitations.partials.tendencias.estrellas.guides',
                ],
                'frame' => [1080, 1080, 'circle', 'Foto como la luna del mapa'],
                'pdf' => ['cover' => 'estrellas', 'motif' => 'linea', 'frame' => 'none', 'paper' => 'dark', 'kicker' => 'Mi bautizo'],
            ],

            // ── Bautizo: su nombre bordado a mano en un bastidor ────────────────────
            'bordado' => [
                'event' => 'bautizo',
                'label' => 'Bordado a mano',
                'tagline' => 'Su nombre bordado en un bastidor, puntada a puntada',
                'description' => 'Se entra bordando su nombre: la aguja recorre el bastidor y el nombre queda en hilo, con florcitas alrededor. La foto va en el bastidor rodeada de una corona bordada; cada sección es un paño de lino con su costura, el día es un muestrario de puntadas y los padrinos, monogramas en bastidores chiquitos.',
                'palette' => ['primary' => '#5F7FA0', 'secondary' => '#C0868A', 'accent' => '#D8C4A2', 'text' => '#34322D', 'background' => '#F5F0E6'],
                'fonts' => ['titulos' => 'Cormorant Garamond', 'cuerpo' => 'Nunito Sans', 'script' => 'Dancing Script'],
                'color_usage' => [
                    'background' => 'El lino.',
                    'text' => 'Los textos.',
                    'primary' => 'El hilo del nombre y los botones.',
                    'secondary' => 'El hilo de las flores.',
                    'accent' => 'La madera del bastidor y las hojas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'destacados', 'rsvp', 'galeria', 'dress_code',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Bordado con amor',
                    'intro_hint' => 'Toca la aguja para bordar su nombre',
                    'guest_banner_eyebrow' => 'Bordado para',
                    'countdown_eyebrow' => 'Puntada a puntada',
                    'location_eyebrow' => 'Dónde será',
                    'itinerary_eyebrow' => 'Así será mi día',
                    'court_eyebrow' => 'Quienes me guiarán',
                    'gallery_eyebrow' => 'Mis primeros días',
                    'dress_eyebrow' => 'Qué ponerse',
                    'gifts_eyebrow' => 'Un detalle para mí',
                    'playlist_eyebrow' => 'Música para la reunión',
                    'polls_eyebrow' => 'Adivina, adivinador',
                    'hashtag_eyebrow' => 'Comparte mis fotos',
                    'video_eyebrow' => 'Un pequeño video',
                    'mural_eyebrow' => 'Fotos de mi día',
                    'post_eyebrow' => 'Recuerdos de mi bautizo',
                    'rsvp_eyebrow' => '¿Me acompañas?',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Te espero',
                    'stories_hint' => 'Toca para seguir',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.bordado.sampler',
                    'destacados' => 'invitations.partials.tendencias.bordado.monograms',
                ],
                'frame' => [1080, 1080, 'circle', 'Foto en el bastidor'],
                'pdf' => ['cover' => 'bordado', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Mi bautizo'],
            ],

            // ── Cumpleaños: la única fecha de una gira mundial ───────────────────────
            'gira' => [
                'event' => 'cumple',
                'label' => 'Gira mundial',
                'tagline' => 'El cumpleaños como la única fecha de una gira',
                'description' => 'Se entra con una pulsera de festival que lleva el nombre del invitado. La portada es el afiche de la gira, con todas las ciudades tachadas menos la fiesta; el programa es el line-up, la playlist es el setlist y al confirmar llega el pase de backstage.',
                'palette' => ['primary' => '#C92A62', 'secondary' => '#0078BF', 'accent' => '#FFE36E', 'text' => '#151515', 'background' => '#F7F2E7'],
                'fonts' => ['titulos' => 'Bricolage Grotesque', 'cuerpo' => 'DM Sans', 'script' => 'Bricolage Grotesque'],
                'color_usage' => [
                    'background' => 'El papel del afiche.',
                    'text' => 'Los textos y la tinta negra del afiche.',
                    'primary' => 'La primera tinta del afiche, la pulsera y los botones.',
                    'secondary' => 'La segunda tinta del afiche.',
                    'accent' => 'Los recuadros resaltados y la cinta adhesiva.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'playlist', 'dress_code', 'galeria',
                    'encuestas', 'destacados', 'regalos', 'video', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Tu acceso está listo',
                    'intro_hint' => 'Toca para escanear tu pulsera',
                    'tour_presents' => 'En concierto',
                    'tour_name' => 'Gira',
                    'tour_cities' => 'Tokio · París · Nueva York · Londres',
                    'tour_cancelled' => 'Cancelado',
                    'tour_only' => 'Fecha única',
                    'tour_doors' => 'Puertas',
                    'tour_wristband' => 'Acceso general',
                    'guest_banner_eyebrow' => 'Acceso a nombre de',
                    'countdown_eyebrow' => 'El show empieza en',
                    'location_eyebrow' => 'El escenario',
                    'location_button' => 'Cómo llegar al show',
                    'itinerary_eyebrow' => 'Line-up',
                    'itinerary_empty' => 'Muy pronto anunciaremos el line-up de la noche.',
                    'nav_itinerario' => 'Line-up',
                    'playlist_eyebrow' => 'Arma el setlist',
                    'playlist_label' => 'Tu tema',
                    'playlist_button' => 'Sumar al setlist',
                    'playlist_list_title' => 'Setlist del público',
                    'nav_playlist' => 'Setlist',
                    'dress_eyebrow' => 'Dress code del show',
                    'gallery_eyebrow' => 'Fotos de la gira',
                    'polls_eyebrow' => 'Votación del público',
                    'court_eyebrow' => 'El crew',
                    'nav_court' => 'Crew',
                    'gifts_eyebrow' => 'Un detalle para la estrella',
                    'video_eyebrow' => 'El tráiler de la gira',
                    'hashtag_eyebrow' => 'Comparte el show',
                    'mural_eyebrow' => 'Fotos desde el público',
                    'post_eyebrow' => 'Así fue el show',
                    'rsvp_eyebrow' => 'Consigue tu acceso',
                    'rsvp_yes' => '¡Voy al show!',
                    'rsvp_no' => 'Esta vez no',
                    'rsvp_submit' => 'Quiero mi acceso',
                    'rsvp_confirmed_eyebrow' => 'Acceso backstage',
                    'rsvp_pass_people' => 'Accesos',
                    'guest_cta' => 'Consigue tu acceso',
                    'nav_rsvp' => 'Acceso',
                    'stories_hint' => 'Toca para el siguiente tema',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.gira.lineup',
                ],
                'frame' => [1080, 1350, 'rect', 'Foto del afiche'],
                'pdf' => ['cover' => 'gira', 'motif' => 'linea', 'frame' => 'block', 'kicker' => 'En concierto'],
            ],

            // ── Cumpleaños: el almanaque de pared, con el día marcado en rojo ────────
            'feriado' => [
                'event' => 'cumple',
                'label' => 'Día feriado',
                'tagline' => 'El almanaque de pared con su cumpleaños marcado en rojo',
                'description' => 'La invitación es el almanaque de la casa: se entra arrancando las hojas de los días que faltan hasta llegar al cumpleaños, impreso en rojo como feriado y marcado con un círculo de bolígrafo. La portada es el almanaque de pared, con la foto como la lámina del mes, la hoja del día con el nombre, la hora, el lugar, la luna y el pensamiento del día, y el mes con su día marcado; el programa es la agenda del día y la gente favorita queda anotada en papelitos pegados.',
                'palette' => ['primary' => '#C63A2F', 'secondary' => '#234B77', 'accent' => '#F2D36B', 'text' => '#1D1B18', 'background' => '#EFE9DC'],
                'fonts' => ['titulos' => 'Barlow Condensed', 'cuerpo' => 'Public Sans', 'script' => 'Caveat'],
                'color_usage' => [
                    'background' => 'La pared donde cuelga el almanaque.',
                    'text' => 'La tinta negra de los días y los textos.',
                    'primary' => 'El rojo del feriado: el número del día, la franja del mes y los botones.',
                    'secondary' => 'La tinta del bolígrafo con que se marca el día y los días de la semana.',
                    'accent' => 'Los papelitos pegados en el almanaque.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'dress_code', 'destacados', 'playlist',
                    'galeria', 'encuestas', 'regalos', 'video', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Este año el almanaque trae un feriado más',
                    'intro_hint' => 'Toca el almanaque para arrancar las hojas',
                    'cal_holiday' => 'Feriado',
                    'cal_line' => 'Cumpleaños de',
                    'cal_note' => '¡Mi cumple!',
                    'cal_age' => 'años',
                    'cal_thought' => 'Pensamiento del día',
                    'guest_banner_eyebrow' => 'Día reservado para',
                    'countdown_eyebrow' => 'Hojas por arrancar',
                    'location_eyebrow' => 'Dónde se celebra',
                    'itinerary_eyebrow' => 'La agenda del día',
                    'itinerary_empty' => 'Muy pronto anotaré la agenda del día.',
                    'nav_itinerario' => 'Agenda',
                    'gallery_eyebrow' => 'Días para recordar',
                    'dress_eyebrow' => 'Qué ponerse ese día',
                    'court_eyebrow' => 'Anotados en mi almanaque',
                    'playlist_eyebrow' => 'La música del feriado',
                    'polls_eyebrow' => 'Preguntas del día',
                    'gifts_eyebrow' => 'Un detalle para el día',
                    'video_eyebrow' => 'Un día para recordar',
                    'hashtag_eyebrow' => 'Comparte el feriado',
                    'mural_eyebrow' => 'Fotos del día',
                    'post_eyebrow' => 'Así fue el feriado',
                    'rsvp_eyebrow' => 'Márcalo en tu almanaque',
                    'rsvp_yes' => '¡Anotado, voy!',
                    'rsvp_no' => 'Ese día no puedo',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Día marcado',
                    'guest_cta' => 'Marcar mi respuesta',
                    'stories_hint' => 'Toca para arrancar la siguiente hoja',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.feriado.agenda',
                    'destacados' => 'invitations.partials.tendencias.feriado.notes',
                ],
                'frame' => [1200, 800, 'rect', 'Foto de la lámina del almanaque'],
                'pdf' => ['cover' => 'feriado', 'motif' => 'linea', 'frame' => 'thin', 'kicker' => 'Mi cumpleaños'],
            ],

            // ── Cumpleaños: la tira que sale de la cabina de fotos ───────────────────
            'cabina' => [
                'event' => 'cumple',
                'label' => 'Cabina de fotos',
                'tagline' => 'Una tira de fotos recién salida de la cabina',
                'description' => 'Se entra a la cabina de fotos: se aprieta el botón, la pantallita cuenta 3, 2, 1, salta el flash en cada pose y la tira sale por la ranura revelándose. La portada es esa tira, con tres poses, el sello naranja de la fecha en cada foto y, al pie, el nombre, el día, la hora y el lugar impresos como en las cabinas de las fiestas; toda la invitación baja como una sola tira larga, el programa va pose por pose y la gente favorita sale en su propio cuadrito.',
                'palette' => ['primary' => '#E2557F', 'secondary' => '#2B8A7E', 'accent' => '#FFB454', 'text' => '#F4EFE8', 'background' => '#17181D'],
                'fonts' => ['titulos' => 'Unbounded', 'cuerpo' => 'DM Sans', 'script' => 'Permanent Marker'],
                'color_usage' => [
                    'background' => 'El interior oscuro de la cabina.',
                    'text' => 'El papel de las fotos y los textos.',
                    'primary' => 'La cortina de terciopelo, el letrero y los botones.',
                    'secondary' => 'El esmalte de la cabina.',
                    'accent' => 'El sello de la fecha en las fotos y la luz del flash.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'galeria', 'dress_code', 'destacados',
                    'playlist', 'encuestas', 'fotomural', 'hashtag', 'regalos', 'video', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Pasa, que la foto te espera',
                    'intro_hint' => 'Toca el botón para la foto',
                    'booth_sign' => 'Fotos',
                    'booth_button' => '¡Foto!',
                    'booth_ready' => 'Mira a la cámara',
                    'booth_slot' => 'Tus fotos salen aquí',
                    'booth_age' => 'años',
                    'guest_banner_eyebrow' => 'Esta tira es para',
                    'countdown_eyebrow' => 'Sonríe en',
                    'location_eyebrow' => 'Dónde será la fiesta',
                    'itinerary_eyebrow' => 'La noche, pose por pose',
                    'itinerary_empty' => 'Muy pronto compartiré cada momento de la noche.',
                    'nav_itinerario' => 'La noche',
                    'gallery_eyebrow' => 'Tiras de otros años',
                    'dress_eyebrow' => 'Para salir bien en la foto',
                    'court_eyebrow' => 'Salen en la foto',
                    'playlist_eyebrow' => 'Suena mientras posas',
                    'polls_eyebrow' => 'Antes del flash',
                    'video_eyebrow' => 'Detrás de la cortina',
                    'hashtag_eyebrow' => 'Sube tu tira',
                    'mural_eyebrow' => 'Las fotos de los invitados',
                    'post_eyebrow' => 'Así salieron las fotos',
                    'rsvp_eyebrow' => '¿Entras a la foto?',
                    'rsvp_yes' => '¡Salgo en la foto!',
                    'rsvp_no' => 'Esta vez no',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Ya sales en la foto',
                    'guest_cta' => 'Entrar a la foto',
                    'stories_hint' => 'Toca para la siguiente pose',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.cabina.poses',
                    'destacados' => 'invitations.partials.tendencias.cabina.cast',
                ],
                'frame' => [1200, 960, 'rect', 'Foto de la tira'],
                'pdf' => ['cover' => 'cabina', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Mi cumpleaños'],
            ],

            // ── Graduación: la portada de una revista de colección ───────────────────
            'revista' => [
                'event' => 'graduacion',
                'label' => 'Edición especial',
                'tagline' => 'La portada de una revista de colección',
                'description' => 'El graduado es la estrella de una edición especial: se entra rompiendo la faja de la revista y los titulares de la portada llevan a cada sección. El programa es la agenda, la familia firma los créditos de la edición y para confirmar se recorta el cupón.',
                'palette' => ['primary' => '#B3122E', 'secondary' => '#F2D04B', 'accent' => '#E9E4DA', 'text' => '#141414', 'background' => '#F6F4EF'],
                'fonts' => ['titulos' => 'Bodoni Moda', 'cuerpo' => 'Source Serif 4', 'script' => 'Bodoni Moda'],
                'color_usage' => [
                    'background' => 'El papel de la revista.',
                    'text' => 'La tinta y los títulos.',
                    'primary' => 'El nombre de la revista, las secciones y los botones.',
                    'secondary' => 'El resaltador de los datos clave y la faja.',
                    'accent' => 'Los recuadros y las columnas de la agenda.',
                ],
                'order' => [
                    'cuenta_regresiva', 'itinerario', 'ubicacion', 'rsvp', 'dress_code', 'galeria', 'destacados',
                    'video', 'regalos', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Edición de colección',
                    'intro_hint' => 'Toca para romper la faja',
                    'mag_name' => 'Promoción',
                    'mag_issue' => 'N.º 1 · Edición especial',
                    'mag_exclusive' => 'Exclusiva',
                    'mag_party' => 'La fiesta del año',
                    'mag_where' => 'Dónde será',
                    'mag_style' => 'Qué ponerte',
                    'mag_agenda' => 'El programa completo',
                    'mag_page' => 'pág.',
                    'mag_contents' => 'En esta edición',
                    'mag_letter' => 'Carta del editor',
                    'mag_pick' => 'Imperdible',
                    'guest_banner_eyebrow' => 'Ejemplar reservado para',
                    'countdown_eyebrow' => 'En circulación en',
                    'location_eyebrow' => 'Dónde será',
                    'itinerary_eyebrow' => 'Agenda',
                    'itinerary_empty' => 'Muy pronto publicaremos la agenda del día.',
                    'nav_itinerario' => 'Agenda',
                    'dress_eyebrow' => 'Guía de estilo',
                    'gallery_eyebrow' => 'Fotorreportaje',
                    'court_eyebrow' => 'Créditos de esta edición',
                    'court_title' => 'Gracias a ustedes',
                    'nav_court' => 'Créditos',
                    'video_eyebrow' => 'Detrás de cámaras',
                    'gifts_eyebrow' => 'Lista de deseos',
                    'playlist_eyebrow' => 'Suena en la fiesta',
                    'polls_eyebrow' => 'Encuesta de lectores',
                    'hashtag_eyebrow' => 'Síguenos',
                    'mural_eyebrow' => 'Fotos de los lectores',
                    'post_eyebrow' => 'Las fotos de la fiesta',
                    'rsvp_eyebrow' => 'Cupón de suscripción',
                    'rsvp_yes' => 'Me suscribo: voy',
                    'rsvp_no' => 'Esta vez no',
                    'rsvp_submit' => 'Enviar mi cupón',
                    'rsvp_confirmed_eyebrow' => 'Suscripción confirmada',
                    'rsvp_pass_people' => 'Ejemplares',
                    'guest_cta' => 'Enviar mi cupón',
                    'nav_rsvp' => 'Cupón',
                    'stories_hint' => 'Toca para pasar la página',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.revista.agenda',
                    'destacados' => 'invitations.partials.tendencias.revista.credits',
                ],
                'frame' => [1080, 1350, 'rect', 'Foto de portada de la revista'],
                'pdf' => ['cover' => 'revista', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Edición especial'],
            ],

            // ── Halloween: el estreno de una película de medianoche ──────────────────
            'funcion' => [
                'event' => 'halloween',
                'label' => 'Función de medianoche',
                'tagline' => 'El estreno de una película de terror de las de antes',
                'description' => 'La fiesta es el estreno de una película de miedo de los años cincuenta, apta para toda la familia: se enciende el proyector, corre la cuenta de la cinta y aparece el afiche con los datos en los créditos. El programa es la cartelera y para confirmar se reserva el boleto en la taquilla.',
                'palette' => ['primary' => '#E8B04B', 'secondary' => '#C0392B', 'accent' => '#2A201B', 'text' => '#F1E6D2', 'background' => '#120E0C'],
                'fonts' => ['titulos' => 'Bungee', 'cuerpo' => 'Public Sans', 'script' => 'Alfa Slab One'],
                'color_usage' => [
                    'background' => 'La sala a oscuras.',
                    'text' => 'Los textos y la luz del proyector.',
                    'primary' => 'Las luces de la marquesina, el título y los botones.',
                    'secondary' => 'El telón y el sello de la clasificación.',
                    'accent' => 'Los recuadros de la cartelera y el boleto.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'dress_code', 'rsvp', 'encuestas', 'playlist',
                    'galeria', 'destacados', 'video', 'regalos', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Función de estreno',
                    'intro_hint' => 'Toca para encender el proyector',
                    'film_rating' => 'Clasificación A',
                    'film_rating_text' => 'Apta para valientes de todas las edades',
                    'film_premiere' => 'Estreno',
                    'film_show' => 'Función',
                    'film_theater' => 'Sala',
                    'film_intermission' => 'Intermedio',
                    'guest_banner_eyebrow' => 'Boleto a nombre de',
                    'countdown_eyebrow' => 'La función empieza en',
                    'countdown_done_title' => '¡Hoy es el estreno!',
                    'location_eyebrow' => 'El cine',
                    'location_button' => 'Cómo llegar a la función',
                    'itinerary_eyebrow' => 'Cartelera',
                    'itinerary_empty' => 'Muy pronto publicaremos la cartelera de la noche.',
                    'nav_itinerario' => 'Cartelera',
                    'dress_eyebrow' => 'Vestuario',
                    'dress_hint' => 'Los colores de la función',
                    'polls_eyebrow' => 'Votación del público',
                    'playlist_eyebrow' => 'La banda sonora',
                    'gallery_eyebrow' => 'Fotogramas',
                    'court_eyebrow' => 'El elenco',
                    'nav_court' => 'Elenco',
                    'video_eyebrow' => 'Tráiler',
                    'gifts_eyebrow' => 'Un detalle para la producción',
                    'hashtag_eyebrow' => 'Comparte la función',
                    'mural_eyebrow' => 'Fotos en la alfombra roja',
                    'post_eyebrow' => 'Así fue el estreno',
                    'rsvp_eyebrow' => 'Taquilla',
                    'rsvp_yes' => 'Quiero mi boleto',
                    'rsvp_no' => 'No podré ir',
                    'rsvp_submit' => 'Reservar mi boleto',
                    'rsvp_confirmed_eyebrow' => 'Tu boleto',
                    'rsvp_pass_people' => 'Admite',
                    'guest_cta' => 'Reservar mi boleto',
                    'nav_rsvp' => 'Taquilla',
                    'stories_hint' => 'Toca para la siguiente escena',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.funcion.showtimes',
                ],
                'frame' => [1080, 1242, 'rect', 'Foto del afiche de la película'],
                'pdf' => ['cover' => 'funcion', 'motif' => 'linea', 'frame' => 'none', 'paper' => 'dark', 'kicker' => 'Función de estreno'],
            ],

            // ── Halloween: la poción que se prepara para la fiesta ──────────────────
            'caldero' => [
                'event' => 'halloween',
                'label' => 'Caldero encantado',
                'tagline' => 'Una poción que se revuelve y un frasco con tu foto',
                'description' => 'Se entra revolviendo el caldero: la poción cambia de color, burbujea y del humo sale el nombre. La portada es el frasco de la poción con la foto adentro y su etiqueta; el programa es la receta paso a paso y cada sección, una página del recetario de pociones. Para toda la familia, sin sustos.',
                'palette' => ['primary' => '#7FD65A', 'secondary' => '#9B5DE5', 'accent' => '#F4A340', 'text' => '#EFE6F7', 'background' => '#1A1326'],
                'fonts' => ['titulos' => 'Bagel Fat One', 'cuerpo' => 'Outfit', 'script' => 'Fredoka'],
                'color_usage' => [
                    'background' => 'La noche del laboratorio.',
                    'text' => 'Los textos.',
                    'primary' => 'La poción y los botones.',
                    'secondary' => 'El humo, los frascos y las burbujas.',
                    'accent' => 'El fuego y las etiquetas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'dress_code', 'rsvp', 'encuestas', 'playlist',
                    'galeria', 'destacados', 'video', 'regalos', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Se está preparando una fiesta',
                    'intro_hint' => 'Toca el caldero para revolver la poción',
                    'potion_label' => 'Receta secreta',
                    'potion_ingredients' => 'Ingredientes: disfraces, dulces y mucha diversión',
                    'potion_step' => 'Paso',
                    'guest_banner_eyebrow' => 'Un frasco para',
                    'countdown_eyebrow' => 'La poción estará lista en',
                    'countdown_done_title' => '¡La poción está lista!',
                    'location_eyebrow' => 'El laboratorio',
                    'itinerary_eyebrow' => 'La receta de la noche',
                    'itinerary_empty' => 'Muy pronto publicaremos la receta de la noche.',
                    'nav_itinerario' => 'La receta',
                    'dress_eyebrow' => 'Tu disfraz',
                    'polls_eyebrow' => 'Votación del caldero',
                    'playlist_eyebrow' => 'Música para revolver',
                    'gallery_eyebrow' => 'Pociones pasadas',
                    'court_eyebrow' => 'Los aprendices del caldero',
                    'court_title' => 'Quién prepara la poción',
                    'video_eyebrow' => 'Un adelanto',
                    'gifts_eyebrow' => 'Un ingrediente para la fiesta',
                    'hashtag_eyebrow' => 'Comparte tu poción',
                    'mural_eyebrow' => 'Fotos del laboratorio',
                    'post_eyebrow' => 'Así quedó la poción',
                    'rsvp_eyebrow' => '¿Te sumas a la receta?',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Tu frasco está listo',
                    'stories_hint' => 'Toca para revolver',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.caldero.recipe',
                    'destacados' => 'invitations.partials.tendencias.caldero.crew',
                ],
                'frame' => [1080, 1350, 'rounded', 'Foto dentro del frasco'],
                'pdf' => ['cover' => 'caldero', 'motif' => 'linea', 'frame' => 'none', 'paper' => 'dark', 'kicker' => 'Fiesta de Halloween'],
            ],

            // ── Baby shower: la ropita colgada en el tendedero ───────────────────────
            'tendedero' => [
                'event' => 'babyshower',
                // Es la primera plantilla del evento: va con las clásicas (todos los planes la tienen)
                'collection' => 'clasica',
                'is_base_demo' => true,
                'label' => 'Tendedero',
                'tagline' => 'Ropita de bebé colgada con pinzas',
                'description' => 'Un tendedero cruza la portada con la ropita del bebé: el nombre va en el enterito, la foto cuelga con pinzas y la fecha, la hora y el lugar van en etiquetas que se mecen. Cada sección cuelga de su propio cordel y el programa son etiquetas en fila.',
                'palette' => ['primary' => '#557559', 'secondary' => '#C98E72', 'accent' => '#DCE7D8', 'text' => '#243128', 'background' => '#F8F6F0'],
                'fonts' => ['titulos' => 'Fredoka', 'cuerpo' => 'Nunito Sans', 'script' => 'Fredoka'],
                'color_usage' => [
                    'background' => 'El cielo detrás del tendedero.',
                    'text' => 'Los textos y el cordel.',
                    'primary' => 'El enterito y los botones.',
                    'secondary' => 'Las medias y las pinzas.',
                    'accent' => 'El gorrito y las etiquetas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'regalos', 'rsvp', 'destacados', 'galeria',
                    'dress_code', 'video', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Viene alguien muy especial',
                    'intro_hint' => 'Toca el canasto para colgar la ropita',
                    'line_date' => 'Fecha',
                    'line_time' => 'Hora',
                    'line_place' => 'Lugar',
                    'guest_banner_eyebrow' => 'Una invitación para',
                    'location_eyebrow' => 'Dónde celebramos',
                    'dress_eyebrow' => 'Qué ponerse',
                    'gifts_eyebrow' => 'Para su llegada',
                    'playlist_eyebrow' => 'Música para la tarde',
                    'polls_eyebrow' => 'Adivina, adivinador',
                    'hashtag_eyebrow' => 'Comparte la celebración',
                    'video_eyebrow' => 'La espera',
                    'mural_eyebrow' => 'Fotos de la tarde',
                    'post_eyebrow' => 'Recuerdos del baby shower',
                    'rsvp_eyebrow' => '¿Nos acompañas?',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Te esperamos',
                    'stories_hint' => 'Toca para seguir',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.tendedero.tags',
                ],
                'frame' => [1080, 1080, 'rect', 'Foto colgada del tendedero'],
                'pdf' => ['cover' => 'tendedero', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Baby shower'],
            ],

            // ── Baby shower: su nombre armado con bloques de juguete ──────────────────
            'bloques' => [
                'event' => 'babyshower',
                'label' => 'Bloques de juguete',
                'tagline' => 'Su nombre armado con bloques de madera',
                'description' => 'Se entra abriendo el baúl de juguetes: los bloques saltan uno por uno y caen en fila hasta formar su nombre. La portada es un bloque grande que gira despacio con la foto en una cara y el nombre en bloquecitos; el programa es una torre de bloques, las anfitrionas van en bloques con su inicial y cada sección es la cara de un bloque con su letra.',
                'palette' => ['primary' => '#D96C4F', 'secondary' => '#3A7FA0', 'accent' => '#F0C04E', 'text' => '#2C313B', 'background' => '#FBF6EC'],
                'fonts' => ['titulos' => 'Bungee', 'cuerpo' => 'Nunito Sans', 'script' => 'Fredoka'],
                'color_usage' => [
                    'background' => 'El cuarto de juegos.',
                    'text' => 'Los textos.',
                    'primary' => 'Los bloques rojos y los botones.',
                    'secondary' => 'Los bloques azules.',
                    'accent' => 'Los bloques amarillos.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'regalos', 'rsvp', 'destacados', 'galeria',
                    'dress_code', 'video', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Viene alguien muy especial',
                    'intro_hint' => 'Toca el baúl para sacar los bloques',
                    'guest_banner_eyebrow' => 'Una invitación para',
                    'location_eyebrow' => 'Dónde jugamos',
                    'itinerary_eyebrow' => 'Bloque por bloque',
                    'dress_eyebrow' => 'Qué ponerse',
                    'gifts_eyebrow' => 'Para su llegada',
                    'playlist_eyebrow' => 'Música para la tarde',
                    'polls_eyebrow' => 'Adivina, adivinador',
                    'hashtag_eyebrow' => 'Comparte la celebración',
                    'video_eyebrow' => 'La espera',
                    'mural_eyebrow' => 'Fotos de la tarde',
                    'post_eyebrow' => 'Recuerdos del baby shower',
                    'rsvp_eyebrow' => '¿Vienes a jugar?',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Te esperamos',
                    'stories_hint' => 'Toca para seguir',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.bloques.tower',
                    'destacados' => 'invitations.partials.tendencias.bloques.hosts',
                ],
                'frame' => [1080, 1080, 'rect', 'Foto en la cara del bloque'],
                'pdf' => ['cover' => 'bloques', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Baby shower'],
            ],

            // ── Baby shower: una encomienda que llega con todo el cuidado ─────────────
            'encomienda' => [
                'event' => 'babyshower',
                'label' => 'Encomienda especial',
                'tagline' => 'Una caja con su nombre, que llega con todo el cuidado',
                'description' => 'Se entra abriendo la encomienda: se despega la cinta, se abren las solapas y del papel de seda sale su nombre. La portada es la guía del envío, con la foto sujeta con cinta y los sellos de «frágil»; el programa es el seguimiento del envío, paso a paso, las anfitrionas son estampillas y cada sección es una hoja de cartón con su cinta.',
                'palette' => ['primary' => '#A8764C', 'secondary' => '#5E7F93', 'accent' => '#E9B9A3', 'text' => '#3A332D', 'background' => '#F7F2EB'],
                'fonts' => ['titulos' => 'Special Elite', 'cuerpo' => 'DM Sans', 'script' => 'Dancing Script'],
                'color_usage' => [
                    'background' => 'El papel de seda.',
                    'text' => 'Los textos.',
                    'primary' => 'El cartón y los botones.',
                    'secondary' => 'Los sellos y las estampillas.',
                    'accent' => 'La cinta y las etiquetas.',
                ],
                'order' => [
                    'cuenta_regresiva', 'ubicacion', 'itinerario', 'regalos', 'rsvp', 'destacados', 'galeria',
                    'dress_code', 'video', 'playlist', 'encuestas', 'hashtag', 'fotomural', 'post_evento',
                ],
                'copy' => [
                    'intro_eyebrow' => 'Llegó una encomienda muy especial',
                    'intro_hint' => 'Toca la cinta para abrir la caja',
                    'parcel_title' => 'Encomienda especial',
                    'parcel_content' => 'Contenido',
                    'parcel_arrival' => 'Llega',
                    'parcel_address' => 'Entrega en',
                    'parcel_fragile' => 'Frágil',
                    'parcel_care' => 'Con mucho amor',
                    'guest_banner_eyebrow' => 'Destinatario',
                    'countdown_eyebrow' => 'Tiempo estimado de entrega',
                    'location_eyebrow' => 'Dirección de entrega',
                    'itinerary_eyebrow' => 'Seguimiento del envío',
                    'court_eyebrow' => 'Remitentes',
                    'dress_eyebrow' => 'Qué ponerse',
                    'gifts_eyebrow' => 'Para su llegada',
                    'playlist_eyebrow' => 'Música para la tarde',
                    'polls_eyebrow' => 'Adivina, adivinador',
                    'hashtag_eyebrow' => 'Comparte la celebración',
                    'video_eyebrow' => 'La espera',
                    'mural_eyebrow' => 'Fotos de la tarde',
                    'post_eyebrow' => 'Recuerdos del baby shower',
                    'rsvp_eyebrow' => 'Confirma la recepción',
                    'rsvp_yes' => 'Ahí estaré',
                    'rsvp_submit' => 'Enviar mi respuesta',
                    'rsvp_confirmed_eyebrow' => 'Recibido',
                    'stories_hint' => 'Toca para seguir',
                ],
                'partials' => [
                    'itinerario' => 'invitations.partials.tendencias.encomienda.tracking',
                    'destacados' => 'invitations.partials.tendencias.encomienda.senders',
                ],
                'frame' => [1080, 1350, 'rect', 'Foto pegada en la guía del envío'],
                'pdf' => ['cover' => 'encomienda', 'motif' => 'linea', 'frame' => 'none', 'kicker' => 'Baby shower'],
            ],
        ];
    }

    /** Vocabulario de un evento: el de su plantilla clásica (o el de baby shower). */
    private static function vocabulary(string $event, array $catalog): array
    {
        if ($event === 'babyshower') {
            return self::BABY_SHOWER_VOCABULARY;
        }

        if ($event === 'xv') {
            return self::XV_VOCABULARY;
        }

        $copy = $catalog[self::VOCABULARY_FROM[$event] ?? '']['copy'] ?? [];

        return array_intersect_key($copy, array_flip(self::VOCABULARY_KEYS));
    }
}
