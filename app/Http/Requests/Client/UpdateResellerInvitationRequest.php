<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\Admin\Invitation\UpdateInvitationRequest;
use App\Models\Invitation;
use App\Support\ResellerSubscription;
use Illuminate\Validation\Rule;

/**
 * Cambios del revendedor sobre su invitación: sin cambiar de dueño y con las plantillas de su
 * plan. La plantilla que ya tiene la invitación se acepta aunque su plan ya no la incluya, para
 * que un cambio de plan no le impida seguir corrigiendo lo que ya entregó.
 */
class UpdateResellerInvitationRequest extends UpdateInvitationRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['user_id'], $rules['package']);

        /** @var Invitation|null $invitation */
        $invitation = $this->route('invitation');
        $allowed = array_keys(ResellerSubscription::allowedTemplates($this->user()));

        if ($invitation instanceof Invitation) {
            $allowed[] = $invitation->template;
        }

        $rules['template'] = ['required', 'string', Rule::in(array_unique($allowed))];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'template.in' => 'Esa plantilla no está incluida en tu plan.',
        ];
    }
}
