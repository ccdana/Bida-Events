@php use SimpleSoftwareIO\QrCode\Facades\QrCode; @endphp
<section class="invitation-section py-16 px-6 z-10 relative" id="rsvp"
    x-data="rsvpForm('{{ $slug }}', '{{ $guest->qr_code_token }}', {{ $guest->passes_allocated }}, '{{ $guest->status }}', {{ $guest->passes_confirmed }})">
    <div class="max-w-md mx-auto">
        @if($guest->status === 'confirmed')
            {{-- Tarjeta VIP con QR --}}
            <div class="rounded-3xl border-2 border-primary bg-gradient-to-b from-white to-accent/30 p-8 text-center shadow-2xl">
                <p class="text-xs uppercase tracking-[0.2em] text-primary mb-2">Pase VIP</p>
                <p class="font-title text-2xl mb-1">{{ $guest->name }}</p>
                <p class="text-sm opacity-70 mb-6">{{ $rsvp['texto_confirmado'] ?? 'Presenta este QR en la entrada' }}</p>
                <div class="inline-block p-4 bg-white rounded-2xl shadow-inner">
                    {!! QrCode::size(180)->margin(1)->generate($guest->qr_code_token) !!}
                </div>
                <p class="mt-4 text-sm"><strong>{{ $guest->passes_confirmed }}</strong> {{ $guest->passes_confirmed === 1 ? 'persona confirmada' : 'personas confirmadas' }}</p>
            </div>
        @elseif($guest->status === 'declined')
            <div class="text-center py-8 rounded-2xl border border-stone-200 bg-white/50">
                <p class="font-title text-lg opacity-70">{{ $rsvp['texto_declinado'] ?? 'Gracias por avisarnos.' }}</p>
            </div>
        @else
            <div class="rounded-2xl border border-primary/20 bg-white/60 backdrop-blur p-6">
                <h2 class="font-title text-xl text-primary text-center mb-2">{{ $rsvp['titulo_confirmacion'] ?? 'Confirma tu asistencia' }}</h2>
                <p class="text-sm text-center opacity-70 mb-6">Hola <strong>{{ $guest->name }}</strong>, tienes <strong>{{ $guest->passes_allocated }}</strong> {{ $guest->passes_allocated === 1 ? 'pase' : 'pases' }}.</p>

                <div class="flex gap-3 mb-6">
                    <button @click="attending = true" :class="attending === true ? 'bg-primary text-white' : 'border border-primary/30'"
                        class="flex-1 py-3 rounded-xl text-sm transition">¡Asistiré!</button>
                    <button @click="attending = false" :class="attending === false ? 'bg-stone-800 text-white' : 'border border-stone-300'"
                        class="flex-1 py-3 rounded-xl text-sm transition">No podré ir</button>
                </div>

                <div x-show="attending === true" x-cloak class="mb-4">
                    <label class="block text-sm mb-2">¿Cuántas personas asistirán?</label>
                    <select x-model.number="passes" class="w-full rounded-xl border border-primary/30 px-4 py-3">
                        <template x-for="n in maxPasses" :key="n">
                            <option :value="n" x-text="n + (n===1 ? ' persona' : ' personas')"></option>
                        </template>
                    </select>
                    <textarea x-model="dietary" placeholder="Restricciones alimentarias (opcional)" rows="2"
                        class="w-full mt-3 rounded-xl border border-primary/30 px-4 py-3 text-sm"></textarea>
                </div>

                <button @click="submit()" :disabled="attending === null || loading"
                    class="w-full py-3 rounded-xl bg-primary text-white font-medium disabled:opacity-50">
                    <span x-text="loading ? 'Procesando...' : 'Confirmar asistencia'"></span>
                </button>

                <div x-show="qrSvg" x-cloak class="mt-6 text-center" x-html="qrSvg"></div>
            </div>
        @endif
    </div>
</section>
<script>
function rsvpForm(slug, token, maxPasses, status, passesConfirmed) {
    return {
        attending: null, passes: 1, dietary: '', loading: false, qrSvg: null,
        maxPasses: maxPasses,
        async submit() {
            if (this.attending === null) return;
            this.loading = true;
            const res = await fetch(`/p/${slug}/i/${token}/confirm`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({
                    status: this.attending ? 'confirmed' : 'declined',
                    passes_confirmed: this.attending ? this.passes : 0,
                    dietary_restrictions: this.dietary
                })
            });
            const data = await res.json();
            this.loading = false;
            if (data.success && data.status === 'confirmed') {
                window.location.reload();
            } else if (data.success) {
                window.location.reload();
            }
        }
    };
}
</script>
