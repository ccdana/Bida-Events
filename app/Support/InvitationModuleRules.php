<?php

namespace App\Support;

use App\Modules\ModuleRegistry;
use Closure;
use Illuminate\Support\Facades\Validator;
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

    /** Los medios deben llegar ya subidos (Cloudinary); una URL blob:/data: significa que la subida no se completó. */
    public static function urlRules(): array
    {
        return ['nullable', 'string', 'max:2048', 'not_regex:/^\s*(blob|data):/i'];
    }

    public static function rules(string $prefix): array
    {
        $p = $prefix;
        $url = self::urlRules();
        $rules = [$p => ['array']];

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $rules["{$p}.{$code}"] = ['nullable', 'array'];
        }

        return $rules + app(ModuleRegistry::class)->rules($p) + [
            "{$p}.config.template" => ['nullable', 'string', 'max:255'],
            "{$p}.config.colores" => ['nullable', 'array'],
            "{$p}.config.colores.*" => ['nullable', 'string', 'max:50'],
            "{$p}.config.tipografias" => ['nullable', 'array'],
            "{$p}.config.tipografias.*" => ['nullable', 'string', 'max:100'],
            "{$p}.config.modulos" => ['nullable', 'array'],
            "{$p}.config.modulos.*" => ['boolean'],
            // Textos propios de la invitación que reemplazan los de la plantilla (App\Support\EditableTexts)
            "{$p}.config.textos" => ['nullable', 'array', 'max:200'],
            "{$p}.config.textos.*" => ['nullable', 'string', 'max:600'],

            "{$p}.bienvenida.imagen_hero" => $url,
            "{$p}.ubicacion.imagen_lugar" => $url,
            "{$p}.video.video_url" => $url,
            "{$p}.video.poster" => $url,
            "{$p}.musica.audio_url" => $url,
            // WhatsApp de las confirmaciones del paquete Estándar: dígitos con código de país (se limpian al guardar)
            "{$p}.rsvp_whatsapp.whatsapp" => ['nullable', 'string', 'max:30', 'regex:/^[\d\s+()-]*$/'],

            "{$p}.itinerario.eventos" => ['nullable', 'array', 'max:50'],
            "{$p}.itinerario.eventos.*" => ['array'],
            "{$p}.itinerario.eventos.*.hora" => ['nullable', 'string', 'max:50'],
            "{$p}.itinerario.eventos.*.titulo" => ['nullable', 'string', 'max:255'],
            "{$p}.itinerario.eventos.*.icono" => ['nullable', 'string', 'max:50'],
            "{$p}.itinerario.eventos.*.descripcion" => ['nullable', 'string', 'max:2000'],

            "{$p}.galeria.fotos" => ['nullable', 'array', 'max:100'],
            "{$p}.galeria.fotos.*" => self::photoEntry($url),
            "{$p}.galeria.fotos.*.alt" => ['nullable', 'string', 'max:255'],
            "{$p}.post_evento.fotos" => ['nullable', 'array', 'max:200'],
            "{$p}.post_evento.fotos.*" => self::photoEntry($url),
            "{$p}.post_evento.fotos.*.alt" => ['nullable', 'string', 'max:255'],

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
            "{$p}.dress_code.sugerencias.*.imagen" => $url,
            "{$p}.destacados.chambelanes" => ['nullable', 'array', 'max:60'],
            "{$p}.destacados.damitas" => ['nullable', 'array', 'max:60'],
            "{$p}.destacados.padrinos" => ['nullable', 'array', 'max:60'],
        ];
    }

    /**
     * Una foto puede llegar como URL suelta o como {url, alt}: en ambos casos se valida la URL,
     * para que una subida a medias (blob:) no entre por la puerta del objeto.
     */
    public static function photoEntry(array $urlRules): array
    {
        return ['nullable', function (string $attribute, mixed $value, Closure $fail) use ($urlRules) {
            $url = is_array($value) ? ($value['url'] ?? null) : $value;

            if ($url === null || $url === '') {
                return;
            }

            $validator = Validator::make(['url' => $url], ['url' => $urlRules]);

            if ($validator->fails()) {
                $fail('La foto no tiene una dirección válida.');
            }
        }];
    }

    public static function attributes(string $prefix): array
    {
        $p = $prefix;
        $attributes = [];

        foreach (InvitationDefaults::moduleCodes() as $code) {
            $attributes["{$p}.{$code}"] = "módulo {$code}";
        }

        return $attributes + app(ModuleRegistry::class)->attributes($p) + [
            "{$p}.config.colores.*" => 'color',
            "{$p}.config.tipografias.*" => 'tipografía',
            "{$p}.config.modulos.*" => 'visibilidad de módulo',
            "{$p}.config.textos.*" => 'texto',
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
            "{$p}.galeria.fotos.*.alt" => 'descripción de la foto #:position',
            "{$p}.post_evento.fotos" => 'fotos post evento',
            "{$p}.post_evento.fotos.*" => 'foto #:position post evento',
            "{$p}.post_evento.fotos.*.alt" => 'descripción de la foto #:position post evento',
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
