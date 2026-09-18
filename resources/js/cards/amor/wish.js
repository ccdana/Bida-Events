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
        micAvailable: Boolean(navigator.mediaDevices?.getUserMedia) && window.isSecureContext,

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

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: { echoCancellation: false, noiseSuppression: false, autoGainControl: false },
                });
                const context = new (window.AudioContext || window.webkitAudioContext)();
                const analyser = context.createAnalyser();
                analyser.fftSize = 1024;
                context.createMediaStreamSource(stream).connect(analyser);

                mic = { stream, context, analyser, data: new Uint8Array(analyser.fftSize), frame: null, strong: 0 };
                mic.timeout = setTimeout(() => this.stopListening(), MIC_LIMIT_MS);
                this.listening = true;
                this.hear();
            } catch {
                this.micError = 'No pudimos usar el micrófono. Desliza hacia arriba para soplar.';
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
        },

        destroy() {
            this.stopListening();
        },
    };
}
