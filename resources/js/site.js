/**
 * ────────────────────────────────────────────────────────────────────────────
 * Sitio público (home y login) — animaciones que necesitan JS
 *
 * - Revelado de bloques al entrar en pantalla ([data-reveal]).
 * - Rotador: la palabra del evento y su foto cambian juntas ([data-rotator]).
 * - Botones magnéticos en escritorio ([data-magnetic]).
 * - Cabecera con fondo al dejar la parte superior ([data-site-header]).
 * - Teléfono de la portada que recorre las aperturas de cada plantilla ([data-cover-reel]).
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
 * Solo corre mientras el bloque está visible y la pestaña activa. Con [data-rotator-driven] no avanza
 * solo: lo mueve el evento "rotator:show" (el teléfono de la portada, al cambiar de plantilla).
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

        if (rotator.hasAttribute('data-rotator-driven')) {
            rotator.addEventListener('rotator:show', (event) => {
                const target = Number(event.detail);

                if (Number.isInteger(target) && target >= 0 && target < count && target !== index) {
                    show(target);
                }
            });

            return;
        }

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

/**
 * Teléfono de la portada: cada muestra abre su apertura sola (?portada=1). Pasado un rato, el teléfono
 * se desvanece y carga la plantilla siguiente. Solo avanza mientras se ve y la pestaña está activa.
 */
function initCoverReel() {
    const reel = document.querySelector('[data-cover-reel]');
    const frame = reel?.querySelector('[data-cover-reel-frame]');
    const items = reel ? JSON.parse(reel.dataset.coverReel || '[]') : [];

    if (!frame || items.length < 2 || reducedMotion) {
        return;
    }

    const label = reel.querySelector('[data-cover-reel-label]');
    // La apertura espera, se abre y la invitación se luce unos segundos antes de cambiar
    const dwell = 9500;
    let index = 0;
    let timer = null;
    let inView = false;

    const stop = () => {
        clearTimeout(timer);
        timer = null;
    };

    const rotator = reel.closest('[data-rotator]');

    const next = () => {
        index = (index + 1) % items.length;
        reel.classList.add('is-switching');

        // Foto, palabra y etiqueta cambian en el mismo instante en que el teléfono empieza a cambiar
        if (rotator && items[index].rotator !== null && items[index].rotator !== undefined) {
            rotator.dispatchEvent(new CustomEvent('rotator:show', { detail: items[index].rotator }));
        }

        if (label) {
            label.textContent = items[index].label;
        }

        // La muestra nueva se carga cuando el teléfono terminó de desvanecerse
        setTimeout(() => {
            frame.src = items[index].url;
        }, 450);
    };

    const schedule = () => {
        stop();

        if (inView && !document.hidden) {
            timer = setTimeout(next, dwell);
        }
    };

    frame.addEventListener('load', () => {
        reel.classList.remove('is-switching');
        schedule();
    });

    new IntersectionObserver(([entry]) => {
        inView = entry.isIntersecting;
        inView ? schedule() : stop();
    }).observe(reel);

    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : schedule()));
}

initReveal();
initRotators();
initMagnetic();
initHeaderState();
initCoverReel();
