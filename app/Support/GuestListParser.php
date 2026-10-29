<?php

namespace App\Support;

/**
 * Lee una lista de invitados pegada de una vez: una línea por invitado, con el nombre y, si se quiere, el teléfono y los
 * pases separados por coma, punto y coma o tabulador («Familia Quispe, 71234567, 3»). Un número de una o dos cifras es la
 * cantidad de pases; lo demás, el teléfono. Sin pases, lleva uno. Si alguna línea no sirve no se agrega ninguna: se dice
 * cuál y por qué, para corregirla y volver a enviar la lista entera.
 */
final class GuestListParser
{
    public const MAX_GUESTS = 200;

    public const MAX_PASSES = 20;

    /**
     * @return array{guests: list<array{name: string, phone: ?string, passes_allocated: int}>, errors: list<string>}
     */
    public static function parse(string $text): array
    {
        $guests = [];
        $errors = [];
        $lines = preg_split('/\R/u', $text) ?: [];

        foreach ($lines as $index => $raw) {
            $line = trim($raw);

            if ($line === '') {
                continue;
            }

            $number = $index + 1;
            $parts = array_map('trim', preg_split('/[,;\t]/u', $line) ?: []);
            $name = array_shift($parts);
            $phone = null;
            $passes = 1;

            if ($name === '') {
                $errors[] = "Línea {$number}: falta el nombre.";

                continue;
            }

            if (mb_strlen($name) > 255) {
                $errors[] = "Línea {$number}: el nombre es demasiado largo.";

                continue;
            }

            foreach (array_filter($parts, fn (string $part) => $part !== '') as $part) {
                if (preg_match('/^\d{1,2}$/', $part) === 1) {
                    $passes = (int) $part;
                } elseif (preg_match('/^[\d+\-\s().]{6,50}$/', $part) === 1) {
                    $phone = $part;
                } else {
                    $errors[] = "Línea {$number}: «{$part}» no es un teléfono ni una cantidad de pases.";

                    continue 2;
                }
            }

            if ($passes < 1 || $passes > self::MAX_PASSES) {
                $errors[] = 'Línea '.$number.': los pases van de 1 a '.self::MAX_PASSES.'.';

                continue;
            }

            $guests[] = ['name' => $name, 'phone' => $phone, 'passes_allocated' => $passes];
        }

        if ($guests === [] && $errors === []) {
            $errors[] = 'Escribe al menos un invitado.';
        }

        if (count($guests) > self::MAX_GUESTS) {
            $errors[] = 'Son demasiados de una vez: pega hasta '.self::MAX_GUESTS.' invitados.';
        }

        return ['guests' => $guests, 'errors' => $errors];
    }
}
