<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** El cliente agrega o cambia su correo: pide la contraseña actual, como al cambiar la contraseña. */
class UpdateEmailRequest extends FormRequest
{
    protected $dontFlash = ['email_current_password'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'email_current_password' => ['required', 'string', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Revisa el correo: parece que le falta algo.',
            'email.unique' => 'Ese correo ya es de otra cuenta.',
            'email_current_password.required' => 'Escribe tu contraseña actual.',
            'email_current_password.current_password' => 'La contraseña actual no es correcta.',
        ];
    }
}
