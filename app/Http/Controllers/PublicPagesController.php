<?php

namespace App\Http\Controllers;

use App\Support\InvitationTemplates;
use App\Support\LeadSource;
use App\Support\LegalPages;
use App\Support\Money;
use App\Support\Offers;
use App\Support\ShareMeta;
use App\Support\ShowcaseDemos;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas públicas que no son de un evento: «Hazlo tú» (planes mensuales para crear invitaciones
 * propias) y las legales (privacidad, cookies y términos).
 */
class PublicPagesController extends Controller
{
    /** Código de WhatsApp de la página «Hazlo tú» («Ref. HAZLO-…»). */
    public const DIY_CODE = 'HAZLO';

    /** Precio de ejemplo al que se vende una invitación, para la tabla «Cuentas claras». (Supuesto) */
    public const EXAMPLE_RESALE_PRICE = 25;

    /** Código de WhatsApp de la guía («Ref. GUIA-…») y fecha de su última revisión (se muestra y va al sitemap). */
    public const GUIDE_CODE = 'GUIA';

    public const GUIDE_UPDATED_AT = '2026-09-24';

    /**
     * «Hazlo tú»: planes mensuales para armar invitaciones propias (revendedores). Qué se obtiene en
     * detalle, para quién sirve, cuentas claras con un ejemplo y los planes con su precio de hoy
     * (config «bida.reseller_plans» con lo que el administrador cambió en Ajustes, descuentos incluidos).
     */
    public function diy(Request $request): View
    {
        $bida = config('bida');
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, self::DIY_CODE);
        $templates = collect(InvitationTemplates::all());

        $plans = collect(Offers::resellerPlans())->map(fn (array $plan, string $key): array => $plan + [
            'key' => $key,
            'templates_count' => $templates->filter(fn (array $template) => in_array($template['collection'] ?? 'clasica', $plan['collections'] ?? [], true))->count(),
            // Cuánto sale cada invitación si se usa todo el cupo del mes
            'cost_per_invitation' => $plan['quota_per_month'] ? round($plan['final_price'] / $plan['quota_per_month'], 2) : null,
            'example_margin' => $plan['quota_per_month'] ? $plan['quota_per_month'] * self::EXAMPLE_RESALE_PRICE - $plan['final_price'] : null,
            'whatsapp' => $whatsapp("Hola {brand}, quiero el plan {$plan['name']} de Hazlo tú (".Money::format($plan['final_price']).' al mes).'),
        ])->values()->all();

        return view('hazlo-tu', [
            'bida' => $bida,
            'user' => $request->user(),
            'plans' => $plans,
            // El que se destaca: el primero con marca propia, el que más conviene a quien vende invitaciones
            'featuredPlan' => collect($plans)->firstWhere('white_label', true)['key'] ?? null,
            'collections' => [
                'lienzo' => ['label' => 'Plantilla en blanco', 'templates' => $templates->where('collection', 'lienzo')->pluck('label')->all()],
                'clasica' => ['label' => 'Plantillas clásicas', 'templates' => $templates->where('collection', 'clasica')->pluck('label')->all()],
                'tematica' => ['label' => 'Temáticas y de temporada', 'templates' => $templates->where('collection', 'tematica')->pluck('label')->all()],
            ],
            'demo' => ShowcaseDemos::find($bida['professionals']['demos'] ?? [])[0] ?? null,
            'examplePrice' => self::EXAMPLE_RESALE_PRICE,
            'contactUrl' => $whatsapp('Hola {brand}, quiero saber cómo funciona Hazlo tú para armar mis propias invitaciones.'),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                "Hazlo tú: crea tus propias invitaciones digitales | {$bida['brand']}",
                'Tu propio panel para crear invitaciones digitales con plantillas profesionales, confirmación de asistencia, pase QR y control de entrada. Planes mensuales desde '.Money::format(collect($plans)->min('final_price')).', sin comisión por invitación.',
                ShareMeta::defaultImage(),
                route('diy'),
            ),
        ]);
    }

    /**
     * Guía pública «Invitaciones digitales»: qué son, cuánto cuestan, cómo elegir una y cómo funciona
     * la entrada con QR. Escrita para responder las preguntas que la gente le hace a Google y a las
     * IA (ver docs/geo-estrategia.md); los precios salen de la configuración de hoy.
     */
    public function guide(Request $request): View
    {
        $bida = config('bida');
        $packages = Offers::packages();

        return view('guia', [
            'bida' => $bida,
            'user' => $request->user(),
            'packages' => $packages,
            'plans' => Offers::resellerPlans(),
            'landings' => HomeController::landingLinks(),
            'contactUrl' => LeadSource::whatsappUrl($request, 'Hola {brand}, leí la guía y quiero mi invitación digital.', self::GUIDE_CODE),
            'updatedAt' => self::GUIDE_UPDATED_AT,
            'share' => ShareMeta::make(
                "Invitaciones digitales: qué son, cuánto cuestan y cómo elegir | {$bida['brand']}",
                'Guía clara para elegir tu invitación digital: qué debe incluir, precios de referencia desde '.Money::format(collect($packages)->min('final_price')).', confirmación de asistencia y control de entrada con código QR.',
                ShareMeta::defaultImage(),
                route('guide'),
            ),
        ]);
    }

    public function legal(Request $request, string $page): View
    {
        abort_unless(in_array($page, LegalPages::PAGES, true), 404);

        $bida = config('bida');
        $content = LegalPages::get($page);

        return view('legal.show', [
            'bida' => $bida,
            'user' => $request->user(),
            'slug' => $page,
            'content' => $content,
            'updatedAt' => $bida['legal']['updated_at'] ?? null,
            'contactUrl' => LeadSource::whatsappUrl($request, 'Hola {brand}, tengo una consulta sobre mis datos.', LeadSource::HOME),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                "{$content['title']} | {$bida['brand']}",
                $content['description'],
                ShareMeta::defaultImage(),
                route('legal', $page),
            ),
        ]);
    }
}
