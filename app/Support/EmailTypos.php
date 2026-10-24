<?php

namespace App\Support;

/**
 * Las erratas más comunes al escribir el dominio del correo y su forma correcta. El formulario de registro las usa
 * para preguntar «¿quisiste decir…?» al salir del campo; es solo un aviso, nunca bloquea el envío (un dominio raro
 * puede ser verdadero).
 */
final class EmailTypos
{
    /** @var array<string, string> dominio mal escrito => dominio correcto */
    public const DOMAINS = [
        'gmial.com' => 'gmail.com',
        'gmai.com' => 'gmail.com',
        'gmaill.com' => 'gmail.com',
        'gamil.com' => 'gmail.com',
        'gnail.com' => 'gmail.com',
        'gmail.co' => 'gmail.com',
        'gmail.con' => 'gmail.com',
        'gmail.cm' => 'gmail.com',
        'hotmial.com' => 'hotmail.com',
        'hotmal.com' => 'hotmail.com',
        'hotmai.com' => 'hotmail.com',
        'hotmail.con' => 'hotmail.com',
        'hotmail.co' => 'hotmail.com',
        'outlok.com' => 'outlook.com',
        'outllok.com' => 'outlook.com',
        'outlook.con' => 'outlook.com',
        'yaho.com' => 'yahoo.com',
        'yahooo.com' => 'yahoo.com',
        'yahoo.con' => 'yahoo.com',
        'icloud.con' => 'icloud.com',
        'iclod.com' => 'icloud.com',
    ];

    /** La dirección corregida, o null si el dominio no es una errata conocida. */
    public static function suggest(string $email): ?string
    {
        $email = strtolower(trim($email));
        $at = strrpos($email, '@');

        if ($at === false || $at === 0) {
            return null;
        }

        $fixed = self::DOMAINS[substr($email, $at + 1)] ?? null;

        return $fixed === null ? null : substr($email, 0, $at + 1).$fixed;
    }
}
