/**
 * Diente de león para pedir un deseo (partials/amor/wish). Se sopla:
 * - deslizando hacia arriba sobre la flor (se va entero) o con el botón «Soplar»;
 * - tocando la flor (se sueltan unas pocas semillas);
 * - con el micrófono, si la persona lo permite: se mide el volumen del soplido y se sueltan
 *   semillas según la fuerza. El micrófono se apaga al terminar, al tocar de nuevo o a los 8 s.
 * Cuando quedan pocas semillas, se van todas y aparece el deseo. Es una puerta del modo historia.
 */
import { celebrate, prefersReducedMotion, vibrate } from '../../story/effects.js';

const SWIPE_UP = 40;
const FINISH_AT = 0.3;
const MIC_THRESHOLD = 0.12;
const MIC_LIMIT_MS = 8000;

const insideEditor = () => window.self !== window.top && !window.invDemo;
const random = (min, max) => min + Math.random() * (max - min);

const MIC_MESSAGES = {
    blocked: 'El micrófono está bloqueado para esta página. Actívalo en los permisos del navegador (el candado junto a la dirección) o desliza hacia arriba para soplar.',
    dismissed: 'No se dio permiso para usar el micrófono. Tócalo otra vez y elige «Permitir», o desliza hacia arriba para soplar.',
    missing: 'No encontramos un micrófono en este dispositivo. Desliza hacia arriba para soplar.',
    busy: 'Otra aplicación está usando el micrófono. Ciérrala e inténtalo de nuevo, o desliza hacia arriba.',
    unsupported: 'Este navegador no deja usar el micrófono aquí. Desliza hacia arriba para soplar.',
};

/** Estado del permiso sin pedirlo: 'granted', 'denied', 'prompt' o null si el navegador no lo dice. */
async function micPermission() {
    try {
        const status = await navigator.permissions?.query({ name: 'microphone' });

        return status?.state ?? null;
    } catch {
        return null;
    }
}

/** Qué pasó al pedir el micrófono, en palabras de quien lo usa. */
async function micErrorMessage(error) {
    switch (error?.name) {
        case 'NotAllowedError':
        case 'PermissionDeniedError':
            // Quedó bloqueado, o la persona cerró el aviso sin elegir (se puede volver a pedir)
            return await micPermission() === 'denied' ? MIC_MESSAGES.blocked : MIC_MESSAGES.dismissed;
        case 'NotFoundError':
        case 'DevicesNotFoundError':
        case 'OverconstrainedError':
            return MIC_MESSAGES.missing;
        case 'NotReadableError':
        case 'TrackStartError':
        case 'AbortError':
            return MIC_MESSAGES.busy;
        default:
            return MIC_MESSAGES.unsupported;
    }
}

export function dandelionWish() {
    let seeds = [];
    let remaining = [];
    let gesture = null;
    let mic = null;
    let finishing = false;

    return {
        blown: false,
        listening: false,
        micError: '',
        // Solo con HTTPS (o en el propio equipo) el navegador ofrece el micrófono
        micAvailable: Boolean(navigator.mediaDevices?.getUserMedia) && window.isSecureContext,
        level: 0,

        init() {
            seeds = [...this.$el.querySelectorAll('[data-seed]')];
            remaining = [...seeds];
            this.$el.addEventListener('story-open', () => this.blowAll());

            if (insideEditor()) {
                this.blown = true;
            }
        },

        press(event) {
            gesture = { y: event.clientY };

            try {
                this.$refs.flower.setPointerCapture(event.pointerId);
            } catch {
                // Punteros sintéticos no admiten captura
            }
        },

        release(event) {
            if (!gesture) {
                return;
            }

            const dy = event.clientY - gesture.y;
            gesture = null;

            dy <= -SWIPE_UP ? this.blowAll() : this.blow(4);
        },

        cancelPress() {
            gesture = null;
        },

        /** Suelta `count` semillas al azar; `strength` las lleva más lejos. */
        blow(count, strength = 1) {
            if (this.blown || remaining.length === 0) {
                return;
            }

            const reduced = prefersReducedMotion();

            for (let i = 0; i < count && remaining.length; i++) {
                const seed = remaining.splice(Math.floor(Math.random() * remaining.length), 1)[0];

                seed.style.setProperty('--dx', `${Math.round(random(-40, 170) * strength)}px`);
                seed.style.setProperty('--dy', `${Math.round(-random(180, 380) * strength)}px`);
                seed.style.setProperty('--wobble', `${Math.round(random(-30, 30))}px`);
                seed.style.setProperty('--spin', `${Math.round(random(-160, 160))}deg`);
                seed.style.setProperty('--dur', `${reduced ? 0.4 : random(2.4, 4.2).toFixed(2)}s`);
                seed.style.setProperty('--delay', `${Math.round(random(0, 260))}ms`);
                seed.classList.add('is-flying');
            }

            vibrate(6);

            if (remaining.length / seeds.length <= FINISH_AT) {
                this.finish();
            }
        },

        blowAll() {
            this.blow(remaining.length, 1.25);
        },

        finish() {
            if (this.blown || finishing) {
                return;
            }

            // Se sueltan las que quedan; blow() vuelve a llamar aquí y el aviso evita repetir
            finishing = true;
            this.blow(remaining.length, 1.1);
            this.blown = true;
            this.stopListening();

            setTimeout(() => {
                celebrate({ rect: this.$refs.flower?.getBoundingClientRect(), amount: 24, variant: 'love' });
            }, 900);
        },

        async listen() {
            this.micError = '';

            // Si ya está bloqueado, se explica cómo activarlo en vez de pedirlo y fallar
            if (await micPermission() === 'denied') {
                this.micError = MIC_MESSAGES.blocked;
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: { echoCancellation: false, noiseSuppression: false, autoGainControl: false },
                });
                const context = new (window.AudioContext || window.webkitAudioContext)();
                await context.resume?.();
                const analyser = context.createAnalyser();
                analyser.fftSize = 1024;
                context.createMediaStreamSource(stream).connect(analyser);

                mic = { stream, context, analyser, data: new Uint8Array(analyser.fftSize), frame: null, strong: 0 };
                mic.timeout = setTimeout(() => this.stopListening(), MIC_LIMIT_MS);
                this.listening = true;
                this.hear();
            } catch (error) {
                this.micError = await micErrorMessage(error);
            }
        },

        /** Mide el volumen: un soplido es ruido fuerte y sostenido unos cuadros seguidos. */
        hear() {
            if (!mic) {
                return;
            }

            mic.analyser.getByteTimeDomainData(mic.data);

            let sum = 0;
            for (const value of mic.data) {
                const centered = (value - 128) / 128;
                sum += centered * centered;
            }

            const level = Math.sqrt(sum / mic.data.length);
            mic.strong = level > MIC_THRESHOLD ? mic.strong + 1 : 0;
            // Para el anillo del botón: cuánto se oye (0 a 1)
            this.level = Math.min(level / (MIC_THRESHOLD * 2), 1);

            if (mic.strong >= 3) {
                this.blow(Math.ceil(level * 12), 1 + level);
            }

            mic.frame = requestAnimationFrame(() => this.hear());
        },

        stopListening() {
            if (!mic) {
                return;
            }

            cancelAnimationFrame(mic.frame);
            clearTimeout(mic.timeout);
            mic.stream.getTracks().forEach((track) => track.stop());
            mic.context.close().catch(() => {});
            mic = null;
            this.listening = false;
            this.level = 0;
        },

        destroy() {
            this.stopListening();
        },
    };
}
