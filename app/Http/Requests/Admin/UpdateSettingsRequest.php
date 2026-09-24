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

            // Cada temporada: encendida o no, precio y fecha de término
            'seasons' => ['array'],
            'seasons.*.active' => ['nullable', 'boolean'],
            'seasons.*.price' => ['required', 'integer', 'min:0', 'max:100000'],
            'seasons.*.promo_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'seasons.*.ends_at' => ['nullable', 'date'],

            // Planes de revendedor: precio mensual y cupo (vacío = sin tope)
            'reseller_plans' => ['array'],
            'reseller_plans.*.price' => ['required', 'integer', 'min:0', 'max:100000'],
            'reseller_plans.*.quota_per_month' => ['nullable', 'integer', 'min:1', 'max:10000'],

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

                foreach ((array) $this->input('seasons', []) as $key => $season) {
                    if (($season['promo_price'] ?? '') !== '' && ($season['promo_price'] ?? null) !== null
                        && (int) $season['promo_price'] >= (int) ($season['price'] ?? 0)) {
                        $validator->errors()->add("seasons.{$key}.promo_price", 'El precio con descuento tiene que ser menor que el normal.');
                    }
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'promo_ends_at' => 'fecha de término de la promoción',
            'seasons.*.price' => 'precio de la temporada',
            'seasons.*.promo_price' => 'precio con descuento de la temporada',
            'seasons.*.ends_at' => 'fecha de término de la temporada',
        ];
    }
}
