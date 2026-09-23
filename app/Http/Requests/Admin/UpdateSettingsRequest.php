<?php

namespace App\Http\Requests\Admin;

use App\Support\InvitationTemplates;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Lo que el administrador cambia en «Ajustes»: precios, promociones y plantillas de temporada. */
class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promo_active' => ['nullable', 'boolean'],
            'promo_ends_at' => ['nullable', 'date'],

            'packages' => ['array'],
            'packages.*.price' => ['required', 'integer', 'min:0', 'max:100000'],
            'packages.*.promo_price' => ['nullable', 'integer', 'min:0', 'max:100000'],

            'season_price' => ['required', 'integer', 'min:0', 'max:100000'],
            'season_promo_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'season_ends_at' => ['nullable', 'date'],

            // Las plantillas de temporada que siguen ofreciéndose
            'templates' => ['array'],
            'templates.*' => ['string', 'in:'.implode(',', array_keys(InvitationTemplates::all()))],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Un descuento que no baja de precio confunde al cliente: se muestra tachado igual
                foreach ((array) $this->input('packages', []) as $key => $package) {
                    if (($package['promo_price'] ?? null) !== null && $package['promo_price'] !== ''
                        && (int) $package['promo_price'] >= (int) ($package['price'] ?? 0)) {
                        $validator->errors()->add("packages.{$key}.promo_price", 'El precio con descuento tiene que ser menor que el normal.');
                    }
                }

                if ($this->filled('season_promo_price') && (int) $this->input('season_promo_price') >= (int) $this->input('season_price')) {
                    $validator->errors()->add('season_promo_price', 'El precio con descuento tiene que ser menor que el normal.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'promo_ends_at' => 'fecha de término de la promoción',
            'season_price' => 'precio de la temporada',
            'season_promo_price' => 'precio con descuento de la temporada',
            'season_ends_at' => 'fecha de término de la temporada',
        ];
    }
}
