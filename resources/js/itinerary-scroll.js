// Línea de lectura: la luz sigue la altura de pantalla donde el ojo lee
const READING_LINE = 0.55;

// Resorte críticamente amortiguado: la luz alcanza el scroll sin rebotar
const STIFFNESS = 110;
const DAMPING = 2 * Math.sqrt(STIFFNESS);

const ACTIVATION_RANGE = 90;   // px antes del nodo en que empieza a encenderse
const FLASH_SPREAD = 36;       // px de ancho del destello al pasar
const RESIDUAL_GLOW = 0.2;
const MAX_TRAIL = 72;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
const prefersReducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

const isDarkBackground = () => {
    const match = getComputedStyle(document.body).backgroundColor.match(/\d+(\.\d+)?/g);

    if (!match) {
        return false;
    }

    const [r, g, b] = match.map(Number);

    return (0.2126 * r + 0.7152 * g + 0.0722 * b) / 255 < 0.42;
};

export function scrollItinerary() {
    // ═══════════════════════════════════════════════════════════════════════
    //  Estado y referencias DOM en el closure, fuera de la reactividad de
    //  Alpine: el bucle escribe estilos directamente y solo corre mientras
    //  la sección es visible y la luz no se ha asentado.
    // ═══════════════════════════════════════════════════════════════════════
    let track = null;
    let fill = null;
    let light = null;
    let items = [];
    let nodes = [];
    let nodeOffsets = [];
    let spineTop = 0;
    let spineHeight = 1;

    let position = 0;
    let velocity = 0;
    let lastTime = 0;
    let rafId = null;
    let visible = false;
    let reducedMotion = false;
    const written = new WeakMap();

    let intersectionObserver = null;
    let resizeObserver = null;

    const setVar = (element, name, value) => {
        let cache = written.get(element);

        if (!cache) {
            cache = {};
            written.set(element, cache);
        }

        if (cache[name] !== value) {
            cache[name] = value;
            element.style.setProperty(name, value);
        }
    };

    const measure = () => {
        if (!track || nodes.length === 0) {
            return;
        }

        const trackTop = track.getBoundingClientRect().top;
        const centers = nodes.map((node) => {
            const rect = node.getBoundingClientRect();

            return rect.top + rect.height / 2 - trackTop;
        });

        spineTop = centers[0];
        spineHeight = Math.max(centers[centers.length - 1] - centers[0], 1);
        nodeOffsets = centers.map((center) => center - spineTop);

        track.style.setProperty('--spine-top', `${spineTop}px`);
        track.style.setProperty('--spine-height', `${spineHeight}px`);
    };

    const targetPosition = () => {
        const lineY = window.innerHeight * READING_LINE;
        const spineViewportTop = track.getBoundingClientRect().top + spineTop;

        return clamp(lineY - spineViewportTop, 0, spineHeight);
    };

    const paint = () => {
        const speed = Math.abs(velocity);

        setVar(track, '--progress', (position / spineHeight).toFixed(4));
        light.style.transform = `translate3d(0, ${position.toFixed(2)}px, 0)`;
        setVar(light, '--trail', Math.min(speed * 0.1, MAX_TRAIL).toFixed(1));
        setVar(light, '--dir', velocity < -1 ? '-1' : '1');
        setVar(light, '--glow', (0.6 + Math.min(speed / 1200, 0.4)).toFixed(3));

        items.forEach((item, index) => {
            const distance = position - nodeOffsets[index];
            const activation = clamp((distance + ACTIVATION_RANGE) / ACTIVATION_RANGE, 0, 1);
            const flash = Math.exp(-(distance * distance) / (2 * FLASH_SPREAD * FLASH_SPREAD));

            setVar(item, '--a', activation.toFixed(3));
            setVar(item, '--glow', Math.max(flash, activation * RESIDUAL_GLOW).toFixed(3));
        });
    };

    const frame = (time) => {
        rafId = null;

        const dt = clamp((time - lastTime) / 1000, 0.001, 0.05);
        lastTime = time;

        const target = targetPosition();

        if (reducedMotion) {
            position = target;
            velocity = 0;
        } else {
            const acceleration = STIFFNESS * (target - position) - DAMPING * velocity;
            velocity += acceleration * dt;
            position += velocity * dt;
        }

        const settled = Math.abs(target - position) < 0.25 && Math.abs(velocity) < 1.5;

        if (settled) {
            position = target;
            velocity = 0;
        }

        paint();

        if (visible && !settled) {
            rafId = requestAnimationFrame(frame);
        }
    };

    const wake = () => {
        if (rafId === null && track) {
            lastTime = performance.now();
            rafId = requestAnimationFrame(frame);
        }
    };

    const onScroll = () => {
        if (visible) {
            wake();
        }
    };

    const onResize = () => {
        measure();
        wake();
    };

    return {
        init() {
            track = this.$refs.track;

            if (!track) {
                return;
            }

            fill = track.querySelector('[data-itinerary-fill]');
            light = track.querySelector('[data-itinerary-light]');
            items = [...track.querySelectorAll('[data-itinerary-item]')];
            nodes = items.map((item) => item.querySelector('[data-itinerary-node]'));
            reducedMotion = prefersReducedMotion();

            if (isDarkBackground()) {
                track.dataset.light = 'additive';
            }

            measure();

            // La posición inicial es la del scroll actual, sin animar desde cero
            position = targetPosition();
            paint();

            intersectionObserver = new IntersectionObserver(([entry]) => {
                visible = entry.isIntersecting;

                if (visible) {
                    measure();
                    wake();
                }
            }, { rootMargin: '20% 0px' });
            intersectionObserver.observe(track);

            // Las imágenes y fuentes que cargan tarde cambian la posición de los nodos
            if (typeof ResizeObserver !== 'undefined') {
                resizeObserver = new ResizeObserver(onResize);
                resizeObserver.observe(track);
            }

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onResize, { passive: true });

            this.$nextTick(() => window.initLottieIcons?.());
        },

        destroy() {
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onResize);
            intersectionObserver?.disconnect();
            resizeObserver?.disconnect();

            if (rafId !== null) {
                cancelAnimationFrame(rafId);
                rafId = null;
            }

            track = fill = light = null;
            items = [];
            nodes = [];
        },
    };
}

window.scrollItinerary = scrollItinerary;
