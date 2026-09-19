<?php

namespace App\Http\Controllers;

use App\Support\LeadSource;
use App\Support\Offers;
use App\Support\ShareMeta;
use App\Support\ShowcaseDemos;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Páginas por tipo de evento (/invitaciones-de-boda, /tarjetas-dia-del-amor…): sus muestras para
 * probar, lo propio de ese evento, precio, preguntas y WhatsApp con su código de origen.
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
            'fromPrice' => Offers::lowestPackagePrice(),
            // Las tarjetas de temporada se venden a su precio mientras dure la temporada
            'season' => ($page['kind'] ?? null) === 'card' ? HomeController::season($request) : null,
            'demos' => ShowcaseDemos::find(self::demoSlugs($page)),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                $page['title'],
                $page['description'],
                ShareMeta::siteImage($page['event']),
                route('landing', $landing),
            ),
        ]);
    }

    /** Muestras de la página: las suyas o, en las tarjetas, las de la temporada. */
    public static function demoSlugs(array $page): array
    {
        return ($page['kind'] ?? null) === 'card'
            ? ($page['demos'] ?? config('bida.season.templates') ?? [])
            : ($page['demos'] ?? []);
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
