<?php

namespace App\Support;

use App\Payments\PaymentGateways;
use Illuminate\Http\Request;

/**
 * Los planes mensuales de «Hazlo tú» listos para mostrarlos (portada y página «Hazlo tú»): precio de
 * hoy con su descuento, a dónde lleva «Elegir» y las familias de plantillas de cada plan.
 */
final class DiyPlans
{
    /**
     * @param  string  $leadCode  código de origen del mensaje de WhatsApp (LeadSource)
     * @return array{plans: list<array<string, mixed>>, featuredPlan: ?string, collections: array<string, array{label: string, templates: list<string>}>}
     */
    public static function make(Request $request, string $leadCode): array
    {
        $templates = collect(InvitationTemplates::all());
        $gateways = app(PaymentGateways::class);

        // Quien todavía no tiene cuenta pasa primero por crearla: el plan elegido viaja con él hasta el pago
        $registering = ! $request->user() && AuthOptions::registrationOpen();

        $plans = collect(Offers::resellerPlans())->map(fn (array $plan, string $key): array => $plan + [
            // Con el pago en línea encendido, «Elegir» lleva a pagar; si no, a WhatsApp como siempre
            'checkout' => $gateways->availableMethods($key)
                ? ($registering ? route('register', ['plan' => $key]) : route('client.subscription', ['plan' => $key]))
                : null,
            'key' => $key,
            'templates_count' => $templates->filter(fn (array $template) => in_array($template['collection'] ?? 'clasica', $plan['collections'] ?? [], true))->count(),
            'whatsapp' => LeadSource::whatsappUrl($request, "Hola {brand}, quiero el plan {$plan['name']} de Hazlo tú (".Money::format($plan['final_price']).' al mes).', $leadCode),
        ])->values()->all();

        return [
            'plans' => $plans,
            // El que se destaca: el primero con marca propia, el que más conviene a quien vende invitaciones
            'featuredPlan' => collect($plans)->firstWhere('white_label', true)['key'] ?? null,
            'collections' => [
                'lienzo' => ['label' => 'Plantilla en blanco', 'templates' => $templates->where('collection', 'lienzo')->pluck('label')->all()],
                'clasica' => ['label' => 'Plantillas clásicas', 'templates' => $templates->where('collection', 'clasica')->pluck('label')->all()],
                'temporada' => ['label' => 'Plantillas de temporada', 'templates' => $templates->where('collection', 'temporada')->pluck('label')->all()],
                // Las que se suman durante el año; mientras no haya, la página dice que llegan
                'nueva' => ['label' => 'Plantillas nuevas de cada evento', 'templates' => $templates->where('collection', 'nueva')->pluck('label')->all()],
            ],
        ];
    }
}
