<section class="invitation-section relative z-10" id="itinerario"
    x-data="scrollItinerary({{ count($itinerario['eventos'] ?? []) }})"
    x-init="init()">
    <div class="section-inner-wide">
        <header class="text-center mb-14">
            <p class="text-[10px] uppercase tracking-[0.35em] text-primary/60 mb-3">El recorrido de la noche</p>
            <h2 class="font-title text-3xl text-primary">{{ $itinerario['titulo'] ?? 'Itinerario' }}</h2>
        </header>

        <div class="relative" x-ref="container">
            {{-- Línea de progreso fija --}}
            <div class="absolute left-[1.125rem] top-2 bottom-2 w-px bg-primary/15" aria-hidden="true">
                <div class="timeline-progress absolute top-0 left-0 w-full rounded-full" :style="`height:${lineProgress}%`"></div>
            </div>

            <div class="space-y-0">
                @foreach($itinerario['eventos'] ?? [] as $index => $evento)
                    @php $iconName = $evento['icono'] ?? 'star'; @endphp
                    <article class="timeline-step relative pl-12 pb-14 last:pb-4"
                        data-step="{{ $index }}"
                        :class="stepClass({{ $index }})">
                        {{-- Nodo --}}
                        <div class="timeline-node absolute left-2 top-1 w-5 h-5 rounded-full border-2 border-primary/30 bg-[var(--bg-color)] transition-all duration-500 z-10"
                            :class="stepClass({{ $index }}) === 'is-done' || stepClass({{ $index }}) === 'is-active' ? 'scale-110 !bg-primary !border-primary' : ''">
                        </div>

                        {{-- Tarjeta --}}
                        <div class="rounded-2xl border border-primary/10 bg-white/70 backdrop-blur-sm p-5 shadow-sm transition-all duration-500"
                            :class="stepClass({{ $index }}) === 'is-active' ? 'border-primary/40 shadow-md -translate-x-0' : ''">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                                    @include('invitations.partials.icon', ['name' => $iconName, 'class' => 'w-5 h-5'])
                                </div>
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <time class="text-[11px] uppercase tracking-[0.2em] text-primary font-medium">{{ $evento['hora'] }}</time>
                                    <h3 class="font-title text-lg leading-snug mt-1">{{ $evento['titulo'] }}</h3>
                                    @if(!empty($evento['descripcion']))
                                        <p class="text-sm opacity-60 mt-2 leading-relaxed">{{ $evento['descripcion'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
<script>
function scrollItinerary(totalSteps) {
    return {
        activeStep: 0,
        lineProgress: 0,
        init() {
            const steps = this.$el.querySelectorAll('[data-step]');
            const update = () => {
                const rect = this.$refs.container.getBoundingClientRect();
                const vh = window.innerHeight;
                const start = vh * 0.15;
                const end = rect.height - vh * 0.5;
                const scrolled = Math.min(Math.max(start - rect.top, 0), Math.max(end, 1));
                const ratio = end > 0 ? scrolled / end : 0;
                this.lineProgress = Math.min(ratio * 100, 100);
                this.activeStep = Math.min(Math.floor(ratio * totalSteps), totalSteps - 1);

                steps.forEach((el, i) => {
                    const r = el.getBoundingClientRect();
                    const visible = r.top < vh * 0.78 && r.bottom > vh * 0.22;
                    el.classList.toggle('is-active', visible && i === this.activeStep);
                    el.classList.toggle('is-done', i < this.activeStep);
                    el.classList.toggle('is-pending', i > this.activeStep && !visible);
                });
            };
            update();
            window.addEventListener('scroll', update, { passive: true });
            window.addEventListener('resize', update, { passive: true });
        },
        stepClass(index) {
            if (index < this.activeStep) return 'is-done';
            if (index === this.activeStep) return 'is-active';
            return 'is-pending';
        }
    };
}
</script>
