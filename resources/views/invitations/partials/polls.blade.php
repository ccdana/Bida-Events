@php
    $preguntas = array_values(array_filter($encuestas['preguntas'] ?? [], fn ($poll) => !empty($poll['id'] ?? null) && !empty($poll['opciones'] ?? [])));
    $totalPreguntas = count($preguntas);
@endphp

<section class="inv-section reveal inv-polls" id="encuestas">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'poll',
            'eyebrow' => 'Tu opinión cuenta',
            'title' => $encuestas['titulo'] ?? 'Encuestas',
            'intro' => 'Toca una opción para votar. Verás los resultados al instante.',
        ])

        @forelse($preguntas as $number => $poll)
            @php
                $pollType = $poll['tipo'] ?? 'single';
                $options = array_values($poll['opciones']);
                $isGrid = in_array($pollType, ['rating', 'emoji'], true);
            @endphp
            <div class="inv-poll"
                x-data="pollVoter(@js($poll['id']), @js($pollResults[$poll['id']] ?? array_fill(0, count($options), 0)), @js($options), @js($slug), @js($guestToken), @js($pollType))">
                @if($totalPreguntas > 1)
                    <span class="inv-label">Pregunta {{ $number + 1 }} de {{ $totalPreguntas }}</span>
                @endif
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
            <p class="inv-empty">Pronto habrá preguntas para votar.</p>
        @endforelse
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
