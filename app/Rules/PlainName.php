<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * El nombre de una persona, nada más: al menos dos letras y sin enlaces, direcciones ni etiquetas. El nombre sale
 * en los correos y en los paneles, así que no debe servir para colar un enlace ni código.
 */
class PlainName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = (string) $value;

        if (preg_match_all('/\p{L}/u', $name) < 2) {
            $fail(__('Escribe tu nombre (al menos 2 letras).'));

            return;
        }

        if (preg_match('/[<>@]|:\/\/|https?:|www\.|\p{C}/iu', $name)) {
            $fail(__('Escribe solo tu nombre, sin enlaces ni símbolos.'));
        }
    }
}
