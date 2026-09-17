/**
 * Gestos de las tarjetas: mantener presionado para abrir (el sello de la carta) y raspar para
 * revelar (el tiempo juntos). Los dos son puertas del modo historia ([data-story-gate]): escuchan
 * «story-open» para abrirse solos y marcan data-gate-done al terminar.
 *
 * El contenido siempre está en el HTML debajo de la puerta: sin JavaScript se lee directo, y un
 * lector de pantalla lo recorre igual. Dentro del editor las puertas nacen abiertas.
 */
import { celebrate, prefersReducedMotion, vibrate } from './effects.js';

const insideEditor = () => window.self !== window.top && !window.invDemo;

const formatNumber = (value, grouped) => (grouped ? String(value).replace(/\B(?=(\d{3})+(?!\d))/g, '.') : String(value));

/** Cuenta desde 0 hasta el valor de cada [data-count-to] dentro de `scope`. */
export function countUp(scope, duration = 1400) {
    scope.querySelectorAll('[data-count-to]').forEach((element, position) => {
        const target = Number(element.dataset.countTo) || 0;
        const grouped = element.dataset.countFormat === 'thousands';

        if (prefersReducedMotion() || target === 0) {
            element.textContent = formatNumber(target, grouped);
            return;
        }

        const delay = position * 120;
        const start = performance.now() + delay;

        element.textContent = '0';

        const step = (now) => {
            const progress = Math.min(Math.max((now - start) / duration, 0), 1);
            const eased = 1 - Math.pow(1 - progress, 3);

            element.textContent = formatNumber(Math.round(target * eased), grouped);

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };

        requestAnimationFrame(step);
    });
}

/**
 * Mantener presionado: el anillo se llena mientras el dedo (o Espacio/Enter) sigue apoyado y al
 * completarse se abre. Soltar antes lo vacía despacio. Un lector de pantalla activa el botón con un
 * «click» sin puntero (detail 0) y abre directo.
 */
export function holdToOpen({ duration = 1100 } = {}) {
    let frame = null;
    let startedAt = 0;
    let startProgress = 0;
    let ticks = 0;

    return {
        opened: false,
        holding: false,
        progress: 0,
        nudged: false,

        init() {
            this.$el.addEventListener('story-open', () => this.open());

            if (insideEditor()) {
                this.opened = true;
            }
        },

        start(event) {
            if (this.opened || this.holding || event?.repeat) {
                return;
            }

            if (event?.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            this.holding = true;
            this.nudged = false;
            startedAt = performance.now();
            startProgress = this.progress;
            ticks = 0;
            vibrate(8);
            cancelAnimationFrame(frame);

            const fill = (now) => {
                if (!this.holding) {
                    return;
                }

                this.progress = Math.min(startProgress + (now - startedAt) / duration, 1);

                // Un pulso suave a cada tercio, como un latido que se acelera
                const reached = Math.floor(this.progress * 3);
                if (reached > ticks && this.progress < 1) {
                    ticks = reached;
                    vibrate(10 + reached * 6);
                }

                this.progress >= 1 ? this.open() : (frame = requestAnimationFrame(fill));
            };

            frame = requestAnimationFrame(fill);
        },

        stop() {
            if (!this.holding) {
                return;
            }

            this.holding = false;
            cancelAnimationFrame(frame);

            // Un toque corto no alcanza: se avisa que hay que mantener
            if (this.progress < 0.2) {
                this.nudged = true;
            }

            let last = performance.now();
            const drain = (now) => {
                if (this.holding || this.opened) {
                    return;
                }

                this.progress = Math.max(this.progress - (now - last) / 450, 0);
                last = now;

                if (this.progress > 0) {
                    frame = requestAnimationFrame(drain);
                }
            };

            frame = requestAnimationFrame(drain);
        },

        /** Activación sin puntero (teclado virtual o lector de pantalla): abre sin mantener. */
        activate(event) {
            if (event.detail === 0 && !this.holding) {
                this.open();
            }
        },

        open() {
            if (this.opened) {
                return;
            }

            cancelAnimationFrame(frame);

            // La carta crece desde la altura doblada hasta la real, y después queda libre
            const letter = this.$el.querySelector('[data-gate-content]');
            if (letter) {
                // En la carta y no en el contenedor: Alpine reescribe el style del contenedor con cada avance del anillo
                letter.style.setProperty('--gate-full', `${letter.scrollHeight}px`);
                setTimeout(() => letter.style.setProperty('--gate-full', 'none'), 1400);
            }

            this.holding = false;
            this.progress = 1;
            this.opened = true;
            vibrate([18, 60, 34]);

            const seal = this.$el.querySelector('[data-gate-trigger]');
            celebrate({ rect: seal?.getBoundingClientRect(), amount: 16 });
        },
    };
}

/**
 * Raspar para revelar: un lienzo con brillo metálico tapa el contenido y el dedo lo borra. Cuando
 * se descubre cerca de la mitad, el resto se desvanece y los números cuentan desde cero.
 */
export function scratchReveal({ threshold = 0.45 } = {}) {
    let canvas = null;
    let context = null;
    let last = null;
    let strokes = 0;
    let observer = null;
    let lastBuzz = 0;

    const cssVar = (element, name, fallback) => getComputedStyle(element).getPropertyValue(name).trim() || fallback;

    return {
        revealed: false,
        scratching: false,

        init() {
            this.$el.addEventListener('story-open', () => this.reveal());
            canvas = this.$refs.canvas;

            if (!canvas || insideEditor()) {
                this.revealed = true;
                return;
            }

            context = canvas.getContext('2d', { willReadFrequently: true });

            if (!context) {
                this.revealed = true;
                return;
            }

            this.paint();

            // Si cambia el tamaño antes de raspar, se vuelve a pintar entero
            observer = new ResizeObserver(() => {
                if (!this.revealed && strokes === 0) {
                    this.paint();
                }
            });
            observer.observe(canvas);

            canvas.addEventListener('pointerdown', (event) => this.begin(event));
            canvas.addEventListener('pointermove', (event) => this.move(event));
            canvas.addEventListener('pointerup', () => this.end());
            canvas.addEventListener('pointercancel', () => this.end());
            canvas.addEventListener('pointerleave', () => this.end());
        },

        paint() {
            // Tamaño de maquetación: la escena puede estar girada fuera de pantalla al pintar
            const rect = { width: canvas.offsetWidth, height: canvas.offsetHeight };

            if (rect.width === 0 || rect.height === 0) {
                return;
            }

            const ratio = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.round(rect.width * ratio);
            canvas.height = Math.round(rect.height * ratio);
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.globalCompositeOperation = 'source-over';

            const from = cssVar(this.$el, '--scratch-from', '#b98a92');
            const to = cssVar(this.$el, '--scratch-to', '#f2d7db');
            const ink = cssVar(this.$el, '--scratch-ink', '#3a1d24');

            const gradient = context.createLinearGradient(0, 0, rect.width, rect.height);
            gradient.addColorStop(0, from);
            gradient.addColorStop(0.5, to);
            gradient.addColorStop(1, from);
            context.fillStyle = gradient;
            context.fillRect(0, 0, rect.width, rect.height);

            // Destellos pequeños para que se lea como papel metalizado
            context.fillStyle = 'rgba(255, 255, 255, 0.55)';
            for (let i = 0; i < 46; i++) {
                const x = (Math.sin(i * 12.9898) * 43758.5453 % 1 + 1) % 1 * rect.width;
                const y = (Math.sin(i * 78.233) * 12345.6789 % 1 + 1) % 1 * rect.height;
                context.beginPath();
                context.arc(x, y, (i % 3) * 0.6 + 0.6, 0, Math.PI * 2);
                context.fill();
            }

            const label = this.$el.dataset.scratchLabel || 'Raspa aquí';
            const family = getComputedStyle(this.$el).getPropertyValue('--font-titles').trim() || 'serif';
            context.fillStyle = ink;
            context.textAlign = 'center';
            context.textBaseline = 'middle';
            context.font = `600 ${Math.max(15, Math.min(rect.width / 16, 22))}px ${family}`;
            context.fillText(label, rect.width / 2, rect.height / 2);
        },

        point(event) {
            const rect = canvas.getBoundingClientRect();

            return { x: event.clientX - rect.left, y: event.clientY - rect.top, width: rect.width };
        },

        begin(event) {
            if (this.revealed) {
                return;
            }

            event.preventDefault();

            try {
                canvas.setPointerCapture(event.pointerId);
            } catch {
                // Punteros sintéticos no admiten captura; el raspe sigue igual
            }

            this.scratching = true;
            last = this.point(event);
            this.scratch(last);
        },

        move(event) {
            if (!this.scratching || this.revealed) {
                return;
            }

            const current = this.point(event);
            this.scratch(current);
            last = current;

            const now = performance.now();
            if (now - lastBuzz > 140) {
                lastBuzz = now;
                vibrate(4);
            }

            if (++strokes % 8 === 0 && this.cleared() >= threshold) {
                this.reveal();
            }
        },

        end() {
            if (!this.scratching) {
                return;
            }

            this.scratching = false;

            if (!this.revealed && this.cleared() >= threshold) {
                this.reveal();
            }
        },

        scratch(current) {
            context.globalCompositeOperation = 'destination-out';
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.lineWidth = Math.max(30, current.width * 0.1);
            context.beginPath();
            context.moveTo(last.x, last.y);
            context.lineTo(current.x + 0.1, current.y + 0.1);
            context.stroke();
        },

        /** Fracción descubierta, muestreando una rejilla de píxeles. */
        cleared() {
            const { width, height } = canvas;

            if (!width || !height) {
                return 1;
            }

            const data = context.getImageData(0, 0, width, height).data;
            const columns = 40;
            const rows = 20;
            let clear = 0;

            for (let row = 0; row < rows; row++) {
                for (let column = 0; column < columns; column++) {
                    const x = Math.floor((column + 0.5) * width / columns);
                    const y = Math.floor((row + 0.5) * height / rows);

                    if (data[(y * width + x) * 4 + 3] < 128) {
                        clear++;
                    }
                }
            }

            return clear / (columns * rows);
        },

        reveal() {
            if (this.revealed) {
                return;
            }

            this.revealed = true;
            this.scratching = false;
            observer?.disconnect();
            vibrate([14, 40, 24]);
            countUp(this.$el.closest('section') ?? this.$el);
            celebrate({ rect: this.$el.getBoundingClientRect(), amount: 18 });
        },
    };
}
