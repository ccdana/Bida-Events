<?php

namespace App\Http\Requests\Client;

use App\Support\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

/** El cliente o el revendedor cambia su contraseña: pide la actual y la nueva dos veces. */
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
            'password' => [...PasswordPolicy::rules($this->user()?->email, $this->user()?->name), 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'La contraseña actual no es correcta.',
            'password.different' => 'La nueva contraseña tiene que ser distinta de la actual.',
            'password.confirmed' => 'Las dos contraseñas nuevas no coinciden.',
        ] + PasswordPolicy::messages('password', new: true);
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'contraseña actual',
            'password' => 'contraseña nueva',
        ];
    }
}
