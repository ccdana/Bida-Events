{{--
    Los planes de «Únete» (el modelo principal), con la misma interfaz que los paquetes de «Te la
    hacemos» (components/site/price-card): una columna por plan y, arriba, el selector Mensual /
    3 meses / Anual: el precio se ve por mes con el descuento de pagar varios meses de una vez, y
    «Elegir» lleva a pagar ese ciclo. Lo usan la portada y la página «Únete».
    Recibe $plans, $featuredPlan, $planCycles (meses => % de descuento) y $contactUrl y, opcionales,
    $diyTitle, $diyLead, $diyDetail (true: suma a cada plan lo que trae: plantillas, confirmación y
    marca) y $diyMoreLink (true: enlace al detalle, para la portada).
--}}
@use('App\Support\Money')

@php
    $quotaLabel = fn (array $plan): string => $plan['quota_per_month'] === null ? __('Invitaciones sin tope') : ($plan['quota_per_month'] === 1 ? __('1 invitación al mes') : __(':quota_per_month invitaciones al mes', ['quota_per_month' => $plan['quota_per_month']]));
    $detailed = (bool) ($diyDetail ?? false);

    // Lo que dice cada plan: el cupo de hoy (se quita el de la lista para que no quede un número viejo)
    // y, en el detalle, lo que desbloquea que la lista no nombra. Con el pago en línea, «Elegir» pasa
    // por crear la cuenta y pagar; si no, va a WhatsApp.
    $featuresFor = function (array $plan) use ($quotaLabel, $detailed): array {
        $features = collect($plan['features'] ?? [])
            ->reject(fn (string $feature) => preg_match('/^(\d+ invitaci(ón|ones) al mes|Invitaciones sin tope)/iu', $feature))
            ->values()
            ->all();
        $list = [$quotaLabel($plan), ...$features];

        if ($detailed) {
            $mentions = fn (string $word): bool => collect($list)->contains(fn (string $line) => mb_stripos($line, $word) !== false);

            $list[] = __(':templates_count plantillas para elegir', ['templates_count' => $plan['templates_count']]);

            if (in_array('whatsapp', $plan['rsvp'] ?? [], true) && ! $mentions('WhatsApp')) {
                $list[] = __('Confirmación de asistencia por WhatsApp');
            }
            if (in_array('pass', $plan['rsvp'] ?? [], true) && ! $mentions('QR')) {
                $list[] = __('Pase QR y control de entrada');
            }
            if (($plan['white_label'] ?? false) && ! $mentions('marca')) {
                $list[] = __('Tu marca al pie de cada invitación');
            }
        }

        return $list;
    };
@endphp

@php
    $cycles = $planCycles ?? [1 => 0];
    $cycleLabels = [1 => __('Mensual'), 3 => __('Cada 3 meses'), 12 => __('Anual')];
@endphp

<section id="planes" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="planes-titulo" x-data="{ cycle: 1 }">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <div class="max-w-3xl">
            <h2 id="planes-titulo" class="site-display site-display--md" data-reveal>{{ $diyTitle ?? __('Elige tu plan') }}</h2>
            <p class="site-muted-lead" data-reveal>
                {{ $diyLead ?? __('Precios por mes, en dólares (:currency). Cada plan suma al anterior.', ['currency' => Money::code()]) }}
            </p>
        </div>

        {{-- Cada cuánto se paga: el precio de cada plan pasa a ser el de un mes de ese ciclo --}}
        @if(count($cycles) > 1)
            <div class="site-cycle mt-10" role="radiogroup" aria-label="{{ __('Cada cuánto pagas') }}" data-reveal>
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

        <div data-carousel @class(['site-plans site-plans--grid', 'mt-14' => count($cycles) <= 1, 'mt-8' => count($cycles) > 1])>
            @foreach($plans as $index => $plan)
                @php($featured = $plan['key'] === $featuredPlan)
                {{-- El de más arriba (sin tope de invitaciones) lleva el estilo premium, como el paquete Premium --}}
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

            {{-- Con un número de planes que no llena la fila, la celda que sobra es para quien necesita algo distinto --}}
            @if(count($plans) % 3 !== 0)
                <div class="site-plan site-plan--note" data-reveal style="--reveal-index: {{ count($plans) }}">
                    <p class="site-plan__name">{{ __('¿Necesitas algo distinto?') }}</p>
                    <p class="mt-4 max-w-[36ch] leading-relaxed text-site-muted">{{ __('Cuéntanos qué organizas y vemos el plan que te conviene.') }}</p>
                    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-link-arrow mt-6">
                        {{ __('Escríbenos') }}
                        <x-phosphor-arrow-right aria-hidden="true" />
                    </a>
                </div>
            @endif
        </div>

        {{-- Lo que da tranquilidad para suscribirse: lo publicado no se cae, y el cupo es por publicadas --}}
        <div class="mt-10 flex flex-col gap-x-10 gap-y-3 text-site-muted md:flex-row md:items-baseline md:justify-between" data-reveal>
            <p class="max-w-[62ch] leading-relaxed">
                {{ __('El cupo cuenta las invitaciones que publicas: los borradores no lo gastan. Y si dejas el plan, lo que ya publicaste sigue en línea.') }}
            </p>
            @if(count($plans) % 3 === 0)
                <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-link-arrow shrink-0">
                    {{ __('¿Necesitas algo distinto? Escríbenos') }}
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            @endif
        </div>

        {{-- La portada no repite el detalle: manda a la página «Únete», donde cada plan trae más --}}
        @if($diyMoreLink ?? false)
            <p class="mt-10" data-reveal>
                <a href="{{ lroute('diy') }}#planes" class="site-link-arrow">
                    {{ __('Ver el detalle de cada plan') }}
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            </p>
        @endif
    </div>
</section>
