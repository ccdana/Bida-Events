{{--
    Respuesta del destinatario (App\Modules\Card\ReplyModule): un mensaje corto de vuelta que queda
    en guest_contributions (type = card_reply) y el remitente ve en su panel.
    Recibe $data (titulo, descripcion, placeholder), $page e $invitation.
--}}
@php
    $replyTo = trim((string) ($modulos['dedicatoria']['de'] ?? ''));
@endphp

<section class="inv-section reveal inv-reply" id="respuesta"
    x-data="cardReply(@js($invitation->slug), @js($page->guestToken), @js(! empty($isPreview)))">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'eyebrow' => $replyTo !== '' ? 'Para '.$replyTo : 'Tu respuesta',
            'title' => ($data['titulo'] ?? null) ?: 'Respóndele',
            'intro' => ($data['descripcion'] ?? null) ?: 'Escribe unas palabras: le llegan solo a quien te mandó la carta.',
        ])

        <form class="inv-reply__form" data-needs-js x-show="!sent" @submit.prevent="submit">
            <label class="inv-label" for="reply-message">Tu mensaje</label>
            <textarea id="reply-message" class="inv-input inv-reply__input" rows="4" maxlength="500" x-model="message"
                placeholder="{{ ($data['placeholder'] ?? null) ?: 'Escribe tu respuesta…' }}"></textarea>
            <p class="inv-help"><span x-text="500 - message.length">500</span> caracteres disponibles</p>

            <div class="inv-actions">
                <button type="submit" class="inv-btn inv-btn--block" x-ref="submit" :disabled="sending || !message.trim()">
                    <span x-text="sending ? 'Enviando…' : 'Enviar respuesta'">Enviar respuesta</span>
                </button>
            </div>
        </form>

        {{-- Al enviar, la hoja se dobla en avión de papel y sale volando; alrededor caen pétalos --}}
        <div class="inv-reply__sent" x-show="sent" x-cloak role="status">
            <svg class="inv-reply__plane" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M6 30L58 8 44 56 30 38z"/><path d="M58 8L30 38v14l7-9"/></svg>
            <p class="inv-reply__sent-title">Tu respuesta va en camino</p>
            <p class="inv-reply__sent-text">¡Gracias por escribir!</p>
        </div>
        <p class="inv-status is-error" x-show="error" x-cloak x-text="error" aria-live="assertive"></p>

        <noscript>
            <p class="inv-noscript">Para responder desde aquí necesitas activar JavaScript en tu navegador.</p>
        </noscript>
    </div>
</section>

<script>
function cardReply(slug, guestToken, isPreview) {
    return {
        message: '',
        sending: false,
        sent: false,
        error: '',
        // La celebración sale del botón, antes de que el formulario se oculte
        celebrateSent() {
            const rect = this.$refs.submit?.getBoundingClientRect();

            this.sent = true;
            window.dispatchEvent(new CustomEvent('inv-celebrate', { detail: { rect, amount: 30 } }));
        },

        async submit() {
            if (!this.message.trim() || this.sending) return;

            if (isPreview) {
                this.error = 'Las respuestas no se envían desde la vista previa.';
                return;
            }

            this.sending = true;
            this.error = '';

            // Muestra de la home: se simula el envío, nada se guarda
            if (window.invDemo) {
                setTimeout(() => { this.sending = false; this.celebrateSent(); }, 500);
                return;
            }

            try {
                const response = await fetch(`/p/${slug}/respuesta`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ content_text: this.message.trim(), guest_token: guestToken || null }),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    this.error = data.message || 'No se pudo enviar. Intenta de nuevo.';
                    return;
                }

                this.celebrateSent();
            } catch (e) {
                this.error = 'No se pudo enviar. Revisa tu conexión e intenta de nuevo.';
            } finally {
                this.sending = false;
            }
        },
    };
}
</script>
