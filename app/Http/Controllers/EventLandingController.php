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
            // Lo de temporada (tarjetas, Halloween) se vende a su precio mientras dure su temporada
            'season' => self::isSeasonal($page) ? self::seasonFor($landing, HomeController::seasons($request)) : null,
            'demos' => ShowcaseDemos::find(self::demoSlugs($page, $landing)),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                $page['title'],
                $page['description'],
                ShareMeta::siteImage($page['event']),
                route('landing', $landing),
            ),
        ]);
    }

    /** Páginas que se venden por temporada, sin paquetes: las tarjetas y las invitaciones de temporada. */
    public static function isSeasonal(array $page): bool
    {
        return in_array($page['kind'] ?? null, ['card', 'season'], true);
    }

    /**
     * La temporada de esta página, si hoy se vende: cada página muestra solo el precio de su
     * temporada (la de Halloween no depende de que el Día del Amor esté encendido, ni al revés).
     */
    private static function seasonFor(string $landing, array $seasons): ?array
    {
        return collect($seasons)->firstWhere('landing', $landing);
    }

    /** Muestras de la página: las suyas o, en las de temporada, las de la temporada que la tiene de página. */
    public static function demoSlugs(array $page, ?string $landing = null): array
    {
        if (isset($page['demos']) || ! self::isSeasonal($page)) {
            return $page['demos'] ?? [];
        }

        return collect(config('bida.seasons', []))->firstWhere('landing', $landing)['templates'] ?? [];
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
