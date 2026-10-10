{{--
    Los planes mensuales de «Hazlo tú» (el modelo principal): una columna por plan y el de una sola
    invitación en su propia fila. Lo usan la portada y la página «Hazlo tú». La comparación punto por
    punto vive solo en la página «Hazlo tú» (diy-compare). Recibe $plans y $featuredPlan
    (App\Support\DiyPlans) y, opcionales, $diyTitle, $diyLead y $diyMoreLink (true: enlace a la
    comparación, para la portada).
--}}
@use('App\Support\Money')

@php
    // El plan de una sola invitación va en su propia fila; los demás, en las columnas
    $single = collect($plans)->first(fn (array $plan) => $plan['quota_per_month'] === 1);
    $monthly = collect($plans)->reject(fn (array $plan) => $plan['quota_per_month'] === 1)->values()->all();
    $quotaLabel = fn (array $plan): string => $plan['quota_per_month'] === null ? 'Invitaciones sin tope' : ($plan['quota_per_month'] === 1 ? '1 invitación al mes' : $plan['quota_per_month'].' invitaciones al mes');
@endphp

<section id="planes" class="scroll-mt-20 border-t border-site-line bg-site-surface" aria-labelledby="planes-titulo">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <h2 id="planes-titulo" class="site-display site-display--md" data-reveal>{{ $diyTitle ?? 'Elige tu plan' }}</h2>
        <p class="site-muted-lead" data-reveal>
            {{ $diyLead ?? 'Precios por mes, en dólares ('.Money::code().'). Cada plan suma al anterior.' }}
        </p>

        <div class="site-plans site-plans--pro mt-14">
            @foreach($monthly as $index => $plan)
                @php($featured = $plan['key'] === $featuredPlan)
                <article @class(['site-plan', 'site-plan--featured' => $featured]) data-reveal style="--reveal-index: {{ $index }}">
                    <div class="site-plan__head">
                        <h3 class="site-plan__name">{{ $plan['name'] }}</h3>
                        @if($featured)
                            <span class="site-plan__tag">Recomendado</span>
                        @endif
                    </div>

                    <p class="site-plan__amount">
                        @if($plan['old_price'])
                            <del class="site-plan__old"><span class="sr-only">Antes </span>{{ Money::format($plan['old_price']) }}</del>
                            <span class="sr-only">, ahora</span>
                        @else
                            {{-- Sin descuento, la línea queda reservada para que los planes se alineen --}}
                            <span class="site-plan__old invisible" aria-hidden="true">&nbsp;</span>
                        @endif
                        <span class="flex items-baseline gap-2">
                            <span class="site-plan__price">{{ $plan['final_price'] }}</span>
                            <span class="text-xl text-site-muted">{{ Money::code() }} al mes</span>
                        </span>
                    </p>

                    <p class="mt-4 max-w-[36ch] leading-relaxed text-site-muted">{{ $plan['summary'] ?? '' }}</p>

                    <ul class="site-plan__features">
                        <li>
                            <x-phosphor-check-bold class="site-plan__check" aria-hidden="true" />
                            <span>{{ $quotaLabel($plan) }}</span>
                        </li>
                        {{-- El cupo sale del plan de hoy; se quita el de la lista para que no quede un número viejo --}}
                        @foreach(collect($plan['features'] ?? [])->reject(fn (string $feature) => preg_match('/^(\d+ invitaci(ón|ones) al mes|Invitaciones sin tope)/iu', $feature)) as $feature)
                            <li>
                                <x-phosphor-check-bold class="site-plan__check" aria-hidden="true" />
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Con el pago en línea, a pagar el plan (pide entrar o crear cuenta); si no, a WhatsApp --}}
                    <a href="{{ $plan['checkout'] ?? $plan['whatsapp'] }}" @unless($plan['checkout']) target="_blank" rel="noopener" @endunless
                        @class(['site-btn site-btn--lg mt-10 justify-center', 'site-btn--ghost' => ! $featured])>
                        Quiero {{ $plan['name'] }}
                        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
                    </a>
                </article>
            @endforeach
        </div>

        @if($single)
            <div class="site-diy-single" data-reveal>
                <div>
                    <p class="site-diy-single__name">¿Solo necesitas una invitación?</p>
                    <p class="site-diy-single__text">Con el plan {{ $single['name'] }} haces una invitación con la plantilla que elijas, para tu evento o para un cliente.</p>
                </div>
                <p class="site-diy-single__price">
                    @if($single['old_price'])
                        <del><span class="sr-only">Antes </span>{{ Money::format($single['old_price']) }}</del>
                    @endif
                    <span>{{ Money::format($single['final_price']) }}</span>
                    <small>por un mes</small>
                </p>
                <a href="{{ $single['checkout'] ?? $single['whatsapp'] }}" @unless($single['checkout']) target="_blank" rel="noopener" @endunless class="site-link-arrow">
                    Quiero el plan {{ $single['name'] }}
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            </div>
        @endif

        {{-- La portada no repite la comparación: manda a la página «Hazlo tú», donde está completa --}}
        @if($diyMoreLink ?? false)
            <p class="mt-10" data-reveal>
                <a href="{{ route('diy') }}#comparar" class="site-link-arrow">
                    Comparar lo que incluye cada plan
                    <x-phosphor-arrow-right aria-hidden="true" />
                </a>
            </p>
        @endif
    </div>
</section>
