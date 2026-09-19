/**
 * Tarjeta «Sobre lacrado»: sobre con sello de lacre, lluvia de flores, vinilo y respuesta con flor.
 * Lo carga app.js solo si la página tiene [data-card="sobre"], antes de arrancar Alpine. Usa el modo
 * historia y los efectos de resources/js/story.
 */
import { flowerReply } from '../amor/reply.js';
import { celebrate, prefersReducedMotion, vibrate } from '../../story/effects.js';

window.flowerReply = flowerReply;

/** Ráfaga de flores que cruza la pantalla (al abrir el sobre y al cambiar de escena). */
function flowerBurst(amount = 26) {
    if (prefersReducedMotion()) {
        return;
    }

    const layer = document.createElement('div');
    layer.className = 'inv-shower';
    layer.setAttribute('aria-hidden', 'true');

    for (let i = 0; i < amount; i++) {
        const flower = document.createElement('span');
        flower.className = 'inv-shower__flower';
        flower.dataset.tone = String(i % 3);
        flower.style.setProperty('--left', `${(Math.random() * 100).toFixed(1)}%`);
        flower.style.setProperty('--size', `${(1.2 + Math.random() * 1.8).toFixed(2)}rem`);
        flower.style.setProperty('--delay', `${Math.round(Math.random() * 900)}ms`);
        flower.style.setProperty('--dur', `${(2.2 + Math.random() * 1.6).toFixed(2)}s`);
        flower.style.setProperty('--drift', `${Math.round(Math.random() * 160 - 80)}px`);
        flower.style.setProperty('--turn', `${Math.round(Math.random() * 720 - 360)}deg`);
        flower.innerHTML = '<svg viewBox="0 0 40 40"><use href="#amor-flower"/></svg>';
        layer.appendChild(flower);
    }

    document.body.appendChild(layer);
    setTimeout(() => layer.remove(), 5200);
}

window.addEventListener('inv-flowers', (event) => flowerBurst(event.detail?.amount ?? 26));

window.addEventListener('inv-story-change', (event) => {
    if ((event.detail?.index ?? 0) > 0) {
        flowerBurst(9);
    }
});

/** Sobre: al tocar el lacre se parte, la solapa se abre y salen la carta y las flores. */
window.waxCover = (timing) => {
    const cover = window.invitationCover(timing);

    return {
        ...cover,
        cracked: false,

        init() {
            cover.init.call(this);

            // Teléfono de la portada de la home: el lacre se rompe solo
            if (!this.closed && window.invCoverAutoplay) {
                setTimeout(() => this.crack(), 1200);
            }
        },

        crack() {
            if (this.cracked || this.stage > 0 || this.closed) {
                return;
            }

            this.cracked = true;
            vibrate([16, 50, 28]);
            celebrate({ rect: this.$el.querySelector('.inv-wax')?.getBoundingClientRect(), amount: 14, variant: 'love' });
            setTimeout(() => flowerBurst(34), prefersReducedMotion() ? 0 : 500);
            setTimeout(() => this.open(), prefersReducedMotion() ? 200 : 900);
        },
    };
};

/** Vinilo: enciende o pausa la canción del reproductor de la página y gira mientras suena. */
window.vinylPlayer = () => ({
    playing: false,

    init() {
        const audio = document.querySelector('.inv-player audio');

        if (!audio) {
            return;
        }

        this.playing = !audio.paused;
        audio.addEventListener('play', () => { this.playing = true; });
        audio.addEventListener('pause', () => { this.playing = false; });
    },

    toggle() {
        const audio = document.querySelector('.inv-player audio');

        if (!audio) {
            return;
        }

        audio.paused ? audio.play().catch(() => {}) : audio.pause();
    },
});
