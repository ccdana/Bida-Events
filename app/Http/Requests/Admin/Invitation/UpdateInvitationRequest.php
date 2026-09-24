<?php

namespace App\Http\Requests\Admin\Invitation;

use App\Http\Requests\Admin\Invitation\Concerns\ValidatesInvitationModules;
use App\Models\Invitation;
use App\Support\Packages;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInvitationRequest extends FormRequest
{
    use ValidatesInvitationModules;

    protected $dontFlash = ['password', 'password_confirmation', 'modulos_data'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Invitation|null $invitation */
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
            'status' => ['required', 'in:active,inactive'],
            // Paquete vendido (App\Support\Packages); vacío = todo incluido (tarjetas y anteriores)
            'package' => ['nullable', Rule::in(Packages::ORDER)],
            'expires_at' => ['required', 'date'],
            ...$this->moduleRules(),
        ];
    }
}
