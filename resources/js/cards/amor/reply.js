/**
 * Responder la tarjeta con una flor (partials/amor/flower-reply). Envía la flor elegida (reaction)
 * y el mensaje al mismo punto que la respuesta común: POST /p/{slug}/respuesta. En la muestra de la
 * home se simula y en la vista previa del editor no se envía.
 */
import { celebrate, vibrate } from '../../story/effects.js';

export function flowerReply({ slug, guestToken = '', isPreview = false, to = '', flowers = {} } = {}) {
    return {
        flowers,
        reaction: '',
        message: '',
        sending: false,
        sent: false,
        error: '',

        init() {
            this.$watch('reaction', (value) => value && vibrate(6));
        },

        get sentTitle() {
            const flower = this.flowers[this.reaction];
            const who = to ? ` a ${to}` : '';

            return flower ? `${flower.label} va en camino${who}` : `Tu respuesta va en camino${who}`;
        },

        async submit() {
            if ((!this.reaction && !this.message.trim()) || this.sending) {
                return;
            }

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
                    body: JSON.stringify({
                        content_text: this.message.trim() || null,
                        reaction: this.reaction || null,
                        guest_token: guestToken || null,
                    }),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    this.error = data.message || 'No se pudo enviar. Intenta de nuevo.';
                    return;
                }

                this.celebrateSent();
            } catch {
                this.error = 'No se pudo enviar. Revisa tu conexión e intenta de nuevo.';
            } finally {
                this.sending = false;
            }
        },

        // La lluvia sale de la flor elegida (o del botón), antes de que el formulario se oculte
        celebrateSent() {
            const origin = this.$el.querySelector('.inv-bouquet__option.is-chosen') ?? this.$refs.submit;
            const rect = origin?.getBoundingClientRect();

            this.sent = true;
            celebrate({ rect, amount: 34, variant: 'love' });
        },
    };
}
