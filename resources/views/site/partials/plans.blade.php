{{-- Precios. Recibe $packages (con su enlace de WhatsApp) y $contactUrl. --}}
<section id="precios" class="scroll-mt-20 border-t border-site-line bg-site-surface">
    <div class="mx-auto max-w-7xl px-5 py-20 lg:px-8 lg:py-28">
        <p class="text-[0.95rem] font-medium text-site-accent" data-reveal>Precios</p>
        <h2 class="mt-4 text-3xl font-semibold leading-[1.1] tracking-tight md:text-5xl" data-reveal>Elige tu paquete</h2>
        <p class="mt-5 max-w-[52ch] text-lg leading-relaxed text-site-muted" data-reveal>
            Pago único por invitación, en bolivianos. Cada paquete incluye todo lo del anterior.
        </p>

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

                    <p class="mt-7 flex items-baseline gap-2">
                        <span class="site-plan__price">{{ $package['price'] }}</span>
                        <span class="text-xl text-site-muted">Bs</span>
                    </p>

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
