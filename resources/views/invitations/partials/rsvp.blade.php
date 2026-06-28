@php use SimpleSoftwareIO\QrCode\Facades\QrCode; @endphp
<section class="invitation-section reveal" id="rsvp"
    x-data="rsvpForm('{{ $slug }}', '{{ $guest->qr_code_token }}', {{ $guest->passes_allocated }}, '{{ $guest->status ?? '' }}', {{ $guest->passes_confirmed ?? 0 }})">
    <div class="section-inner-wide">

        {{-- Estado: CONFIRMADO --}}
        <div x-show="currentStatus === 'confirmed'" x-cloak class="vip-pass text-center">
            <div class="relative mx-auto max-w-sm">
                <div class="flex items-center justify-center gap-2 mb-2">
                    @include('invitations.partials.icon', ['name' => 'star', 'class' => 'w-5 h-5 text-primary', 'animated' => false])
                    <p class="text-[11px] uppercase tracking-[0.35em] text-primary font-semibold">Tu asistencia confirmada</p>
                    @include('invitations.partials.icon', ['name' => 'star', 'class' => 'w-5 h-5 text-primary', 'animated' => false])
                </div>
                <p class="font-title text-4xl sm:text-5xl mt-4 mb-2" x-text="guestName || '{{ $guest->name }}'"></p>
                <p class="text-base opacity-70 mb-6 max-w-xs mx-auto leading-relaxed">{{ $rsvp['texto_confirmado'] ?? 'Presenta este código en la entrada' }}</p>

                {{-- QR estático: ya estaba confirmado cuando se cargó la página --}}
                @if($guest->status === 'confirmed')
                <div class="vip-pass-qr-wrapper my-8 p-6 rounded-2xl bg-gradient-to-br from-primary/5 to-primary/0 border-2 border-primary/20 inline-block">
                    <div class="vip-pass-qr bg-white p-3 rounded-xl shadow-lg">
                        {!! QrCode::size(200)->margin(1)->generate($guest->qr_code_token) !!}
                    </div>
                </div>
                @endif

                {{-- QR dinámico: recién confirmado en esta sesión (viene del servidor via JSON) --}}
                <div x-show="qrSvg" x-cloak
                    class="vip-pass-qr-wrapper my-8 p-6 rounded-2xl bg-gradient-to-br from-primary/5 to-primary/0 border-2 border-primary/20 inline-block">
                    <div class="vip-pass-qr bg-white p-3 rounded-xl shadow-lg" x-html="qrSvg"></div>
                </div>

                <div class="mt-8 inline-flex items-center gap-3 px-6 py-3 rounded-full inv-card-soft text-sm font-semibold border-2 border-primary/20">
                    @include('invitations.partials.icon', ['name' => 'users', 'class' => 'w-5 h-5 text-primary', 'animated' => false])
                    <span>
                        <strong x-text="passesConfirmed">{{ $guest->passes_confirmed ?? 1 }}</strong>
                        <span x-text="passesConfirmed === 1 ? ' persona confirmada' : ' personas confirmadas'"></span>
                    </span>
                </div>

                <p class="text-xs text-center opacity-50 mt-8">
                    Código de identificación: <code class="font-mono text-primary font-semibold">{{ substr($guest->qr_code_token, 0, 8) }}...</code>
                </p>
            </div>
        </div>

        {{-- Estado: DECLINADO --}}
        <div x-show="currentStatus === 'declined'" x-cloak class="text-center py-12 theme-card rounded-2xl">
            <svg class="w-12 h-12 mx-auto text-stone-400 mb-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="font-title text-2xl text-primary mb-2">{{ $rsvp['texto_declinado'] ?? 'Gracias por avisarnos' }}</p>
            <p class="text-sm opacity-70">Te esperamos en próximas ocasiones.</p>
        </div>

        {{-- Estado: PENDIENTE (formulario) --}}
        <div x-show="currentStatus === 'pending'" class="theme-card p-8 rounded-2xl max-w-md mx-auto">
            <div class="text-center mb-8">
                <svg class="w-12 h-12 text-primary mx-auto mb-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <h2 class="font-title text-2xl text-primary font-semibold mb-2">{{ $rsvp['titulo_confirmacion'] ?? '¿Vendrás?' }}</h2>
                <p class="text-sm opacity-70">Hola <strong>{{ $guest->name }}</strong>, tienes <strong>{{ $guest->passes_allocated }}</strong> {{ $guest->passes_allocated === 1 ? 'pase disponible' : 'pases disponibles' }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <button type="button" @click="attending = true"
                    class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl text-sm font-semibold transition-all duration-200"
                    :class="attending === true ? 'bg-primary text-white shadow-lg scale-105' : 'theme-card-soft opacity-70 hover:opacity-100'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>¡Asistiré!</span>
                </button>
                <button type="button" @click="attending = false"
                    class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl text-sm font-semibold transition-all duration-200 text-white"
                    :class="attending === false ? 'shadow-lg scale-105' : 'opacity-70 hover:opacity-100'"
                    :style="attending === false ? 'background: var(--secondary-color)' : 'background: var(--secondary-color); opacity: 0.5'">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>No podré ir</span>
                </button>
            </div>

            <div x-show="attending === true" x-cloak class="mb-6 space-y-4 animate-fadeIn">
                <div>
                    <label class="block text-sm font-semibold mb-2">¿Cuántas personas asistirán?</label>
                    <select x-model.number="passes" class="w-full rounded-xl border-2 border-primary/20 px-4 py-3 theme-card-soft text-base font-medium focus:border-primary focus:outline-none transition">
                        <template x-for="n in maxPasses" :key="n">
                            <option :value="n" x-text="n + (n===1 ? ' persona' : ' personas')"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2">Restricciones alimentarias (opcional)</label>
                    <textarea x-model="dietary" placeholder="Ej: Sin gluten, vegetariano..." rows="3"
                        class="w-full rounded-xl border-2 border-primary/20 px-4 py-3 text-sm theme-card-soft focus:border-primary focus:outline-none transition resize-none"></textarea>
                </div>
            </div>

            <button type="button" @click="submit()" :disabled="attending === null || loading"
                class="w-full py-4 rounded-xl bg-primary text-white font-semibold text-base disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.98] transition-all duration-200 hover:shadow-lg">
                <span x-show="!loading" x-text="'Confirmar ' + (attending === false ? 'que no asistiré' : 'asistencia')"></span>
                <span x-show="loading" class="inline-flex items-center gap-2">
                    <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Procesando...
                </span>
            </button>
        </div>

    </div>
</section>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn { animation: fadeIn 0.3s ease-out; }
</style>

<script>
function rsvpForm(slug, token, maxPasses, initialStatus, initialPasses) {
    return {
        attending: null,
        passes: 1,
        dietary: '',
        loading: false,
        maxPasses: maxPasses,
        // El estado inicial viene del servidor (puede ser 'confirmed', 'declined', o '' = pending)
        currentStatus: initialStatus || 'pending',
        passesConfirmed: initialPasses || 0,
        guestName: '',
        qrSvg: null,

        async submit() {
            if (this.attending === null) return;
            this.loading = true;
            try {
                const res = await fetch(`/p/${slug}/i/${token}/confirm`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        status: this.attending ? 'confirmed' : 'declined',
                        passes_confirmed: this.attending ? this.passes : 0,
                        dietary_restrictions: this.dietary
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.currentStatus = data.status;
                    this.passesConfirmed = data.passes_confirmed || 0;
                    this.guestName = data.guest_name || '';
                    // Mostrar el QR SVG que viene en la respuesta JSON (sin recargar página)
                    if (data.status === 'confirmed' && data.qr_svg) {
                        this.qrSvg = data.qr_svg;
                    }
                    // Scroll suave al inicio de la sección para que se vea el resultado
                    this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } catch (e) {
                console.error('RSVP error:', e);
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
