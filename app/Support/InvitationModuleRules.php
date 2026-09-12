<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Esquema de validación de los módulos que envía el editor (cada módulo llega como JSON).
 * Solo restringe estructura y tamaños; las claves sin regla se aceptan para no romper plantillas.
 */
class InvitationModuleRules
{
    public const POLL_TYPES = ['single', 'rating', 'yesno', 'emoji'];

    /**
     * Decodifica modulos[codigo]; un JSON inválido se conserva como texto para que falle la regla "array".
     */
    public static function decode(mixed $input): array
    {
        $input = is_array($input) ? $input : [];
        $decoded = [];

        foreach (InvitationDefaults::moduleCodes() as $code) {
            if (! array_key_exists($code, $input)) {
                continue;
            }

            $raw = $input[$code];

            if (! is_string($raw)) {
                $decoded[$code] = $raw;
                continue;
            }

            $value = json_decode($raw, true);
            $decoded[$code] = json_last_error() === JSON_ERROR_NONE ? $value : $raw;
        }

        return $decoded;
    }

    public static function rules(string $prefix): array
    {
        $p = $prefix;
        $url = ['nullable', 'string', 'max:2048'];
        $rules = [$p => ['array']];

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $rules["{$p}.{$code}"] = ['nullable', 'array'];
        }

        return $rules + [
            "{$p}.config.template" => ['nullable', 'string', 'max:255'],
            "{$p}.config.colores" => ['nullable', 'array'],
            "{$p}.config.colores.*" => ['nullable', 'string', 'max:50'],
            "{$p}.config.tipografias" => ['nullable', 'array'],
            "{$p}.config.tipografias.*" => ['nullable', 'string', 'max:100'],
            "{$p}.config.modulos" => ['nullable', 'array'],
            "{$p}.config.modulos.*" => ['boolean'],

            "{$p}.bienvenida.imagen_hero" => $url,
            "{$p}.ubicacion.imagen_lugar" => $url,
            "{$p}.video.video_url" => $url,
            "{$p}.video.poster" => $url,
            "{$p}.musica.audio_url" => $url,

            "{$p}.itinerario.eventos" => ['nullable', 'array', 'max:50'],
            "{$p}.itinerario.eventos.*" => ['array'],
            "{$p}.itinerario.eventos.*.hora" => ['nullable', 'string', 'max:50'],
            "{$p}.itinerario.eventos.*.titulo" => ['nullable', 'string', 'max:255'],
            "{$p}.itinerario.eventos.*.icono" => ['nullable', 'string', 'max:50'],
            "{$p}.itinerario.eventos.*.descripcion" => ['nullable', 'string', 'max:2000'],

            "{$p}.galeria.fotos" => ['nullable', 'array', 'max:100'],
            "{$p}.galeria.fotos.*" => $url,
            "{$p}.post_evento.fotos" => ['nullable', 'array', 'max:200'],
            "{$p}.post_evento.fotos.*" => $url,

            "{$p}.encuestas.preguntas" => ['nullable', 'array', 'max:30'],
            "{$p}.encuestas.preguntas.*" => ['array'],
            "{$p}.encuestas.preguntas.*.id" => ['nullable', 'string', 'max:100', 'distinct'],
            "{$p}.encuestas.preguntas.*.tipo" => ['nullable', Rule::in(self::POLL_TYPES)],
            "{$p}.encuestas.preguntas.*.pregunta" => ['nullable', 'string', 'max:500'],
            "{$p}.encuestas.preguntas.*.opciones" => ['nullable', 'array', 'max:20'],
            "{$p}.encuestas.preguntas.*.opciones.*" => ['nullable', 'string', 'max:255'],

            "{$p}.regalos.tienda_url" => $url,
            "{$p}.regalos.banco.qr_url" => $url,
            "{$p}.regalos.opciones" => ['nullable', 'array', 'max:20'],
            "{$p}.regalos.opciones.*" => ['array'],
            "{$p}.regalos.opciones.*.enlace" => $url,

            "{$p}.dress_code.sugerencias" => ['nullable', 'array', 'max:30'],
            "{$p}.destacados.chambelanes" => ['nullable', 'array', 'max:60'],
            "{$p}.destacados.damitas" => ['nullable', 'array', 'max:60'],
            "{$p}.destacados.padrinos" => ['nullable', 'array', 'max:60'],
        ];
    }

    public static function attributes(string $prefix): array
    {
        $p = $prefix;
        $attributes = [];

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $attributes["{$p}.{$code}"] = "módulo {$code}";
        }

        return $attributes + [
            "{$p}.config.colores.*" => 'color',
            "{$p}.config.tipografias.*" => 'tipografía',
            "{$p}.config.modulos.*" => 'visibilidad de módulo',
            "{$p}.bienvenida.imagen_hero" => 'imagen principal',
            "{$p}.ubicacion.imagen_lugar" => 'imagen del lugar',
            "{$p}.video.video_url" => 'URL del video',
            "{$p}.video.poster" => 'póster del video',
            "{$p}.musica.audio_url" => 'URL del audio',
            "{$p}.itinerario.eventos" => 'itinerario',
            "{$p}.itinerario.eventos.*.hora" => 'hora del momento #:position del itinerario',
            "{$p}.itinerario.eventos.*.titulo" => 'título del momento #:position del itinerario',
            "{$p}.itinerario.eventos.*.icono" => 'icono del momento #:position del itinerario',
            "{$p}.itinerario.eventos.*.descripcion" => 'descripción del momento #:position del itinerario',
            "{$p}.galeria.fotos" => 'galería',
            "{$p}.galeria.fotos.*" => 'foto #:position de la galería',
            "{$p}.post_evento.fotos" => 'fotos post evento',
            "{$p}.post_evento.fotos.*" => 'foto #:position post evento',
            "{$p}.encuestas.preguntas" => 'encuestas',
            "{$p}.encuestas.preguntas.*.id" => 'identificador de la encuesta #:position',
            "{$p}.encuestas.preguntas.*.tipo" => 'tipo de la encuesta #:position',
            "{$p}.encuestas.preguntas.*.pregunta" => 'pregunta de la encuesta #:position',
            "{$p}.encuestas.preguntas.*.opciones" => 'opciones de la encuesta #:position',
            "{$p}.encuestas.preguntas.*.opciones.*" => 'opción #:second-position de la encuesta #:position',
            "{$p}.regalos.tienda_url" => 'enlace de la tienda',
            "{$p}.regalos.banco.qr_url" => 'QR bancario',
            "{$p}.regalos.opciones" => 'opciones de regalo',
            "{$p}.regalos.opciones.*.enlace" => 'enlace de la opción de regalo #:position',
            "{$p}.dress_code.sugerencias" => 'sugerencias de vestimenta',
            "{$p}.destacados.chambelanes" => 'chambelanes',
            "{$p}.destacados.damitas" => 'damitas',
            "{$p}.destacados.padrinos" => 'padrinos',
        ];
    }
}
