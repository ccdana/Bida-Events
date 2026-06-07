<section class="invitation-section reveal" id="itinerario"
    x-data="scrollItinerary({{ count($itinerario['eventos'] ?? []) }})"
    x-init="init()">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">El recorrido de la noche</span>
            <h2 class="section-title">{{ $itinerario['titulo'] ?? 'Itinerario' }}</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="relative" x-ref="container">
            <div class="absolute left-[1.125rem] top-2 bottom-2 w-0.5 bg-primary/10 rounded-full" aria-hidden="true">
                <div class="timeline-progress absolute top-0 left-0 w-full rounded-full" :style="`height:${lineProgress}%`"></div>
            </div>

            <div class="space-y-0">
                @foreach($itinerario['eventos'] ?? [] as $index => $evento)
                    @php $iconName = $evento['icono'] ?? 'star'; @endphp
                    <article class="timeline-step relative pl-12 pb-10 last:pb-2"
                        data-step="{{ $index }}"
                        :class="stepClass({{ $index }})">
                        <div class="timeline-node absolute left-2 top-1 w-5 h-5 rounded-full border-2 transition-all duration-700 z-10"
                            style="background: var(--bg-color)"
                            :class="stepClass({{ $index }}) !== 'is-pending' ? 'scale-110 border-primary bg-primary shadow-[0_0_0_4px_color-mix(in_srgb,var(--primary-color)_18%,transparent)]' : 'border-primary/25'">
                        </div>

                        <div class="timeline-card inv-card p-5 transition-all duration-700 ease-out">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 w-11 h-11 rounded-xl inv-card-soft text-primary flex items-center justify-center">
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
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;
                    const i = parseInt(entry.target.dataset.step, 10);
                    this.activeStep = i;
                    this.lineProgress = totalSteps > 1 ? ((i + 1) / totalSteps) * 100 : 100;
                });
            }, { threshold: 0.45, rootMargin: '-12% 0px -22% 0px' });
            steps.forEach(el => observer.observe(el));
        },
        stepClass(index) {
            if (index < this.activeStep) return 'is-done';
            if (index === this.activeStep) return 'is-active';
            return 'is-pending';
        }
    };
}
</script>
