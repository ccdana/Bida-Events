<?php

namespace App\Support;

use App\Payments\PaymentGateways;
use App\Payments\SubscriptionPrice;
use Illuminate\Http\Request;

/**
 * Los planes de «Únete» listos para mostrarlos (portada y página «Únete»): precio de hoy con su
 * descuento, cuánto queda por mes pagando 3 o 12 meses de una vez («cycles», el selector de la
 * página), a dónde lleva «Elegir» en cada ciclo y las familias de plantillas de cada plan.
 */
final class DiyPlans
{
    /**
     * @param  string  $leadCode  código de origen del mensaje de WhatsApp (LeadSource)
     * @return array{plans: list<array<string, mixed>>, featuredPlan: ?string, cycles: array<int, int>, collections: array<string, array{label: string, templates: list<string>}>}
     */
    public static function make(Request $request, string $leadCode): array
    {
        $templates = collect(InvitationTemplates::all());
        $gateways = app(PaymentGateways::class);

        // Quien todavía no tiene cuenta pasa primero por crearla: el plan elegido viaja con él hasta el pago
        $registering = ! $request->user() && AuthOptions::registrationOpen();

        $prepay = SubscriptionPrice::prepayOptions();

        $plans = collect(Offers::resellerPlans())->map(function (array $plan, string $key) use ($gateways, $registering, $templates, $request, $leadCode, $prepay): array {
            $online = (bool) $gateways->availableMethods($key);
            // Con el pago en línea encendido, «Elegir» lleva a pagar (con el ciclo elegido); si no, a WhatsApp como siempre
            $checkout = fn (int $months) => $online
                ? route($registering ? 'register' : 'client.subscription', array_filter(['plan' => $key, 'meses' => $months > 1 ? $months : null]))
                : null;

            return $plan + [
                'checkout' => $checkout(1),
                'key' => $key,
                'templates_count' => $templates->filter(fn (array $template) => in_array($template['collection'] ?? 'clasica', $plan['collections'] ?? [], true))->count(),
                'whatsapp' => LeadSource::whatsappUrl($request, __('Hola {brand}, quiero el plan :name de Únete (:price al mes).', ['name' => $plan['name'], 'price' => Money::format($plan['final_price'])]), $leadCode),
                // Por ciclo: lo que queda por mes, el precio que se tacha, lo que se paga y se ahorra, y a dónde lleva
                'cycles' => collect($prepay)->map(function (int $discount, int $months) use ($plan, $key, $checkout, $request, $leadCode) {
                    $total = (float) SubscriptionPrice::amount($key, $months, 'USD');
                    $full = $plan['final_price'] * $months;
                    $period = $months === 12 ? __('al año') : __('cada :months meses', ['months' => $months]);

                    return [
                        'price' => Money::number(round($total / $months, 2)),
                        'old' => $months > 1 && $total < $full
                            ? Money::format($plan['final_price'])
                            : ($plan['old_price'] ? Money::format($plan['old_price']) : null),
                        'saving' => $months === 1
                            ? ($plan['old_price'] ? __('Ahorras :price al mes', ['price' => Money::format($plan['old_price'] - $plan['final_price'])]) : null)
                            : Money::format($total).' '.$period.($full > $total ? ' · '.__('ahorras :price', ['price' => Money::format($full - $total)]) : ''),
                        'url' => $checkout($months) ?? LeadSource::whatsappUrl(
                            $request,
                            $months === 1
                                ? __('Hola {brand}, quiero el plan :name de Únete.', ['name' => $plan['name']])
                                : __('Hola {brand}, quiero el plan :name de Únete, pagando :months meses.', ['name' => $plan['name'], 'months' => $months]),
                            $leadCode,
                        ),
                    ];
                })->all(),
            ];
        })->values()->all();

        return [
            'plans' => $plans,
            // El que se destaca: el primero con marca propia, el que más conviene a quien vende invitaciones
            'featuredPlan' => collect($plans)->firstWhere('featured', true)['key'] ?? collect($plans)->firstWhere('white_label', true)['key'] ?? null,
            // El selector de la página: meses => descuento en % (solo si hay más de un ciclo)
            'cycles' => $prepay,
            'collections' => [
                'lienzo' => ['label' => __('Plantilla en blanco'), 'templates' => $templates->where('collection', 'lienzo')->pluck('label')->all()],
                'clasica' => ['label' => __('Plantillas clásicas'), 'templates' => $templates->where('collection', 'clasica')->pluck('label')->all()],
                'temporada' => ['label' => __('Plantillas de temporada'), 'templates' => $templates->where('collection', 'temporada')->pluck('label')->all()],
                // Las que se suman durante el año; mientras no haya, la página dice que llegan
                'nueva' => ['label' => __('Plantillas nuevas de cada evento'), 'templates' => $templates->where('collection', 'nueva')->pluck('label')->all()],
            ],
        ];
    }
}
