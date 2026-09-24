<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

/** Cuántas personas de un pase entran ahora por la puerta (el tope real lo pone el pase). */
class CheckInGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'people' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return ['people' => 'personas'];
    }
}
