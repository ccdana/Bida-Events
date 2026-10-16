<?php

namespace App\Support;

use App\EventProfiles\EventProfile;
use App\EventProfiles\EventProfiles;
use App\Modules\ModuleRegistry;

/**
 * Contenido de ejemplo con el que nace cada parte de una invitación: al crearla y al encender un
 * módulo en el editor, la parte aparece en la vista previa con textos y fotos genéricos para que
 * el cliente los toque y los cambie («tocar y cambiar», admin/invitations/editor).
 *
 * Lo de ejemplo se guarda como contenido real. El editor lo compara con esto para avisar antes de
 * publicar qué textos o fotos siguen sin cambiar, así que cada texto debe ser reconocible como
 * ejemplo («Escribe aquí…», «Nombre del lugar») y las fotos son siempre las de public/images/ejemplo.
 *
 * Un módulo nuevo suma aquí su ejemplo: tests/Feature/SampleContentTest falla mientras no lo tenga.
 */
final class SampleContent
{
    /** Fotos de ejemplo (public/images/ejemplo): dicen «Tu foto aquí» para que se note que falta la propia. */
    public const PHOTO_VERTICAL = '/images/ejemplo/foto-vertical.svg';

    public const PHOTO_HORIZONTAL = '/images/ejemplo/foto-horizontal.svg';

    /** Seis distintas: el juego de memoria necesita pares que se puedan diferenciar. */
    public const PHOTOS_SQUARE = [
        '/images/ejemplo/foto-1.svg',
        '/images/ejemplo/foto-2.svg',
        '/images/ejemplo/foto-3.svg',
        '/images/ejemplo/foto-4.svg',
        '/images/ejemplo/foto-5.svg',
        '/images/ejemplo/foto-6.svg',
    ];

    /** Módulos que solo se encienden o apagan: no tienen nada que escribir. */
    private const TOGGLES = ['cuenta_regresiva', 'agendar', 'fotomural'];

    /**
     * Ejemplo de cada perfil de evento, con la forma de cada módulo que ese evento ofrece.
     *
     * @return array<string, array<string, mixed>> código del perfil → módulo → forma
     */
    public static function forEditor(): array
    {
        return collect(app(EventProfiles::class)->all())
            ->mapWithKeys(fn (EventProfile $profile) => [$profile->code() => self::forProfile($profile)])
            ->all();
    }

    /** @return array<string, mixed> módulo → forma, solo los módulos del perfil */
    public static function forProfile(EventProfile $profile): array
    {
        $samples = [];

        foreach ($profile->modules() as $code) {
            $sample = self::module($code, $profile);
            if ($sample !== null) {
                $samples[$code] = $sample;
            }
        }

        return $samples;
    }

    /** Textos de ejemplo que no sirven tal cual (los demás, como «Recepción» o «Itinerario», pueden quedarse). */
    private const TO_REPLACE = ['Nombre del lugar', 'Calle, número y zona', 'Tu nombre', 'Su nombre', 'Nombre y nombre', 'Elige la canción de fondo'];

    /**
     * Lo de ejemplo que hay que cambiar antes de publicar: las fotos de ejemplo, los «Escribe aquí…»
     * y los nombres genéricos. El editor los busca en la invitación y avisa al publicar.
     *
     * @return list<string>
     */
    public static function placeholders(): array
    {
        $values = [];

        foreach (Localization::locales() as $locale) {
            Localization::using($locale, function () use (&$values) {
                $samples = self::forEditor();
                array_walk_recursive($samples, function (mixed $value) use (&$values) {
                    if (is_string($value) && self::mustBeReplaced($value)) {
                        $values[$value] = true;
                    }
                });
            });
        }

        return array_keys($values);
    }

    public static function mustBeReplaced(string $value): bool
    {
        return str_starts_with($value, '/images/ejemplo/')
            || str_starts_with($value, __('Escribe aquí'))
            || str_starts_with($value, __('Escribe algo'))
            || in_array($value, self::TO_REPLACE, true)
            // Los invitados de honor numerados («Chambelán 1», «Dama 2»)
            || (bool) preg_match('/^\p{L}[\p{L} ]* [0-9]$/u', $value);
    }

    /** Si un módulo registrado tiene ejemplo (los que solo se encienden no lo necesitan). */
    public static function covers(string $code): bool
    {
        return in_array($code, self::TOGGLES, true)
            || self::module($code, app(EventProfiles::class)->all()['xv'] ?? array_values(app(EventProfiles::class)->all())[0]) !== null;
    }

    /** @return list<string> Módulos registrados que todavía no tienen ejemplo. */
    public static function missing(): array
    {
        return array_values(array_filter(
            array_keys(app(ModuleRegistry::class)->all()),
            fn (string $code) => $code !== 'config' && ! self::covers($code),
        ));
    }

    public static function module(string $code, EventProfile $profile): ?array
    {
        $groups = $profile->featuredGroups();
        $singular = fn (string $group, string $fallback) => $groups[$group][1] ?? $fallback;

        return match ($code) {
            'bienvenida' => [
                // Los nombres los pide el asistente al crear; aquí solo lo que acompaña
                'subtitulo' => (string) ($profile->sample()['subtitle'] ?? __('Te invito a celebrar')),
                'mensaje' => __('Escribe aquí unas palabras para tus invitados: por qué es un día especial y cuánto te alegra compartirlo.'),
                'imagen_hero' => self::PHOTO_VERTICAL,
            ],
            'ubicacion' => [
                'nombre_lugar' => __('Nombre del lugar'),
                'direccion' => __('Calle, número y zona'),
                'nota' => __('Escribe aquí cómo llegar o dónde estacionar.'),
                'lat' => -16.5,
                'lng' => -68.15,
            ],
            'itinerario' => [
                'titulo' => __('Itinerario'),
                'eventos' => [
                    ['hora' => '19:00', 'titulo' => __('Recepción'), 'icono' => 'recepcion', 'descripcion' => __('Escribe aquí qué pasa en este momento.')],
                    ['hora' => '20:00', 'titulo' => __('Brindis'), 'icono' => 'brindis', 'descripcion' => ''],
                    ['hora' => '21:00', 'titulo' => __('Cena'), 'icono' => 'cena', 'descripcion' => ''],
                    ['hora' => '22:00', 'titulo' => __('Fiesta'), 'icono' => 'fiesta', 'descripcion' => ''],
                ],
            ],
            'dress_code' => [
                'titulo' => __('Código de vestimenta'),
                'estilo' => __('Formal'),
                'descripcion' => __('Escribe aquí cómo te gustaría que vengan vestidos tus invitados.'),
                'sugerencias' => [
                    ['para' => __('Damas'), 'titulo' => __('Vestido largo o de cóctel'), 'descripcion' => '', 'ejemplos' => []],
                    ['para' => __('Caballeros'), 'titulo' => __('Traje y corbata'), 'descripcion' => '', 'ejemplos' => []],
                ],
                'colores_permitidos' => [],
                'evitar' => [],
            ],
            'destacados' => [
                'padrinos' => [
                    ['rol' => $groups['padrinos'][0] ?? __('Padrinos'), 'nombres' => __('Nombre y nombre'), 'mensaje' => __('Escribe aquí unas palabras para ellos.')],
                ],
                'chambelanes' => [
                    ['nombre' => $singular('chambelanes', __('Invitado')).' 1', 'iniciales' => '1', 'detalle' => __('Escribe algo sobre esta persona.')],
                    ['nombre' => $singular('chambelanes', __('Invitado')).' 2', 'iniciales' => '2', 'detalle' => ''],
                ],
                'damitas' => [
                    ['nombre' => $singular('damitas', __('Invitada')).' 1', 'iniciales' => '1', 'detalle' => __('Escribe algo sobre esta persona.')],
                    ['nombre' => $singular('damitas', __('Invitada')).' 2', 'iniciales' => '2', 'detalle' => ''],
                ],
            ],
            'galeria' => [
                'titulo' => __('Nuestros momentos'),
                'fotos' => array_slice(self::PHOTOS_SQUARE, 0, 4),
            ],
            'musica' => [
                'titulo' => __('Nuestra canción'),
                'artista' => __('Elige la canción de fondo'),
                'autoplay' => false,
            ],
            'video' => [
                'titulo' => __('Un video para ti'),
                'poster' => self::PHOTO_HORIZONTAL,
            ],
            'playlist' => [
                'titulo' => __('Sugiere una canción'),
                'descripcion' => __('¿Qué canción no puede faltar en la fiesta? Escríbela aquí.'),
                'placeholder' => __('Nombre de la canción o del artista'),
            ],
            'hashtag' => [
                'hashtag' => '#NuestraFiesta',
                'plataforma' => 'instagram',
                'texto_boton' => __('Comparte tus fotos'),
            ],
            'encuestas' => [
                'titulo' => __('Queremos saber'),
                'preguntas' => [
                    ['id' => 'ejemplo-fiesta', 'tipo' => 'single', 'pregunta' => __('¿Qué no puede faltar en la fiesta?'), 'opciones' => [__('Baile'), __('Karaoke'), __('Hora loca')]],
                ],
            ],
            'regalos' => [
                'titulo' => __('Regalos'),
                'tienda_url' => '',
                'tienda_texto' => '',
                'opciones' => [
                    ['titulo' => __('Tu presencia es nuestro mejor regalo'), 'descripcion' => __('Escribe aquí cómo pueden hacerte un regalo si lo desean.'), 'enlace' => ''],
                ],
                'sobres' => ['titulo' => '', 'direccion' => ''],
                'banco' => ['banco' => '', 'titular' => '', 'ci' => '', 'cuenta' => '', 'qr_url' => ''],
            ],
            'post_evento' => [
                'titulo' => __('Fotos de la fiesta'),
                'descripcion' => __('Después del evento subiremos aquí las fotos oficiales.'),
                'fotos' => [],
                'enlace_externo' => '',
            ],
            'rsvp' => [
                'titulo_confirmacion' => __('Confirma tu asistencia'),
                'mensaje_personalizado' => __('Escribe aquí hasta cuándo pueden confirmar.'),
                'texto_confirmado' => __('¡Gracias por confirmar! Te esperamos.'),
                'texto_declinado' => __('Gracias por avisarnos. Te vamos a extrañar.'),
            ],
            // Sin un número de WhatsApp no hay a quién mandar la respuesta: el editor lo pide al encenderla
            'rsvp_whatsapp' => [],
            'dedicatoria' => [
                'de' => __('Tu nombre'),
                'para' => __('Su nombre'),
                'mensaje' => __('Escribe aquí lo que quieres decirle: un recuerdo, un deseo o lo que sientes.'),
                'firma' => __('Con cariño'),
            ],
            'juntos_desde' => [
                'titulo' => __('Llevamos juntos'),
                'fecha' => now()->subYear()->toDateString(),
            ],
            'respuesta' => [
                'titulo' => __('Respóndeme'),
                'descripcion' => __('Escríbeme unas palabras: me llegan a mí.'),
            ],
            'historia' => [
                'titulo' => __('Nuestra historia'),
                'capitulos' => [
                    ['titulo' => __('Cómo nos conocimos'), 'fecha' => now()->subYears(2)->toDateString(), 'texto' => __('Escribe aquí cómo empezó todo.'), 'foto' => self::PHOTOS_SQUARE[0], 'alt' => ''],
                    ['titulo' => __('Nuestro primer viaje'), 'fecha' => now()->subYear()->toDateString(), 'texto' => __('Escribe aquí un recuerdo que no olvidan.'), 'foto' => self::PHOTOS_SQUARE[1], 'alt' => ''],
                ],
            ],
            'recuerdos' => [
                'titulo' => __('Recuerdos especiales'),
                'recuerdos' => [
                    ['titulo' => __('Un recuerdo'), 'fecha' => '', 'texto' => __('Escribe aquí por qué es especial.'), 'foto' => self::PHOTOS_SQUARE[2], 'alt' => ''],
                    ['titulo' => __('Otro recuerdo'), 'fecha' => '', 'texto' => __('Escribe aquí por qué es especial.'), 'foto' => self::PHOTOS_SQUARE[3], 'alt' => ''],
                ],
            ],
            'collage' => [
                'titulo' => __('Nuestro collage'),
                'fotos' => self::PHOTOS_SQUARE,
            ],
            'marcos' => [
                'titulo' => __('Enmarcados para siempre'),
                'fotos' => array_map(fn (string $url) => ['url' => $url, 'alt' => ''], array_slice(self::PHOTOS_SQUARE, 0, 3)),
            ],
            'memoria' => [
                'titulo' => __('Encuentra los pares'),
                'mensaje_final' => __('¡Los encontraste todos!'),
                'fotos' => self::PHOTOS_SQUARE,
            ],
            'aventuras' => [
                'titulo' => __('Aventuras por vivir'),
                'lista' => [
                    ['titulo' => __('Escribe aquí algo que quieren hacer juntos')],
                    ['titulo' => __('Un viaje pendiente')],
                    ['titulo' => __('Un lugar por conocer')],
                ],
            ],
            'relato' => [
                'primeras_impresiones' => __('Escribe aquí qué pensaron el uno del otro la primera vez.'),
                'momentos' => [
                    ['cuando' => __('El primer día'), 'titulo' => __('Cómo empezó'), 'descripcion' => __('Escribe aquí ese momento.'), 'foto' => self::PHOTOS_SQUARE[0]],
                    ['cuando' => __('Después'), 'titulo' => __('Un momento clave'), 'descripcion' => __('Escribe aquí ese momento.'), 'foto' => self::PHOTOS_SQUARE[1]],
                ],
                'anecdota_titulo' => __('Una anécdota'),
                'anecdota' => __('Escribe aquí una historia que siempre cuentan.'),
                'reflexion' => __('Escribe aquí lo que aprendieron juntos.'),
                'promesa' => __('Escribe aquí una promesa.'),
            ],
            default => null,
        };
    }
}
