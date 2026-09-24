<?php

namespace App\Http\Requests\Admin\Reseller;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta de un revendedor: su nombre, el plan con el que empieza y, si quiere, su nombre comercial. */
class StoreResellerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string', Rule::in(array_keys(config('bida.reseller_plans', [])))],
            'business_name' => ['nullable', 'string', 'max:120'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'plan' => 'plan',
            'business_name' => 'nombre comercial',
        ];
    }
}
