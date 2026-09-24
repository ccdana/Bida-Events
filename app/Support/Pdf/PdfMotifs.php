<?php

namespace App\Support\Pdf;

/**
 * Adornos de los PDF, en SVG embebido (DomPDF los dibuja nítidos a cualquier tamaño).
 *
 * Cada adorno es lo que hace reconocible a una plantilla impresa: los destellos de «Noche de
 * gala», las ramas del jardín, las nubes del bautizo… Todos reciben los colores de la invitación,
 * así que el mismo adorno acompaña la paleta que eligió el cliente.
 *
 * Plantilla nueva: se le asigna uno de estos nombres en PdfTemplateStyle, o se agrega otro aquí.
 */
final class PdfMotifs
{
    /** Devuelve el adorno como data URI listo para <img src="…">. */
    public static function get(string $name, string $color, string $soft, string $paper = '#ffffff'): string
    {
        $svg = match ($name) {
            'destellos' => self::destellos($color, $soft),
            'ramas' => self::ramas($color, $soft),
            'nubes' => self::nubes($color, $soft),
            'banderines' => self::banderines($color, $soft),
            'flor' => self::flor($color, $soft),
            'brujula' => self::brujula($color, $soft),
            'luna' => self::luna($color, $soft, $paper),
            'birretes' => self::birretes($color, $soft),
            'murcielagos' => self::murcielagos($color, $soft),
            'linea' => self::linea($color),
            default => self::diamante($color, $soft),
        };

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /** Sello redondo con iniciales: lacre de la boda, flor de la tarjeta, medalla del bautizo. */
    public static function seal(string $initials, string $color, string $paper): string
    {
        $initials = htmlspecialchars(mb_strtoupper(mb_substr(trim($initials), 0, 3)), ENT_QUOTES);
        $petals = '';

        for ($angle = 0; $angle < 360; $angle += 30) {
            $x = 60 + 46 * cos(deg2rad($angle));
            $y = 60 + 46 * sin(deg2rad($angle));
            $petals .= sprintf('<circle cx="%.1f" cy="%.1f" r="5" fill="%s"/>', $x, $y, $color);
        }

        return self::uri(<<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120" width="120" height="120">
        {$petals}
        <circle cx="60" cy="60" r="40" fill="{$color}"/>
        <circle cx="60" cy="60" r="34" fill="none" stroke="{$paper}" stroke-width="1.5"/>
        <text x="60" y="68" text-anchor="middle" font-family="serif" font-size="24" fill="{$paper}">{$initials}</text>
        </svg>
        SVG);
    }

    /** Un rectángulo de color, para franjas y fondos que DomPDF no puede degradar. */
    public static function band(string $from, string $to): string
    {
        return self::uri(<<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 10" width="100" height="10" preserveAspectRatio="none">
        <defs><linearGradient id="b" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0" stop-color="{$from}"/><stop offset="1" stop-color="{$to}"/>
        </linearGradient></defs>
        <rect width="100" height="10" fill="url(#b)"/>
        </svg>
        SVG);
    }

    /** Confeti disperso para el cumpleaños: siempre el mismo (semilla fija) para que no baile. */
    public static function confeti(array $colors): string
    {
        mt_srand(2026);
        $pieces = '';

        for ($i = 0; $i < 46; $i++) {
            $x = mt_rand(0, 3000) / 10;
            $y = mt_rand(0, 1000) / 10;
            $color = $colors[$i % count($colors)];
            $pieces .= mt_rand(0, 1) === 0
                ? sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="%s"/>', $x, $y, mt_rand(8, 16) / 10, $color)
                : sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s"/>', $x, $y, mt_rand(15, 30) / 10, mt_rand(8, 14) / 10, $color);
        }

        mt_srand();

        return self::uri('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 100" width="300" height="100" preserveAspectRatio="none">'.$pieces.'</svg>');
    }

    /** Cinta adhesiva del cuaderno de recortes. */
    public static function cinta(string $color): string
    {
        return self::uri(<<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 40" width="120" height="40">
        <path d="M4 12 L112 4 L116 28 L8 36 Z" fill="{$color}"/>
        </svg>
        SVG);
    }

    // ── Los adornos ──────────────────────────────────────────────────────────

    /** Destellos art déco: dos rayos finos y rombos, como el telón de «Noche de gala». */
    private static function destellos(string $color, string $soft): string
    {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">
        <path d="M6 20 H78" stroke="{$color}" stroke-width="1"/>
        <path d="M162 20 H234" stroke="{$color}" stroke-width="1"/>
        <path d="M120 2 L128 20 L120 38 L112 20 Z" fill="{$color}"/>
        <path d="M96 10 L103 20 L96 30 L89 20 Z" fill="{$soft}"/>
        <path d="M144 10 L151 20 L144 30 L137 20 Z" fill="{$soft}"/>
        <path d="M84 20 L88 20 M152 20 L156 20" stroke="{$color}" stroke-width="2"/>
        </svg>
        SVG;
    }

    /** Ramas con hojas que se abren desde el centro, como el arco del jardín. */
    private static function ramas(string $color, string $soft): string
    {
        $branch = function (float $direction) use ($color, $soft): string {
            $path = sprintf('<path d="M120 26 C %.0f 26, %.0f 14, %.0f 10" fill="none" stroke="%s" stroke-width="1.2"/>',
                120 + $direction * 34, 120 + $direction * 72, 120 + $direction * 112, $color);

            for ($i = 1; $i <= 6; $i++) {
                $x = 120 + $direction * (14 + $i * 15);
                $y = 26 - $i * 2.6;
                $tipX = $x + $direction * 11;
                $path .= sprintf('<path d="M%.1f %.1f Q %.1f %.1f %.1f %.1f Q %.1f %.1f %.1f %.1f Z" fill="%s"/>',
                    $x, $y, $x + $direction * 5, $y - 9, $tipX, $y - 5, $x + $direction * 6, $y + 2, $x, $y,
                    $i % 2 === 0 ? $soft : $color);
            }

            return $path;
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .$branch(-1).$branch(1)
            .'<circle cx="120" cy="24" r="5" fill="'.$color.'"/>'
            .'<circle cx="120" cy="24" r="2" fill="'.$soft.'"/>'
            .'</svg>';
    }

    /** Nubes con una paloma: el cielo del bautizo. */
    private static function nubes(string $color, string $soft): string
    {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 60" width="240" height="60">
        <path d="M0 58 C 18 58, 20 44, 38 44 C 48 30, 74 32, 80 46 C 96 44, 104 58, 120 58 Z" fill="{$soft}"/>
        <path d="M120 58 C 140 58, 142 42, 162 44 C 172 30, 196 34, 200 46 C 216 46, 222 58, 240 58 Z" fill="{$soft}"/>
        <path d="M120 10 C 130 10, 138 16, 142 24 C 134 22, 128 24, 124 30 C 120 22, 112 18, 104 18 C 110 12, 114 10, 120 10 Z" fill="{$color}"/>
        <path d="M124 30 C 128 36, 134 38, 140 36" fill="none" stroke="{$color}" stroke-width="1.4"/>
        <circle cx="60" cy="18" r="1.8" fill="{$color}"/>
        <circle cx="186" cy="16" r="1.8" fill="{$color}"/>
        <circle cx="30" cy="28" r="1.2" fill="{$color}"/>
        <circle cx="212" cy="28" r="1.2" fill="{$color}"/>
        </svg>
        SVG;
    }

    /** Banderines colgados de un hilo, para el cumpleaños. */
    private static function banderines(string $color, string $soft): string
    {
        $flags = '';

        for ($i = 0; $i < 9; $i++) {
            $x = 12 + $i * 27;
            // El hilo cuelga: los banderines del centro bajan un poco más
            $drop = 10 + 8 * sin(deg2rad(($i / 8) * 180));
            $flags .= sprintf('<path d="M%.1f %.1f L%.1f %.1f L%.1f %.1f Z" fill="%s"/>',
                $x, $drop, $x + 20, $drop + 1.5, $x + 10, $drop + 24, $i % 2 === 0 ? $color : $soft);
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .'<path d="M2 8 Q 120 26 238 8" fill="none" stroke="'.$color.'" stroke-width="1.2"/>'
            .$flags
            .'</svg>';
    }

    /** Flor de ocho pétalos: el sello de la carta que florece. */
    private static function flor(string $color, string $soft): string
    {
        $petals = '';

        for ($angle = 0; $angle < 360; $angle += 45) {
            $radians = deg2rad($angle);
            $x = 120 + 13 * cos($radians);
            $y = 20 + 13 * sin($radians);
            $tipX = 120 + 26 * cos($radians);
            $tipY = 20 + 26 * sin($radians);
            $petals .= sprintf(
                '<path d="M120 20 Q %.1f %.1f %.1f %.1f Q %.1f %.1f 120 20 Z" fill="%s"/>',
                $x + 9 * cos($radians + 1.6), $y + 9 * sin($radians + 1.6), $tipX, $tipY,
                $x + 9 * cos($radians - 1.6), $y + 9 * sin($radians - 1.6),
                $color
            );
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .'<path d="M8 20 H84" stroke="'.$soft.'" stroke-width="1.2"/>'
            .'<path d="M156 20 H232" stroke="'.$soft.'" stroke-width="1.2"/>'
            .$petals
            .'<circle cx="120" cy="20" r="6" fill="'.$soft.'"/>'
            .'</svg>';
    }

    /** Rosa de los vientos del libro de aventuras. */
    private static function brujula(string $color, string $soft): string
    {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">
        <path d="M10 20 H86" stroke="{$soft}" stroke-width="1.2" stroke-dasharray="4 3"/>
        <path d="M154 20 H230" stroke="{$soft}" stroke-width="1.2" stroke-dasharray="4 3"/>
        <circle cx="120" cy="20" r="17" fill="none" stroke="{$color}" stroke-width="1.4"/>
        <circle cx="120" cy="20" r="12" fill="none" stroke="{$soft}" stroke-width="0.8"/>
        <path d="M120 3 L125 20 L120 37 L115 20 Z" fill="{$color}"/>
        <path d="M103 20 L120 15 L137 20 L120 25 Z" fill="{$soft}"/>
        <circle cx="120" cy="20" r="2.4" fill="{$color}"/>
        </svg>
        SVG;
    }

    /**
     * Luna creciente entre estrellas: «Bajo la misma luna». La media luna se dibuja con un
     * círculo y otro encima del color del fondo: los recortes con fill-rule no son fiables
     * en el SVG que entiende DomPDF.
     */
    private static function luna(string $color, string $soft, string $paper): string
    {
        $stars = '';

        foreach ([[34, 12, 5], [62, 28, 3.4], [188, 13, 4.4], [212, 29, 3], [88, 9, 2.6]] as [$x, $y, $size]) {
            $stars .= sprintf(
                '<path d="M%1$.1f %2$.1f Q %3$.1f %4$.1f %5$.1f %4$.1f Q %3$.1f %4$.1f %3$.1f %6$.1f Q %3$.1f %4$.1f %7$.1f %4$.1f Q %3$.1f %4$.1f %1$.1f %2$.1f Z" fill="%8$s"/>',
                $x, $y - $size * 1.9, $x, $y, $x + $size, $y + $size * 1.9, $x - $size, $soft
            );
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .$stars
            .'<circle cx="122" cy="20" r="17" fill="'.$color.'"/>'
            .'<circle cx="132" cy="16" r="15" fill="'.$paper.'"/>'
            .'<path d="M8 20 H78" stroke="'.$soft.'" stroke-width="0.8"/>'
            .'<path d="M166 20 H232" stroke="'.$soft.'" stroke-width="0.8"/>'
            .'</svg>';
    }

    /** Tres birretes al aire con su borla: la graduación. */
    private static function birretes(string $color, string $soft): string
    {
        $caps = '';

        foreach ([[84, 22, -14, 0.8], [120, 16, 0, 1], [156, 22, 14, 0.8]] as [$x, $y, $angle, $scale]) {
            $caps .= sprintf(
                '<g transform="translate(%1$d %2$d) rotate(%3$d) scale(%4$.2f)">'
                .'<path d="M-16 0 L0 -7 L16 0 L0 7 Z" fill="%5$s"/>'
                .'<path d="M-9 3 V9 Q0 13 9 9 V3" fill="%5$s"/>'
                .'<path d="M0 0 L12 4 V12" stroke="%6$s" stroke-width="1.4" fill="none"/>'
                .'<circle cx="12" cy="13" r="2" fill="%6$s"/></g>',
                $x, $y, $angle, $scale, $color, $soft
            );
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .'<path d="M8 22 H62" stroke="'.$color.'" stroke-width="0.8"/>'
            .'<path d="M178 22 H232" stroke="'.$color.'" stroke-width="0.8"/>'
            .$caps
            .'</svg>';
    }

    /** Murciélagos alrededor de una calabaza: Halloween. */
    private static function murcielagos(string $color, string $soft): string
    {
        $bat = fn (float $x, float $y, float $scale) => sprintf(
            '<path transform="translate(%.1f %.1f) scale(%.2f)" d="M0 0 C-3 -5 -9 -6 -14 -3 C-11 -2 -10 1 -11 3 C-8 1 -5 2 -4 4 C-3 2 -1 2 0 3 C1 2 3 2 4 4 C5 2 8 1 11 3 C10 1 11 -2 14 -3 C9 -6 3 -5 0 0 Z" fill="%s"/>',
            $x, $y, $scale, $soft
        );

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .$bat(40, 16, 1.1).$bat(72, 26, 0.8).$bat(170, 14, 0.9).$bat(204, 24, 1.1)
            .'<ellipse cx="108" cy="24" rx="9" ry="11" fill="'.$color.'"/>'
            .'<ellipse cx="132" cy="24" rx="9" ry="11" fill="'.$color.'"/>'
            .'<ellipse cx="120" cy="24" rx="11" ry="12" fill="'.$color.'"/>'
            .'<path d="M120 12 Q121 6 125 4" stroke="'.$soft.'" stroke-width="2" fill="none" stroke-linecap="round"/>'
            .'</svg>';
    }

    /** Una línea fina con un punto: el único adorno de la plantilla en blanco. */
    private static function linea(string $color): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">'
            .'<path d="M70 20 H112" stroke="'.$color.'" stroke-width="0.8"/>'
            .'<circle cx="120" cy="20" r="2.2" fill="'.$color.'"/>'
            .'<path d="M128 20 H170" stroke="'.$color.'" stroke-width="0.8"/>'
            .'</svg>';
    }

    /** Rombo entre dos líneas: el adorno neutro de cualquier plantilla nueva. */
    private static function diamante(string $color, string $soft): string
    {
        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 40" width="240" height="40">
        <path d="M10 20 H104" stroke="{$color}" stroke-width="1"/>
        <path d="M136 20 H230" stroke="{$color}" stroke-width="1"/>
        <path d="M120 8 L128 20 L120 32 L112 20 Z" fill="{$color}"/>
        <path d="M120 14 L124 20 L120 26 L116 20 Z" fill="{$soft}"/>
        </svg>
        SVG;
    }

    private static function uri(string $svg): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
