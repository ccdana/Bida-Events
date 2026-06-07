@php use SimpleSoftwareIO\QrCode\Facades\QrCode; @endphp
<section class="invitation-section reveal" id="rsvp"
    x-data="rsvpForm('{{ $slug }}', '{{ $guest->qr_code_token }}', {{ $guest->passes_allocated }}, '{{ $guest->status }}', {{ $guest->passes_confirmed }})">
    <div class="section-inner-wide">
        @if($guest->status === 'confirmed')
            <div class="vip-pass">
                <div class="relative">
                    <div class="flex items-center justify-center gap-2 mb-1">
                        @include('invitations.partials.icon', ['name' => 'star', 'class' => 'w-4 h-4 text-primary', 'animated' => false])
                        <p class="text-[10px] uppercase tracking-[0.3em] text-primary">Pase confirmado</p>
                        @include('invitations.partials.icon', ['name' => 'star', 'class' => 'w-4 h-4 text-primary', 'animated' => false])
                    </div>
                    <p class="font-title text-2xl sm:text-3xl mt-2">{{ $guest->name }}</p>
                    <p class="text-sm opacity-60 mt-2 mb-6 max-w-xs mx-auto">{{ $rsvp['texto_confirmado'] ?? 'Presenta este código en la entrada' }}</p>

                    <div class="vip-pass-qr">
                        {!! QrCode::size(168)->margin(0)->generate($guest->qr_code_token) !!}
                    </div>

                    <div class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 rounded-full inv-card-soft text-sm">
                        @include('invitations.partials.icon', ['name' => 'users', 'class' => 'w-4 h-4 text-primary', 'animated' => false])
                        <span><strong>{{ $guest->passes_confirmed }}</strong> {{ $guest->passes_confirmed === 1 ? 'persona confirmada' : 'personas confirmadas' }}</span>
                    </div>
                </div>
            </div>
        @elseif($guest->status === 'declined')
            <div class="text-center py-10 inv-card">
                <p class="font-title text-lg opacity-70">{{ $rsvp['texto_declinado'] ?? 'Gracias por avisarnos.' }}</p>
            </div>
        @else
            <div class="inv-card p-6">
                <h2 class="font-title text-xl text-primary text-center mb-2">{{ $rsvp['titulo_confirmacion'] ?? 'Confirma tu asistencia' }}</h2>
                <p class="text-sm text-center opacity-70 mb-6">Hola <strong>{{ $guest->name }}</strong>, tienes <strong>{{ $guest->passes_allocated }}</strong> {{ $guest->passes_allocated === 1 ? 'pase' : 'pases' }}.</p>

                <div class="flex gap-3 mb-6">
                    <button type="button" @click="attending = true"
                        class="flex-1 py-3 rounded-xl text-sm transition inv-card-soft"
                        :class="attending === true ? 'bg-primary text-white shadow-md' : ''">¡Asistiré!</button>
                    <button type="button" @click="attending = false"
                        class="flex-1 py-3 rounded-xl text-sm transition inv-card-soft text-white"
                        :class="attending === false ? 'shadow-md' : ''"
                        :style="attending === false ? 'background: var(--secondary-color)' : ''">No podré ir</button>
                </div>

                <div x-show="attending === true" x-cloak class="mb-4">
                    <label class="block text-sm mb-2">¿Cuántas personas asistirán?</label>
                    <select x-model.number="passes" class="w-full rounded-xl border border-primary/20 px-4 py-3 inv-card-soft">
                        <template x-for="n in maxPasses" :key="n">
                            <option :value="n" x-text="n + (n===1 ? ' persona' : ' personas')"></option>
                        </template>
                    </select>
                    <textarea x-model="dietary" placeholder="Restricciones alimentarias (opcional)" rows="2"
                        class="w-full mt-3 rounded-xl border border-primary/20 px-4 py-3 text-sm inv-card-soft"></textarea>
                </div>

                <button type="button" @click="submit()" :disabled="attending === null || loading"
                    class="w-full py-3.5 rounded-xl bg-primary text-white font-medium disabled:opacity-50 active:scale-[0.98] transition-transform">
                    <span x-text="loading ? 'Procesando...' : 'Confirmar asistencia'"></span>
                </button>
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
            if (data.success) window.location.reload();
        }
    };
}
</script>
