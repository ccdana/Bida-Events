<?php

namespace App\Http\Requests\Admin\Reseller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Pago de suscripción registrado a mano. El plan puede cambiar con el pago (así se sube o baja de
 * plan) y el monto llega con el precio del plan, pero se puede corregir si se cobró otra cosa.
 */
class RegisterPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', Rule::in(array_keys(config('bida.reseller_plans', [])))],
            'amount' => ['required', 'numeric', 'min:0', 'max:100000'],
            'note' => ['nullable', 'string', 'max:500'],
            // Código único del formulario: si llega dos veces (doble clic, recarga), el pago se guarda una sola
            'request_token' => ['required', 'uuid'],
        ];
    }

    public function attributes(): array
    {
        return [
            'plan' => 'plan',
            'amount' => 'monto',
            'note' => 'nota',
        ];
    }
}
