<?php

namespace App\Http\Requests\Client;

use App\Payments\PaymentGateways;
use App\Payments\SubscriptionPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Pagar un plan en línea: qué plan y con qué método (tarjeta o QR). */
class StartCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan' => ['required', 'string', Rule::in(array_keys(config('bida.reseller_plans', [])))],
            'method' => ['required', 'string', Rule::in(PaymentGateways::METHODS)],
            // Meses que paga de una vez (QR) o cada cuánto se renueva (tarjeta, si la pasarela tiene ese ciclo)
            'months' => ['nullable', 'integer', Rule::in(array_keys(SubscriptionPrice::prepayOptions()))],
        ];
    }

    public function messages(): array
    {
        return [
            'plan.required' => 'Elige un plan.',
            'plan.in' => 'Ese plan no existe.',
            'method.required' => 'Elige cómo pagar.',
            'method.in' => 'Esa forma de pago no existe.',
            'months.in' => 'Elige 1, 3 o 12 meses.',
        ];
    }
}
