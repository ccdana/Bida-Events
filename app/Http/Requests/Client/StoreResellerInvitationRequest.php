<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\Admin\Invitation\StoreInvitationRequest;
use App\Support\ResellerSubscription;
use Illuminate\Validation\Rule;

/**
 * Invitación nueva desde el editor del revendedor: las mismas reglas que el administrador, sin
 * elegir dueño (siempre es el propio revendedor) y con las plantillas de su plan.
 */
class StoreResellerInvitationRequest extends StoreInvitationRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['user_id']);

        $rules['template'] = [
            'required',
            'string',
            Rule::in(array_keys(ResellerSubscription::allowedTemplates($this->user()))),
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'template.in' => 'Esa plantilla no está incluida en tu plan.',
        ];
    }
}
