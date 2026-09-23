<?php

namespace App\ViewModels\Client;

use App\Models\Invitation;
use App\Support\InvitationTemplates;
use App\Support\Pdf\PdfAssets;
use App\Support\Pdf\PdfMotifs;
use App\Support\Pdf\PdfTemplateStyle;
use App\Support\Pdf\PrintLayout;
use Illuminate\Support\Str;

/**
 * Datos de la invitación impresa: toma los módulos activos de la plantilla del cliente, sus
 * colores y tipografías, y prepara los adornos y los QR embebidos. Las fotos no van al papel:
 * el PDF es para imprimir y repartir, y la galería vive en la invitación digital.
 *
 * Dos hojas: en la primera va la invitación (portada, nombres, cuándo y dónde, QR) y en la
 * segunda los complementos. Para que no se pase de ahí, las listas largas se recortan y el PDF
 * avisa cuánto quedó fuera, que de todos modos está completo en la invitación digital.
 */
class InvitationPrintData
{
    /** Cuánto entra sin que la invitación se pase de dos hojas. */
    private const LIMITS = [
        'itinerario' => 8,
        'padrinos' => 6,
        'cortejo' => 10,
        'colores' => 6,
        'sugerencias' => 3,
        'evitar' => 5,
        'regalos' => 3,
        // Textos de la portada: en papel, un mensaje muy largo desarma la composición
        'mensaje' => 220,
        'rsvp' => 120,
    ];

    /** @var array<string, int> lo que se recortó para que quepa, por bloque */
    private array $omitted = [];

    public function __construct(private PdfAssets $assets) {}

    public function make(Invitation $invitation, array $modules): array
    {
        $this->omitted = [];

        $config = $this->section($modules, 'config');
        $enabled = fn (string $code): bool => (bool) data_get($config, "modulos.{$code}", false);
        $welcome = $this->section($modules, 'bienvenida');
        $fonts = (array) ($config['tipografias'] ?? []);
        $publicUrl = route('invitation.show', $invitation->slug);
        $style = PdfTemplateStyle::for($invitation->template);
        $colors = $this->colors((array) ($config['colores'] ?? []), $style['isDark']);
        $name = $this->text($welcome['nombre_quinceanera'] ?? null) ?? $invitation->title;

        $data = [
            'invitation' => $invitation,
            'colors' => $colors,
            'style' => $style,
            // Adornos de la plantilla, ya con los colores del cliente
            'motifs' => [
                'main' => PdfMotifs::get($style['motif'], $colors['primary'], $colors['soft'], $colors['paper']),
                'ink' => PdfMotifs::get($style['motif'], $colors['onPanelAccent'], $colors['onPanelSoft'], $colors['panel']),
                'seal' => PdfMotifs::seal($this->initials($name), $colors['primary'], $colors['paper']),
                'confeti' => PdfMotifs::confeti([$colors['primary'], $colors['accent'], $colors['ink'], $colors['line']]),
                'cinta' => PdfMotifs::cinta($colors['line']),
            ],
            'fonts' => [
                'titles' => $this->assets->googleFont($fonts['titulos'] ?? null),
                'script' => $this->assets->googleFont($fonts['script'] ?? null),
            ],
            'hero' => [
                'name' => $name,
                'subtitle' => $this->text($welcome['subtitulo'] ?? null) ?? $style['kicker'],
                'message' => Str::limit((string) $this->text($welcome['mensaje'] ?? null), self::LIMITS['mensaje']) ?: null,
                'date' => $this->text($welcome['fecha_texto'] ?? null)
                    ?? ($invitation->event_date ? Str::ucfirst($invitation->event_date->locale('es')->translatedFormat('l j \d\e F \d\e Y')) : null),
                'time' => $invitation->event_date?->format('H:i'),
                // Un nombre largo baja de cuerpo en vez de partirse en tres renglones
                'nameSize' => match (true) {
                    mb_strlen($name) <= 18 => '38pt',
                    mb_strlen($name) <= 30 => '30pt',
                    default => '22pt',
                },
            ],
            'location' => $enabled('ubicacion') ? $this->location($this->section($modules, 'ubicacion')) : null,
            'itinerary' => $enabled('itinerario') ? $this->itinerary($this->section($modules, 'itinerario')) : [],
            'dressCode' => $enabled('dress_code') ? $this->dressCode($this->section($modules, 'dress_code')) : null,
            'honor' => $enabled('destacados') ? $this->honor($this->section($modules, 'destacados')) : null,
            'copy' => InvitationTemplates::copy($invitation->template),
            'gifts' => $enabled('regalos') ? $this->gifts($this->section($modules, 'regalos')) : null,
            'hashtag' => $enabled('hashtag') ? $this->text(data_get($modules, 'hashtag.hashtag')) : null,
            'rsvp' => [
                'enabled' => $enabled('rsvp'),
                'title' => $enabled('rsvp')
                    ? ($this->text(data_get($modules, 'rsvp.titulo_confirmacion')) ?? 'Confirma tu asistencia')
                    : 'Invitación digital',
                'message' => $enabled('rsvp')
                    ? (Str::limit((string) $this->text(data_get($modules, 'rsvp.mensaje_personalizado')), self::LIMITS['rsvp']) ?: null)
                    : null,
                'url' => $publicUrl,
                'qr' => $this->assets->qr($publicUrl),
            ],
            'logo' => $this->assets->logo('#b8902e', '#ffffff'),
            // Lo que no entró en las dos hojas; el PDF lo dice en vez de cortar sin avisar
            'omitted' => $this->omitted,
        ];

        // El reparto de las hojas va sobre los datos ya armados: la 2 puede recortar descripciones
        $data['cover'] = PrintLayout::cover($data);
        $data['details'] = PrintLayout::make($data);

        return $data;
    }

    /** Recorta una lista al máximo que entra y anota cuántos quedaron fuera. */
    private function fit(array $items, string $block): array
    {
        $limit = self::LIMITS[$block];

        if (count($items) > $limit) {
            $this->omitted[$block] = count($items) - $limit;
        }

        return array_slice($items, 0, $limit);
    }

    /** "Sofía Valentina" -> "SV"; sirve de sello en las portadas que lo llevan. */
    private function initials(string $name): string
    {
        $letters = collect(preg_split('/[\s&]+/', Str::ascii($name)) ?: [])
            ->filter(fn (string $word) => preg_match('/^[A-Za-z]/', $word))
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->take(2)
            ->implode('');

        return $letters !== '' ? $letters : 'B';
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
            ->pipe(fn ($events) => $this->fit($events->all(), 'itinerario'));
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
                ->pipe(fn ($colors) => $this->fit($colors->all(), 'colores')),
            'suggestions' => collect($data['sugerencias'] ?? [])
                ->map(fn ($suggestion) => [
                    'for' => $this->text(data_get($suggestion, 'para')),
                    'title' => $this->text(data_get($suggestion, 'titulo')),
                    'description' => $this->text(data_get($suggestion, 'descripcion')),
                ])
                ->filter(fn (array $suggestion) => $suggestion['title'] || $suggestion['description'])
                ->values()
                ->pipe(fn ($suggestions) => $this->fit($suggestions->all(), 'sugerencias')),
            'avoid' => collect($data['evitar'] ?? [])
                ->map(fn ($item) => $this->text(is_string($item) ? $item : data_get($item, 'texto')))
                ->filter()
                ->values()
                ->pipe(fn ($items) => $this->fit($items->all(), 'evitar')),
        ];

        return array_filter($result) === [] ? null : $result;
    }

    private function honor(array $data): ?array
    {
        $names = fn (string $key) => collect($data[$key] ?? [])
            ->map(fn ($person) => $this->text(is_string($person) ? $person : data_get($person, 'nombre')))
            ->filter()
            ->values()
            ->pipe(fn ($people) => $this->fit($people->all(), 'cortejo'));

        $result = [
            'godparents' => collect($data['padrinos'] ?? [])
                ->map(fn ($godparent) => ['role' => $this->text(data_get($godparent, 'rol')), 'names' => $this->text(data_get($godparent, 'nombres'))])
                ->filter(fn (array $godparent) => $godparent['names'])
                ->values()
                ->pipe(fn ($godparents) => $this->fit($godparents->all(), 'padrinos')),
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
                ->pipe(fn ($options) => $this->fit($options->all(), 'regalos')),
        ];

        return $result['envelopes'] || $result['bank'] || $result['store'] || $result['options'] ? $result : null;
    }

    /**
     * Colores de la plantilla ajustados para que el texto se lea bien impreso sobre papel claro.
     * Además del papel, cada portada puede pintar un panel de color (el telón de la gala, la noche
     * de «Bajo la misma luna»): ahí el texto va claro, y esos tonos son los "onPanel".
     */
    private function colors(array $colors, bool $isDark = false): array
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

        // Panel de color: la noche de la plantilla si ya es oscura, o el tono más profundo que tenga
        $panel = $isDark && $this->luminance($background) < 0.25
            ? $background
            : $this->mix($this->luminance($secondary) < 0.3 ? $secondary : $ink, '#000000', 0.25);
        $onPanel = $this->contrast('#fdfaf3', $panel) >= 4.5 ? '#fdfaf3' : '#ffffff';
        $onPanelAccent = $this->contrast($primary, $panel) >= 3 ? $primary : $this->mix($primary, '#ffffff', 0.55);

        return [
            'paper' => $paper,
            'primary' => $primary,
            'ink' => $ink,
            'body' => $body,
            'accent' => $accent,
            'muted' => $this->mix($body, $paper, 0.35),
            'line' => $this->mix($primary, $paper, 0.6),
            'soft' => $this->mix($primary, $paper, 0.88),
            'tint' => $this->mix($primary, $paper, 0.94),
            'panel' => $panel,
            'onPanel' => $onPanel,
            'onPanelSoft' => $this->mix($onPanel, $panel, 0.4),
            'onPanelAccent' => $onPanelAccent,
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
