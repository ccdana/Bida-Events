<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * De dónde viene cada cliente.
 *
 * Los enlaces de campaña traen utm_source, utm_medium y utm_campaign (o ?ref= en material
 * impreso y códigos QR). El middleware CaptureLeadSource los guarda 30 días en una cookie,
 * y cada botón de WhatsApp agrega un código corto al mensaje, por ejemplo «Ref. BODA-FB-MAYO»:
 * página donde escribió, fuente y campaña. Así se sabe el origen al responder, sin guardar nada.
 */
final class LeadSource
{
    public const COOKIE = 'bida_origen';

    public const DAYS = 30;

    /** Código de la portada; las páginas por evento usan el suyo (config bida.landings.*.code). */
    public const HOME = 'WEB';

    /**
     * Origen que trae la visita actual en la URL, o null si llegó sin parámetros de campaña.
     *
     * @return array{source: string, medium: string, campaign: string}|null
     */
    public static function fromQuery(Request $request): ?array
    {
        $source = self::clean($request->query('utm_source') ?? $request->query('ref'));
        $campaign = self::clean($request->query('utm_campaign'));

        if ($source === '' && $campaign === '') {
            return null;
        }

        return [
            'source' => $source,
            'medium' => self::clean($request->query('utm_medium')),
            'campaign' => $campaign,
        ];
    }

    /**
     * El origen de esta visita o, si no trae parámetros, el que se recordó en una visita anterior.
     *
     * @return array{source: string, medium: string, campaign: string}|null
     */
    public static function current(Request $request): ?array
    {
        if ($fromQuery = self::fromQuery($request)) {
            return $fromQuery;
        }

        $stored = json_decode((string) $request->cookie(self::COOKIE), true);

        if (! is_array($stored)) {
            return null;
        }

        return [
            'source' => self::clean($stored['source'] ?? null),
            'medium' => self::clean($stored['medium'] ?? null),
            'campaign' => self::clean($stored['campaign'] ?? null),
        ];
    }

    /** Código corto: página, fuente abreviada y campaña. Sin campaña queda solo la página. */
    public static function code(Request $request, string $page): string
    {
        $origin = self::current($request);

        $parts = [
            strtoupper(self::clean($page)) ?: self::HOME,
            $origin ? self::abbreviate($origin['source']) : '',
            $origin ? strtoupper(Str::limit(str_replace('-', '', $origin['campaign']), 10, '')) : '',
        ];

        return implode('-', array_filter($parts));
    }

    /** Enlace a WhatsApp con el mensaje prellenado y el código de origen al final. */
    public static function whatsappUrl(Request $request, string $message, string $page): string
    {
        $number = preg_replace('/\D+/', '', (string) config('bida.whatsapp'));
        $text = str_replace('{brand}', (string) config('bida.brand'), $message)."\n\nRef. ".self::code($request, $page);

        return "https://wa.me/{$number}?text=".rawurlencode($text);
    }

    /** facebook → FB; las que no están en config quedan en mayúsculas, hasta 10 letras (feria-cbba → FERIACBBA). */
    private static function abbreviate(string $source): string
    {
        if ($source === '') {
            return '';
        }

        return config("bida.lead_sources.{$source}") ?? strtoupper(substr(str_replace('-', '', $source), 0, 10));
    }

    /** Solo minúsculas, números y guiones: el valor viaja a una cookie y a un mensaje. */
    private static function clean(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return Str::limit(trim(preg_replace('/[^a-z0-9]+/', '-', Str::lower(Str::ascii($value))), '-'), 40, '');
    }
}
