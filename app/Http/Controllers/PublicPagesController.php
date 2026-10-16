<?php

namespace App\Http\Controllers;

use App\Support\DiyPlans;
use App\Support\LeadSource;
use App\Support\LegalPages;
use App\Support\Money;
use App\Support\Offers;
use App\Support\PricingPage;
use App\Support\ShareMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas públicas que no son de un evento: «Únete» (planes mensuales para crear invitaciones
 * propias) y las legales (privacidad, cookies y términos).
 */
class PublicPagesController extends Controller
{
    /** Código de WhatsApp de la página «Únete» («Ref. HAZLO-…»). */
    public const DIY_CODE = 'HAZLO';

    /** Precio de ejemplo al que se vende una invitación, para la tabla «Cuentas claras». (Supuesto) */
    public const EXAMPLE_RESALE_PRICE = 25;

    /** Código de WhatsApp de la guía («Ref. GUIA-…») y fecha de su última revisión (se muestra y va al sitemap). */
    public const GUIDE_CODE = 'GUIA';

    public const GUIDE_UPDATED_AT = '2026-09-24';

    /**
     * «Únete»: el plan mensual para armar invitaciones propias y venderlas (revendedores) es lo
     * principal; que el equipo se las haga («paquetes») es lo secundario. Los precios salen de la
     * config «bida.reseller_plans» con lo que el administrador cambió en Ajustes (descuentos
     * incluidos); el precio de venta de ejemplo solo se usa en las preguntas.
     */
    public function diy(Request $request): View
    {
        $bida = config('bida');
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, self::DIY_CODE);
        $diy = DiyPlans::make($request, self::DIY_CODE);
        $plans = $diy['plans'];

        return view('unete', [
            'bida' => $bida,
            'user' => $request->user(),
            'plans' => $plans,
            'featuredPlan' => $diy['featuredPlan'],
            // Pagar 3 o 12 meses de una vez, con su descuento (el selector de la página de planes)
            'planCycles' => $diy['cycles'],
            'collections' => $diy['collections'],
            // Lo secundario: que el equipo haga la invitación (pago único por invitación)
            'packages' => HomeController::packages($bida['packages'], $whatsapp),
            'examplePrice' => self::EXAMPLE_RESALE_PRICE,
            'contactUrl' => $whatsapp(__('Hola {brand}, quiero saber cómo funciona Únete para armar mis propias invitaciones.')),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                __('Únete: haz tus propias invitaciones digitales y véndelas con tu marca | :brand', ['brand' => $bida['brand']]),
                __('Arma tus propias invitaciones digitales con plantillas profesionales, para tu evento o para vender con tu marca. Desde :price al mes, sin comisión por invitación.', ['price' => Money::format(collect($plans)->min('final_price'))]),
                ShareMeta::defaultImage(),
                lroute('diy'),
            ),
        ]);
    }

    /**
     * Precios: las tres formas de tener la invitación lado a lado (Hazlo tú, Planes y Te la hacemos),
     * la comparación de los niveles y las preguntas. Los datos los arma App\Support\PricingPage.
     */
    public function pricing(Request $request): View
    {
        return view('precios', PricingPage::make($request));
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
            'contactUrl' => LeadSource::whatsappUrl($request, __('Hola {brand}, leí la guía y quiero mi invitación digital.'), self::GUIDE_CODE),
            'updatedAt' => self::GUIDE_UPDATED_AT,
            'share' => ShareMeta::make(
                __('Invitaciones digitales: qué son, cuánto cuestan y cómo elegir | :brand', ['brand' => $bida['brand']]),
                __('Guía clara para elegir tu invitación digital: qué debe incluir, precios de referencia desde :price, confirmación de asistencia y control de entrada con código QR.', ['price' => Money::format(collect($packages)->min('final_price'))]),
                ShareMeta::defaultImage(),
                lroute('guide'),
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
            'contactUrl' => LeadSource::whatsappUrl($request, __('Hola {brand}, tengo una consulta sobre mis datos.'), LeadSource::HOME),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                "{$content['title']} | {$bida['brand']}",
                $content['description'],
                ShareMeta::defaultImage(),
                lroute('legal', $page),
            ),
        ]);
    }
}
