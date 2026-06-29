<?php

namespace App\Http\Requests\Admin\Invitation;

use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\Invitation|null $invitation */
        $invitation = $this->route('invitation');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('invitations', 'slug')->ignore($invitation instanceof Invitation ? $invitation->id : null),
            ],
            'template' => ['required', 'string'],
            'event_type_id' => ['required', 'exists:event_types,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,suspended,expired'],
            'expires_at' => ['required', 'date'],
        ];
    }
}
