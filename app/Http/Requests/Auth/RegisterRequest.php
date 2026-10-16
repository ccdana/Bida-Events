<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/** Cuenta nueva desde el sitio: nombre, correo (único, en minúsculas) y una contraseña propia. */
class RegisterRequest extends FormRequest
{
    protected $dontFlash = ['password'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Escribe tu nombre.'),
            'email.required' => __('Escribe tu correo.'),
            'email.email' => __('Revisa el correo: parece que le falta algo.'),
            'email.unique' => __('Ya hay una cuenta con este correo. Entra con él o recupera tu contraseña.'),
            'password.required' => __('Elige una contraseña.'),
            'password.min' => __('La contraseña necesita al menos 8 caracteres.'),
        ];
    }
}
