@php
    $preguntas = array_values(array_filter($encuestas['preguntas'] ?? [], fn ($poll) => !empty($poll['id'] ?? null) && !empty($poll['opciones'] ?? [])));
    $totalPreguntas = count($preguntas);
@endphp

{{-- Una pregunta a la vez: se avanza con las flechas o solo al votar --}}
<section class="inv-section reveal inv-polls" id="encuestas"
    x-data="{ step: 0, total: {{ $totalPreguntas }} }"
    @poll-voted="setTimeout(() => { if (step < total - 1) step++ }, 1800)">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'lottie' => 'poll',
            'eyebrow' => $invCopy['polls_eyebrow'] ?? 'Tu opinión cuenta',
            'title' => $encuestas['titulo'] ?? 'Encuestas',
            'intro' => $invCopy['polls_intro'] ?? 'Toca una opción para votar. Verás los resultados al instante.',
        ])

        @forelse($preguntas as $number => $poll)
            @php
                $pollType = $poll['tipo'] ?? 'single';
                $options = array_values($poll['opciones']);
                $isGrid = in_array($pollType, ['rating', 'emoji'], true);
            @endphp
            <div class="inv-poll" data-needs-js
                x-show="step === {{ $number }}" @if($number > 0) x-cloak @endif
                x-transition:enter="inv-poll-enter" x-transition:enter-start="inv-poll-enter-start"
                x-data="pollVoter(@js($poll['id']), @js($pollResults[$poll['id']] ?? array_fill(0, count($options), 0)), @js($options), @js($slug), @js($guestToken), @js($pollType))">
                <h3 class="inv-poll__question">{{ $poll['pregunta'] ?? '' }}</h3>

                <ul class="inv-poll__options {{ $isGrid ? 'inv-poll__options--grid' : '' }}" :aria-busy="loading.toString()">
                    @foreach($options as $index => $option)
                        <li>
                            <button type="button" class="inv-poll__option"
                                :class="{ 'is-selected': selected === {{ $index }}, 'is-voted': voted }"
                                :style="voted ? '--pct:' + (percentages[{{ $index }}] / 100) : ''"
                                :aria-pressed="(selected === {{ $index }}).toString()"
                                :disabled="voted || loading"
                                @click="vote({{ $index }})">
                                <span class="inv-poll__bar" aria-hidden="true"></span>
                                <span class="inv-poll__mark" aria-hidden="true"></span>
                                <span class="inv-poll__text">{{ $option }}</span>
                                <span class="inv-poll__pct" x-show="voted" x-cloak x-text="percentages[{{ $index }}] + '%'"></span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                <p class="inv-status" :class="{ 'is-error': error }" x-text="message" aria-live="polite"></p>
            </div>
        @empty
            <p class="inv-empty">{{ $invCopy['polls_empty'] ?? 'Pronto habrá preguntas para votar.' }}</p>
        @endforelse

        @if($totalPreguntas > 1)
            <nav class="inv-polls__nav" aria-label="Preguntas">
                <button type="button" class="inv-gallery__arrow" @click="step = Math.max(0, step - 1)" :disabled="step === 0" aria-label="Pregunta anterior">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <p class="inv-polls__count" aria-live="polite">
                    <span x-text="step + 1">1</span> de {{ $totalPreguntas }}
                </p>
                <button type="button" class="inv-gallery__arrow" @click="step = Math.min(total - 1, step + 1)" :disabled="step === total - 1" aria-label="Pregunta siguiente">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </nav>
        @endif

        <noscript>
            <p class="inv-noscript">Para votar en las encuestas necesitas activar JavaScript en tu navegador.</p>
        </noscript>
    </div>
</section>
<script>
function pollVoter(pollId, initialPct, options, slug, guestToken, pollType) {
    return {
        pollId,
        options,
        pollType,
        percentages: initialPct,
        voted: false,
        selected: null,
        loading: false,
        message: '',
        error: false,
        async vote(idx) {
            if (this.voted || this.loading) return;

            this.loading = true;
            this.selected = idx;
            this.message = '';
            this.error = false;

            try {
                // Muestra de la home: el voto se suma a los resultados solo en este navegador
                if (window.invDemo) {
                    await new Promise((resolve) => setTimeout(resolve, 350));
                    const counts = this.percentages.map((pct) => pct / 5);
                    const hasVotes = counts.some((count) => count > 0);
                    const tally = counts.map((count, index) => (hasVotes ? count : 0) + (index === idx ? 1 : 0));
                    const total = tally.reduce((sum, count) => sum + count, 0);
                    this.percentages = tally.map((count) => Math.round((count / total) * 100));
                    this.voted = true;
                    this.message = 'Así se ve tu voto. Es una muestra: no se guarda.';
                    this.$dispatch('poll-voted');
                    return;
                }

                const res = await fetch(`/p/${slug}/polls/${pollId}/vote`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    body: JSON.stringify({ option_index: idx, guest_token: guestToken || null })
                });
                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'No pudimos registrar tu voto. Intenta de nuevo.');
                }

                this.percentages = data.percentages;
                this.voted = true;
                this.message = 'Gracias, tu voto quedó registrado.';
                // La sección pasa sola a la siguiente pregunta después de mostrar los resultados
                this.$dispatch('poll-voted');
            } catch (e) {
                this.selected = null;
                this.error = true;
                this.message = e instanceof TypeError ? 'Revisa tu conexión e intenta de nuevo.' : e.message;
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
