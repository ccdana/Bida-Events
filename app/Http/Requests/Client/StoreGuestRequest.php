<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

/** Alta de un invitado desde el panel del cliente: nombre, teléfono y cuántos pases lleva. */
class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'passes_allocated' => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'phone' => 'teléfono',
            'passes_allocated' => 'cantidad de pases',
        ];
    }
}
