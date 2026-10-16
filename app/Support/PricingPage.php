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
            'tierNames' => Packages::tierNames(),
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
        $days = (array) config('bida.diy.days_after_event', []);
        $packages = collect(config('bida.packages', []))->keyBy('key');

        $tiers = [[
            'key' => Packages::FREE,
            'name' => __('Gratis'),
            'price' => __('Gratis'),
            'old' => null,
            'suffix' => '',
            'summary' => __('Para invitar a lo simple: la fecha, el lugar y cómo llegar.'),
            'features' => [
                __('Portada con foto, nombre y mensaje'),
                __('Cuenta regresiva y botón para agendar la fecha'),
                __('Ubicación con mapa y cómo llegar'),
                __('En línea hasta :value días después del evento', ['value' => ((int) ($days['gratis'] ?? 7))]),
                __('Con la marca de Bida Events a la vista'),
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
                'suffix' => __(':currency, pago único', ['currency' => Money::code()]),
                'summary' => $package['summary'] ?? '',
                'features' => [
                    ...array_map(fn (string $feature) => str_replace(__('del paquete'), __('del nivel'), $feature), $package['features'] ?? []),
                    __('En línea hasta :value días después del evento', ['value' => ((int) ($days['paid'] ?? 30))]),
                ],
                'featured' => (bool) ($package['featured'] ?? false),
                'premium' => (bool) ($package['premium'] ?? false),
            ];
        }

        return $tiers;
    }

    /**
     * Qué trae cada nivel, fila por fila, para la tabla de comparación (Gratis, Básico, Estándar y
     * Premium).
     *
     * @return list<array{label: string, tiers: array<string, bool>}>
     */
    private static function tierMatrix(): array
    {
        return array_map(fn (array $row) => [
            'label' => __($row['label']),
            'tiers' => collect(Packages::TIERS)->mapWithKeys(fn (string $tier) => [$tier => match (true) {
                isset($row['module']) => Packages::allowsModule($tier, $row['module']),
                isset($row['feature']) => Packages::allows($tier, $row['feature']),
                default => $tier !== Packages::FREE,
            }])->all(),
        ], self::TIER_ROWS);
    }

    /** Las dos formas, comparadas en lo que cambia de verdad (quién arma, cuántas, cómo se paga…). */
    private static function products(array $plans): array
    {
        $fromPlan = collect($plans)->min('final_price');
        $brandFrom = collect($plans)->firstWhere('white_label', true)['name'] ?? null;

        return [
            'columns' => [__('Hazlo tú'), __('Planes')],
            'rows' => [
                [__('¿Quién la arma?'), __('Tú, desde tu panel'), __('Tú, para tus eventos o los de tus clientes')],
                [__('¿Cuántas?'), __('Una por cada pago'), __('Las de tu plan, cada mes')],
                [__('¿Cómo se paga?'), __('Gratis, o un pago único al publicarla'), __('Por mes, por 3 meses o por el año')],
                [__('Desde'), __('Gratis'), $fromPlan !== null ? __(':price al mes', ['price' => Money::format($fromPlan)]) : '—'],
                [__('Tu marca al pie'), __('No'), $brandFrom ? __('Desde :brandFrom', ['brandFrom' => $brandFrom]) : __('No')],
                [__('Ideal para'), __('Tu propio evento'), __('Fotógrafos, salones y organizadores')],
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
            [__('¿Puedo empezar gratis?'), __('Sí. Creas tu cuenta y armas tu invitación con todas las partes que quieras, sin pagar nada. Al publicarla decides: gratis, con la portada, la cuenta regresiva y la ubicación, o con el nivel de las partes que usaste, en un solo pago.')],
            [__('¿Y si después quiero más?'), __('Enciendes las partes que quieras y la subes de nivel: pagas solo la diferencia con el nivel que ya tenías. Tu enlace sigue siendo el mismo.')],
            $breakEven
                ? [__('¿Me conviene un plan o pagar por invitación?'), __('Si armas una o dos, paga cada una. Desde la :from.ª invitación del mes, el plan :plan (:price al mes) sale más barato que pagar cada una con el nivel Estándar (:price2).', ['from' => $breakEven['from'], 'plan' => $breakEven['plan'], 'price' => Money::format($breakEven['price']), 'price2' => Money::format($breakEven['perInvitation'])])]
                : [__('¿Me conviene un plan o pagar por invitación?'), __('Si armas invitaciones para varios eventos cada mes (los tuyos o los de tus clientes), un plan te sale mejor; para tu propio evento, paga solo la tuya.')],
            [__('¿Cuánto tiempo queda en línea mi invitación?'), __('La gratuita, hasta :value días después del evento; la pagada, hasta :value2 días después, para que tus invitados vean las fotos. Con un plan, tus invitaciones publicadas siguen en línea aunque dejes el plan.', ['value' => ((int) ($days['gratis'] ?? 7)), 'value2' => ((int) ($days['paid'] ?? 30))])],
            [__('¿Cómo se paga?'), $prepayText !== ''
                ? __('Con tarjeta de crédito o débito, en dólares, desde cualquier país, o con el QR de cualquier banco de Bolivia, en bolivianos al cambio oficial. Los planes se pagan por mes o varios meses de una vez, con descuento (:discounts).', ['discounts' => $prepayText])
                : __('Con tarjeta de crédito o débito, en dólares, desde cualquier país, o con el QR de cualquier banco de Bolivia, en bolivianos al cambio oficial. Los planes se pagan por mes.')],
            [__('¿Hacen invitaciones a medida?'), __('Si quieres algo a medida, escríbenos por WhatsApp o al correo :email y lo conversamos.', ['email' => config('bida.email')])],
            [__('¿Puedo cambiar de plan o cancelarlo?'), __('Sí. Con tarjeta lo haces tú mismo desde «Mi plan»; con QR, eliges otro plan en tu próximo pago. Lo que ya publicaste sigue en línea.')],
        ]));
    }
}
