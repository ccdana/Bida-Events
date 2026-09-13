/**
 * ────────────────────────────────────────────────────────────────────────────
 * Sitio público (home y login) — animaciones que necesitan JS
 *
 * - Revelado de bloques al entrar en pantalla ([data-reveal]).
 * - Rotador: la palabra del evento y su foto cambian juntas ([data-rotator]).
 * - Botones magnéticos en escritorio ([data-magnetic]).
 * - Cabecera con fondo al dejar la parte superior ([data-site-header]).
 *
 * Las entradas, el scroll-driven y los hovers viven en resources/css/site/site.css.
 * No usa listeners de scroll: todo se basa en IntersectionObserver.
 * ────────────────────────────────────────────────────────────────────────────
 */
import { animate } from 'motion';

const root = document.documentElement;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

root.classList.add('site-motion');

function initReveal() {
    const items = document.querySelectorAll('[data-reveal]');

    if (!('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-revealed'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -12% 0px' });

    items.forEach((item) => observer.observe(item));
}

/**
 * Cada [data-rotator] contiene uno o más [data-rotator-group] con la misma
 * cantidad de hijos (palabras, fotos). Todos avanzan al mismo índice.
 * Solo corre mientras el bloque está visible y la pestaña activa.
 */
function initRotators() {
    if (reducedMotion) {
        return;
    }

    document.querySelectorAll('[data-rotator]').forEach((rotator) => {
        const groups = [...rotator.querySelectorAll('[data-rotator-group]')].map((group) => [...group.children]);
        const count = Math.min(...groups.map((items) => items.length));

        if (!groups.length || count < 2) {
            return;
        }

        const interval = Number(rotator.dataset.rotatorInterval) || 3200;
        let index = 0;
        let timer = null;
        let inView = false;

        const show = (next) => {
            groups.forEach((items) => items.forEach((item, i) => {
                item.classList.toggle('is-active', i === next);
                item.classList.toggle('is-leaving', i === index && i !== next);
            }));
            index = next;
        };

        const stop = () => {
            clearInterval(timer);
            timer = null;
        };

        const start = () => {
            if (timer || !inView || document.hidden) {
                return;
            }
            timer = setInterval(() => show((index + 1) % count), interval);
        };

        new IntersectionObserver(([entry]) => {
            inView = entry.isIntersecting;
            inView ? start() : stop();
        }).observe(rotator);

        document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
    });
}

/** El botón sigue levemente al cursor y vuelve con un resorte al salir. */
function initMagnetic() {
    if (reducedMotion || !window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
        return;
    }

    document.querySelectorAll('[data-magnetic]').forEach((element) => {
        const strength = Number(element.dataset.magnetic) || 0.22;

        element.addEventListener('pointermove', (event) => {
            const rect = element.getBoundingClientRect();
            animate(element, {
                x: (event.clientX - rect.left - rect.width / 2) * strength,
                y: (event.clientY - rect.top - rect.height / 2) * strength,
            }, { type: 'spring', stiffness: 320, damping: 22, mass: 0.6 });
        });

        element.addEventListener('pointerleave', () => {
            animate(element, { x: 0, y: 0 }, { type: 'spring', stiffness: 220, damping: 14, mass: 0.6 });
        });
    });
}

function initHeaderState() {
    const header = document.querySelector('[data-site-header]');
    const sentinel = document.querySelector('[data-header-sentinel]');

    if (!header || !sentinel || !('IntersectionObserver' in window)) {
        return;
    }

    new IntersectionObserver(([entry]) => {
        header.classList.toggle('is-scrolled', !entry.isIntersecting);
    }).observe(sentinel);
}

initReveal();
initRotators();
initMagnetic();
initHeaderState();
