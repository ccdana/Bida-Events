<?php

namespace App\ViewModels\Client;

use App\Models\Invitation;
use App\Support\Pdf\PdfAssets;
use Illuminate\Support\Str;

/**
 * Datos de la invitación impresa: toma los módulos activos de la plantilla del
 * cliente, sus colores y tipografías, y prepara la foto y los QR embebidos.
 */
class InvitationPrintData
{
    public function __construct(private PdfAssets $assets) {}

    public function make(Invitation $invitation, array $modules): array
    {
        $config = $this->section($modules, 'config');
        $enabled = fn (string $code): bool => (bool) data_get($config, "modulos.{$code}", false);
        $welcome = $this->section($modules, 'bienvenida');
        $fonts = (array) ($config['tipografias'] ?? []);
        $publicUrl = route('invitation.show', $invitation->slug);
        $colors = $this->colors((array) ($config['colores'] ?? []));

        return [
            'invitation' => $invitation,
            'colors' => $colors,
            'fonts' => [
                'titles' => $this->assets->googleFont($fonts['titulos'] ?? null),
                'script' => $this->assets->googleFont($fonts['script'] ?? null),
            ],
            'hero' => [
                'name' => $this->text($welcome['nombre_quinceanera'] ?? null) ?? $invitation->title,
                'subtitle' => $this->text($welcome['subtitulo'] ?? null),
                'message' => $this->text($welcome['mensaje'] ?? null),
                'date' => $this->text($welcome['fecha_texto'] ?? null)
                    ?? ($invitation->event_date ? Str::ucfirst($invitation->event_date->locale('es')->translatedFormat('l j \d\e F \d\e Y')) : null),
                'time' => $invitation->event_date?->format('H:i'),
                'photo' => $this->assets->photo($welcome['imagen_hero'] ?? null, 1440, 800, 0.3),
            ],
            'location' => $enabled('ubicacion') ? $this->location($this->section($modules, 'ubicacion')) : null,
            'itinerary' => $enabled('itinerario') ? $this->itinerary($this->section($modules, 'itinerario')) : [],
            'dressCode' => $enabled('dress_code') ? $this->dressCode($this->section($modules, 'dress_code')) : null,
            'honor' => $enabled('destacados') ? $this->honor($this->section($modules, 'destacados')) : null,
            'copy' => \App\Support\InvitationTemplates::copy($invitation->template),
            'gifts' => $enabled('regalos') ? $this->gifts($this->section($modules, 'regalos')) : null,
            'hashtag' => $enabled('hashtag') ? $this->text(data_get($modules, 'hashtag.hashtag')) : null,
            'rsvp' => [
                'enabled' => $enabled('rsvp'),
                'title' => $enabled('rsvp')
                    ? ($this->text(data_get($modules, 'rsvp.titulo_confirmacion')) ?? 'Confirma tu asistencia')
                    : 'Invitación digital',
                'message' => $enabled('rsvp') ? $this->text(data_get($modules, 'rsvp.mensaje_personalizado')) : null,
                'url' => $publicUrl,
                'qr' => $this->assets->qr($publicUrl),
            ],
            'logo' => $this->assets->logo('#b8902e', '#ffffff'),
        ];
    }

    private function location(array $data): ?array
    {
        $name = $this->text($data['nombre_lugar'] ?? null);
        $address = $this->text($data['direccion'] ?? null);

        if (! $name && ! $address) {
            return null;
        }

        $mapsUrl = $this->text($data['maps_url'] ?? null);

        if (! $mapsUrl && is_numeric($data['lat'] ?? null) && is_numeric($data['lng'] ?? null)) {
            $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$data['lat']},{$data['lng']}";
        }

        return [
            'name' => $name,
            'address' => $address,
            'note' => $this->text($data['nota'] ?? null),
            'qr' => $this->assets->qr($mapsUrl),
        ];
    }

    private function itinerary(array $data): array
    {
        return collect($data['eventos'] ?? [])
            ->map(fn ($event) => [
                'time' => $this->text(data_get($event, 'hora')),
                'title' => $this->text(data_get($event, 'titulo')),
                'description' => $this->text(data_get($event, 'descripcion')),
            ])
            ->filter(fn (array $event) => $event['time'] || $event['title'])
            ->values()
            ->all();
    }

    private function dressCode(array $data): ?array
    {
        $result = [
            'style' => $this->text($data['estilo'] ?? null),
            'description' => $this->text($data['descripcion'] ?? null),
            'colors' => collect($data['colores_permitidos'] ?? [])
                ->filter(fn ($color) => preg_match('/^#[0-9a-f]{3,8}$/i', (string) data_get($color, 'hex')))
                ->map(fn ($color) => ['name' => $this->text(data_get($color, 'nombre')), 'hex' => data_get($color, 'hex')])
                ->values()
                ->all(),
            'suggestions' => collect($data['sugerencias'] ?? [])
                ->map(fn ($suggestion) => [
                    'for' => $this->text(data_get($suggestion, 'para')),
                    'title' => $this->text(data_get($suggestion, 'titulo')),
                    'description' => $this->text(data_get($suggestion, 'descripcion')),
                ])
                ->filter(fn (array $suggestion) => $suggestion['title'] || $suggestion['description'])
                ->values()
                ->all(),
            'avoid' => collect($data['evitar'] ?? [])
                ->map(fn ($item) => $this->text(is_string($item) ? $item : data_get($item, 'texto')))
                ->filter()
                ->values()
                ->all(),
        ];

        return array_filter($result) === [] ? null : $result;
    }

    private function honor(array $data): ?array
    {
        $names = fn (string $key) => collect($data[$key] ?? [])
            ->map(fn ($person) => $this->text(is_string($person) ? $person : data_get($person, 'nombre')))
            ->filter()
            ->values()
            ->all();

        $result = [
            'godparents' => collect($data['padrinos'] ?? [])
                ->map(fn ($godparent) => ['role' => $this->text(data_get($godparent, 'rol')), 'names' => $this->text(data_get($godparent, 'nombres'))])
                ->filter(fn (array $godparent) => $godparent['names'])
                ->values()
                ->all(),
            'chambelanes' => $names('chambelanes'),
            'damitas' => $names('damitas'),
        ];

        return array_filter($result) === [] ? null : $result;
    }

    private function gifts(array $data): ?array
    {
        $bank = (array) ($data['banco'] ?? []);
        $bankRows = array_filter([
            'Banco' => $this->text($bank['banco'] ?? null),
            'Titular' => $this->text($bank['titular'] ?? null),
            'CI' => $this->text($bank['ci'] ?? null),
            'Cuenta' => $this->text($bank['cuenta'] ?? null),
        ]);
        $envelopes = (array) ($data['sobres'] ?? []);
        $storeUrl = $this->text($data['tienda_url'] ?? null);

        $result = [
            'title' => $this->text($data['titulo'] ?? null) ?? 'Regalos',
            'envelopes' => $this->text($envelopes['direccion'] ?? null)
                ? ['title' => $this->text($envelopes['titulo'] ?? null) ?? 'Lluvia de sobres', 'address' => $this->text($envelopes['direccion'])]
                : null,
            'bank' => $bankRows ?: null,
            'bankQr' => $bankRows ? $this->assets->photo($bank['qr_url'] ?? null, 600, 600, 0.5, contain: true) : null,
            'store' => $storeUrl ? ['label' => $this->text($data['tienda_texto'] ?? null) ?? 'Lista de regalos', 'url' => $storeUrl] : null,
            'options' => collect($data['opciones'] ?? [])
                ->map(fn ($option) => ['title' => $this->text(data_get($option, 'titulo')), 'description' => $this->text(data_get($option, 'descripcion'))])
                ->filter(fn (array $option) => $option['title'])
                ->values()
                ->all(),
        ];

        return $result['envelopes'] || $result['bank'] || $result['store'] || $result['options'] ? $result : null;
    }

    /** Colores de la plantilla ajustados para que el texto se lea bien impreso sobre papel claro. */
    private function colors(array $colors): array
    {
        $hex = fn ($value, string $fallback) => is_string($value) && preg_match('/^#[0-9a-f]{6}$/i', $value) ? strtolower($value) : $fallback;

        $primary = $hex($colors['primary'] ?? null, '#c9a96e');
        $secondary = $hex($colors['secondary'] ?? null, '#2c1810');
        $text = $hex($colors['text'] ?? null, '#1a1a1a');
        $background = $hex($colors['background'] ?? null, '#fffaf5');

        $paper = $this->luminance($background) > 0.85 ? $background : '#ffffff';
        $body = $this->contrast($text, $paper) >= 4.5 ? $text : '#2b2b2b';
        $ink = $this->contrast($secondary, $paper) >= 4.5 ? $secondary : $body;
        $accent = $this->contrast($primary, $paper) >= 3 ? $primary : $this->mix($primary, '#000000', 0.4);

        return [
            'paper' => $paper,
            'primary' => $primary,
            'ink' => $ink,
            'body' => $body,
            'accent' => $accent,
            'muted' => $this->mix($body, $paper, 0.35),
            'line' => $this->mix($primary, $paper, 0.6),
            'soft' => $this->mix($primary, $paper, 0.88),
        ];
    }

    private function section(array $modules, string $code): array
    {
        $value = $modules[$code] ?? [];

        return is_array($value) ? $value : json_decode(json_encode($value), true) ?? [];
    }

    private function text(mixed $value): ?string
    {
        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }

    private function rgb(string $hex): array
    {
        return array_map(fn (string $pair) => hexdec($pair), str_split(ltrim($hex, '#'), 2));
    }

    private function luminance(string $hex): float
    {
        [$red, $green, $blue] = array_map(function (int $channel) {
            $value = $channel / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $this->rgb($hex));

        return 0.2126 * $red + 0.7152 * $green + 0.0722 * $blue;
    }

    private function contrast(string $first, string $second): float
    {
        $values = [$this->luminance($first), $this->luminance($second)];

        return (max($values) + 0.05) / (min($values) + 0.05);
    }

    /** Mezcla $from con $to; $amount es la proporción de $to (0 a 1). */
    private function mix(string $from, string $to, float $amount): string
    {
        $mixed = array_map(
            fn (int $start, int $end) => (int) round($start + ($end - $start) * $amount),
            $this->rgb($from),
            $this->rgb($to),
        );

        return sprintf('#%02x%02x%02x', ...$mixed);
    }
}
