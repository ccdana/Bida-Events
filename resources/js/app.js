/**
 * ────────────────────────────────────────────────────────────────────────────
 * Bida Events — Entry Point (Core Bundle)
 *
 * Este archivo es el punto de entrada principal.  Solo carga de forma
 * síncrona lo estrictamente necesario (Alpine.js + axios ≈ 30 kB).
 * Todo lo demás se carga dinámicamente según lo que la página necesite.
 * ────────────────────────────────────────────────────────────────────────────
 */
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ═══════════════════════════════════════════════════════════════════════════
//  LOADING INDICATOR
//
//  Barra de progreso animada (estilo GitHub/YouTube) que aparece en la
//  parte superior de la pantalla mientras los chunks dinámicos se cargan.
//  Solo se muestra si hay chunks pendientes; en páginas ligeras (admin,
//  login) no aparece.
// ═══════════════════════════════════════════════════════════════════════════

const createLoader = () => {
    const bar = document.createElement('div');
    bar.id = 'app-chunk-loader';
    bar.setAttribute('aria-hidden', 'true');

    // Estilos inline mínimos para que el loader aparezca antes que el CSS
    // del bundle (el CSS del bundle podría tardar en parsed/aplicarse).
    Object.assign(bar.style, {
        position:        'fixed',
        top:             '0',
        left:            '0',
        width:           '0%',
        height:          '3px',
        background:      'linear-gradient(90deg, var(--primary-color, #C9A96E), var(--accent-color, #E8D5A3))',
        zIndex:          '99999',
        transition:      'width 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease',
        opacity:         '1',
        pointerEvents:   'none',
        borderRadius:    '0 2px 2px 0',
        boxShadow:       '0 0 8px var(--primary-color, #C9A96E)',
    });

    document.body.prepend(bar);

    // Animación progresiva: simula avance mientras esperamos
    let progress = 0;
    const interval = setInterval(() => {
        // Avanza rápido al inicio, se ralentiza al acercarse a 90%
        progress += (90 - progress) * 0.08;
        bar.style.width = `${Math.min(progress, 90)}%`;
    }, 100);

    return {
        finish() {
            clearInterval(interval);
            bar.style.width = '100%';
            setTimeout(() => {
                bar.style.opacity = '0';
                setTimeout(() => bar.remove(), 300);
            }, 200);
        },
        abort() {
            clearInterval(interval);
            bar.remove();
        },
    };
};

// ═══════════════════════════════════════════════════════════════════════════
//  DYNAMIC IMPORTS  —  Code-splitting por detección de DOM
//
//  Cada import() genera un chunk separado en el build de Vite.
//  Solo se descargan los chunks que la página actual necesita.
// ═══════════════════════════════════════════════════════════════════════════

const loaders = [];

// ── Video.js (~500 kB) — solo si hay reproductores de video ─────────────
if (document.querySelector('[data-video-player]')) {
    loaders.push(import('./video-player.js'));
}

// ── Lottie Icons (~1 MB) — solo si hay iconos animados ──────────────────
if (document.querySelector('[data-lottie-icon]')) {
    loaders.push(
        import('./lottie-icons.js').then(({ initLottieIcons, refreshLottieIconColors }) => {
            window.initLottieIcons = initLottieIcons;
            window.refreshLottieIconColors = refreshLottieIconColors;
            initLottieIcons();
        }),
    );
}

// ── Gallery Stack (~110 kB con motion) — solo si hay galería ────────────
if (document.querySelector('[x-data*="galleryStack"]')) {
    loaders.push(import('./gallery-stack.js'));
}

// ── Itinerary Scroll (~12 kB) — solo si hay itinerario ──────────────────
if (document.querySelector('[x-data*="scrollItinerary"]')) {
    loaders.push(import('./itinerary-scroll.js'));
}

// ═══════════════════════════════════════════════════════════════════════════
//  BOOTSTRAP  —  Esperar chunks y luego iniciar Alpine
//
//  Alpine.start() se difiere hasta que TODOS los chunks dinámicos se
//  hayan cargado.  Esto garantiza que funciones globales como
//  galleryStack() y scrollItinerary() estén disponibles cuando Alpine
//  evalúe los atributos x-data en el DOM.
//
//  En páginas sin chunks (admin, login), el Promise.all se resuelve
//  inmediatamente y Alpine arranca sin delay perceptible.
// ═══════════════════════════════════════════════════════════════════════════

const loader = loaders.length > 0 ? createLoader() : null;

Promise.all(loaders)
    .then(() => {
        Alpine.start();
        loader?.finish();
    })
    .catch((error) => {
        console.error('[Bida Events] Error al cargar módulos dinámicos:', error);

        // Iniciar Alpine de todas formas para que los componentes inline
        // de Blade (countdown, rsvpForm, etc.) sigan funcionando.
        Alpine.start();
        loader?.finish();
    });
