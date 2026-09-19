/**
 * Escenario de «Nuestra historia» (partials/historia/stage): la luna, el agua y los cielos siguen
 * la lectura. La posición se mide en actos: s = 0 al empezar el Acto I, s = 4 al terminar el IV,
 * así el recorrido no depende de cuánto escribió cada pareja.
 *
 * Cada capa recibe transform/opacity directo (no una variable en un ancestro, que recalcularía
 * todo el árbol). Con movimiento reducido, las capas solo cambian de opacidad.
 */
import { prefersReducedMotion } from '../../story/effects.js';

const clamp = (value, min = 0, max = 1) => Math.min(max, Math.max(min, value));

/** Recorrido por tramos [s, valor], suavizado entre puntos. */
function track(points) {
    return (s) => {
        if (s <= points[0][0]) {
            return points[0][1];
        }

        for (let i = 1; i < points.length; i++) {
            const [s1, v1] = points[i];

            if (s <= s1) {
                const [s0, v0] = points[i - 1];
                const t = (s - s0) / (s1 - s0);

                return v0 + (v1 - v0) * t * t * (3 - 2 * t);
            }
        }

        return points[points.length - 1][1];
    };
}

// La luna vive en la franja de arriba para no pasar detrás de los textos (moonY = borde superior, en vh).
// I: medio hundida en el horizonte (58 vh) · II: sube · III: de frente, lo más grande · IV: arriba a la derecha, como en el cuadro
const moonY = track([[0, 48], [0.85, 47], [1.6, 5], [2.2, 4], [2.9, 3], [3.8, 2]]);
const moonX = track([[0, 0], [3, 0], [3.8, 24]]);
const moonScale = track([[0, 0.42], [0.85, 0.45], [1.6, 0.58], [2.2, 0.72], [2.9, 0.95], [3.8, 0.62]]);
const moonOpacity = track([[0, 0.92], [1.6, 1]]);
const haloOpacity = track([[0, 0.3], [2, 0.6], [2.9, 1], [3.8, 0.85]]);
// El agua se hunde al terminar el Acto I: «subimos desde la superficie»
const waterY = track([[0, 0], [0.6, 0], [1.5, 70]]);
const waterOpacity = track([[0, 1], [1.05, 1], [1.6, 0]]);
const deepOpacity = track([[1.4, 0], [2.2, 1]]);
const starryOpacity = track([[2.7, 0], [3.3, 1]]);

export function initStage() {
    const stage = document.querySelector('[data-story-stage]');
    const acts = [...document.querySelectorAll('[data-act]')];

    if (!stage || acts.length === 0) {
        return;
    }

    const layer = (name) => stage.querySelector(`[data-layer="${name}"]`);
    const moon = layer('moon');
    const halo = moon?.querySelector('.story-moon__halo');
    const water = layer('water');
    const deep = layer('deep');
    const starry = layer('starry');
    const tide = document.querySelector('[data-tide]');
    const tideFill = document.querySelector('[data-tide-fill]');

    let bounds = [];
    let tideBounds = null;
    let frame = 0;

    const measure = () => {
        bounds = acts.map((act) => {
            const rect = act.getBoundingClientRect();

            return { top: rect.top + window.scrollY, height: Math.max(1, rect.height) };
        });

        if (tide) {
            const rect = tide.getBoundingClientRect();
            tideBounds = { top: rect.top + window.scrollY, height: Math.max(1, rect.height) };
        }
    };

    const render = () => {
        frame = 0;

        const vh = window.innerHeight;
        const reading = window.scrollY + vh * 0.55;
        let s = 0;

        bounds.forEach(({ top, height }, index) => {
            if (reading >= top) {
                s = index + clamp((reading - top) / height);
            }
        });

        const still = prefersReducedMotion();

        if (moon) {
            moon.style.transform = still
                ? 'translate3d(-50%, 4vh, 0) scale(0.7)'
                : `translate3d(calc(-50% + ${moonX(s).toFixed(2)}vw), ${moonY(s).toFixed(2)}vh, 0) scale(${moonScale(s).toFixed(3)})`;
            moon.style.opacity = moonOpacity(s).toFixed(3);
        }

        if (halo) {
            halo.style.opacity = haloOpacity(s).toFixed(3);
        }

        if (water) {
            water.style.transform = still ? '' : `translate3d(0, ${waterY(s).toFixed(2)}vh, 0)`;
            water.style.opacity = waterOpacity(s).toFixed(3);
        }

        if (deep) {
            deep.style.opacity = deepOpacity(s).toFixed(3);
        }

        if (starry) {
            starry.style.opacity = starryOpacity(s).toFixed(3);
        }

        stage.classList.toggle('is-starry', s >= 2.85);
        stage.dataset.sea = s > 0.75 && s < 1.9 ? 'rough' : 'calm';

        if (tideFill && tideBounds) {
            tideFill.style.transform = `scaleY(${clamp((reading - tideBounds.top) / tideBounds.height).toFixed(3)})`;
        }
    };

    const schedule = () => {
        if (!frame) {
            frame = requestAnimationFrame(render);
        }
    };

    const remeasure = () => {
        measure();
        schedule();
    };

    measure();
    render();

    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', remeasure, { passive: true });
    window.addEventListener('load', remeasure);
    // Las fotos y las fuentes cambian el alto de los actos al cargar
    new ResizeObserver(remeasure).observe(document.body);
}

/** Las fotos de los momentos salen del agua cuando llegan a la vista. */
export function initSurfacing() {
    const photos = document.querySelectorAll('[data-surface]');

    if (!photos.length) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-surfaced');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -15% 0px' });

    photos.forEach((photo) => observer.observe(photo));
}

/** Al enviar la respuesta (modules/respuesta dispara inv-celebrate), cruza una estrella fugaz. */
export function initShootingStar() {
    const star = document.querySelector('[data-shooting-star]');

    if (!star) {
        return;
    }

    window.addEventListener('inv-celebrate', () => {
        if (prefersReducedMotion()) {
            return;
        }

        star.classList.remove('is-shooting');
        void star.offsetWidth;
        star.classList.add('is-shooting');
    });
}
