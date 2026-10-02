<?php

namespace App\Support;

/**
 * Todos los textos que muestra una invitación y que se pueden cambiar desde el editor: la frase
 * sobre cada título, las introducciones, lo que se ve cuando todavía no hay datos, los botones…
 *
 * Cada texto tiene una clave (la misma que usan las vistas: $invCopy['clave'] ?? 'por defecto'),
 * un nombre legible para el editor y su valor por defecto. Una plantilla puede traer su propia
 * versión en «copy» (InvitationTemplates) y cada invitación puede reemplazarla con la suya
 * (tabla invitation_texts). Orden de prioridad: la invitación, la plantilla, este catálogo.
 *
 * Texto nuevo en una vista: se escribe como $invCopy['clave'] ?? 'por defecto' y se suma aquí,
 * en el módulo donde aparece. EditableTextsTest comprueba que las claves del catálogo existan en
 * las vistas, para que ninguna quede sin efecto.
 */
final class EditableTexts
{
    /** Claves de «copy» que no son textos (animaciones, orden de pestañas). */
    public const NOT_TEXT = ['court_lottie', 'countdown_lottie', 'court_first'];

    /** Textos de toda la invitación (portada, menú, pie): van en la pestaña General. */
    public const GENERAL = 'general';

    /**
     * Módulo => clave => [nombre en el editor, valor por defecto, ¿texto largo?].
     *
     * @return array<string, array<string, array{0: string, 1: string, 2?: bool}>>
     */
    public static function catalog(): array
    {
        return self::withMenuNames([
            self::GENERAL => [
                'menu_heading' => ['Encabezado del menú', 'Invitación de'],
                'footer_pitch' => ['Invitación a crear la propia (al pie)', '¿Te gustó esta invitación? Crea la tuya'],
                'menu_label' => ['Botón del menú', 'Menú'],
                'nav_inicio' => ['Primer enlace del menú', 'Inicio'],
                'scroll_hint' => ['Indicación para bajar (portada)', 'Desliza'],
                'back_to_top' => ['Enlace para volver arriba (pie)', 'Volver al inicio'],
                'skip_link' => ['Enlace para saltar al contenido (lectores de pantalla)', 'Saltar al contenido'],
            ],
            'bienvenida' => [
                'hero_eyebrow' => ['Frase sobre el nombre (si no hay subtítulo)', 'Mis XV Años'],
            ],
            'cuenta_regresiva' => [
                'countdown_eyebrow' => ['Frase sobre el título', 'El gran día se acerca'],
                'countdown_title' => ['Título', 'Faltan'],
                'countdown_done_eyebrow' => ['Frase cuando llega el día', 'Llegó el momento'],
                'countdown_done_title' => ['Título cuando llega el día', '¡Hoy es el gran día!'],
                'countdown_calendar' => ['Botón para agendar', 'Agendar en mi calendario'],
            ],
            'ubicacion' => [
                'location_eyebrow' => ['Frase sobre el título', '¿Dónde nos vemos?'],
                'location_title' => ['Título (si no hay nombre del lugar)', 'Ubicación'],
                'location_button' => ['Botón del mapa', 'Cómo llegar'],
            ],
            'itinerario' => [
                'itinerary_eyebrow' => ['Frase sobre el título', 'El recorrido de la noche'],
                'itinerary_empty' => ['Mientras no haya horarios', 'Muy pronto compartiremos el orden de la noche.', true],
            ],
            'dress_code' => [
                'dress_eyebrow' => ['Frase sobre el título', 'Vestimenta'],
                'dress_hint' => ['Texto sobre los colores', 'Tonos sugeridos para la noche'],
                'dress_folds_hint' => ['Ayuda de las opciones', 'Toca cada opción para ver el detalle'],
                'dress_empty' => ['Mientras no haya sugerencias', 'Viste elegante y cómodo para disfrutar toda la noche.', true],
            ],
            'destacados' => [
                'court_eyebrow' => ['Frase sobre el título', 'Quienes me acompañan'],
                'court_title' => ['Título', 'Mi cortejo'],
                'court_intro' => ['Introducción', 'Personas muy especiales que estarán a mi lado esta noche.', true],
                'court_group_tab' => ['Pestaña del cortejo', 'Cortejo'],
                'court_sponsors_tab' => ['Pestaña de padrinos', 'Padrinos'],
                'court_men' => ['Nombre del primer grupo', 'Chambelanes'],
                'court_women' => ['Nombre del segundo grupo', 'Damitas'],
                'court_folds_hint' => ['Ayuda de los grupos', 'Toca cada grupo para ver los nombres'],
                'court_empty' => ['Mientras no haya nombres', 'Pronto presentaremos a quienes me acompañan.', true],
                'nav_court' => ['Nombre en el menú', 'Cortejo'],
            ],
            'galeria' => [
                'gallery_eyebrow' => ['Frase sobre el título', 'Momentos especiales'],
                'gallery_hint' => ['Ayuda para pasar las fotos', 'Desliza la foto hacia un lado para ver la siguiente'],
                'gallery_empty' => ['Mientras no haya fotos', 'Pronto compartiremos aquí las fotos.'],
            ],
            'video' => [
                'video_eyebrow' => ['Frase sobre el título', 'Save the date'],
                'video_help' => ['Ayuda para verlo', 'Toca el video para reproducirlo y sube el volumen de tu teléfono.'],
                'video_empty' => ['Mientras no haya video', 'Muy pronto compartiremos el video.'],
            ],
            'hashtag' => [
                'hashtag_eyebrow' => ['Frase sobre el título', 'Redes sociales'],
                'hashtag_intro' => ['Introducción (:red se cambia por Instagram o TikTok)', 'Publica tus fotos y videos en :red con esta etiqueta para verlos todos juntos.', true],
                'hashtag_copy' => ['Botón para copiar', 'Copiar hashtag'],
            ],
            'playlist' => [
                'playlist_eyebrow' => ['Frase sobre el título', 'Colabora con la fiesta'],
                'playlist_label' => ['Nombre del campo', 'Tu canción'],
                'playlist_button' => ['Botón', 'Sugerir'],
                'playlist_help' => ['Ayuda del campo', 'Escribe el nombre y el artista, o pega un enlace de YouTube.'],
                'playlist_empty' => ['Mientras nadie sugiere', 'Sé la primera persona en sugerir una canción.'],
                'playlist_list_title' => ['Título de la lista', 'Canciones sugeridas'],
            ],
            'encuestas' => [
                'polls_eyebrow' => ['Frase sobre el título', 'Tu opinión cuenta'],
                'polls_intro' => ['Introducción', 'Toca una opción para votar. Verás los resultados al instante.', true],
                'polls_empty' => ['Mientras no haya preguntas', 'Pronto habrá preguntas para votar.'],
            ],
            'regalos' => [
                'gifts_eyebrow' => ['Frase sobre el título', 'Detalles especiales'],
                'gifts_intro' => ['Introducción', 'Tu presencia es mi mejor regalo. Si deseas tener un detalle, aquí tienes algunas opciones.', true],
                'gifts_bank_title' => ['Título de la transferencia', 'Transferencia bancaria'],
                'gifts_bank_hint' => ['Ayuda para copiar los datos', 'Toca «Copiar» y pega el dato en la app de tu banco.'],
                'gifts_qr_hint' => ['Ayuda del QR', 'Escanea el código desde la app de tu banco'],
            ],
            'rsvp' => [
                'guest_banner_eyebrow' => ['Saludo al invitado', 'Esta invitación es para'],
                'guest_cta' => ['Botón del saludo', 'Confirmar asistencia'],
                'guest_banner_attendance' => ['Saludo: rótulo de la asistencia', 'Asistencia'],
                'guest_banner_passes' => ['Saludo: rótulo de los pases', 'Pases'],
                'guest_help' => ['Ayuda del saludo', 'Te toma menos de un minuto y nos ayuda a organizar la noche.', true],
                'rsvp_eyebrow' => ['Frase sobre el título', 'Confirma tu asistencia'],
                'rsvp_attend_question' => ['Primera pregunta', '¿Asistirás?'],
                'rsvp_yes' => ['Respuesta afirmativa', 'Sí, asistiré'],
                'rsvp_no' => ['Respuesta negativa', 'No podré ir'],
                'rsvp_people_question' => ['Pregunta por las personas', '¿Cuántas personas vendrán?'],
                'rsvp_dietary_label' => ['Campo de alimentación', 'Alergias o restricciones alimentarias (opcional)'],
                'rsvp_submit' => ['Botón para confirmar', 'Confirmar asistencia'],
                'rsvp_submit_decline' => ['Botón al decir que no', 'Enviar respuesta'],
                'rsvp_pass_people' => ['Pase: rótulo de las personas', 'Personas'],
                'rsvp_pass_code' => ['Pase: rótulo del código', 'Código'],
                'rsvp_confirmed_eyebrow' => ['Frase al confirmar', 'Asistencia confirmada'],
                'rsvp_tip' => ['Consejo del pase', 'Toma una captura de pantalla por si no tienes señal en el lugar.', true],
                'rsvp_declined_eyebrow' => ['Frase al decir que no', 'Respuesta enviada'],
                'rsvp_declined_intro' => ['Texto al decir que no', 'Si cambias de planes, comunícate con la familia para actualizar tu respuesta.', true],
                'rsvp_whatsapp_name' => ['WhatsApp: campo del nombre', 'Tu nombre'],
                'rsvp_whatsapp_submit' => ['WhatsApp: botón para enviar', 'Enviar por WhatsApp'],
                'rsvp_whatsapp_help' => ['WhatsApp: ayuda del botón', 'Se abre WhatsApp con tu respuesta lista; solo tienes que enviarla.', true],
            ],
            'musica' => [
                'music_hint' => ['Ayuda del reproductor', 'Toca para escuchar'],
            ],
            'agendar' => [
                'calendar_add' => ['Botón de Google Calendar', 'Agregar a Google Calendar'],
            ],
            'fotomural' => [
                'mural_eyebrow' => ['Frase sobre el título', 'Recuerdos en vivo'],
                'mural_title' => ['Título', 'Fotomural'],
                'mural_intro' => ['Introducción', 'Toma o sube una foto durante la fiesta y aparecerá aquí para todos.', true],
                'mural_button' => ['Botón para subir', 'Compartir una foto'],
                'mural_help' => ['Ayuda del botón', 'Puedes usar la cámara o elegir una foto de tu galería.'],
            ],
            'post_evento' => [
                'post_eyebrow' => ['Frase sobre el título', 'Recuerdos oficiales'],
                'post_button' => ['Botón de la galería completa', 'Ver galería completa'],
                'post_empty' => ['Mientras no haya fotos', 'Las fotos oficiales se publicarán muy pronto.'],
            ],
        ]);
    }

    /**
     * Cada sección que aparece en el menú de la invitación tiene su nombre editable («nav_{módulo}»),
     * en el grupo de su módulo. Los invitados de honor ya tienen el suyo (nav_court).
     */
    private static function withMenuNames(array $catalog): array
    {
        foreach (InvitationPage::NAV_LABELS as $module => $label) {
            if ($module !== 'destacados') {
                $catalog[$module]['nav_'.$module] = ['Nombre en el menú', $label];
            }
        }

        return $catalog;
    }

    /**
     * Nombre legible de los textos que solo existen en algunas plantillas (la apertura del libro,
     * la margarita de la carta, los actos de «Bajo la misma luna»…). Los que no estén aquí se
     * muestran igual, con un nombre genérico.
     */
    private const TEMPLATE_LABELS = [
        'cover_eyebrow' => 'Frase de la apertura', 'intro_eyebrow' => 'Frase de la apertura', 'intro_hint' => 'Ayuda de la apertura', 'intro_cheer' => 'Frase al abrir',
        'cover_hint' => 'Ayuda de la apertura', 'cover_skip' => 'Botón para entrar sin esperar',
        'daisy_title' => 'Margarita: título', 'daisy_hint' => 'Margarita: ayuda', 'daisy_yes' => 'Margarita: pétalo que sí',
        'daisy_no' => 'Margarita: pétalo que no', 'daisy_answer' => 'Margarita: respuesta final',
        'wish_eyebrow' => 'Deseo: frase', 'wish_title' => 'Deseo: título', 'wish_hint' => 'Deseo: ayuda', 'wish_message' => 'Deseo: mensaje',
        'butterflies_found' => 'Mensaje de las mariposas encontradas',
        'hero_ticket_label' => 'Portada: rótulo de la fecha', 'hero_day_label' => 'Portada: rótulo del día',
        'hero_time_label' => 'Portada: rótulo de la hora', 'hero_class_label' => 'Portada: rótulo del año',
        'reply_title' => 'Respuesta: título', 'reply_intro' => 'Respuesta: introducción',
        'stories_hint' => 'Aviso del modo historia',
        'hero_place_label' => 'Portada: rótulo del lugar',
        // «Atelier»
        'atelier_house' => 'Atelier: antes del nombre en la etiqueta', 'atelier_collection' => 'Atelier: colección de la etiqueta',
        'atelier_sheet' => 'Atelier: título de la ficha', 'atelier_date' => 'Atelier: rótulo de la fecha', 'atelier_time' => 'Atelier: rótulo de la hora',
        'atelier_place' => 'Atelier: rótulo del lugar', 'atelier_piece' => 'Atelier: palabra antes del número de pieza', 'atelier_look' => 'Atelier: palabra antes del número de look',
        // «Cuento desplegable»
        'book_title' => 'Cuento: tapa, antes del nombre', 'book_opening' => 'Cuento: primera frase',
        'book_chapter' => 'Cuento: palabra antes del número de capítulo', 'book_plate' => 'Cuento: rótulo de la foto',
        // «El cambio de zapatos»
        'shoe_line' => 'Zapatos: línea de la caja, debajo del nombre', 'shoe_made' => 'Zapatos: antes del nombre en la plantilla',
        'shoe_model' => 'Zapatos: rótulo del modelo', 'shoe_model_name' => 'Zapatos: nombre del modelo', 'shoe_size' => 'Zapatos: rótulo de la talla',
        'shoe_date' => 'Zapatos: rótulo de la fecha', 'shoe_time' => 'Zapatos: rótulo de la hora', 'shoe_place' => 'Zapatos: rótulo del lugar',
        'shoe_ref' => 'Zapatos: palabra antes del número de caja', 'shoe_moment' => 'Zapatos: sello del momento del cambio de zapatos',
        // «Mesa de honor»
        'table_seat' => 'Mesa: tarjeta del lugar sin invitado', 'table_menu' => 'Mesa: título del menú de la portada', 'table_course' => 'Mesa: palabra después del número de tiempo',
        // «A la misma hora»
        'watch_brand' => 'Reloj: texto de la esfera', 'watch_date' => 'Reloj: rótulo del día', 'watch_time' => 'Reloj: rótulo de la hora', 'watch_place' => 'Reloj: rótulo del lugar',
        // «Joyero musical»
        'jewel_card' => 'Joyero: título de la tarjeta',
        // «Esencia XV»
        'scent_line' => 'Esencia: nombre de la fragancia', 'scent_edition' => 'Esencia: palabra antes del año', 'scent_launch' => 'Esencia: rótulo de la fecha',
        'scent_top' => 'Esencia: primer grupo del programa', 'scent_heart' => 'Esencia: segundo grupo del programa', 'scent_base' => 'Esencia: tercer grupo del programa',
        // «Dos caminos»
        'hero_meet' => 'Portada: antes de la fecha', 'route_end' => 'Recorrido: última parada',
        // «Próxima salida»
        'board_title' => 'Panel: título', 'board_destination' => 'Panel: rótulo del destino', 'board_date' => 'Panel: rótulo de la fecha',
        'board_time' => 'Panel: rótulo de la hora', 'board_gate' => 'Panel: rótulo del lugar', 'board_status' => 'Panel: rótulo del estado',
        'board_on_time' => 'Panel: estado antes del día', 'board_today' => 'Panel: estado el mismo día',
        'pass_title' => 'Pase: título', 'pass_passenger' => 'Pase: rótulo del pasajero', 'pass_guest' => 'Pase: pasajero en el enlace general',
        // «La gota»
        'ring_label' => 'Portada: rótulo del anillo de padrinos',
        // «Álbum de stickers»
        'sticker_age' => 'Sticker: después de la edad', 'sticker_going' => 'Sticker: para confirmar',
        'sticker_missing' => 'Sticker: casilla vacía', 'sticker_time' => 'Sticker: franja de la hora',
        'sticker_place' => 'Sticker: franja del lugar', 'intro_pack' => 'Sobre: antes del nombre',
        'sticker_number' => 'Programa: antes del número de cada sticker',
        // «Expediente abierto»
        'case_label' => 'Expediente: rótulo', 'case_open' => 'Expediente: sello', 'case_lead' => 'Expediente: rótulo del nombre',
        'case_seen' => 'Expediente: rótulo de la fecha', 'case_place' => 'Expediente: rótulo del lugar', 'case_notes' => 'Expediente: rótulo del mensaje',
        'lights_on' => 'Botón para encender las luces', 'lights_off' => 'Botón para volver a la linterna',
        // «Mapa de estrellas»
        'sky_label' => 'Mapa: título del cielo', 'constellation_label' => 'Apertura: antes del nombre de la constelación',
        // «Caldero encantado»
        'potion_label' => 'Frasco: etiqueta', 'potion_ingredients' => 'Frasco: ingredientes', 'potion_step' => 'Receta: palabra antes del número',
        // «Casa de muñecas de medianoche»
        'house_welcome' => 'Casa: antes del nombre al abrirse', 'house_room' => 'Casa: palabra antes del número de cada cuarto',
        'house_date' => 'Casa: rótulo del calendario', 'house_time' => 'Casa: rótulo del reloj', 'house_place' => 'Casa: letrero del jardín',
        'house_stair' => 'Escalera: palabra antes del número de escalón',
        // «Partitura a dos voces»
        'score_program' => 'Partitura: encabezado', 'score_title' => 'Partitura: nombre de la obra', 'score_opus' => 'Partitura: número de obra',
        'score_date' => 'Partitura: rótulo de la fecha', 'score_hall' => 'Partitura: rótulo del lugar',
        // «Gira mundial»
        'tour_presents' => 'Gira: sobre el nombre', 'tour_name' => 'Gira: antes de la edad', 'tour_cities' => 'Gira: ciudades canceladas (separadas por ·)',
        'tour_cancelled' => 'Gira: estado de las ciudades', 'tour_only' => 'Gira: sello de la fiesta', 'tour_doors' => 'Gira: rótulo de la hora',
        'tour_wristband' => 'Gira: texto de la pulsera',
        // «Día feriado»
        'cal_holiday' => 'Almanaque: rótulo del feriado', 'cal_line' => 'Almanaque: antes del nombre', 'cal_note' => 'Almanaque: nota junto al día marcado',
        'cal_age' => 'Almanaque: después de la edad', 'cal_thought' => 'Almanaque: rótulo del mensaje',
        // «Cabina de fotos»
        'booth_sign' => 'Cabina: letrero', 'booth_button' => 'Cabina: texto del botón', 'booth_slot' => 'Cabina: rótulo de la ranura',
        'booth_age' => 'Cabina: después de la edad', 'booth_ready' => 'Cabina: pantallita antes de la foto',
        // «Edición especial»
        'mag_name' => 'Revista: nombre', 'mag_issue' => 'Revista: línea de la edición', 'mag_exclusive' => 'Revista: rótulo de la estrella de tapa',
        'mag_party' => 'Revista: titular de la fecha', 'mag_where' => 'Revista: titular del lugar', 'mag_style' => 'Revista: titular de la vestimenta',
        'mag_agenda' => 'Revista: titular del programa', 'mag_page' => 'Revista: palabra antes del número de página',
        'mag_contents' => 'Revista: título del índice', 'mag_letter' => 'Revista: título del mensaje', 'mag_pick' => 'Revista: sello del primer momento',
        // «Función de medianoche»
        'film_rating' => 'Película: clasificación', 'film_rating_text' => 'Película: texto de la clasificación',
        'film_premiere' => 'Película: rótulo de la fecha', 'film_show' => 'Película: rótulo de la hora', 'film_theater' => 'Película: rótulo del lugar',
        'film_intermission' => 'Película: cartel del intermedio',
        // «Tendedero»
        'parcel_title' => 'Encomienda: título de la guía', 'parcel_content' => 'Encomienda: rótulo del contenido', 'parcel_arrival' => 'Encomienda: rótulo de la fecha',
        'parcel_address' => 'Encomienda: rótulo del lugar', 'parcel_fragile' => 'Encomienda: sello de frágil', 'parcel_care' => 'Encomienda: segundo sello',
        'line_date' => 'Tendedero: etiqueta de la fecha', 'line_time' => 'Tendedero: etiqueta de la hora', 'line_place' => 'Tendedero: etiqueta del lugar',
        'act1_label' => 'Acto 1: nombre', 'act1_title' => 'Acto 1: título', 'act1_intro_fallback' => 'Acto 1: introducción si no hay texto',
        'act1_met_prefix' => 'Acto 1: antes de la fecha', 'act1_met_fallback' => 'Acto 1: si no hay fecha', 'act1_first_fallback' => 'Acto 1: primeras impresiones si no hay texto',
        'act2_label' => 'Acto 2: nombre', 'act2_title' => 'Acto 2: título', 'act2_moments_fallback' => 'Acto 2: si no hay momentos',
        'act3_label' => 'Acto 3: nombre', 'act3_title_fallback' => 'Acto 3: título si no hay', 'act3_anecdote_fallback' => 'Acto 3: anécdota si no hay',
        'act3_song_label' => 'Acto 3: rótulo de la canción', 'act3_dedication_fallback' => 'Acto 3: dedicatoria si no hay', 'act3_photos_label' => 'Acto 3: rótulo de las fotos',
        'act4_label' => 'Acto 4: nombre', 'act4_title' => 'Acto 4: título', 'act4_reflection_fallback' => 'Acto 4: reflexión si no hay',
        'act4_promise_fallback' => 'Acto 4: promesa si no hay', 'act4_together' => 'Acto 4: antes del tiempo juntos',
    ];

    /** Todas las claves que se pueden guardar: las del catálogo y las propias de cada plantilla. */
    public static function keys(): array
    {
        $catalog = array_merge(...array_values(array_map('array_keys', self::catalog())));
        $templates = array_merge(...array_values(array_map(fn (array $template) => array_keys($template['copy'] ?? []), InvitationTemplates::all())));

        return array_values(array_diff(array_unique([...$catalog, ...$templates]), self::NOT_TEXT));
    }

    /**
     * Lo que el editor necesita para una plantilla: los textos agrupados por módulo, con el valor
     * por defecto de esa plantilla (el que se ve si el campo queda vacío).
     *
     * @return array<string, list<array{key: string, label: string, default: string, long: bool}>>
     */
    public static function forTemplate(?string $template): array
    {
        $copy = InvitationTemplates::copy($template);
        $groups = [];

        foreach (self::catalog() as $module => $texts) {
            foreach ($texts as $key => $text) {
                $groups[$module][] = self::field($key, $text[0], (string) ($copy[$key] ?? $text[1]), $text[2] ?? false);
            }
        }

        // Los textos propios de la plantilla que no están en el catálogo van a General
        $known = array_merge(...array_values(array_map('array_keys', self::catalog())));

        foreach ($copy as $key => $value) {
            if (in_array($key, $known, true) || in_array($key, self::NOT_TEXT, true) || ! is_string($value)) {
                continue;
            }

            $groups[self::GENERAL][] = self::field($key, self::TEMPLATE_LABELS[$key] ?? 'Texto de la plantilla', $value, mb_strlen($value) > 70);
        }

        return $groups;
    }

    /**
     * Deja solo los textos que existen y que de verdad dicen algo; un campo vacío vuelve al de la
     * plantilla.
     *
     * @return array<string, string>
     */
    public static function sanitize(mixed $texts): array
    {
        if (! is_array($texts)) {
            return [];
        }

        $allowed = array_flip(self::keys());
        $clean = [];

        foreach ($texts as $key => $value) {
            if (! is_string($key) || ! isset($allowed[$key]) || ! is_scalar($value)) {
                continue;
            }

            $value = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');

            if ($value !== '') {
                $clean[$key] = mb_substr($value, 0, 600);
            }
        }

        return $clean;
    }

    private static function field(string $key, string $label, string $default, bool $long): array
    {
        return ['key' => $key, 'label' => $label, 'default' => $default, 'long' => $long];
    }
}
