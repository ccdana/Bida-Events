<?php

namespace App\Http\Controllers;

use App\Support\LeadSource;
use App\Support\ShareMeta;
use App\Support\ShowcaseDemos;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas por tipo de evento (/invitaciones-de-boda, /invitaciones-xv-anos…): una muestra
 * embebida, lo propio de ese evento, preguntas y WhatsApp con su código de origen.
 * El contenido vive en config('bida.landings').
 */
class EventLandingController extends Controller
{
    public function __invoke(Request $request, string $landing): View
    {
        $page = config("bida.landings.{$landing}");
        abort_unless(is_array($page), 404);

        $bida = config('bida');
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, $page['code']);

        return view('landing', [
            'bida' => $bida,
            'user' => $request->user(),
            'slug' => $landing,
            'page' => $page,
            'contactUrl' => $whatsapp($page['whatsapp']),
            'packages' => HomeController::packages($bida['packages'], $whatsapp),
            'demo' => ShowcaseDemos::find([$page['demo']])[0] ?? null,
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                $page['title'],
                $page['description'],
                ShareMeta::siteImage($page['event']),
                route('landing', $landing),
            ),
        ]);
    }

    /** Mapa del sitio con las páginas públicas que sí deben aparecer en buscadores. */
    public function sitemap()
    {
        $urls = collect([route('home')])
            ->merge(collect(config('bida.landings', []))->keys()->map(fn (string $slug) => route('landing', $slug)));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
