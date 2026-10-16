<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\Admin\Invitation\StoreInvitationRequest;
use App\Support\DiyInvitations;
use Illuminate\Validation\Rule;

/**
 * Invitación nueva de «Hazlo tú»: las mismas reglas que el administrador, sin elegir dueño (siempre es
 * quien la crea), ni paquete (sale de sus partes al publicar), ni vencimiento (va con la fecha del
 * evento). Nace sin publicar, venga lo que venga en «status».
 */
class StoreDiyInvitationRequest extends StoreInvitationRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['user_id'], $rules['package'], $rules['expires_at']);

        $rules['status'] = ['nullable', 'in:active,inactive'];
        $rules['template'] = ['required', 'string', Rule::in(array_keys(DiyInvitations::allowedTemplates()))];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'template.in' => 'Esa plantilla no está disponible para armarla tú mismo.',
        ];
    }
}
