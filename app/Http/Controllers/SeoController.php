<?php

namespace App\Http\Controllers;

use App\Support\LegalPages;
use App\Support\Money;
use App\Support\Offers;
use Illuminate\Http\Response;

/**
 * Archivos para buscadores y motores de respuesta (Google, Bing, ChatGPT, Perplexity, Gemini):
 * sitemap.xml y llms.txt. Se arman en cada pedido con la configuración de hoy (dominio, precios con
 * sus descuentos, páginas por evento), así nunca quedan desactualizados respecto del sitio. Se
 * guardan una hora en la caché del navegador y de los rastreadores.
 *
 * robots.txt es estático (public/robots.txt: nginx lo sirve directo y el healthcheck de Docker lo
 * usa); sus Disallow tienen que coincidir con PRIVATE_PATHS, y la prueba SeoResourcesTest lo revisa.
 */
class SeoController extends Controller
{
    /** Rutas que nunca se indexan: datos de clientes e invitados, paneles y la puerta del evento. */
    public const PRIVATE_PATHS = ['/p/', '/muestra/', '/admin/', '/client/', '/puerta/', '/entrada/', '/login', '/dashboard'];

    /** Mapa del sitio: solo las páginas públicas, con la fecha de su última revisión. */
    public function sitemap(): Response
    {
        $today = now()->toDateString();
        $legalUpdated = config('bida.legal.updated_at', $today);

        $urls = collect([
            ['loc' => route('home'), 'lastmod' => $today, 'priority' => '1.0'],
            ['loc' => route('guide'), 'lastmod' => PublicPagesController::GUIDE_UPDATED_AT, 'priority' => '0.8'],
            ['loc' => route('diy'), 'lastmod' => $today, 'priority' => '0.8'],
        ])
            ->merge(collect(config('bida.landings', []))->keys()->map(fn (string $slug) => ['loc' => route('landing', $slug), 'lastmod' => $today, 'priority' => '0.9']))
            ->merge(collect(LegalPages::PAGES)->map(fn (string $page) => ['loc' => route('legal', $page), 'lastmod' => $legalUpdated, 'priority' => '0.3']));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * llms.txt (llmstxt.org): un resumen en Markdown pensado para los modelos de lenguaje que citan
     * el sitio. Dice qué es la marca, qué vende, a qué precio y dónde está cada cosa, en frases
     * cortas que se pueden citar tal cual.
     */
    public function llms(): Response
    {
        $bida = config('bida');

        return $this->text(view('seo.llms', [
            'bida' => $bida,
            'packages' => Offers::packages(),
            'plans' => Offers::resellerPlans(),
            'landings' => HomeController::landingLinks(),
            'currency' => Money::code(),
        ])->render(), 'text/markdown');
    }

    private function text(string $content, string $type = 'text/plain'): Response
    {
        return response($content, 200, [
            'Content-Type' => $type.'; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
