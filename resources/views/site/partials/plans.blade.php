{{--
    Precios de los paquetes (pago único por invitación). Recibe $packages (con su enlace de WhatsApp)
    y $contactUrl. Con la promoción encendida, el precio normal va tachado y una línea dice hasta cuándo.
--}}
@php
    $promoActive = collect($packages)->contains(fn (array $package) => ! empty($package['old_price']));
    $promoEnds = \App\Support\Offers::date(config('bida.launch_promo.ends_at'));
@endphp
<section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="precios-titulo">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <div class="max-w-3xl">
            <h2 id="precios-titulo" class="site-display site-display--md" data-reveal>Elige tu paquete</h2>
            <p class="site-muted-lead" data-reveal>
                Pago único por invitación, en dólares ({{ \App\Support\Money::code() }}). Cada paquete incluye todo lo del anterior.
            </p>
            @if($promoActive)
                <p class="site-promo-line" data-reveal>
                    <span class="site-live-dot" aria-hidden="true"></span>
                    {{ config('bida.launch_promo.label') }}{{ $promoEnds ? ' hasta el '.$promoEnds->locale('es')->translatedFormat('j \d\e F') : '' }}: los precios tachados vuelven después.
                </p>
            @endif
        </div>

        <div class="site-plans mt-14">
            @foreach($packages as $index => $package)
                @php($featured = $package['featured'] ?? false)
                @php($premium = $package['premium'] ?? false)
                <article @class(['site-plan', 'site-plan--featured' => $featured, 'site-plan--premium' => $premium])
                    data-reveal style="--reveal-index: {{ $index }}">
                    <div class="site-plan__head">
                        <h3 class="site-plan__name">
                            {{ $package['name'] }}
                            @if($premium)
                                <x-phosphor-crown-simple-fill class="site-plan__crown" aria-hidden="true" />
                            @endif
                        </h3>
                        @if($featured)
                            <span class="site-plan__tag">Recomendado</span>
                        @elseif($premium)
                            <span class="site-plan__tag">Experiencia completa</span>
                        @endif
                    </div>

                    <p class="site-plan__amount">
                        @if(! empty($package['old_price']))
                            <del class="site-plan__old"><span class="sr-only">Antes </span>{{ \App\Support\Money::format($package['old_price']) }}</del>
                            <span class="sr-only">, ahora</span>
                        @endif
                        <span class="flex items-baseline gap-2">
                            <span class="site-plan__price">{{ $package['final_price'] ?? $package['price'] }}</span>
                            <span class="text-xl text-site-muted">{{ \App\Support\Money::code() }}</span>
                        </span>
                    </p>
                    @if(! empty($package['old_price']))
                        <p class="site-plan__saving">Ahorras {{ \App\Support\Money::format($package['old_price'] - $package['final_price']) }}</p>
                    @endif

                    <p class="mt-4 max-w-[36ch] leading-relaxed text-site-muted">{{ $package['summary'] }}</p>

                    <ul class="site-plan__features">
                        @foreach($package['features'] as $feature)
                            <li>
                                <x-phosphor-check-bold class="site-plan__check" aria-hidden="true" />
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ $package['whatsapp'] }}" target="_blank" rel="noopener"
                        @class([
                            'site-btn site-btn--lg mt-10 justify-center',
                            'site-btn--gold' => $premium,
                            'site-btn--ghost' => ! $featured && ! $premium,
                        ])>
                        Elegir {{ $package['name'] }}
                        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                    </a>
                </article>
            @endforeach
        </div>

        <p class="mt-10 text-site-muted" data-reveal>
            ¿Buscas algo distinto?
            <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="font-medium text-site-ink underline underline-offset-4">Escríbenos</a>
            y armamos un paquete para tu evento.
        </p>
    </div>
</section>
