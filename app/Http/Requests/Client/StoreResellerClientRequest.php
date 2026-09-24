<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

/** El cliente de un evento del revendedor: solo su nombre; el usuario y la contraseña se generan. */
class StoreResellerClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nombre del cliente'];
    }
}
