<?php

namespace App\Support;

use App\Models\Invitation;

/**
 * Invitaciones de muestra que el visitante puede probar sin que nada se guarde.
 * Las usan la portada (una por plantilla) y cada página por tipo de evento.
 */
final class ShowcaseDemos
{
    private const EVENTS = [
        'xv' => ['XV años', 'crown-simple'],
        'boda' => ['Boda', 'heart'],
        'bautizo' => ['Bautizo', 'baby'],
        'cumple' => ['Cumpleaños', 'cake'],
        'amor' => ['Día del Amor', 'heart'],
        'aventura' => ['Día del Amor', 'book-open'],
        'historia' => ['Día del Amor', 'moon-stars'],
    ];

    /**
     * Invitaciones que se pueden abrir como muestra (/muestra/{slug}): las de la portada, las de
     * la temporada y las que enseña cada página por evento.
     *
     * @return list<string>
     */
    public static function allowedSlugs(): array
    {
        return array_values(array_unique(array_filter([
            ...config('bida.demo_invitations', []),
            ...(config('bida.season.templates') ?? []),
            ...array_merge(...array_values(array_map(fn (array $landing) => $landing['demos'] ?? [], config('bida.landings', [])))),
        ])));
    }

    /**
     * Muestras activas en el orden recibido; las que no existen se omiten.
     *
     * @param  list<string>  $slugs
     */
    public static function find(array $slugs): array
    {
        if (! $slugs) {
            return [];
        }

        // Si la base de datos no responde, las páginas se muestran sin teléfono en lugar de fallar
        $invitations = rescue(
            fn () => Invitation::whereIn('slug', $slugs)->where('status', 'active')->get(['slug', 'title', 'template'])->keyBy('slug'),
            collect(),
            report: false,
        );

        // Las plantillas apagadas desde Ajustes dejan de ofrecerse en el sitio
        $disabled = (array) config('bida.templates_disabled', []);

        return collect($slugs)
            ->map(function (string $slug) use ($invitations, $disabled): ?array {
                $invitation = $invitations->get($slug);
                $template = $invitation ? (InvitationTemplates::all()[$invitation->template] ?? null) : null;

                if (! $template || in_array($invitation->template, $disabled, true)) {
                    return null;
                }

                [$event, $icon] = self::EVENTS[$template['event']] ?? ['Evento', 'sparkle'];

                return [
                    // Muestra interactiva (nada se guarda) y la misma con la apertura que se abre sola
                    'demoUrl' => route('invitation.demo', $slug),
                    'coverUrl' => route('invitation.demo', ['slug' => $slug, 'portada' => 1]),
                    'title' => $invitation->title,
                    'label' => $template['label'],
                    'description' => $template['description'],
                    // Una línea para las listas de diseños (la temporada de la portada)
                    'tagline' => $template['tagline'] ?? null,
                    'event' => $event,
                    'eventKey' => $template['event'],
                    'icon' => $icon,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }
}
