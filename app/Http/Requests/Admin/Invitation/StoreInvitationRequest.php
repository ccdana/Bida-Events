<?php

namespace App\Http\Requests\Admin\Invitation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('invitations', 'slug')],
            'template' => ['required', 'string'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,suspended,expired'],
            'expires_at' => ['required', 'date'],
        ];
    }
}
