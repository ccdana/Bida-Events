<?php

namespace App\Support;

use App\Models\Guest;
use App\Models\Invitation;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * Datos comunes de cualquier plantilla pública: paleta, fuentes, módulos visibles,
 * menú, orden de secciones y textos propios de la plantilla.
 */
final class InvitationPage
{
    // Después del evento solo quedan los módulos de recuerdos
    private const POST_EVENT_MODULES = ['musica', 'fotomural', 'galeria', 'hashtag', 'post_evento'];

    private const POST_EVENT_ORDER = ['post_evento', 'fotomural', 'galeria', 'hashtag'];

    // Pesos publicados en Google Fonts: pedir uno inexistente invalida toda la hoja de estilos
    private const FONT_WEIGHTS = [
        'Playfair Display' => '400;600;700', 'Cormorant Garamond' => '400;600;700', 'Cinzel' => '400;600;700',
        'Libre Baskerville' => '400;700', 'Bodoni Moda' => '400;600;700', 'Lora' => '400;500;600;700',
        'Merriweather' => '300;400;700', 'Montserrat' => '300;400;500;600;700', 'Inter' => '300;400;500;600;700',
        'Lato' => '300;400;700', 'Nunito Sans' => '300;400;600;700', 'Source Sans 3' => '300;400;600;700',
        'Poppins' => '300;400;500;600;700', 'Raleway' => '300;400;500;600;700', 'Open Sans' => '300;400;600;700',
        'Dancing Script' => '400;700', 'Tangerine' => '400;700', 'Fredoka' => '400;500;600;700',
    ];

    private const NAV_LABELS = [
        'cuenta_regresiva' => 'Cuenta regresiva',
        'video' => 'Video',
        'galeria' => 'Galería',
        'itinerario' => 'Itinerario',
        'dress_code' => 'Dress code',
        'destacados' => 'Cortejo',
        'ubicacion' => 'Ubicación',
        'hashtag' => 'Hashtag',
        'encuestas' => 'Encuestas',
        'playlist' => 'Playlist',
        'regalos' => 'Regalos',
        'rsvp' => 'Confirmar asistencia',
        'fotomural' => 'Fotomural',
        'post_evento' => 'Fotos oficiales',
    ];

    public readonly array $config;

    /** Tipo de evento de la plantilla (xv, boda, bautizo, cumple) */
    public readonly string $eventKey;

    /** Nombre del lugar, para la vista previa al compartir */
    public readonly ?string $placeName;

    public readonly array $colors;

    public readonly array $fonts;

    public readonly array $flags;

    public readonly array $welcome;

    public readonly array $music;

    public readonly array $copy;

    public readonly array $order;

    public readonly bool $isPostEvent;

    public readonly bool $hasPlayer;

    public readonly ?string $heroImage;

    public readonly string $guestToken;

    public readonly string $displayName;

    public readonly CarbonInterface $eventDate;

    public readonly string $eventLabel;

    public function __construct(
        public readonly Invitation $invitation,
        array $modules,
        public readonly ?Guest $guest,
        string $template,
    ) {
        $meta = InvitationTemplates::get($template);

        $this->config = (array) ($modules['config'] ?? []);
        $this->colors = array_merge(
            ['primary' => '#C9A96E', 'secondary' => '#2C1810', 'accent' => '#F5E6D3', 'text' => '#1A1A1A', 'background' => '#FFFAF5'],
            array_filter((array) ($this->config['colores'] ?? [])),
        );
        $this->fonts = array_merge(
            ['titulos' => 'Playfair Display', 'cuerpo' => 'Montserrat', 'script' => 'Great Vibes'],
            array_filter((array) ($this->config['tipografias'] ?? [])),
        );
        $this->flags = (array) ($this->config['modulos'] ?? []);
        $this->welcome = (array) ($modules['bienvenida'] ?? []);
        $this->music = (array) ($modules['musica'] ?? []);
        $this->copy = $meta['copy'];
        $this->isPostEvent = (bool) $invitation->is_post_event;
        // Después del evento los recuerdos pasan al frente, sin importar el orden de la plantilla
        $this->order = $this->isPostEvent
            ? array_values(array_unique([...self::POST_EVENT_ORDER, ...$meta['order']]))
            : $meta['order'];
        $this->guestToken = $guest?->qr_code_token ?? '';
        $this->heroImage = ($this->welcome['imagen_hero'] ?? null) ?: null;
        $this->hasPlayer = $this->visible('musica') && ! empty($this->music['audio_url'] ?? null);
        $this->eventDate = $invitation->event_date->copy()->timezone(config('app.timezone'));
        $this->eventLabel = Str::ucfirst($this->eventDate->locale('es')->translatedFormat('l j \d\e F · H:i \h'));
        $this->displayName = ($this->welcome['nombre_quinceanera'] ?? null) ?: $invitation->title;
        $this->eventKey = $meta['event'] ?? 'xv';
        $this->placeName = trim((string) ($modules['ubicacion']['nombre_lugar'] ?? '')) ?: null;
    }

    /** Título, descripción e imagen que se ven al compartir el enlace (Open Graph). */
    public function share(string $url, bool $personal = true): array
    {
        return ShareMeta::forInvitation($this, $url, $personal);
    }

    public function visible(string $module): bool
    {
        if ($this->isPostEvent && ! in_array($module, self::POST_EVENT_MODULES, true)) {
            return false;
        }

        return (bool) ($this->flags[$module] ?? false);
    }

    public function fontQuery(): string
    {
        return collect([$this->fonts['titulos'], $this->fonts['cuerpo'], $this->fonts['script']])
            ->filter()
            ->unique()
            ->map(fn (string $family) => 'family='.urlencode($family).(isset(self::FONT_WEIGHTS[$family]) ? ':wght@'.self::FONT_WEIGHTS[$family] : ''))
            ->implode('&');
    }

    /** Enlaces del menú en el mismo orden en que aparecen las secciones. */
    public function navItems(): array
    {
        $items = [['id' => 'inicio', 'label' => 'Inicio']];

        if ($this->visible('rsvp') && $this->guest) {
            $items[] = ['id' => 'guest-banner', 'label' => 'Tu invitación'];
        }

        foreach ($this->order as $module) {
            if (! $this->visible($module)
                || ($module === 'rsvp' && ! $this->guest)
                || ($module === 'post_evento' && ! $this->isPostEvent)) {
                continue;
            }

            $label = $module === 'destacados'
                ? ($this->copy['nav_court'] ?? self::NAV_LABELS['destacados'])
                : (self::NAV_LABELS[$module] ?? Str::headline($module));

            $items[] = ['id' => str_replace('_', '-', $module), 'label' => $label];
        }

        return $items;
    }

    /** Separa "Ana & Luis" o "Ana y Luis" en dos nombres; si no hay pareja devuelve uno solo. */
    public function names(): array
    {
        $parts = preg_split('/\s+(?:&|\+|y|e)\s+/iu', trim($this->displayName), 2) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), fn (string $part) => $part !== ''));

        return $parts ?: [$this->displayName];
    }

    /** Edad escrita en el subtítulo ("Mis 30 años" → 30); null si no trae un número. */
    public function age(): ?int
    {
        return preg_match('/\b(\d{1,3})\b/u', (string) ($this->welcome['subtitulo'] ?? ''), $match) ? (int) $match[1] : null;
    }

    /** Iniciales para monogramas y sellos, p. ej. "A & L". */
    public function initials(): string
    {
        return collect($this->names())
            ->map(fn (string $name) => Str::upper(Str::substr($name, 0, 1)))
            ->implode(' & ');
    }
}
