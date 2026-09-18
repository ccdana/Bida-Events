/**
 * Apertura de la tarjeta de amor: un capullo que se riega con tres toques (partials/amor/intro).
 * Se apoya en invitationCover (shell/cover-component) para bloquear la página, pausar la portada y
 * retirarse; aquí solo se cuentan las gotas y se abre cuando la flor terminó de florecer.
 */
import { prefersReducedMotion, vibrate } from '../../story/effects.js';

const DROPS = 3;
const BLOOM_MS = 1600;

// Dónde cae cada gota: a la altura del capullo, que sube con cada riego
const LANDING = ['5.6rem', '3.8rem', '2.2rem'];

export function bloomCover(timing) {
    const cover = window.invitationCover(timing);

    return {
        ...cover,
        drops: 0,

        init() {
            cover.init.call(this);

            // Teléfono de la portada de la home: se riega solo (el clic automático de cover-script se ignora)
            if (!this.closed && window.invCoverAutoplay) {
                [900, 1500, 2100].forEach((delay) => setTimeout(() => this.water(), delay));
            }
        },

        water() {
            if (this.drops >= DROPS || this.stage > 0 || this.closed) {
                return;
            }

            this.drop(this.drops);
            this.drops++;
            vibrate(this.drops === DROPS ? [14, 40, 22] : 10);

            if (this.drops === DROPS) {
                setTimeout(() => this.open(), prefersReducedMotion() ? 300 : BLOOM_MS);
            }
        },

        /** «Abrir sin esperar»: florece de una vez. */
        bloomNow() {
            if (this.stage > 0 || this.closed) {
                return;
            }

            this.drops = DROPS;
            setTimeout(() => this.open(), prefersReducedMotion() ? 0 : 900);
        },

        drop(index) {
            const sky = this.$refs.sky;

            if (!sky || prefersReducedMotion()) {
                return;
            }

            const drop = document.createElement('span');
            drop.className = 'inv-bloom__drop';
            drop.style.setProperty('--x', `${Math.round((Math.random() - 0.5) * 18)}px`);
            drop.style.setProperty('--land', LANDING[index] ?? '2rem');
            sky.appendChild(drop);
            setTimeout(() => drop.remove(), 900);
        },
    };
}
