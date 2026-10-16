<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\Admin\Invitation\UpdateInvitationRequest;
use App\Models\Invitation;
use App\Support\DiyInvitations;
use Illuminate\Validation\Rule;

/**
 * Cambios del dueño sobre su invitación de «Hazlo tú». «status» no publica por sí solo: «active» es el
 * pedido de publicarla y lo resuelve Client\DiyInvitationController (gratis o con el nivel que pida).
 * La plantilla que ya tiene se acepta aunque hoy no se ofrezca.
 */
class UpdateDiyInvitationRequest extends UpdateInvitationRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['user_id'], $rules['package'], $rules['expires_at']);

        /** @var Invitation|null $invitation */
        $invitation = $this->route('invitation');
        $allowed = array_keys(DiyInvitations::allowedTemplates());

        if ($invitation instanceof Invitation) {
            $allowed[] = $invitation->template;
        }

        $rules['status'] = ['nullable', 'in:active,inactive'];
        $rules['template'] = ['required', 'string', Rule::in(array_unique($allowed))];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'template.in' => 'Esa plantilla no está disponible para armarla tú mismo.',
        ];
    }
}
