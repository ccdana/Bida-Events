<?php

namespace App\Support;

use App\Http\Controllers\HomeController;
use App\Payments\InvitationPrice;
use App\Payments\SubscriptionPrice;
use Illuminate\Http\Request;

/**
 * La página de precios (/precios): las tres formas de tener la invitación, lado a lado.
 *
 * - Hazlo tú: la armas tú, gratis con lo básico, o un pago único por el nivel que usaste
 *   (App\Payments\InvitationPrice). Los niveles son los mismos de «Te la hacemos».
 * - Planes: para quien arma muchas (sus eventos o los de sus clientes), por mes, 3 o 12 meses
 *   (App\Support\DiyPlans).
 * - Te la hacemos: el equipo la arma, con plazo de entrega y rondas de cambios (config «packages»).
 *
 * Todo sale de la configuración de hoy y de Ajustes: ningún precio ni promesa se escribe a mano.
 */
final class PricingPage
{
    /** Código de WhatsApp de la página («Ref. PRECIOS-…»). */
    public const CODE = 'PRECIOS';

    /**
     * Qué tiene cada nivel, fila por fila: un módulo de la invitación («module») o una función
     * («feature», App\Support\Packages::FEATURES). La última fila es la franja de la gratuita.
     */
    private const TIER_ROWS = [
        ['label' => 'Portada con foto, nombre y mensaje', 'module' => 'bienvenida'],
        ['label' => 'Cuenta regresiva y agendar la fecha', 'module' => 'cuenta_regresiva'],
        ['label' => 'Ubicación con mapa y cómo llegar', 'module' => 'ubicacion'],
        ['label' => 'Itinerario del evento', 'module' => 'itinerario'],
        ['label' => 'Música de fondo', 'module' => 'musica'],
        ['label' => 'Galería de fotos y video', 'module' => 'galeria'],
        ['label' => 'Dress code, padrinos y cortejo', 'module' => 'dress_code'],
        ['label' => 'Mesa de regalos', 'module' => 'regalos'],
        ['label' => 'Enlace personal para cada invitado', 'feature' => 'personal_links'],
        ['label' => 'Confirmación por WhatsApp', 'module' => 'rsvp_whatsapp'],
        ['label' => 'Confirmación con pase QR', 'module' => 'rsvp'],
        ['label' => 'Control de entrada en la puerta', 'feature' => 'door'],
        ['label' => 'Encuestas y playlist de tus invitados', 'module' => 'encuestas'],
        ['label' => 'Fotomural y fotos después del evento', 'module' => 'fotomural'],
        ['label' => 'Reportes en PDF y Excel, e invitación para imprimir', 'feature' => 'exports'],
        ['label' => 'Sin la franja de invitación gratuita', 'paid' => true],
    ];

    public static function make(Request $request): array
    {
        $bida = config('bida');
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, self::CODE);
        $diy = DiyPlans::make($request, self::CODE);
        $packages = HomeController::packages($bida['packages'], $whatsapp);
        $tiers = self::diyTiers($request);
        $plans = $diy['plans'];

        return [
            'bida' => $bida,
            'user' => $request->user(),
            'startUrl' => DiyInvitations::startUrl($request->user()),
            'contactUrl' => $whatsapp('Hola {brand}, tengo una duda sobre los precios de las invitaciones.'),
            'diyTiers' => $tiers,
            'plans' => $plans,
            'featuredPlan' => $diy['featuredPlan'],
            'planCycles' => $diy['cycles'],
            'packages' => $packages,
            'tierNames' => Packages::tierNames(),
            'tierMatrix' => self::tierMatrix(),
            'products' => self::products($plans, $packages),
            'faqs' => self::faqs($plans),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                "Precios de las invitaciones digitales | {$bida['brand']}",
                'Arma tu invitación gratis y paga solo si quieres más, elige un plan si armas muchas o pídenos que la hagamos. Precios en dólares, sin letra chica.',
                ShareMeta::defaultImage(),
                route('pricing'),
            ),
        ];
    }

    /**
     * Las columnas de «Hazlo tú»: Gratis y los niveles pagados, con lo que cuestan hoy (pago único)
     * y lo que trae cada uno (lo mismo que el paquete del mismo nombre de «Te la hacemos»).
     */
    private static function diyTiers(Request $request): array
    {
        $prices = InvitationPrice::tierPrices();
        $days = (array) config('bida.diy.days_after_event', []);
        $packages = collect(config('bida.packages', []))->keyBy('key');

        $tiers = [[
            'key' => Packages::FREE,
            'name' => 'Gratis',
            'price' => 'Gratis',
            'old' => null,
            'suffix' => '',
            'summary' => 'Para invitar a lo simple: la fecha, el lugar y cómo llegar.',
            'features' => [
                'Portada con foto, nombre y mensaje',
                'Cuenta regresiva y botón para agendar la fecha',
                'Ubicación con mapa y cómo llegar',
                'En línea hasta '.((int) ($days['gratis'] ?? 7)).' días después del evento',
                'Con la marca de Bida Events a la vista',
            ],
        ]];

        foreach (Packages::ORDER as $tier) {
            if (! isset($prices[$tier])) {
                continue;
            }

            $package = $packages->get($tier, []);
            $tiers[] = [
                'key' => $tier,
                'name' => Packages::tierNames()[$tier] ?? $tier,
                'price' => (string) $prices[$tier]['final'],
                'old' => $prices[$tier]['regular'] ? Money::format($prices[$tier]['regular']) : null,
                'suffix' => Money::code().', pago único',
                'summary' => $package['summary'] ?? '',
                'features' => [
                    ...array_map(fn (string $feature) => str_replace('del paquete', 'del nivel', $feature), $package['features'] ?? []),
                    'En línea hasta '.((int) ($days['paid'] ?? 30)).' días después del evento',
                ],
                'featured' => (bool) ($package['featured'] ?? false),
                'premium' => (bool) ($package['premium'] ?? false),
            ];
        }

        return $tiers;
    }

    /**
     * Qué trae cada nivel, fila por fila, para la tabla de comparación (Gratis, Básico, Estándar y
     * Premium; los mismos en «Hazlo tú» y en «Te la hacemos»).
     *
     * @return list<array{label: string, tiers: array<string, bool>}>
     */
    private static function tierMatrix(): array
    {
        return array_map(fn (array $row) => [
            'label' => $row['label'],
            'tiers' => collect(Packages::TIERS)->mapWithKeys(fn (string $tier) => [$tier => match (true) {
                isset($row['module']) => Packages::allowsModule($tier, $row['module']),
                isset($row['feature']) => Packages::allows($tier, $row['feature']),
                default => $tier !== Packages::FREE,
            }])->all(),
        ], self::TIER_ROWS);
    }

    /** Las tres formas, comparadas en lo que cambia de verdad (quién arma, cuántas, cómo se paga…). */
    private static function products(array $plans, array $packages): array
    {
        $fromPlan = collect($plans)->min('final_price');
        $fromPackage = collect($packages)->min('final_price');
        $brandFrom = collect($plans)->firstWhere('white_label', true)['name'] ?? null;

        return [
            'columns' => ['Hazlo tú', 'Planes', 'Te la hacemos'],
            'rows' => [
                ['¿Quién la arma?', 'Tú, desde tu panel', 'Tú, para tus eventos o los de tus clientes', 'Nuestro equipo, contigo'],
                ['¿Cuántas?', 'Una por cada pago', 'Las de tu plan, cada mes', 'Una por paquete'],
                ['¿Cómo se paga?', 'Gratis, o un pago único al publicarla', 'Por mes, por 3 meses o por el año', 'Un pago único'],
                ['Desde', 'Gratis', $fromPlan !== null ? Money::format($fromPlan).' al mes' : '—', $fromPackage !== null ? Money::format($fromPackage) : '—'],
                ['Tu marca al pie', 'No', $brandFrom ? "Desde {$brandFrom}" : 'No', 'No'],
                ['Ideal para', 'Tu propio evento', 'Fotógrafos, salones y organizadores', 'Quien no tiene tiempo de armarla'],
            ],
        ];
    }

    /**
     * Desde cuántas invitaciones al mes conviene el plan de entrada frente a pagar cada una con el
     * nivel Estándar (el que trae las confirmaciones). null si el plan no gana nunca o gana desde la
     * primera (entonces la comparación no ayuda a decidir).
     */
    public static function planBreakEven(array $plans): ?array
    {
        $entry = collect($plans)->first();
        $perInvitation = InvitationPrice::tierPrice(Packages::STANDARD);

        if (! $entry || $perInvitation <= 0) {
            return null;
        }

        $from = intdiv((int) $entry['final_price'], $perInvitation) + 1;

        return $from >= 2 && ($entry['quota_per_month'] === null || $from <= $entry['quota_per_month'])
            ? ['plan' => $entry['name'], 'from' => $from, 'price' => $entry['final_price'], 'perInvitation' => $perInvitation]
            : null;
    }

    /** Las preguntas de la página: las cuentas salen de los precios de hoy. */
    private static function faqs(array $plans): array
    {
        $days = (array) config('bida.diy.days_after_event', []);
        $breakEven = self::planBreakEven($plans);
        $prepay = array_filter(SubscriptionPrice::prepayOptions(), fn (int $discount, int $months) => $months > 1 && $discount > 0, ARRAY_FILTER_USE_BOTH);
        $prepayText = collect($prepay)->map(fn (int $discount, int $months) => ($months === 12 ? 'el año' : "{$months} meses").": {$discount} % menos")->join(', ', ' y ');

        return array_values(array_filter([
            ['¿Puedo empezar gratis?', 'Sí. Creas tu cuenta y armas tu invitación con todas las partes que quieras, sin pagar nada. Al publicarla decides: gratis, con la portada, la cuenta regresiva y la ubicación, o con el nivel de las partes que usaste, en un solo pago.'],
            ['¿Y si después quiero más?', 'Enciendes las partes que quieras y la subes de nivel: pagas solo la diferencia con el nivel que ya tenías. Tu enlace sigue siendo el mismo.'],
            $breakEven
                ? ['¿Me conviene un plan o pagar por invitación?', "Si armas una o dos, paga cada una. Desde la {$breakEven['from']}.ª invitación del mes, el plan {$breakEven['plan']} (".Money::format($breakEven['price']).' al mes) sale más barato que pagar cada una con el nivel Estándar ('.Money::format($breakEven['perInvitation']).').']
                : ['¿Me conviene un plan o pagar por invitación?', 'Si armas invitaciones para varios eventos cada mes (los tuyos o los de tus clientes), un plan te sale mejor; para tu propio evento, paga solo la tuya.'],
            ['¿Cuánto tiempo queda en línea mi invitación?', 'La gratuita, hasta '.((int) ($days['gratis'] ?? 7)).' días después del evento; la pagada, hasta '.((int) ($days['paid'] ?? 30)).' días después, para que tus invitados vean las fotos. Con un plan, tus invitaciones publicadas siguen en línea aunque dejes el plan.'],
            ['¿Cómo se paga?', 'Con tarjeta de crédito o débito, en dólares, desde cualquier país, o con el QR de cualquier banco de Bolivia, en bolivianos al cambio oficial. Los planes se pagan por mes'.($prepayText !== '' ? ' o varios meses de una vez, con descuento ('.$prepayText.')' : '').'. Los paquetes de «Te la hacemos» se coordinan por WhatsApp.'],
            ['¿Qué diferencia hay entre Hazlo tú y Te la hacemos?', 'Los niveles traen las mismas partes. En «Hazlo tú» la armas tú; en «Te la hacemos» la arma nuestro equipo contigo, con un plazo de entrega y rondas de cambios.'],
            ['¿Puedo cambiar de plan o cancelarlo?', 'Sí. Con tarjeta lo haces tú mismo desde «Mi plan»; con QR, eliges otro plan en tu próximo pago. Lo que ya publicaste sigue en línea.'],
        ]));
    }
}
