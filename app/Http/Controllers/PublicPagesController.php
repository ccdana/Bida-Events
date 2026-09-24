<?php

namespace App\Http\Controllers;

use App\Support\InvitationTemplates;
use App\Support\LeadSource;
use App\Support\LegalPages;
use App\Support\ShareMeta;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas públicas que no son de un evento: la de profesionales (revendedores con suscripción) y
 * las legales (privacidad, cookies y términos).
 */
class PublicPagesController extends Controller
{
    /** Código de WhatsApp de la página de profesionales («Ref. PRO-…»). */
    public const PROFESSIONALS_CODE = 'PRO';

    /**
     * Publicidad para el nicho de revendedores: qué obtienen, cómo funciona y los planes con su
     * precio de hoy (config «bida.reseller_plans», que el administrador cambia en Ajustes).
     */
    public function professionals(Request $request): View
    {
        $bida = config('bida');
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, self::PROFESSIONALS_CODE);
        $templates = collect(InvitationTemplates::all());

        $plans = collect($bida['reseller_plans'] ?? [])->map(fn (array $plan, string $key): array => $plan + [
            'key' => $key,
            'templates_count' => $templates->filter(fn (array $template) => in_array($template['collection'] ?? 'clasica', $plan['collections'] ?? [], true))->count(),
            'whatsapp' => $whatsapp("Hola {brand}, soy profesional de eventos y me interesa el plan {$plan['name']} ({$plan['price']} Bs al mes)."),
        ])->values()->all();

        return view('professionals', [
            'bida' => $bida,
            'user' => $request->user(),
            'plans' => $plans,
            // El plan que se destaca: el del medio con marca blanca, el que más conviene a quien empieza a vender
            'featuredPlan' => collect($plans)->firstWhere('white_label', true)['key'] ?? null,
            'collections' => [
                'lienzo' => ['label' => 'Plantilla en blanco', 'templates' => $templates->where('collection', 'lienzo')->pluck('label')->all()],
                'clasica' => ['label' => 'Plantillas clásicas', 'templates' => $templates->where('collection', 'clasica')->pluck('label')->all()],
                'tematica' => ['label' => 'Temáticas y de temporada', 'templates' => $templates->where('collection', 'tematica')->pluck('label')->all()],
            ],
            'contactUrl' => $whatsapp('Hola {brand}, soy profesional de eventos y quiero saber cómo funcionan los planes para revendedores.'),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                "Invitaciones digitales para profesionales de eventos | {$bida['brand']}",
                'Fotógrafos, organizadores y decoradores: arma invitaciones digitales con tu marca para tus clientes, con un plan mensual y sin comisiones por invitación.',
                ShareMeta::siteImage('profesionales'),
                route('professionals'),
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
                ShareMeta::siteImage('inicio'),
                route('legal', $page),
            ),
        ]);
    }
}
