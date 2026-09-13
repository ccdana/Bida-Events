@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;

    $maxPasses = max(1, (int) $guest->passes_allocated);
@endphp
<section class="inv-section reveal inv-rsvp" id="rsvp"
    x-data="rsvpForm(@js($slug), @js($guest->qr_code_token), {{ $maxPasses }}, @js($guest->status ?? ''), {{ (int) ($guest->passes_confirmed ?? 0) }})">
    <div class="inv-wrap">

        {{-- Confirmado: pase de entrada --}}
        <div x-show="currentStatus === 'confirmed'" x-cloak>
            <header class="inv-head">
                @include('invitations.partials.lottie-framed-icon', ['name' => 'rsvp'])
                <p class="inv-head__eyebrow">Asistencia confirmada</p>
                <h2 class="inv-head__title" x-text="guestName || @js($guest->name)">{{ $guest->name }}</h2>
                <div class="inv-head__rule" aria-hidden="true"></div>
                <p class="inv-head__intro">{{ $rsvp['texto_confirmado'] ?? 'Presenta este pase en la entrada.' }}</p>
            </header>

            <div class="inv-pass">
                <div class="inv-pass__qr">
                    @if($guest->status === 'confirmed')
                        {!! QrCode::size(200)->margin(1)->generate($guest->qr_code_token) !!}
                    @endif
                    <div x-show="qrSvg" x-cloak x-html="qrSvg"></div>
                </div>

                <dl class="inv-pass__facts">
                    <div>
                        <dt class="inv-label">Personas</dt>
                        <dd x-text="passesConfirmed">{{ $guest->passes_confirmed ?? 1 }}</dd>
                    </div>
                    <div>
                        <dt class="inv-label">Código</dt>
                        <dd>{{ strtoupper(substr($guest->qr_code_token, 0, 8)) }}</dd>
                    </div>
                </dl>
            </div>

            <p class="inv-help inv-rsvp__tip">Toma una captura de pantalla por si no tienes señal en el lugar.</p>
        </div>

        {{-- Declinado --}}
        <div x-show="currentStatus === 'declined'" x-cloak>
            @include('invitations.partials.section-header', [
                'lottie' => 'rsvp',
                'eyebrow' => 'Respuesta enviada',
                'title' => $rsvp['texto_declinado'] ?? 'Gracias por avisarnos',
                'intro' => 'Si cambias de planes, comunícate con la familia para actualizar tu respuesta.',
            ])
        </div>

        {{-- Pendiente: formulario en pasos --}}
        <div x-show="currentStatus === 'pending'">
            @include('invitations.partials.section-header', [
                'lottie' => 'rsvp',
                'eyebrow' => 'Confirma tu asistencia',
                'title' => $rsvp['titulo_confirmacion'] ?? '¿Nos acompañas?',
                'intro' => $rsvp['mensaje_personalizado'] ?? null,
            ])

            <p class="inv-rsvp__greeting">
                Hola <strong>{{ $guest->name }}</strong>, reservamos
                <strong>{{ $maxPasses }} {{ $maxPasses === 1 ? 'lugar' : 'lugares' }}</strong> para ti.
            </p>

            <form class="inv-rsvp__form" @submit.prevent="submit()">
                <fieldset class="inv-rsvp__step">
                    <legend class="inv-rsvp__legend"><span class="inv-rsvp__num">1</span>¿Asistirás?</legend>
                    <div class="inv-rsvp__choices">
                        <button type="button" class="inv-choice" :class="{ 'is-selected': attending === true }"
                            :aria-pressed="(attending === true).toString()" @click="attending = true">Sí, asistiré</button>
                        <button type="button" class="inv-choice" :class="{ 'is-selected': attending === false }"
                            :aria-pressed="(attending === false).toString()" @click="attending = false">No podré ir</button>
                    </div>
                </fieldset>

                <fieldset class="inv-rsvp__step" x-show="attending === true" x-cloak x-transition.opacity>
                    <legend class="inv-rsvp__legend"><span class="inv-rsvp__num">2</span>¿Cuántas personas vendrán?</legend>
                    @if($maxPasses > 1)
                        <div class="inv-stepper">
                            <button type="button" class="inv-stepper__btn" @click="passes = Math.max(1, passes - 1)" :disabled="passes <= 1" aria-label="Una persona menos">−</button>
                            <output class="inv-stepper__value" x-text="passes" aria-live="polite">1</output>
                            <button type="button" class="inv-stepper__btn" @click="passes = Math.min(maxPasses, passes + 1)" :disabled="passes >= maxPasses" aria-label="Una persona más">+</button>
                        </div>
                        <p class="inv-help">Puedes confirmar hasta {{ $maxPasses }} personas con esta invitación.</p>
                    @else
                        <p class="inv-rsvp__note">Esta invitación es para 1 persona.</p>
                    @endif

                    <label class="inv-label inv-rsvp__label" for="rsvp-dietary">Alergias o restricciones alimentarias (opcional)</label>
                    <textarea id="rsvp-dietary" class="inv-input" x-model="dietary" rows="2" maxlength="500" placeholder="Ej. vegetariano, sin gluten"></textarea>
                </fieldset>

                <div class="inv-rsvp__submit">
                    <button type="submit" class="inv-btn inv-btn--block" :disabled="attending === null || loading">
                        <span x-text="loading ? 'Enviando…' : (attending === false ? 'Enviar respuesta' : 'Confirmar asistencia')">Confirmar asistencia</span>
                    </button>
                    <p class="inv-help" x-show="attending === null">Elige una opción para continuar.</p>
                    <p class="inv-status is-error" x-show="error" x-cloak x-text="error" aria-live="assertive"></p>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
function rsvpForm(slug, token, maxPasses, initialStatus, initialPasses) {
    return {
        attending: null,
        passes: 1,
        dietary: '',
        loading: false,
        error: '',
        maxPasses: maxPasses,
        // El estado inicial viene del servidor ('confirmed', 'declined' o vacío = pendiente)
        currentStatus: initialStatus || 'pending',
        passesConfirmed: initialPasses || 0,
        guestName: '',
        qrSvg: null,

        async submit() {
            if (this.attending === null || this.loading) return;
            this.loading = true;
            this.error = '';
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
                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'No pudimos guardar tu respuesta. Intenta de nuevo.');
                }

                this.currentStatus = data.status;
                this.passesConfirmed = data.passes_confirmed || 0;
                this.guestName = data.guest_name || '';
                // El QR llega como SVG en la respuesta, sin recargar la página
                if (data.status === 'confirmed' && data.qr_svg) {
                    this.qrSvg = data.qr_svg;
                }
                this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (e) {
                this.error = e instanceof TypeError ? 'Revisa tu conexión e intenta de nuevo.' : e.message;
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
