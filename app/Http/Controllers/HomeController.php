<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Support\LeadSource;
use App\Support\Offers;
use App\Support\ShareMeta;
use App\Support\ShowcaseDemos;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $bida = config('bida');
        // Cada mensaje lleva el código de origen (LeadSource) para saber de qué campaña escribió
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, LeadSource::HOME);
        $fromPrice = Offers::lowestPackagePrice();
        $demos = ShowcaseDemos::find($bida['demo_invitations'] ?? []);

        return view('home', [
            'bida' => $bida,
            'user' => $request->user(),
            'contactUrl' => $whatsapp('Hola {brand}, quiero información sobre las invitaciones digitales.'),
            'packages' => $this->packages($bida['packages'], $whatsapp),
            'season' => $season = self::season($request),
            'services' => self::services($season, $fromPrice, count($demos) > 0),
            'fromPrice' => $fromPrice,
            'demoUrl' => $this->demoUrl($bida['demo_slug'] ?? null),
            'demos' => $demos,
            'landings' => self::landingLinks(),
            'share' => ShareMeta::make(
                "{$bida['brand']} | Invitaciones y tarjetas digitales para tus eventos",
                "Invitaciones digitales para bodas, bautizos, cumpleaños y XV años, con confirmación de asistencia, música, fotos y mapa. Paquetes desde {$fromPrice} Bs.",
                ShareMeta::siteImage('inicio'),
                route('home'),
            ),
        ]);
    }

    /** Paquetes con su precio de hoy y su botón de WhatsApp; también los usa cada página por evento. */
    public static function packages(array $packages, \Closure $whatsapp): array
    {
        return collect(Offers::packages($packages))->map(fn (array $package): array => $package + [
            'whatsapp' => $whatsapp("Hola {brand}, me interesa el paquete {$package['name']} ({$package['final_price']} Bs) para mi invitación."),
        ])->all();
    }

    /** La temporada vigente con su WhatsApp (código de la campaña), o null si ya terminó. */
    public static function season(Request $request): ?array
    {
        $season = Offers::season();

        if (! $season) {
            return null;
        }

        $message = str_replace('{price}', (string) $season['final_price'], $season['whatsapp']);

        return $season + [
            'whatsappUrl' => LeadSource::whatsappUrl($request, $message, $season['code']),
            'landingUrl' => ! empty($season['landing']) && config("bida.landings.{$season['landing']}") ? route('landing', $season['landing']) : null,
        ];
    }

    /** Servicios de la portada (config «bida.services») con su precio de hoy y su enlace. */
    public static function services(?array $season, int $fromPrice, bool $hasTemplates = true): array
    {
        $cardLanding = collect(config('bida.landings', []))->search(fn (array $landing) => ($landing['kind'] ?? null) === 'card');

        return array_map(function (array $service) use ($season, $fromPrice, $cardLanding, $hasTemplates): array {
            $bySeason = ($service['price'] ?? null) === 'season';
            $href = $service['href'] ?? null;

            // Sin plantillas de muestra en la portada, las invitaciones llevan a los precios
            if ($href === '#plantillas' && ! $hasTemplates) {
                $href = '#precios';
            }

            if ($href === 'season') {
                $href = $season ? '#temporada' : ($cardLanding ? route('landing', $cardLanding) : null);
            }

            return $service + [
                'fromPrice' => $bySeason ? ($season['final_price'] ?? null) : (($service['price'] ?? null) === 'packages' ? $fromPrice : null),
                'url' => $href,
                // La temporada que se vende hoy («Ahora: Día del Amor y la Primavera»)
                'live' => $bySeason ? ($season['name'] ?? null) : null,
            ];
        }, config('bida.services', []));
    }

    /** Enlaces a las páginas por tipo de evento, para el pie y el enlazado interno. */
    public static function landingLinks(): array
    {
        return collect(config('bida.landings', []))
            ->map(fn (array $landing, string $slug) => ['url' => route('landing', $slug), 'label' => $landing['link'], 'slug' => $slug, 'event' => $landing['event']])
            ->values()
            ->all();
    }

    /** Enlace a la invitación de ejemplo que se muestra dentro del teléfono de la portada. */
    private function demoUrl(?string $slug): ?string
    {
        if (! $slug) {
            return null;
        }

        // Si la base de datos no responde, la portada muestra una imagen en lugar de la vista previa
        $exists = rescue(
            fn () => Invitation::where('slug', $slug)->where('status', 'active')->exists(),
            false,
            report: false,
        );

        return $exists ? route('invitation.show', $slug) : null;
    }
}
