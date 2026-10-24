<?php

namespace App\Http\Requests\Auth;

use App\Rules\PlainName;
use App\Support\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Cuenta nueva desde el sitio: nombre (solo el nombre, sin enlaces), correo (único, en minúsculas) y una contraseña
 * propia que cumple App\Support\PasswordPolicy (letras y números, distinta del correo y del nombre).
 */
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
            'name' => trim((string) preg_replace('/\s+/u', ' ', (string) $this->input('name'))),
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120', new PlainName],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => PasswordPolicy::rules($this->input('email'), $this->input('name')),
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Escribe tu nombre.'),
            'name.min' => __('Escribe tu nombre (al menos 2 letras).'),
            'email.required' => __('Escribe tu correo.'),
            'email.email' => __('Revisa el correo: parece que le falta algo.'),
            // El formulario le agrega los enlaces a «Ingresar» y «¿Olvidaste tu contraseña?» (auth/register.blade.php),
            // y la vista reconoce este aviso por su texto: si se cambia aquí, se cambia allá
            'email.unique' => __('Este correo ya tiene una cuenta.'),
        ] + PasswordPolicy::messages();
    }
}
