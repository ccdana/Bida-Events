<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Lo que se le pide a una contraseña nueva (al crear la cuenta, al recuperarla y al cambiarla), escrito una sola vez
 * para que los tres formularios no se contradigan:
 *
 *  - de 8 a 72 caracteres (bcrypt ignora lo que pasa de 72: una contraseña más larga solo parecería más segura),
 *  - con letras y números,
 *  - distinta del correo, de la parte del correo antes de la arroba y del nombre.
 *
 * No consulta servicios externos (como las listas de contraseñas filtradas): todo se comprueba aquí.
 */
final class PasswordPolicy
{
    public const MIN = 8;

    public const MAX = 72;

    /** @return list<mixed> */
    public static function rules(?string $email = null, ?string $name = null): array
    {
        return [
            'required',
            'string',
            Password::min(self::MIN)->letters()->numbers(),
            'max:'.self::MAX,
            function (string $attribute, mixed $value, Closure $fail) use ($email, $name): void {
                $typed = Str::lower(trim((string) $value));
                $personal = array_filter([
                    Str::lower(trim((string) $email)),
                    Str::lower(Str::before(trim((string) $email), '@')),
                    Str::lower(trim((string) $name)),
                ]);

                if (in_array($typed, $personal, true)) {
                    $fail(__('La contraseña no puede ser tu correo ni tu nombre.'));
                }
            },
        ];
    }

    /** @return array<string, string> los mensajes de las reglas, para el campo «password» (o el que se pida) */
    public static function messages(string $field = 'password', bool $new = false): array
    {
        return [
            "{$field}.required" => $new ? __('Elige una contraseña nueva.') : __('Elige una contraseña.'),
            "{$field}.min" => __('La contraseña necesita al menos :value caracteres.', ['value' => self::MIN]),
            "{$field}.max" => __('La contraseña puede tener hasta :value caracteres.', ['value' => self::MAX]),
            "{$field}.letters" => __('La contraseña necesita al menos una letra.'),
            "{$field}.numbers" => __('La contraseña necesita al menos un número.'),
        ];
    }
}
