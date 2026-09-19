<?php

namespace App\Support;

/**
 * Paletas listas que ofrece el editor en «Estética» (config/palettes.php).
 *
 * Cada paleta dice para qué tipos de evento se pensó («events»). La primera de la lista es la
 * paleta original de cada plantilla, tomada de InvitationTemplates: así el cliente siempre puede
 * volver a los colores con los que se diseñó la invitación que eligió.
 */
final class ColorPalettes
{
    public const ROLES = ['primary', 'secondary', 'accent', 'text', 'background'];

    /**
     * Todas las paletas para el editor, en orden: las originales de cada plantilla, las de cada
     * tipo de evento y las que sirven para cualquiera.
     *
     * @return list<array{name: string, description: string, mode: string, events: list<string>, template: ?string, colors: array<string, string>}>
     */
    public static function all(): array
    {
        $palettes = [];

        foreach (InvitationTemplates::all() as $view => $template) {
            $palettes[] = self::make(
                'Original · '.$template['label'],
                'Los colores con los que se diseñó esta plantilla',
                $template['palette'],
                [$template['event']],
                $view,
            );
        }

        foreach (config('palettes', []) as $palette) {
            $palettes[] = self::make(
                $palette['name'],
                $palette['description'] ?? '',
                $palette['colors'],
                $palette['events'] ?? [],
                null,
                $palette['mode'] ?? null,
            );
        }

        return $palettes;
    }

    /**
     * @param  array<string, string>  $colors
     * @param  list<string>  $events
     * @return array{name: string, description: string, mode: string, events: list<string>, template: ?string, colors: array<string, string>}
     */
    private static function make(string $name, string $description, array $colors, array $events, ?string $template = null, ?string $mode = null): array
    {
        $colors = array_intersect_key($colors, array_flip(self::ROLES));

        return [
            'name' => $name,
            'description' => $description,
            'mode' => $mode ?? (self::isDark($colors['background'] ?? '#FFFFFF') ? 'night' : 'light'),
            'events' => $events,
            'template' => $template,
            'colors' => $colors,
        ];
    }

    private static function isDark(string $hex): bool
    {
        return ColorContrast::luminance($hex) < 0.18;
    }
}
