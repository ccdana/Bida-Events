<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Payments\InvitationPrice;
use App\Payments\SubscriptionPrice;
use App\Support\DiyInvitations;
use App\Support\DiyPlans;
use App\Support\LeadSource;
use App\Support\Localization;
use App\Support\Money;
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
        // Lo principal: que cada persona arme su invitación gratis («Hazlo tú»); los planes son para quien arma muchas
        $diyFromPrice = (int) collect(Offers::resellerPlans())->min('final_price');
        $demos = ShowcaseDemos::find($bida['demo_invitations'] ?? []);
        $diy = DiyPlans::make($request, LeadSource::HOME);
        $packages = $this->packages($bida['packages'], $whatsapp);
        $startUrl = DiyInvitations::startUrl($request->user());

        return view('home', [
            'bida' => $bida,
            'user' => $request->user(),
            'contactUrl' => $whatsapp(__('Hola {brand}, quiero información sobre las invitaciones digitales.')),
            // «Crear mi invitación»: la cuenta o, con sesión, el editor de «Hazlo tú»
            'startUrl' => $startUrl,
            'plans' => $diy['plans'],
            'collections' => $diy['collections'],
            // Lo adicional: que el equipo la haga (paquetes, pago único)
            'packages' => $packages,
            // Las tres formas, en resumen; el detalle está en /precios
            'pricingSummary' => self::pricingSummary($diy['plans'], $packages, $startUrl),
            'seasons' => $seasons = self::seasons($request),
            'services' => self::services($seasons, $fromPrice, count($demos) > 0),
            'fromPrice' => $fromPrice,
            'diyFromPrice' => $diyFromPrice,
            'demoUrl' => $this->demoUrl($bida['demo_slug'] ?? null),
            'demos' => $demos,
            'designs' => self::designsByEvent($bida, $seasons),
            'landings' => self::landingLinks(),
            'share' => ShareMeta::make(
                __(':brand | Invitaciones digitales para bodas, XV años, bautizos y graduaciones', ['brand' => $bida['brand']]),
                __('Arma tu invitación digital gratis con plantillas profesionales y paga solo si quieres más: confirmación de asistencia, galería y pase QR. O pídenos que la hagamos.'),
                ShareMeta::defaultImage(),
                lroute('home'),
            ),
        ]);
    }

    /**
     * Las tres formas de tener la invitación, en resumen para la portada (Hazlo tú, Te la hacemos y
     * Planes), con su precio de hoy y a dónde lleva cada una; el detalle está en /precios.
     *
     * @return list<array<string, mixed>>
     */
    public static function pricingSummary(array $plans, array $packages, string $startUrl): array
    {
        $tierPrices = InvitationPrice::tierPrices();
        $fromTier = collect($tierPrices)->min('final');
        $cheapestPackage = collect($packages)->sortBy('final_price')->first();
        $cheapestPlan = collect($plans)->sortBy('final_price')->first();
        $prepay = array_filter(SubscriptionPrice::prepayOptions(), fn (int $discount, int $months) => $months > 1 && $discount > 0, ARRAY_FILTER_USE_BOTH);
        $brandFrom = collect($plans)->firstWhere('white_label', true)['name'] ?? null;

        return array_values(array_filter([
            [
                'name' => __('Hazlo tú'),
                'price' => __('Gratis'),
                'suffix' => '',
                'summary' => __('La armas tú y la pruebas completa. Pagas solo si al publicarla quieres más que lo básico.'),
                'features' => array_values(array_filter([
                    __('Gratis con portada, cuenta regresiva y ubicación'),
                    $fromTier !== null ? __('Galería, confirmaciones y pase QR en un solo pago, desde :price', ['price' => Money::format($fromTier)]) : null,
                    __('Subir de nivel cobra solo la diferencia'),
                ])),
                'url' => $startUrl,
                'cta' => __('Crear mi invitación gratis'),
                'featured' => true,
            ],
            $cheapestPackage ? [
                'name' => __('Te la hacemos'),
                'price' => (string) $cheapestPackage['final_price'],
                'old' => $cheapestPackage['old_price'] ? Money::format($cheapestPackage['old_price']) : null,
                'suffix' => __(':currency, pago único', ['currency' => Money::code()]),
                'summary' => __('Nos cuentas tu evento y nuestro equipo arma la invitación contigo.'),
                'features' => $cheapestPackage['service_lines'] ?: [__('Atención por WhatsApp')],
                'url' => lroute('pricing').'#te-la-hacemos',
                'cta' => __('Ver los paquetes'),
            ] : null,
            $cheapestPlan ? [
                'name' => __('Planes'),
                'price' => (string) $cheapestPlan['final_price'],
                'old' => $cheapestPlan['old_price'] ? Money::format($cheapestPlan['old_price']) : null,
                'suffix' => __(':currency al mes', ['currency' => Money::code()]),
                'summary' => __('Para quien arma muchas: las tuyas o las de tus clientes, sin comisión por invitación.'),
                'features' => array_values(array_filter([
                    __('Invitaciones cada mes, con su cupo'),
                    $prepay ? __('Menos pagando :discounts', ['discounts' => Localization::join(array_map(
                        fn (int $discount, int $months) => $months === 12
                            ? __('el año (:discount %)', ['discount' => $discount])
                            : __(':months meses (:discount %)', ['months' => $months, 'discount' => $discount]),
                        $prepay,
                        array_keys($prepay),
                    ), 'o')]) : null,
                    $brandFrom ? __('Tu marca al pie, desde :brandFrom', ['brandFrom' => $brandFrom]) : null,
                ])),
                'url' => lroute('pricing').'#planes',
                'cta' => __('Ver los planes'),
            ] : null,
        ]));
    }

    /** Paquetes con su precio de hoy y su botón de WhatsApp; también los usa cada página por evento. */
    public static function packages(array $packages, \Closure $whatsapp): array
    {
        return collect(Offers::packages($packages))->map(fn (array $package): array => $package + [
            'whatsapp' => $whatsapp(__('Hola {brand}, me interesa el paquete :name (:price) para mi invitación.', ['name' => $package['name'], 'price' => Money::format($package['final_price'])])),
        ])->all();
    }

    /**
     * Las temporadas que se venden hoy, cada una con su WhatsApp (código de su campaña) y su página.
     *
     * @return list<array<string, mixed>>
     */
    public static function seasons(Request $request): array
    {
        return array_values(array_map(fn (array $season) => self::withContact($request, $season), Offers::seasons()));
    }

    private static function withContact(Request $request, array $season): array
    {
        $message = str_replace('{price}', Money::format($season['final_price']), $season['whatsapp']);

        return $season + [
            'whatsappUrl' => LeadSource::whatsappUrl($request, $message, $season['code']),
            'landingUrl' => ! empty($season['landing']) && config("bida.landings.{$season['landing']}") ? lroute('landing', $season['landing']) : null,
        ];
    }

    /** Servicios de la portada (config «bida.services») con su precio de hoy y su enlace. */
    public static function services(array $seasons, int $fromPrice, bool $hasTemplates = true): array
    {
        $cardLanding = collect(config('bida.landings', []))->search(fn (array $landing) => ($landing['kind'] ?? null) === 'card');

        return array_map(function (array $service) use ($seasons, $fromPrice, $cardLanding, $hasTemplates): array {
            $bySeason = ($service['price'] ?? null) === 'season';
            $href = $service['href'] ?? null;

            // Sin plantillas de muestra en la portada, las invitaciones llevan a los precios
            if ($href === '#plantillas' && ! $hasTemplates) {
                $href = lroute('pricing');
            }

            if ($href === 'season') {
                $href = $seasons ? '#temporada' : ($cardLanding ? lroute('landing', $cardLanding) : null);
            }

            // Crear la invitación (la cuenta o el editor), la página de precios (con su ancla) o «Únete»
            $href = match (true) {
                $href === 'create' => DiyInvitations::startUrl(request()->user()),
                $href === 'diy' => lroute('diy'),
                is_string($href) && str_starts_with($href, 'pricing') => lroute('pricing').(str_contains($href, '#') ? '#'.explode('#', $href, 2)[1] : ''),
                default => $href,
            };

            return $service + [
                'fromPrice' => match ($service['price'] ?? null) {
                    'season' => collect($seasons)->min('final_price'),
                    'packages' => $fromPrice,
                    'reseller' => collect(Offers::resellerPlans())->min('final_price'),
                    default => null,
                },
                // «Hazlo tú» empieza gratis: no lleva «Desde», lleva «Gratis»
                'priceLabel' => ($service['price'] ?? null) === 'free' ? __('Gratis') : null,
                // Los planes de profesionales se pagan por mes
                'priceSuffix' => ($service['price'] ?? null) === 'reseller' ? __('al mes') : null,
                'url' => $href,
                // Las temporadas que se venden hoy («Ahora: Halloween»)
                'live' => $bySeason && $seasons ? Localization::join(array_column($seasons, 'name')) : null,
            ];
        }, config('bida.services', []));
    }

    /** Enlaces a las páginas por tipo de evento, para el pie y el enlazado interno. */
    public static function landingLinks(): array
    {
        return collect(config('bida.landings', []))
            ->map(fn (array $landing, string $slug) => ['url' => lroute('landing', $slug), 'label' => $landing['link'], 'slug' => $slug, 'event' => $landing['event']])
            ->values()
            ->all();
    }

    /**
     * Los diseños de cada evento para «Pruébala como invitado»: los mismos de su página (config
     * «landings», en su orden), con los eventos en el orden de las muestras de la portada. Las
     * tarjetas y la temporada que se vende hoy (su página) tienen su propio panel: no se mezclan.
     *
     * @return list<array{event: string, label: string, icon: string, landingUrl: string, landingLabel: string, demos: list<array<string, mixed>>}>
     */
    public static function designsByEvent(array $bida, array $seasons = []): array
    {
        $seasonLandings = array_filter(array_column($seasons, 'landing'));
        $order = array_flip(array_map(fn (array $demo) => $demo['eventKey'], ShowcaseDemos::find($bida['demo_invitations'] ?? [])));

        return collect($bida['landings'] ?? [])
            ->reject(fn (array $landing, string $slug) => ($landing['kind'] ?? null) === 'card' || empty($landing['demos']) || in_array($slug, $seasonLandings, true))
            ->map(function (array $landing, string $slug): ?array {
                $demos = ShowcaseDemos::find($landing['demos']);

                return $demos ? [
                    'event' => $landing['event'],
                    'label' => $demos[0]['event'],
                    'icon' => $demos[0]['icon'],
                    'landingUrl' => lroute('landing', $slug),
                    'landingLabel' => $landing['link'],
                    'demos' => $demos,
                ] : null;
            })
            ->filter()
            ->sortBy(fn (array $group) => $order[$group['event']] ?? PHP_INT_MAX)
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
