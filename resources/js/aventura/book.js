/**
 * «Libro de aventuras»: las hojas del cuaderno se pasan con una vuelta de página realista (page-flip).
 * - En el teléfono se ve una hoja por vez; en pantallas anchas, el libro abierto de a dos.
 * - Se pasa arrastrando la esquina o deslizando, con los botones ‹ › y con las flechas del teclado.
 *   Tocar dentro de una hoja no la pasa, para que el juego y la respuesta funcionen.
 * - Los enlaces del menú (#historia, #memoria…) llevan a la hoja donde está esa parte.
 * - Con «reducir movimiento» o ?hojas=todas las hojas quedan apiladas y se leen con scroll, igual que
 *   sin JavaScript.
 */
import { PageFlip } from 'page-flip';
import { prefersReducedMotion } from '../story/effects.js';
import { countUp } from '../story/gestures.js';

// Proporción de una hoja (ancho × alto) en píxeles de referencia
const PAGE = { width: 460, height: 660 };

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

    // Que el libro abierto entre en la pantalla junto con los botones
    const maxHeight = Math.max(420, Math.min(PAGE.height * 1.25, window.innerHeight - 150));
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
        disableFlipByClick: true,
        // Doblar la esquina al pasar el ratón se cruza con los botones y las flechas: pasa hojas de más
        showPageCorners: false,
        flippingTime: 850,
        maxShadowOpacity: 0.45,
        startPage: Math.min(Number(root.dataset.start) || 0, pages.length - 1),
    });

    flip.loadFromHTML(pages);

    const status = root.querySelector('[data-nb-status]');
    const prev = root.querySelector('[data-nb-prev]');
    const next = root.querySelector('[data-nb-next]');
    const counted = new WeakSet();

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

        if (prev) prev.disabled = index === 0;
        if (next) next.disabled = index + (landscape && index > 0 ? 2 : 1) > last;

        root.classList.toggle('is-landscape', landscape);
        root.classList.toggle('is-closed', index === 0);
        root.classList.toggle('is-ended', index >= last);

        visible.forEach((page) => {
            if (page.hasAttribute('data-nb-count') && !counted.has(page)) {
                counted.add(page);
                countUp(page);
            }
        });
    };

    flip.on('flip', update);
    flip.on('changeOrientation', update);
    flip.on('init', update);
    update();

    prev?.addEventListener('click', () => flip.flipPrev());
    next?.addEventListener('click', () => flip.flipNext());
    root.querySelector('[data-nb-open]')?.addEventListener('click', () => flip.flipNext());

    document.addEventListener('keydown', (event) => {
        if (isTyping(document.activeElement) || event.altKey || event.ctrlKey || event.metaKey) {
            return;
        }

        if (event.key === 'ArrowRight') {
            flip.flipNext();
        } else if (event.key === 'ArrowLeft') {
            flip.flipPrev();
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
