<?php

namespace App\Http\Requests\Admin\Guest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuestRequest extends FormRequest
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
}
