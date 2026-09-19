/**
 * «Libro de aventuras»: un cuaderno anillado cuyas hojas se pasan con una vuelta realista (page-flip).
 * - Sin botones: la hoja se arrastra con el dedo o con el mouse y sigue al gesto. Hacia la izquierda
 *   avanza y hacia la derecha vuelve, empiece donde empiece el gesto. Un deslizamiento rápido también
 *   la pasa. Las flechas del teclado, el menú y el índice siguen funcionando.
 * - En el teléfono se ve una hoja por vez; en pantallas anchas, el libro abierto de a dos. La espiral
 *   queda fija sobre el lomo.
 * - Tocar dentro del juego o de la respuesta no pasa la hoja ([data-nb-nodrag]).
 * - Con «reducir movimiento» o ?hojas=todas las hojas quedan apiladas y se leen con scroll, igual que
 *   sin JavaScript.
 */
import { PageFlip } from 'page-flip';
import { prefersReducedMotion } from '../story/effects.js';
import { countUp } from '../story/gestures.js';

// Proporción de una hoja (ancho × alto) en píxeles de referencia
const PAGE = { width: 460, height: 660 };

// Valores internos de page-flip (FlipDirection y FlippingState)
const FORWARD = 0;
const BACK = 1;
const USER_FOLD = 'user_fold';

const isTyping = (element) => element?.matches?.('input, textarea, select, [contenteditable="true"]');

function linkWithout(param) {
    const url = new URL(window.location.href);
    url.searchParams.delete(param);
    url.hash = '';

    return url.pathname + url.search;
}

function linkWith(param, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(param, value);
    url.hash = '';

    return url.pathname + url.search;
}

/** Hojas apiladas: todo se lee con scroll, y los números cuentan al llegar a su hoja. */
function stack(root, pages, fromParam) {
    document.documentElement.classList.add('nb-stacked');
    root.querySelector('[data-nb-controls]')?.setAttribute('hidden', '');
    root.querySelector('[data-nb-teach]')?.remove();
    root.querySelector('[data-nb-all]')?.setAttribute('hidden', '');

    const back = root.querySelector('[data-nb-book]');

    if (back && fromParam) {
        back.href = linkWithout('hojas');
        back.hidden = false;
    }

    const counted = new WeakSet();
    const observer = 'IntersectionObserver' in window
        ? new IntersectionObserver((entries) => entries.forEach((entry) => {
            if (entry.isIntersecting && !counted.has(entry.target)) {
                counted.add(entry.target);
                countUp(entry.target);
            }
        }), { threshold: 0.4 })
        : null;

    pages.filter((page) => page.hasAttribute('data-nb-count')).forEach((page) => observer?.observe(page));
}

/**
 * La dirección de la vuelta sale del gesto y no de dónde empezó: page-flip, en una sola hoja, solo
 * vuelve atrás si el dedo arranca en el borde izquierdo. Mientras el usuario arrastra, se compara
 * dónde empezó con dónde está; con el teclado o un enlace se usa lo de siempre.
 */
function followGesture(flip, block) {
    const controller = flip.getFlipController();
    const original = controller.getDirectionByPoint.bind(controller);
    let startX = null;
    let startTime = 0;

    const area = () => block.querySelector('.stf__block')?.getBoundingClientRect();

    // Misma medida que usa page-flip: X relativa al libro dentro de su bloque
    const bookX = (clientX) => {
        const box = area();

        return box ? clientX - box.left - flip.getRender().getRect().left : clientX;
    };

    const begin = (event) => {
        const point = event.touches?.[0] ?? event;
        startX = bookX(point.clientX);
        startTime = Date.now();
    };

    /*
     * Al soltar, la hoja termina de pasar si se la arrastró al menos un 15 % de su ancho; si no, vuelve.
     * page-flip solo la pasa si cruza el lomo, y en el teléfono el lomo es el borde de la pantalla:
     * habría que llevar el dedo de un lado al otro. Por eso aquí se decide por el recorrido del gesto y
     * se hace la animación final. Un deslizamiento rápido (menos de 300 ms) pasa la hoja con turn().
     */
    const release = (event) => {
        if (startX === null) {
            return;
        }

        const point = event.changedTouches?.[0] ?? event;
        const dx = bookX(point.clientX) - startX;

        // Deslizamiento rápido con el dedo: pasa la hoja en la dirección del gesto
        if (event.type === 'touchend' && Date.now() - startTime < 300 && controller.state !== USER_FOLD) {
            if (Math.abs(dx) > 30) {
                turn(flip, dx < 0);
            }

            return;
        }

        const calc = controller.getCalculation();

        if (controller.state !== USER_FOLD || !calc) {
            return;
        }

        // page-flip no atiende este «soltar»: la decisión y la animación final las hace esta función
        event.stopImmediatePropagation();
        startX = null;

        const rect = flip.getRender().getRect();
        const done = (calc.getDirection() === FORWARD ? -dx : dx) >= rect.pageWidth * 0.15;
        const y = calc.getCorner() === 'bottom' ? rect.height : 0;

        // Lo mismo que hace page-flip al soltar, pero decidiendo por el recorrido del gesto
        controller.animateFlippingTo(calc.getPosition(), { x: done ? -rect.pageWidth : rect.pageWidth, y }, done);
        // Pone en cero el gesto de page-flip sin mover nada (como si hubiera sido un deslizamiento)
        flip.userStop({ x: 0, y: 0 }, true);
    };

    block.addEventListener('mousedown', begin, { capture: true, passive: true });
    block.addEventListener('touchstart', begin, { capture: true, passive: true });
    // En captura: corre antes de que page-flip atienda el mismo evento
    window.addEventListener('mouseup', release, { capture: true });
    window.addEventListener('touchend', release, { capture: true });
    ['mouseup', 'touchend', 'touchcancel'].forEach((type) => window.addEventListener(type, () => {
        // Después de que page-flip termine de atender el mismo evento
        setTimeout(() => { startX = null; }, 0);
    }));

    controller.getDirectionByPoint = (point) => {
        if (startX !== null && controller.state === USER_FOLD && Math.abs(point.x - startX) > 4) {
            return point.x > startX ? BACK : FORWARD;
        }

        return original(point);
    };
}

/**
 * Pasa una hoja hacia adelante o hacia atrás con su animación (teclado y deslizamiento rápido).
 * page-flip, con una sola hoja en pantalla, no puede volver atrás con flipPrev: busca la esquina en un
 * lugar donde no hay hoja. Aquí se parte de la esquina real de la hoja visible.
 */
function turn(flip, forward) {
    const controller = flip.getFlipController();
    const rect = flip.getRender().getRect();
    const portrait = flip.getOrientation?.() === 'portrait';
    const settings = flip.getSettings();
    const keep = settings.disableFlipByClick;

    settings.disableFlipByClick = false;
    controller.flip({
        x: forward ? rect.left + rect.pageWidth * 2 - 10 : rect.left + (portrait ? rect.pageWidth : 0) + 10,
        y: rect.top + 1,
    });
    settings.disableFlipByClick = keep;
}

/**
 * Teléfonos del sitio (?portada=1): la tapa se luce un momento, el libro se abre solo y pasa una hoja.
 * Con ?reel=1 la muestra se cargó oculta (resources/js/site.js) y espera la señal de que ya se ve.
 */
function autoplay(flip) {
    if (!window.invCoverAutoplay) {
        return;
    }

    const standby = new URLSearchParams(window.location.search).has('reel');
    const play = new Promise((resolve) => {
        if (!standby) {
            resolve();
            return;
        }

        window.addEventListener('message', (event) => {
            if (event.origin === window.location.origin && event.data === 'bida:cover-play') {
                resolve();
            }
        });
    });

    play.then(() => {
        setTimeout(() => turn(flip, true), 1800);
        setTimeout(() => turn(flip, true), 5200);
    });
}

/** Espiral de alambre fija sobre el lomo: con dos hojas va al centro; con una, a la izquierda. */
function placeSpiral(flip, block) {
    const area = block.querySelector('.stf__block');

    if (!area) {
        return;
    }

    let spiral = area.querySelector('.nb-spiral');

    if (!spiral) {
        spiral = document.createElement('div');
        spiral.className = 'nb-spiral';
        spiral.setAttribute('aria-hidden', 'true');
        area.appendChild(spiral);
    }

    const rect = flip.getRender().getRect();
    spiral.style.left = `${rect.left + rect.pageWidth}px`;
    spiral.style.top = `${rect.top}px`;
    spiral.style.height = `${rect.height}px`;
    spiral.style.setProperty('--nb-page-width', `${rect.pageWidth}px`);
}

export function initNotebook(root) {
    const block = root.querySelector('[data-nb-pages]');
    const pages = [...block.querySelectorAll(':scope > [data-nb-page]')];
    const fromParam = new URLSearchParams(window.location.search).get('hojas') === 'todas';

    if (!pages.length) {
        return;
    }

    if (fromParam || prefersReducedMotion()) {
        stack(root, pages, fromParam);
        return;
    }

    const allLink = root.querySelector('[data-nb-all]');

    if (allLink) {
        allLink.href = linkWith('hojas', 'todas');
    }

    // Que el libro abierto entre en la pantalla junto con la ayuda de abajo
    const maxHeight = Math.max(420, Math.min(PAGE.height * 1.25, window.innerHeight - 130));
    const maxWidth = Math.round((maxHeight * PAGE.width) / PAGE.height);

    // Tocar dentro del juego o del formulario no empieza a doblar la hoja
    root.querySelectorAll('[data-nb-nodrag]').forEach((zone) => {
        ['mousedown', 'touchstart'].forEach((type) => zone.addEventListener(type, (event) => event.stopPropagation(), { passive: true }));
    });

    root.classList.add('nb-book--flip');

    const flip = new PageFlip(block, {
        width: PAGE.width,
        height: PAGE.height,
        size: 'stretch',
        minWidth: 260,
        maxWidth,
        minHeight: Math.round((260 * PAGE.height) / PAGE.width),
        maxHeight,
        showCover: true,
        usePortrait: true,
        mobileScrollSupport: true,
        // Un toque suelto no pasa la hoja: hay que arrastrarla o deslizarla
        disableFlipByClick: true,
        // Doblar la esquina al pasar el ratón se cruza con el arrastre y pasa hojas de más
        showPageCorners: false,
        // El deslizamiento rápido lo resuelve followGesture (el de page-flip no vuelve atrás con una hoja)
        swipeDistance: 100000,
        flippingTime: 850,
        maxShadowOpacity: 0.45,
        startPage: Math.min(Number(root.dataset.start) || 0, pages.length - 1),
    });

    flip.loadFromHTML(pages);
    followGesture(flip, block);
    const status = root.querySelector('[data-nb-status]');
    const teach = root.querySelector('[data-nb-teach]');
    const counted = new WeakSet();
    let turned = false;

    const hideTeach = () => {
        teach?.classList.add('is-gone');
        setTimeout(() => teach?.remove(), 600);
    };

    setTimeout(hideTeach, 7000);

    const update = () => {
        const index = flip.getCurrentPageIndex();
        const last = pages.length - 1;
        const landscape = flip.getOrientation?.() === 'landscape';
        const visible = [pages[index], landscape ? pages[index + 1] : null].filter(Boolean);
        const folios = visible.map((page) => page.querySelector('.nb-folio')?.textContent.trim()).filter(Boolean);

        if (status) {
            status.textContent = index === 0
                ? 'Tapa'
                : index >= last
                    ? 'Contratapa'
                    : `Hoja ${folios.join('–') || index} de ${last - 1}`;
        }

        root.classList.toggle('is-landscape', landscape);
        root.classList.toggle('is-closed', index === 0);
        root.classList.toggle('is-ended', index >= last);

        placeSpiral(flip, block);

        visible.forEach((page) => {
            if (page.hasAttribute('data-nb-count') && !counted.has(page)) {
                counted.add(page);
                countUp(page);
            }
        });
    };

    flip.on('flip', () => {
        if (!turned) {
            turned = true;
            hideTeach();
        }

        update();
    });
    flip.on('changeOrientation', update);
    flip.on('update', update);
    flip.on('init', update);
    window.addEventListener('resize', () => requestAnimationFrame(() => placeSpiral(flip, block)));
    update();

    // Mientras se arrastra, el cursor «agarra» la hoja
    flip.on('changeState', (event) => root.classList.toggle('is-dragging', event.data === USER_FOLD));

    autoplay(flip);

    document.addEventListener('keydown', (event) => {
        if (isTyping(document.activeElement) || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        if (event.key === 'ArrowRight') {
            turn(flip, true);
        } else if (event.key === 'ArrowLeft') {
            turn(flip, false);
        }
    });

    // Enlaces a una parte del libro (menú, índice de la historia, «volver al inicio»)
    document.addEventListener('click', (event) => {
        const link = event.target.closest?.('a[href^="#"]');
        const id = link ? decodeURIComponent(link.getAttribute('href').slice(1)) : '';
        const target = id ? document.getElementById(id) : null;
        const page = target?.closest('[data-nb-page]');
        const index = page ? pages.indexOf(page) : -1;

        if (index < 0) {
            return;
        }

        event.preventDefault();
        flip.turnToPage(index);
        update();
        root.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
    });
}
