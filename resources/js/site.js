/**
 * ────────────────────────────────────────────────────────────────────────────
 * Sitio público (home y login) — animaciones que necesitan JS
 *
 * - Revelado de bloques al entrar en pantalla ([data-reveal]).
 * - Rotador: la palabra del evento y su foto cambian juntas ([data-rotator]).
 * - Botones magnéticos en escritorio ([data-magnetic]).
 * - Cabecera con fondo al dejar la parte superior ([data-site-header]).
 * - Teléfonos que recorren las aperturas de las muestras sin quedar en blanco ([data-cover-reel]).
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
 * Teléfonos que recorren aperturas ([data-cover-reel] con la lista de muestras en JSON): la portada,
 * la temporada y cada página por evento. Cada muestra abre su apertura sola (?portada=1).
 *
 * Para que la pantalla nunca quede en blanco mientras carga la muestra siguiente, hay dos iframes:
 * el que se ve y uno oculto detrás donde se va cargando la próxima con «&reel=1» (así espera quieta).
 * Cuando ya cargó y se cumplió el tiempo, se le avisa que empiece (postMessage) y se cruzan. Con una
 * sola muestra y [data-cover-reel-replay], la apertura se repite igual. Solo avanza mientras se ve.
 * Evento «reel:go» (detail = índice): cambia a esa muestra en cuanto esté lista.
 */
function initCoverReels() {
    document.querySelectorAll('[data-cover-reel]').forEach((reel) => {
        const first = reel.querySelector('[data-cover-reel-frame]');

        // La muestra del teléfono es una invitación completa (CSS, JS, fuentes) y vive en el mismo hilo que la
        // portada: espera a que la home termine de cargar, a que acabe la entrada del teléfono y a que el
        // navegador esté libre, para no competir con lo que el visitante ya ve
        if (first?.hasAttribute('data-defer-until-idle')) {
            const whenReady = () => afterEntrance(reel).then(() => idle(() => {
                const src = first.dataset.deferSrc;

                delete first.dataset.deferSrc;
                startReel(reel, first, src);
            }));

            document.readyState === 'complete' ? whenReady() : window.addEventListener('load', whenReady, { once: true });

            return;
        }

        // Teléfono dentro de un panel cerrado (la temporada de la portada): no carga nada hasta que
        // el panel se abre y avisa con «reel:wake»
        if (first?.dataset.lazySrc) {
            reel.addEventListener('reel:wake', () => {
                const src = first.dataset.lazySrc;

                delete first.dataset.lazySrc;
                startReel(reel, first, src);
            }, { once: true });

            return;
        }

        initCoverReel(reel);
    });
}

const idle = (callback, timeout = 2000) => ('requestIdleCallback' in window ? requestIdleCallback(callback, { timeout }) : setTimeout(callback, 300));

/** Espera a que terminen las animaciones de entrada de un bloque (o un tope, por si alguna nunca avanza). */
function afterEntrance(element, limit = 4000) {
    const animations = element.getAnimations?.() ?? [];

    return Promise.race([
        Promise.allSettled(animations.map((animation) => animation.finished)),
        new Promise((resolve) => setTimeout(resolve, limit)),
    ]);
}

/**
 * Milisegundos promedio entre fotogramas durante un instante. Cerca de 16 es un equipo holgado; más de 50, uno que
 * ya va justo sin cargar nada más. Con la pestaña oculta, o si el navegador no pinta, devuelve 0 (no decide).
 */
function frameInterval(duration = 500) {
    return new Promise((resolve) => {
        if (document.hidden) {
            resolve(0);

            return;
        }

        let begin = 0;
        let last = 0;
        let count = 0;
        const tick = (now) => {
            begin ||= now;
            last = now;
            count++;

            if (now - begin >= duration) {
                resolve(count > 1 ? (last - begin) / (count - 1) : 0);

                return;
            }

            requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
        setTimeout(() => resolve(0), duration + 1500);
    });
}

/**
 * Modo ligero: el teléfono muestra capturas de las aperturas en lugar de invitaciones vivas. Solo si las muestras
 * traen su captura y el equipo es de pocos recursos (poca memoria o procesador, ahorro de datos) o ya va justo.
 * «?hero=lite» o «?hero=live» lo fuerzan, para probar.
 */
async function wantsLite(items) {
    const forced = new URLSearchParams(window.location.search).get('hero');

    if (forced === 'live' || !items.some((item) => item.capture)) {
        return false;
    }

    if (forced === 'lite' || navigator.connection?.saveData === true) {
        return true;
    }

    if ((typeof navigator.deviceMemory === 'number' && navigator.deviceMemory <= 2) || (typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 2)) {
        return true;
    }

    return (await frameInterval()) > 50;
}

async function startReel(reel, first, src) {
    const items = JSON.parse(reel.dataset.coverReel || '[]');

    if (await wantsLite(items)) {
        first.remove();
        initCoverReelLite(reel, items);

        return;
    }

    first.src = src;
    initCoverReel(reel);
}

/**
 * Teléfono en modo ligero: las capturas de las aperturas rotan con la misma cadencia que las invitaciones vivas
 * (y cambian la foto, la palabra y la etiqueta a la par). Nada pesado corre en el hilo: son imágenes de unos 20 KB.
 */
function initCoverReelLite(reel, items) {
    const screen = reel.querySelector('.site-phone__screen');
    const shots = items.filter((item) => item.capture);

    if (!screen || !shots.length) {
        return;
    }

    const label = reel.querySelector('[data-cover-reel-label]');
    const rotator = reel.closest('[data-rotator]');
    const dwell = Number(reel.dataset.coverReelDwell) || 9500;
    const frames = shots.map(() => {
        const frame = new Image();

        frame.alt = '';
        frame.decoding = 'async';
        frame.draggable = false;
        frame.setAttribute('aria-hidden', 'true');
        screen.append(frame);

        return frame;
    });
    let index = 0;
    let timer = null;
    let inView = false;

    const load = (position) => {
        if (!frames[position].getAttribute('src')) {
            frames[position].src = shots[position].capture;
        }
    };

    const stop = () => {
        clearTimeout(timer);
        timer = null;
    };

    const schedule = () => {
        stop();

        if (inView && !document.hidden && shots.length > 1 && !reducedMotion) {
            timer = setTimeout(advance, dwell);
        }
    };

    function advance() {
        const next = (index + 1) % shots.length;
        const target = frames[next];
        const swap = () => {
            frames[index].classList.remove('is-shown');
            target.classList.add('is-shown');
            index = next;

            // Foto, palabra y etiqueta cambian en el mismo instante que el teléfono
            if (rotator && Number.isInteger(shots[index].rotator)) {
                rotator.dispatchEvent(new CustomEvent('rotator:show', { detail: shots[index].rotator }));
            }

            if (label) {
                label.textContent = shots[index].label;
            }

            load((index + 1) % shots.length);
            schedule();
        };

        load(next);
        target.complete && target.naturalWidth ? swap() : target.addEventListener('load', swap, { once: true });
        target.addEventListener('error', schedule, { once: true });
    }

    frames[0].addEventListener('load', () => {
        frames[0].classList.add('is-shown');
        screen.classList.add('is-live');

        // Si la primera muestra no tiene captura, foto y palabra arrancan con la primera que sí la tiene
        if (shots[0] !== items[0]) {
            rotator?.dispatchEvent(new CustomEvent('rotator:show', { detail: shots[0].rotator }));

            if (label) {
                label.textContent = shots[0].label;
            }
        }

        load(1 % shots.length);
        schedule();
    }, { once: true });
    load(0);

    new IntersectionObserver(([entry]) => {
        inView = entry.isIntersecting;
        inView ? schedule() : stop();
    }).observe(reel);

    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : schedule()));
}

function frameIsLoaded(frame) {
    try {
        const doc = frame.contentDocument;

        return Boolean(doc && doc.readyState === 'complete' && doc.location.href !== 'about:blank');
    } catch {
        return true;
    }
}

function standbyUrl(url) {
    const target = new URL(url, window.location.href);
    target.searchParams.set('reel', '1');

    return target.toString();
}

function initCoverReel(reel) {
    const first = reel.querySelector('[data-cover-reel-frame]');
    const items = JSON.parse(reel.dataset.coverReel || '[]');

    if (!first || !items.length) {
        return;
    }

    // La primera muestra aparece con un fundido cuando ya pintó su apertura; el brillo de espera se apaga
    const screen = first.closest('.site-phone__screen');
    const reveal = () => {
        first.classList.add('is-shown');
        screen?.classList.add('is-live');
    };
    frameIsLoaded(first) ? reveal() : first.addEventListener('load', reveal, { once: true });

    const loops = items.length > 1 || reel.hasAttribute('data-cover-reel-replay');

    if (reducedMotion || !loops) {
        return;
    }

    const label = reel.querySelector('[data-cover-reel-label]');
    const rotator = reel.closest('[data-rotator]');
    // La apertura espera, se abre y la muestra se luce unos segundos antes de cambiar
    const dwell = Number(reel.dataset.coverReelDwell) || 9500;
    let shown = first;
    let standby = null;
    let index = 0;
    let pending = null;
    let ready = false;
    let due = false;
    let timer = null;
    let preloadTimer = null;
    let inView = false;

    // Los dos iframes se turnan: el que termina de cargar detrás queda listo para entrar
    const listen = (frame) => frame.addEventListener('load', () => {
        if (frame === standby && pending !== null && frameIsLoaded(frame)) {
            ready = true;

            if (due) {
                swap();
            }
        } else if (frame === shown) {
            schedule();
        }
    });

    const createStandby = () => {
        const frame = document.createElement('iframe');
        frame.title = first.title;
        frame.tabIndex = -1;
        frame.setAttribute('aria-hidden', 'true');
        frame.setAttribute('data-cover-reel-frame', '');
        listen(frame);
        first.after(frame);

        return frame;
    };

    const preload = (target, force = false) => {
        if (!force && (!inView || document.hidden)) {
            return;
        }

        if (pending === target && standby) {
            return;
        }

        standby ??= createStandby();
        pending = target;
        ready = false;
        standby.src = standbyUrl(items[target].url);
    };

    const stop = () => {
        clearTimeout(timer);
        clearTimeout(preloadTimer);
        timer = null;
        preloadTimer = null;
    };

    const schedule = () => {
        stop();

        if (inView && !document.hidden) {
            timer = setTimeout(() => {
                due = true;
                ready ? swap() : preload((index + 1) % items.length);
            }, dwell);

            // La siguiente invitación carga detrás solo cuando la actual ya lleva un rato luciéndose y el navegador
            // está libre: dos invitaciones completas cargando a la vez en el mismo hilo era lo que congelaba la portada
            preloadTimer = setTimeout(() => idle(() => preload((index + 1) % items.length), 1500), Math.max(1500, dwell - 4500));
        }
    };

    function swap() {
        const incoming = standby;
        const outgoing = shown;

        due = false;
        ready = false;
        index = pending;
        pending = null;

        incoming.contentWindow?.postMessage('bida:cover-play', window.location.origin);
        incoming.classList.add('is-shown');
        outgoing.classList.remove('is-shown');
        shown = incoming;
        standby = outgoing;

        // Foto, palabra y etiqueta cambian en el mismo instante que el teléfono
        if (rotator && Number.isInteger(items[index].rotator)) {
            rotator.dispatchEvent(new CustomEvent('rotator:show', { detail: items[index].rotator }));
        }

        if (label) {
            label.textContent = items[index].label;
        }

        // Pasado el fundido, la que salió se vacía: una invitación oculta sigue corriendo sus animaciones y su JS
        // en el mismo hilo que la portada. La siguiente la pide «schedule», ya con la actual luciéndose.
        setTimeout(() => {
            if (standby === outgoing && pending === null) {
                outgoing.src = 'about:blank';
            }
        }, 900);

        schedule();
    }

    reel.addEventListener('reel:go', (event) => {
        const target = Number(event.detail);

        if (!Number.isInteger(target) || target < 0 || target >= items.length) {
            return;
        }

        stop();
        due = true;

        if (pending === target && ready) {
            swap();
        } else {
            pending = null;
            preload(target, true);
        }
    });

    const start = () => schedule();

    listen(first);

    new IntersectionObserver(([entry]) => {
        inView = entry.isIntersecting;
        inView ? start() : stop();
    }).observe(reel);

    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
}

/**
 * Renglones plegables de la guía (details.site-fold): un enlace a su ancla (#precios, #entrada…) abre el renglón
 * antes de llegar a él, para que quien llega desde otra página no encuentre un título cerrado.
 */
function initFolds() {
    const reveal = () => {
        const target = location.hash.length > 1 ? document.getElementById(decodeURIComponent(location.hash.slice(1))) : null;
        const fold = target?.closest('details.site-fold');

        if (fold && !fold.open) {
            fold.open = true;
            fold.scrollIntoView();
        }
    };

    reveal();
    window.addEventListener('hashchange', reveal);
}

initReveal();
initRotators();
initMagnetic();
initHeaderState();
initCoverReels();
initFolds();
