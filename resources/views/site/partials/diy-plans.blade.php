{{--
    Los planes (/precios#planes), con la misma interfaz que los niveles de «Hazlo tú» (components/site/price-card):
    una fila por plan (una columna en pantallas anchas) y, arriba, el selector Mensual / 3 meses / Anual: el
    precio se ve por mes con el descuento de pagar varios meses de una vez, y «Elegir» lleva a pagar ese ciclo.
    Lista corta: el cupo y las tres cosas que decide cada plan.
    Recibe $plans, $featuredPlan, $planCycles (meses => % de descuento) y $contactUrl y, opcionales, $diyTitle y $diyLead.
--}}
@use('App\Support\Money')

@php
    $quotaLabel = fn (array $plan): string => $plan['quota_per_month'] === null ? __('Invitaciones sin tope') : ($plan['quota_per_month'] === 1 ? __('1 invitación al mes') : __(':quota_per_month invitaciones al mes', ['quota_per_month' => $plan['quota_per_month']]));

    // Lo que dice cada plan: el cupo de hoy (la primera línea de la lista es siempre el cupo y se cambia por el de
    // hoy, para que no quede un número viejo; en inglés el texto es otro, por eso no se busca por su texto) y hasta
    // tres de lo que trae. Con el pago en línea, «Elegir» pasa por crear la cuenta y pagar; si no, va a WhatsApp.
    $featuresFor = fn (array $plan): array => [
        $quotaLabel($plan),
        ...collect($plan['features'] ?? [])->slice(1)->take(3)->values()->all(),
    ];

    $cycles = $planCycles ?? [1 => 0];
    $cycleLabels = [1 => __('Mensual'), 3 => __('Cada 3 meses'), 12 => __('Anual')];
@endphp

<section id="planes" class="site-section site-section--surface scroll-mt-20" aria-labelledby="planes-titulo" x-data="{ cycle: 1 }">
    <h2 id="planes-titulo" class="site-display site-display--md" data-reveal>{{ $diyTitle ?? __('Planes') }}</h2>
    <p class="site-muted-lead" data-reveal>
        {{ $diyLead ?? __('Precios por mes, en dólares (:currency). Cada plan suma al anterior.', ['currency' => Money::code()]) }}
    </p>

    {{-- Cada cuánto se paga: el precio de cada plan pasa a ser el de un mes de ese ciclo --}}
    @if(count($cycles) > 1)
        <div class="site-cycle mt-5" role="radiogroup" aria-label="{{ __('Cada cuánto pagas') }}" data-reveal>
            @foreach($cycles as $months => $discount)
                <button type="button" role="radio" @click="cycle = {{ $months }}"
                    :aria-checked="(cycle === {{ $months }}).toString()" aria-checked="{{ $months === 1 ? 'true' : 'false' }}"
                    @class(['site-cycle__option', 'is-active' => $months === 1]) :class="{ 'is-active': cycle === {{ $months }} }">
                    <span class="site-cycle__name">{{ $cycleLabels[$months] ?? __(':months meses', ['months' => $months]) }}</span>
                    @if($discount > 0)
                        <span class="site-cycle__off">{{ __(':discount % menos', ['discount' => $discount]) }}</span>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    <div class="site-plans site-plans--grid mt-5">
        @foreach($plans as $index => $plan)
            @php($featured = $plan['key'] === $featuredPlan)
            {{-- El de más arriba (sin tope de invitaciones) lleva el estilo premium, como el nivel Premium --}}
            @php($premium = $plan['quota_per_month'] === null)
            <x-site.price-card
                :name="$plan['name']"
                :price="$plan['final_price']"
                :old="$plan['old_price'] ? Money::format($plan['old_price']) : null"
                :suffix="__(':currency al mes', ['currency' => Money::code()])"
                :saving="$plan['old_price'] ? __('Ahorras :price al mes', ['price' => Money::format($plan['old_price'] - $plan['final_price'])]) : null"
                :summary="$plan['summary'] ?? ''"
                :features="$featuresFor($plan)"
                :featured="$featured"
                :premium="$premium"
                :tag="$featured ? __('Recomendado') : ($premium ? __('Sin tope') : null)"
                :url="$plan['checkout'] ?? $plan['whatsapp']"
                :external="! $plan['checkout']"
                :cta="__('Elegir :name', ['name' => $plan['name']])"
                :index="$index"
                :reserve="true"
                :cycles="$plan['cycles'] ?? null" />
        @endforeach
    </div>

    {{-- Lo que da tranquilidad para suscribirse: lo publicado no se cae, y el cupo es por publicadas --}}
    <p class="site-note" data-reveal>{{ __('El cupo cuenta lo que publicas; los borradores no. Y si dejas el plan, lo publicado sigue en línea.') }}</p>
</section>
