<?php

namespace App\Support;

/**
 * Contraste de color según WCAG 2.1.
 *
 * La invitación no pinta los colores del cliente tal cual: los tonos apagados salen de
 * mezclar el texto con el fondo (ver las variables --inv-* en resources/css/invitation/base.css).
 * Aquí se repiten esas mezclas para poder medir lo que de verdad lee el invitado, tanto en
 * la página del sistema visual (/admin/sistema-visual) como en las pruebas.
 */
final class ColorContrast
{
    /** Texto normal necesita 4.5:1; texto grande y bordes, 3:1. */
    public const AA_TEXT = 4.5;

    public const AA_LARGE = 3.0;

    /** Mezclas que usa la invitación, en el mismo orden que base.css. */
    public const MIXES = [
        'muted' => ['text', 'background', 0.78],
        'soft' => ['text', 'background', 0.70],
        'line' => ['text', 'background', 0.11],
        'line-strong' => ['text', 'background', 0.22],
        'tint' => ['accent', 'background', 0.20],
        'accent-ink' => ['primary', 'text', 0.55],
        'accent-deco' => ['primary', 'text', 0.80],
        'surface' => ['accent', 'background', 0.32],
    ];

    /** @return array{0:int,1:int,2:int} */
    public static function rgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            return [0, 0, 0];
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /** Mezcla en sRGB, igual que color-mix(in srgb, $a {$weight}%, $b). */
    public static function mix(string $a, string $b, float $weight): string
    {
        [$ar, $ag, $ab] = self::rgb($a);
        [$br, $bg, $bb] = self::rgb($b);

        return sprintf(
            '#%02X%02X%02X',
            (int) round($ar * $weight + $br * (1 - $weight)),
            (int) round($ag * $weight + $bg * (1 - $weight)),
            (int) round($ab * $weight + $bb * (1 - $weight)),
        );
    }

    public static function luminance(string $hex): float
    {
        $channels = array_map(function (int $value): float {
            $channel = $value / 255;

            return $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        }, self::rgb($hex));

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    public static function ratio(string $foreground, string $background): float
    {
        $a = self::luminance($foreground);
        $b = self::luminance($background);

        return round((max($a, $b) + 0.05) / (min($a, $b) + 0.05), 2);
    }

    /**
     * Resuelve los tonos derivados de una paleta ('primary', 'text', 'background', 'accent').
     *
     * @param  array<string, string>  $palette
     * @return array<string, string>
     */
    public static function tokens(array $palette): array
    {
        $tokens = [];

        foreach (self::MIXES as $name => [$a, $b, $weight]) {
            $tokens[$name] = self::mix($palette[$a] ?? '#000000', $palette[$b] ?? '#FFFFFF', $weight);
        }

        return $tokens;
    }

    /**
     * Cada pieza de texto de la invitación con su contraste real y el mínimo que le toca.
     *
     * @param  array<string, string>  $palette
     * @return list<array{label: string, color: string, on: string, ratio: float, min: float, passes: bool}>
     */
    public static function audit(array $palette): array
    {
        $background = $palette['background'] ?? '#FFFFFF';
        $text = $palette['text'] ?? '#000000';
        $tokens = self::tokens($palette);

        $checks = [
            ['Textos', $text, $background, self::AA_TEXT],
            ['Textos apagados', $tokens['muted'], $background, self::AA_TEXT],
            ['Etiquetas y ayudas', $tokens['soft'], $background, self::AA_TEXT],
            ['Detalles en color', $tokens['accent-ink'], $background, self::AA_TEXT],
            ['Texto sobre recuadro', $text, $tokens['tint'], self::AA_TEXT],
            ['Texto de los botones', $background, $text, self::AA_TEXT],
            ['Números grandes', $tokens['accent-deco'], $background, self::AA_LARGE],
            ['Anillo de foco', $tokens['accent-ink'], $background, self::AA_LARGE],
        ];

        return array_map(function (array $check): array {
            [$label, $color, $on, $min] = $check;
            $ratio = self::ratio($color, $on);

            return [
                'label' => $label,
                'color' => $color,
                'on' => $on,
                'ratio' => $ratio,
                'min' => $min,
                'passes' => $ratio >= $min,
            ];
        }, $checks);
    }
}
