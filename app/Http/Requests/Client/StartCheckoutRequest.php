<?php

namespace App\Http\Requests\Client;

use App\Payments\PaymentGateways;
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
        ];
    }

    public function messages(): array
    {
        return [
            'plan.required' => 'Elige un plan.',
            'plan.in' => 'Ese plan no existe.',
            'method.required' => 'Elige cómo pagar.',
            'method.in' => 'Esa forma de pago no existe.',
        ];
    }
}
