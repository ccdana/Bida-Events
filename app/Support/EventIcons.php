<?php

namespace App\Support;

/**
 * Un ícono suelto (de Phosphor, en su trazo «light») por tipo de evento, para reconocerlo de un vistazo en el asistente de
 * creación y en las listas del panel. Un tipo nuevo, sin código aquí, usa el de «default».
 */
final class EventIcons
{
    /** @var array<string, string> código del tipo de evento → nombre del ícono (sin el prefijo «phosphor-» ni el sufijo «-light») */
    public const MAP = [
        'xv' => 'crown',
        'boda' => 'diamond',
        'bautizo' => 'drop',
        'cumple' => 'cake',
        'graduacion' => 'graduation-cap',
        'babyshower' => 'baby-carriage',
        'lienzo' => 'sparkle',
        'halloween' => 'ghost',
        'amor' => 'heart',
        'aventura' => 'compass',
        'historia' => 'book-open-text',
    ];

    public const FALLBACK = 'confetti';

    public static function for(?string $code): string
    {
        return self::MAP[(string) $code] ?? self::FALLBACK;
    }
}
