<?php

namespace App\Support\Pdf;

use App\Support\InvitationTemplates;

/**
 * Cómo se imprime cada plantilla. La invitación en PDF no es una captura: la primera hoja
 * imita la portada de la plantilla (su adorno, su marco, su composición) para que, al verla en
 * papel, se reconozca la misma invitación que está en el celular. Sin fotos: el PDF se imprime.
 *
 * Plantilla nueva:
 *  1. Si le sirve una de las portadas de abajo, basta con agregarla a STYLES (o con nada: hereda
 *     la de su tipo de evento en BY_EVENT).
 *  2. Si quiere una propia, se crea la vista en resources/views/client/exports/pdf/covers/ y se
 *     nombra aquí. EditorPanelsTest comprueba que toda plantilla tenga su portada y su adorno.
 */
final class PdfTemplateStyle
{
    /** Valores de una plantilla que no declara nada: sobria, con rombo y marco doble. */
    private const DEFAULTS = [
        'cover' => 'clasica',
        'motif' => 'diamante',
        'frame' => 'double',
        'paper' => 'light',
        'kicker' => 'Te esperamos',
    ];

    /** Portada propia de cada plantilla del catálogo. */
    private static function styles(): array
    {
        return [
            InvitationTemplates::XV_PREMIUM => [
                'cover' => 'gala',
                'motif' => 'destellos',
                'kicker' => 'Mis XV años',
            ],
            InvitationTemplates::BODA_JARDIN => [
                'cover' => 'jardin',
                'motif' => 'ramas',
                'frame' => 'thin',
                'kicker' => 'Nos casamos',
            ],
            InvitationTemplates::BAUTIZO_CIELO => [
                'cover' => 'nubes',
                'motif' => 'nubes',
                'frame' => 'none',
                'kicker' => 'Mi bautizo',
            ],
            InvitationTemplates::CUMPLE_FIESTA => [
                'cover' => 'fiesta',
                'motif' => 'banderines',
                'frame' => 'block',
                'kicker' => '¡Celebremos!',
            ],
            InvitationTemplates::TARJETA_AMOR => [
                'cover' => 'carta',
                'motif' => 'flor',
                'frame' => 'thin',
                'kicker' => 'Para ti',
            ],
            InvitationTemplates::TARJETA_AVENTURA => [
                'cover' => 'cuaderno',
                'motif' => 'brujula',
                'frame' => 'dashed',
                'kicker' => 'Nuestro libro de aventuras',
            ],
            InvitationTemplates::WE_STORY_TOGETHER => [
                'cover' => 'luna',
                'motif' => 'luna',
                'frame' => 'none',
                'paper' => 'dark',
                'kicker' => 'Nuestra historia',
            ],
        ];
    }

    /** Portada que hereda una plantilla nueva según su tipo de evento. */
    private const BY_EVENT = [
        'xv' => 'gala',
        'boda' => 'jardin',
        'bautizo' => 'nubes',
        'cumple' => 'fiesta',
        'amor' => 'carta',
        'aventura' => 'cuaderno',
        'historia' => 'luna',
    ];

    /**
     * @return array{cover: string, view: string, motif: string, frame: string, paper: string,
     *     kicker: string, label: string, tagline: string, isDark: bool}
     */
    public static function for(?string $template): array
    {
        $meta = InvitationTemplates::get($template);
        $style = self::styles()[self::key($template)] ?? self::inherited($meta['event']);
        $style = [...self::DEFAULTS, ...$style];

        return [
            ...$style,
            'view' => 'client.exports.pdf.covers.'.$style['cover'],
            'label' => $meta['label'],
            'tagline' => $meta['tagline'],
            'isDark' => $style['paper'] === 'dark',
        ];
    }

    /** Lo que hereda una plantilla sin portada propia: la de otra plantilla de su mismo evento. */
    private static function inherited(string $event): array
    {
        $cover = self::BY_EVENT[$event] ?? self::DEFAULTS['cover'];

        foreach (self::styles() as $template => $style) {
            if (($style['cover'] ?? null) === $cover) {
                // La portada y el adorno se heredan; el rótulo no: es de cada plantilla
                return [
                    'cover' => $cover,
                    'motif' => $style['motif'],
                    'frame' => $style['frame'] ?? self::DEFAULTS['frame'],
                    'paper' => $style['paper'] ?? 'light',
                ];
            }
        }

        return ['cover' => $cover];
    }

    /** Nombres antiguos con prefijo "pages." (ver InvitationDefaults::resolveTemplate). */
    private static function key(?string $template): string
    {
        $template = (string) $template;

        return str_starts_with($template, 'pages.') ? substr($template, strlen('pages.')) : $template;
    }
}
