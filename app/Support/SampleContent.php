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
        $samples = self::forEditor();
        array_walk_recursive($samples, function (mixed $value) use (&$values) {
            if (is_string($value) && self::mustBeReplaced($value)) {
                $values[$value] = true;
            }
        });

        return array_keys($values);
    }

    public static function mustBeReplaced(string $value): bool
    {
        return str_starts_with($value, '/images/ejemplo/')
            || str_starts_with($value, 'Escribe aquí')
            || str_starts_with($value, 'Escribe algo')
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
                'subtitulo' => (string) ($profile->sample()['subtitle'] ?? 'Te invito a celebrar'),
                'mensaje' => 'Escribe aquí unas palabras para tus invitados: por qué es un día especial y cuánto te alegra compartirlo.',
                'imagen_hero' => self::PHOTO_VERTICAL,
            ],
            'ubicacion' => [
                'nombre_lugar' => 'Nombre del lugar',
                'direccion' => 'Calle, número y zona',
                'nota' => 'Escribe aquí cómo llegar o dónde estacionar.',
                'lat' => -16.5,
                'lng' => -68.15,
            ],
            'itinerario' => [
                'titulo' => 'Itinerario',
                'eventos' => [
                    ['hora' => '19:00', 'titulo' => 'Recepción', 'icono' => 'recepcion', 'descripcion' => 'Escribe aquí qué pasa en este momento.'],
                    ['hora' => '20:00', 'titulo' => 'Brindis', 'icono' => 'brindis', 'descripcion' => ''],
                    ['hora' => '21:00', 'titulo' => 'Cena', 'icono' => 'cena', 'descripcion' => ''],
                    ['hora' => '22:00', 'titulo' => 'Fiesta', 'icono' => 'fiesta', 'descripcion' => ''],
                ],
            ],
            'dress_code' => [
                'titulo' => 'Código de vestimenta',
                'estilo' => 'Formal',
                'descripcion' => 'Escribe aquí cómo te gustaría que vengan vestidos tus invitados.',
                'sugerencias' => [
                    ['para' => 'Damas', 'titulo' => 'Vestido largo o de cóctel', 'descripcion' => '', 'ejemplos' => []],
                    ['para' => 'Caballeros', 'titulo' => 'Traje y corbata', 'descripcion' => '', 'ejemplos' => []],
                ],
                'colores_permitidos' => [],
                'evitar' => [],
            ],
            'destacados' => [
                'padrinos' => [
                    ['rol' => $groups['padrinos'][0] ?? 'Padrinos', 'nombres' => 'Nombre y nombre', 'mensaje' => 'Escribe aquí unas palabras para ellos.'],
                ],
                'chambelanes' => [
                    ['nombre' => $singular('chambelanes', 'Invitado').' 1', 'iniciales' => '1', 'detalle' => 'Escribe algo sobre esta persona.'],
                    ['nombre' => $singular('chambelanes', 'Invitado').' 2', 'iniciales' => '2', 'detalle' => ''],
                ],
                'damitas' => [
                    ['nombre' => $singular('damitas', 'Invitada').' 1', 'iniciales' => '1', 'detalle' => 'Escribe algo sobre esta persona.'],
                    ['nombre' => $singular('damitas', 'Invitada').' 2', 'iniciales' => '2', 'detalle' => ''],
                ],
            ],
            'galeria' => [
                'titulo' => 'Nuestros momentos',
                'fotos' => array_slice(self::PHOTOS_SQUARE, 0, 4),
            ],
            'musica' => [
                'titulo' => 'Nuestra canción',
                'artista' => 'Elige la canción de fondo',
                'autoplay' => false,
            ],
            'video' => [
                'titulo' => 'Un video para ti',
                'poster' => self::PHOTO_HORIZONTAL,
            ],
            'playlist' => [
                'titulo' => 'Sugiere una canción',
                'descripcion' => '¿Qué canción no puede faltar en la fiesta? Escríbela aquí.',
                'placeholder' => 'Nombre de la canción o del artista',
            ],
            'hashtag' => [
                'hashtag' => '#NuestraFiesta',
                'plataforma' => 'instagram',
                'texto_boton' => 'Comparte tus fotos',
            ],
            'encuestas' => [
                'titulo' => 'Queremos saber',
                'preguntas' => [
                    ['id' => 'ejemplo-fiesta', 'tipo' => 'single', 'pregunta' => '¿Qué no puede faltar en la fiesta?', 'opciones' => ['Baile', 'Karaoke', 'Hora loca']],
                ],
            ],
            'regalos' => [
                'titulo' => 'Regalos',
                'tienda_url' => '',
                'tienda_texto' => '',
                'opciones' => [
                    ['titulo' => 'Tu presencia es nuestro mejor regalo', 'descripcion' => 'Escribe aquí cómo pueden hacerte un regalo si lo desean.', 'enlace' => ''],
                ],
                'sobres' => ['titulo' => '', 'direccion' => ''],
                'banco' => ['banco' => '', 'titular' => '', 'ci' => '', 'cuenta' => '', 'qr_url' => ''],
            ],
            'post_evento' => [
                'titulo' => 'Fotos de la fiesta',
                'descripcion' => 'Después del evento subiremos aquí las fotos oficiales.',
                'fotos' => [],
                'enlace_externo' => '',
            ],
            'rsvp' => [
                'titulo_confirmacion' => 'Confirma tu asistencia',
                'mensaje_personalizado' => 'Escribe aquí hasta cuándo pueden confirmar.',
                'texto_confirmado' => '¡Gracias por confirmar! Te esperamos.',
                'texto_declinado' => 'Gracias por avisarnos. Te vamos a extrañar.',
            ],
            // Sin un número de WhatsApp no hay a quién mandar la respuesta: el editor lo pide al encenderla
            'rsvp_whatsapp' => [],
            'dedicatoria' => [
                'de' => 'Tu nombre',
                'para' => 'Su nombre',
                'mensaje' => 'Escribe aquí lo que quieres decirle: un recuerdo, un deseo o lo que sientes.',
                'firma' => 'Con cariño',
            ],
            'juntos_desde' => [
                'titulo' => 'Llevamos juntos',
                'fecha' => now()->subYear()->toDateString(),
            ],
            'respuesta' => [
                'titulo' => 'Respóndeme',
                'descripcion' => 'Escríbeme unas palabras: me llegan a mí.',
            ],
            'historia' => [
                'titulo' => 'Nuestra historia',
                'capitulos' => [
                    ['titulo' => 'Cómo nos conocimos', 'fecha' => now()->subYears(2)->toDateString(), 'texto' => 'Escribe aquí cómo empezó todo.', 'foto' => self::PHOTOS_SQUARE[0], 'alt' => ''],
                    ['titulo' => 'Nuestro primer viaje', 'fecha' => now()->subYear()->toDateString(), 'texto' => 'Escribe aquí un recuerdo que no olvidan.', 'foto' => self::PHOTOS_SQUARE[1], 'alt' => ''],
                ],
            ],
            'recuerdos' => [
                'titulo' => 'Recuerdos especiales',
                'recuerdos' => [
                    ['titulo' => 'Un recuerdo', 'fecha' => '', 'texto' => 'Escribe aquí por qué es especial.', 'foto' => self::PHOTOS_SQUARE[2], 'alt' => ''],
                    ['titulo' => 'Otro recuerdo', 'fecha' => '', 'texto' => 'Escribe aquí por qué es especial.', 'foto' => self::PHOTOS_SQUARE[3], 'alt' => ''],
                ],
            ],
            'collage' => [
                'titulo' => 'Nuestro collage',
                'fotos' => self::PHOTOS_SQUARE,
            ],
            'marcos' => [
                'titulo' => 'Enmarcados para siempre',
                'fotos' => array_map(fn (string $url) => ['url' => $url, 'alt' => ''], array_slice(self::PHOTOS_SQUARE, 0, 3)),
            ],
            'memoria' => [
                'titulo' => 'Encuentra los pares',
                'mensaje_final' => '¡Los encontraste todos!',
                'fotos' => self::PHOTOS_SQUARE,
            ],
            'aventuras' => [
                'titulo' => 'Aventuras por vivir',
                'lista' => [
                    ['titulo' => 'Escribe aquí algo que quieren hacer juntos'],
                    ['titulo' => 'Un viaje pendiente'],
                    ['titulo' => 'Un lugar por conocer'],
                ],
            ],
            'relato' => [
                'primeras_impresiones' => 'Escribe aquí qué pensaron el uno del otro la primera vez.',
                'momentos' => [
                    ['cuando' => 'El primer día', 'titulo' => 'Cómo empezó', 'descripcion' => 'Escribe aquí ese momento.', 'foto' => self::PHOTOS_SQUARE[0]],
                    ['cuando' => 'Después', 'titulo' => 'Un momento clave', 'descripcion' => 'Escribe aquí ese momento.', 'foto' => self::PHOTOS_SQUARE[1]],
                ],
                'anecdota_titulo' => 'Una anécdota',
                'anecdota' => 'Escribe aquí una historia que siempre cuentan.',
                'reflexion' => 'Escribe aquí lo que aprendieron juntos.',
                'promesa' => 'Escribe aquí una promesa.',
            ],
            default => null,
        };
    }
}
