<?php

namespace App\Http\Requests\Admin\Invitation;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Solo se pide el nombre: el usuario y la contraseña se generan al crear el cliente. */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del cliente.',
            'name.min' => 'El nombre es demasiado corto.',
        ];
    }
}
