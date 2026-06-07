<section class="invitation-section py-16 px-6 z-10 relative">
    <div class="max-w-md mx-auto">
        <h2 class="font-title text-2xl text-center text-primary mb-8">{{ $encuestas['titulo'] ?? 'Encuestas' }}</h2>
        <div class="space-y-8">
            @foreach($encuestas['preguntas'] ?? [] as $poll)
                <div x-data="pollVoter('{{ $poll['id'] }}', @js($pollResults[$poll['id']] ?? array_fill(0, count($poll['opciones']), 0)), @js($poll['opciones']), '{{ $slug }}', '{{ $guestToken }}')" class="rounded-2xl border border-primary/20 bg-white/50 p-6">
                    <p class="font-title text-lg mb-4">{{ $poll['pregunta'] }}</p>
                    <div class="space-y-2">
                        <template x-for="(opcion, idx) in options" :key="idx">
                            <button @click="vote(idx)" :disabled="voted"
                                class="w-full text-left px-4 py-3 rounded-xl border transition relative overflow-hidden"
                                :class="voted ? 'border-primary/30' : 'border-primary/20 hover:border-primary'">
                                <div class="absolute inset-0 bg-primary/10 transition-all duration-700" :style="`width:${percentages[idx]}%`"></div>
                                <span class="relative flex justify-between text-sm">
                                    <span x-text="opcion"></span>
                                    <span x-show="voted" x-text="percentages[idx]+'%'" class="text-primary font-medium"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<script>
function pollVoter(pollId, initialPct, options, slug, guestToken) {
    return {
        pollId, options, percentages: initialPct, voted: false,
        async vote(idx) {
            if (this.voted) return;
            const res = await fetch(`/p/${slug}/polls/${pollId}/vote`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ option_index: idx, guest_token: guestToken || null })
            });
            const data = await res.json();
            if (data.success) { this.percentages = data.percentages; this.voted = true; }
        }
    };
}
</script>
