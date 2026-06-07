<section class="invitation-section relative z-10">
    <div class="section-inner-wide">
        <header class="text-center mb-10">
            @include('invitations.partials.icon', ['name' => 'poll', 'class' => 'w-8 h-8 text-primary mx-auto mb-4'])
            <h2 class="font-title text-3xl text-primary">{{ $encuestas['titulo'] ?? 'Encuestas' }}</h2>
            <p class="text-sm opacity-50 mt-2">Tu opinión cuenta — elige cómo prefieres votar</p>
        </header>

        <div class="space-y-12">
            @foreach($encuestas['preguntas'] ?? [] as $poll)
                <div x-data="pollVoter('{{ $poll['id'] }}', @js($pollResults[$poll['id']] ?? array_fill(0, count($poll['opciones']), 0)), @js($poll['opciones']), '{{ $slug }}', '{{ $guestToken }}')"
                    class="rounded-3xl border border-primary/15 bg-white/60 backdrop-blur-sm overflow-hidden">

                    <div class="p-6 pb-4">
                        <p class="font-title text-lg leading-snug">{{ $poll['pregunta'] }}</p>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex gap-1 p-0.5 rounded-full bg-primary/5 border border-primary/10">
                                <button type="button" @click="mode='list'" class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider transition"
                                    :class="mode==='list' ? 'bg-primary text-white' : 'opacity-50'">Lista</button>
                                <button type="button" @click="mode='cards'" class="px-3 py-1 rounded-full text-[10px] uppercase tracking-wider transition"
                                    :class="mode==='cards' ? 'bg-primary text-white' : 'opacity-50'">Tarjetas</button>
                            </div>
                            <span class="text-[10px] uppercase tracking-wider opacity-40" x-show="voted" x-cloak>Voto registrado</span>
                        </div>
                    </div>

                    {{-- Modo lista --}}
                    <div x-show="mode==='list'" class="px-6 pb-6 space-y-2">
                        <template x-for="(opcion, idx) in options" :key="'list-'+idx">
                            <button type="button" @click="vote(idx)" :disabled="voted"
                                class="w-full text-left rounded-xl border px-4 py-3.5 transition-all duration-300 relative overflow-hidden group active:scale-[0.99]"
                                :class="[
                                    voted && selected === idx ? 'border-primary ring-2 ring-primary/20' : 'border-primary/15 hover:border-primary/40',
                                    voted ? 'cursor-default' : 'cursor-pointer'
                                ]">
                                <div class="absolute inset-y-0 left-0 bg-primary/12 transition-all duration-700 ease-out" :style="`width:${percentages[idx]}%`"></div>
                                <span class="relative flex items-center justify-between gap-3 text-sm">
                                    <span class="flex items-center gap-3">
                                        <span class="w-6 h-6 rounded-full border flex items-center justify-center shrink-0 transition-all duration-300"
                                            :class="voted && selected === idx ? 'bg-primary border-primary text-white scale-110' : 'border-primary/30'">
                                            <span x-show="voted && selected === idx" x-cloak>@include('invitations.partials.icon', ['name' => 'check', 'class' => 'w-3 h-3', 'animated' => false])</span>
                                        </span>
                                        <span x-text="opcion"></span>
                                    </span>
                                    <span x-show="voted" x-text="percentages[idx]+'%'" class="text-primary font-semibold tabular-nums" x-cloak></span>
                                </span>
                            </button>
                        </template>
                    </div>

                    {{-- Modo tarjetas (tap para votar) --}}
                    <div x-show="mode==='cards'" x-cloak class="px-6 pb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <template x-for="(opcion, idx) in options" :key="'card-'+idx">
                                <button type="button" @click="vote(idx)" :disabled="voted"
                                    class="relative rounded-2xl border p-4 min-h-[5.5rem] flex flex-col justify-end text-left transition-all duration-300 active:scale-95 overflow-hidden"
                                    :class="voted && selected === idx ? 'border-primary bg-primary/10 shadow-md' : 'border-primary/15 bg-white/80 hover:border-primary/35'">
                                    <div x-show="voted" class="absolute top-3 right-3 text-primary font-bold text-sm tabular-nums" x-text="percentages[idx]+'%'" x-cloak></div>
                                    <span class="text-sm font-medium leading-snug relative" x-text="opcion"></span>
                                    <div x-show="voted" class="mt-2 h-1 rounded-full bg-primary/15 overflow-hidden" x-cloak>
                                        <div class="h-full bg-primary rounded-full transition-all duration-700" :style="`width:${percentages[idx]}%`"></div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
<script>
function pollVoter(pollId, initialPct, options, slug, guestToken) {
    return {
        pollId, options, percentages: initialPct, voted: false, selected: null, mode: 'list',
        async vote(idx) {
            if (this.voted) return;
            const res = await fetch(`/p/${slug}/polls/${pollId}/vote`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ option_index: idx, guest_token: guestToken || null })
            });
            const data = await res.json();
            if (data.success) {
                this.percentages = data.percentages;
                this.selected = idx;
                this.voted = true;
            }
        }
    };
}
</script>
