<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * El cliente o el revendedor cambia su usuario (con el que entra, igual que con su correo): pide la contraseña actual,
 * como al cambiar el correo. El usuario nuevo va en minúsculas, de 3 a 30 caracteres, con letras, números, punto y guion
 * (sin empezar ni terminar con un signo ni repetirlos), no es de los reservados del sistema y no lo usa nadie más. El
 * anterior queda libre. Si no cambia (escribe el que ya tiene), no se vuelve a validar: algunos usuarios que arma el
 * equipo pasan de 30 caracteres y no por eso dejan de ser válidos.
 */
class UpdateUsernameRequest extends FormRequest
{
    public const MIN = 3;

    public const MAX = 30;

    /** Nombres que sugieren ser del equipo o del sistema: nadie los puede elegir (suplantarían a quien da soporte). */
    public const RESERVED = ['admin', 'administrador', 'administrator', 'root', 'soporte', 'support', 'ayuda', 'sistema', 'system', 'bida', 'bidaevents', 'bida.events', 'equipo', 'staff', 'ventas', 'pagos'];

    protected $dontFlash = ['username_current_password'];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['username' => Str::lower(trim((string) $this->input('username')))]);
    }

    private function unchanged(): bool
    {
        return $this->input('username') === $this->user()?->username;
    }

    public function rules(): array
    {
        return [
            'username' => $this->unchanged()
                ? ['required', 'string']
                : [
                    'required',
                    'string',
                    'min:'.self::MIN,
                    'max:'.self::MAX,
                    'regex:/^[a-z0-9]+([._-][a-z0-9]+)*$/',
                    Rule::notIn(self::RESERVED),
                    Rule::unique('users', 'username')->ignore($this->user()->id),
                ],
            'username_current_password' => ['required', 'string', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Escribe tu usuario.',
            'username.min' => 'El usuario necesita al menos '.self::MIN.' caracteres.',
            'username.max' => 'El usuario puede tener hasta '.self::MAX.' caracteres.',
            'username.regex' => 'Solo minúsculas, números, punto o guion, sin espacios ni signos al principio o al final.',
            'username.not_in' => 'Ese usuario no está disponible.',
            'username.unique' => 'Ese usuario ya lo usa otra persona.',
            'username_current_password.required' => 'Escribe tu contraseña actual.',
            'username_current_password.current_password' => 'La contraseña actual no es correcta.',
        ];
    }
}
