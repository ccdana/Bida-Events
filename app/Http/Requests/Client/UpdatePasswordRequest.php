<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** El revendedor cambia su contraseña: pide la actual y la nueva dos veces. */
class UpdatePasswordRequest extends FormRequest
{
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.different' => 'La nueva contraseña tiene que ser distinta de la actual.',
            'password.confirmed' => 'Las dos contraseñas nuevas no coinciden.',
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'contraseña actual',
            'password' => 'contraseña nueva',
        ];
    }
}
