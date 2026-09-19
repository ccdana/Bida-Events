/**
 * Recuerdos en un tendedero (partials/amor/clothesline): la fila se desliza con el dedo (scroll
 * nativo con imán) y las fotos se balancean según la velocidad; con el ratón se arrastra.
 * Un toque le da la vuelta a la foto. El balanceo es la variable --swing (-1 a 1) del tendedero.
 */
import { prefersReducedMotion, vibrate } from '../../story/effects.js';

export function clothesline() {
    let settle = null;

    return {
        flipped: null,
        dragged: false,

        init() {
            const track = this.$refs.track;
            let lastX = track.scrollLeft;
            let lastT = performance.now();

            track.addEventListener('scroll', () => {
                if (prefersReducedMotion()) {
                    return;
                }

                const now = performance.now();
                const velocity = (track.scrollLeft - lastX) / Math.max(now - lastT, 16);
                lastX = track.scrollLeft;
                lastT = now;

                this.$el.style.setProperty('--swing', Math.max(-1, Math.min(1, -velocity * 0.9)).toFixed(3));
                clearTimeout(settle);
                settle = setTimeout(() => this.$el.style.setProperty('--swing', '0'), 140);
            }, { passive: true });

            this.dragWithMouse(track);
        },

        flip(index) {
            if (this.dragged) {
                return;
            }

            this.flipped = this.flipped === index ? null : index;
            vibrate(6);
        },

        /** En computadora: arrastrar la fila con el ratón, como en el teléfono. */
        dragWithMouse(track) {
            let start = null;

            track.addEventListener('pointerdown', (event) => {
                if (event.pointerType !== 'mouse' || event.button !== 0) {
                    return;
                }

                start = { x: event.clientX, left: track.scrollLeft };
                this.dragged = false;
            });

            window.addEventListener('pointermove', (event) => {
                if (!start) {
                    return;
                }

                const dx = event.clientX - start.x;

                if (Math.abs(dx) > 5) {
                    this.dragged = true;
                    track.classList.add('is-dragging');
                }

                track.scrollLeft = start.left - dx;
            });

            window.addEventListener('pointerup', () => {
                start = null;
                track.classList.remove('is-dragging');
                // El clic que termina un arrastre no voltea la foto
                setTimeout(() => { this.dragged = false; }, 0);
            });
        },
    };
}
