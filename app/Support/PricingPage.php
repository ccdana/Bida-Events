<?php

namespace App\Support;

use App\Http\Controllers\HomeController;
use App\Payments\InvitationPrice;
use App\Payments\SubscriptionPrice;
use Illuminate\Http\Request;

/**
 * La página de precios (/precios): las formas de tener la invitación, lado a lado.
 *
 * - Hazlo tú: la armas tú, gratis con lo básico, o un pago único por el nivel que usaste
 *   (App\Payments\InvitationPrice).
 * - Planes: para quien arma muchas (sus eventos o los de sus clientes), por mes, 3 o 12 meses
 *   (App\Support\DiyPlans).
 *
 * Lo que se arma a pedido no lleva precio: la página solo avisa que se escribe por WhatsApp o correo.
 * Todo sale de la configuración de hoy y de Ajustes: ningún precio ni promesa se escribe a mano.
 */
final class PricingPage
{
    /** Código de WhatsApp de la página («Ref. PRECIOS-…»). */
    public const CODE = 'PRECIOS';

    /**
     * Qué cambia entre los niveles de pago, fila por fila: un módulo de la invitación («module») o una
     * función («feature», App\Support\Packages::FEATURES). Lo de Gratis (portada, cuenta regresiva y
     * ubicación) está en todos y no necesita fila. «lead» marca lo que se destaca en la lista corta de cada
     * nivel (hasta tres): lo que ese nivel suma al anterior.
     */
    private const TIER_ROWS = [
        ['label' => 'Itinerario y música de fondo', 'module' => 'itinerario', 'lead' => true],
        ['label' => 'Galería de fotos y video', 'module' => 'galeria', 'lead' => true],
        ['label' => 'Dress code, padrinos y regalos', 'module' => 'dress_code', 'lead' => true],
        ['label' => 'Confirmación por WhatsApp', 'module' => 'rsvp_whatsapp', 'lead' => true],
        ['label' => 'Pase QR y control de entrada', 'module' => 'rsvp', 'lead' => true],
        ['label' => 'Encuestas y playlist', 'module' => 'encuestas', 'lead' => true],
        ['label' => 'Fotomural en vivo', 'module' => 'fotomural', 'lead' => true],
        ['label' => 'Reportes e impresión', 'feature' => 'exports'],
    ];

    /**
     * Los textos de las filas de la tabla, para comprobar que lang/en.json los tiene.
     *
     * @return list<string>
     */
    public static function strings(): array
    {
        return array_column(self::TIER_ROWS, 'label');
    }

    public static function make(Request $request): array
    {
        $bida = config('bida');
        $whatsapp = fn (string $message): string => LeadSource::whatsappUrl($request, $message, self::CODE);
        $diy = DiyPlans::make($request, self::CODE);
        $tiers = self::diyTiers($request);
        $plans = $diy['plans'];

        return [
            'bida' => $bida,
            'user' => $request->user(),
            'startUrl' => DiyInvitations::startUrl($request->user()),
            'contactUrl' => $whatsapp(__('Hola {brand}, tengo una duda sobre los precios de las invitaciones.')),
            'diyTiers' => $tiers,
            'plans' => $plans,
            'featuredPlan' => $diy['featuredPlan'],
            'planCycles' => $diy['cycles'],
            // Solo los niveles de pago: lo de Gratis ya está en su fila y es parte de todos
            'tierNames' => array_diff_key(Packages::tierNames(), [Packages::FREE => true]),
            'tierMatrix' => self::tierMatrix(),
            'products' => self::products($plans),
            'faqs' => self::faqs($plans),
            'landings' => HomeController::landingLinks(),
            'share' => ShareMeta::make(
                __('Precios de las invitaciones digitales | :brand', ['brand' => $bida['brand']]),
                __('Arma tu invitación gratis y paga solo si quieres más, o elige un plan si armas muchas. Precios en dólares, sin letra chica.'),
                ShareMeta::defaultImage(),
                lroute('pricing'),
            ),
        ];
    }

    /**
     * Las columnas de «Hazlo tú»: Gratis y los niveles pagados, con lo que cuestan hoy (pago único)
     * y lo que trae cada uno.
     */
    private static function diyTiers(Request $request): array
    {
        $prices = InvitationPrice::tierPrices();
        $packages = collect(config('bida.packages', []))->keyBy('key');

        $tiers = [[
            'key' => Packages::FREE,
            'name' => __('Gratis'),
            'price' => __('Gratis'),
            'old' => null,
            'suffix' => '',
            'summary' => __('Para invitar a lo simple: la fecha, el lugar y cómo llegar.'),
            'features' => [
                __('Portada con foto y mensaje'),
                __('Cuenta regresiva y agendar'),
                __('Ubicación con mapa'),
                __('Con la marca de Bida Events'),
            ],
        ]];

        $previous = Packages::FREE;
        foreach (Packages::ORDER as $tier) {
            if (! isset($prices[$tier])) {
                continue;
            }

            $package = $packages->get($tier, []);
            $highlights = self::highlights($tier, $previous);
            $previous = $tier;
            $tiers[] = [
                'key' => $tier,
                'name' => Packages::tierNames()[$tier] ?? $tier,
                'price' => (string) $prices[$tier]['final'],
                'old' => $prices[$tier]['regular'] ? Money::format($prices[$tier]['regular']) : null,
                'suffix' => __(':currency, pago único', ['currency' => Money::code()]),
                'summary' => $package['summary'] ?? '',
                'features' => $highlights,
                'featured' => (bool) ($package['featured'] ?? false),
                'premium' => (bool) ($package['premium'] ?? false),
            ];
        }

        return $tiers;
    }

    /**
     * La lista corta de un nivel de pago: «Todo lo de» el anterior y hasta tres de las cosas que suma
     * (las filas marcadas «lead»). Sale de los mismos módulos que la comparación, así no se contradicen.
     *
     * @return list<string>
     */
    private static function highlights(string $tier, string $previous): array
    {
        $adds = collect(self::TIER_ROWS)
            ->filter(fn (array $row) => ($row['lead'] ?? false) && self::allows($tier, $row) && ! self::allows($previous, $row))
            ->take(3)
            ->map(fn (array $row) => __($row['label']))
            ->values()
            ->all();

        return [
            __('Todo lo de :tier', ['tier' => Packages::tierNames()[$previous] ?? $previous]),
            ...$adds,
            // De Gratis a Básico lo que cambia es, sobre todo, la franja con la marca de Bida Events
            ...($previous === Packages::FREE ? [__('Sin la franja de invitación gratuita')] : []),
        ];
    }

    private static function allows(string $tier, array $row): bool
    {
        return match (true) {
            isset($row['module']) => Packages::allowsModule($tier, $row['module']),
            isset($row['feature']) => Packages::allows($tier, $row['feature']),
            default => $tier !== Packages::FREE,
        };
    }

    /**
     * Qué trae cada nivel de pago, fila por fila, para la tabla de comparación (Básico, Estándar y Premium).
     *
     * @return list<array{label: string, tiers: array<string, bool>}>
     */
    private static function tierMatrix(): array
    {
        $paid = array_values(array_diff(Packages::TIERS, [Packages::FREE]));

        return array_map(fn (array $row) => [
            'label' => __($row['label']),
            'tiers' => collect($paid)->mapWithKeys(fn (string $tier) => [$tier => self::allows($tier, $row)])->all(),
        ], self::TIER_ROWS);
    }

    /** Las dos formas, comparadas en lo que cambia de verdad: celdas de pocas palabras para que quepan en el celular. */
    private static function products(array $plans): array
    {
        $brandFrom = collect($plans)->firstWhere('white_label', true)['name'] ?? null;

        return [
            'columns' => [__('Hazlo tú'), __('Planes')],
            'rows' => [
                [__('Cuántas'), __('Una por pago'), __('Las de tu cupo')],
                [__('Pago'), __('Único'), __('Mensual o anual')],
                [__('Tu marca'), '—', $brandFrom ? __('Desde :brandFrom', ['brandFrom' => $brandFrom]) : '—'],
                [__('Ideal para'), __('Tu evento'), __('Profesionales')],
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
        $prepayText = SubscriptionPrice::prepaySummary();

        return array_values(array_filter([
            [__('¿Puedo empezar gratis?'), __('Sí. Creas tu cuenta y armas tu invitación sin pagar. Al publicarla decides: gratis con lo básico, o un pago único por el nivel de las partes que usaste.')],
            [__('¿Y si después quiero más?'), __('Subes de nivel y pagas solo la diferencia. Tu enlace sigue siendo el mismo.')],
            $breakEven
                ? [__('¿Me conviene un plan o pagar por invitación?'), __('Si armas una o dos, paga cada una. Desde la :from.ª del mes, el plan :plan (:price al mes) sale más barato que el nivel Estándar por invitación (:price2).', ['from' => $breakEven['from'], 'plan' => $breakEven['plan'], 'price' => Money::format($breakEven['price']), 'price2' => Money::format($breakEven['perInvitation'])])]
                : [__('¿Me conviene un plan o pagar por invitación?'), __('Si armas para varios eventos cada mes, un plan te sale mejor; para tu propio evento, paga solo la tuya.')],
            [__('¿Cuánto tiempo queda en línea mi invitación?'), __('La gratuita, :value días después del evento; la pagada, :value2 días. Con un plan, lo que publicaste sigue en línea aunque lo dejes.', ['value' => ((int) ($days['gratis'] ?? 7)), 'value2' => ((int) ($days['paid'] ?? 30))])],
            [__('¿Cómo se paga?'), ($prepayText !== ''
                ? __('Hazlo tú se paga una vez, al publicar; los planes, por mes o por varios meses de una vez, con descuento (:discounts).', ['discounts' => $prepayText])
                : __('Hazlo tú se paga una vez, al publicar; los planes, por mes.')).' '.PlanTerms::payment()],
            [PlanTerms::question(), PlanTerms::answer()],
        ]));
    }
}
