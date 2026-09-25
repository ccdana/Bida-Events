/**
 * Modo historia: la invitación se recorre por escenas a pantalla completa en vez de bajar con scroll.
 *
 * Cada escena es una parte que ya existe en el HTML (la portada #inicio, cada <section> de
 * #contenido y el pie), así que sin JavaScript la página se lee entera y en orden. Al arrancar se
 * enciende la clase html.inv-story-on (estilos en resources/css/invitation/story.css) y:
 * - toque a la derecha o deslizar a la izquierda avanza; a la izquierda retrocede;
 * - flechas del teclado, rueda del ratón al final de la escena y botones de abajo;
 * - las escenas con una puerta ([data-story-gate], p. ej. el sello o el raspe) se abren antes de
 *   avanzar: el primer «siguiente» la abre, el segundo sigue;
 * - «Ver todo» vuelve a la página completa con scroll, y se recuerda en esta pestaña.
 *
 * Con { optIn: true } es el modo historia de las invitaciones: no arranca solo (se abre con el
 * botón «Ver como historia», el menú o ?historias en el enlace), avanza solo cuando el invitado
 * toca o desliza, y cerrarlo vuelve a la página de siempre. Cada plantilla le pone su transición
 * (invitation/story.css, «Historia de cada plantilla»).
 */

const IGNORE = 'a, button, input, textarea, select, label, summary, video, iframe, canvas, [contenteditable], [data-story-ignore], .inv-gallery__stack';
const FIELDS = 'input, textarea, select, [contenteditable], [data-story-ignore], .inv-gallery__stack';
const SWIPE_DISTANCE = 56;
const TAP_TOLERANCE = 10;
const TAP_MAX_MS = 450;
const WHEEL_THRESHOLD = 70;
const WHEEL_PAUSE_MS = 900;
const DRAG_START = 12;
const TURN_MS = 950;

const root = document.documentElement;

const storageKey = () => `inv-story-off:${location.pathname}`;

const rememberScrollMode = (off) => {
    try {
        off ? sessionStorage.setItem(storageKey(), '1') : sessionStorage.removeItem(storageKey());
    } catch {
        // Sin almacenamiento (navegación privada): solo se pierde la preferencia
    }
};

const prefersScrollMode = () => {
    try {
        return sessionStorage.getItem(storageKey()) === '1';
    } catch {
        return false;
    }
};

/** Las escenas en orden de lectura: portada, secciones y pie. */
const collectScenes = () => [
    document.getElementById('inicio'),
    ...document.querySelectorAll('#contenido > section'),
    document.querySelector('body > footer'),
].filter(Boolean);

const sceneTitle = (scene, index) => {
    if (scene.id === 'inicio') return 'Portada';
    if (scene.tagName === 'FOOTER') return 'Final';

    const heading = scene.querySelector('h1, h2, .inv-letter__to');

    return heading?.textContent.trim().replace(/\s+/g, ' ') || `Parte ${index + 1}`;
};

const isBlocked = () => root.classList.contains('inv-lock') || root.classList.contains('inv-cover-waiting');

/** El enlace pide abrir directo como historia (?historias o #historias). */
const askedForStory = () => new URLSearchParams(location.search).has('historias') || location.hash === '#historias';

export function invitationStory({ enabled = true, optIn = false } = {}) {
    let scenes = [];
    let pointer = null;
    let wheelTotal = 0;
    let wheelPausedUntil = 0;
    let visited = 0;
    let turnTimer = null;

    return {
        ready: false,
        on: false,
        index: 0,
        count: 0,
        moved: false,
        announce: '',
        // Invitaciones: el botón de historia se ve arriba de todo y se retira al bajar (queda en el menú)
        atTop: true,

        init() {
            scenes = collectScenes();
            this.count = scenes.length;

            // En la vista previa del editor la página se edita completa; en la home (muestra) sí se luce
            const insideEditor = window.self !== window.top && !window.invDemo;

            if (!enabled || insideEditor || scenes.length < 2) {
                return;
            }

            scenes.forEach((scene, index) => {
                scene.classList.add('inv-scene');
                scene.dataset.sceneTitle = sceneTitle(scene, index);

                if (!scene.hasAttribute('tabindex')) {
                    scene.tabIndex = -1;
                }
            });

            document.addEventListener('pointerdown', (event) => this.onPointerDown(event));
            document.addEventListener('pointermove', (event) => this.onPointerMove(event), { passive: true });
            document.addEventListener('pointerup', (event) => this.onPointerUp(event));
            document.addEventListener('pointercancel', () => this.cancelPointer());
            document.addEventListener('click', (event) => this.onLinkClick(event), true);
            window.addEventListener('keydown', (event) => this.onKey(event));
            window.addEventListener('wheel', (event) => this.onWheel(event), { passive: true });
            // «Ver todo en una página» del menú
            window.addEventListener('inv-story-leave', () => this.on && this.leave());

            this.ready = true;

            if (optIn) {
                // «Ver como historia» del botón o del menú
                window.addEventListener('inv-story-enter', () => !this.on && this.start());

                if (askedForStory()) {
                    this.enter();
                }

                return;
            }

            if (!prefersScrollMode()) {
                this.enter();
            }
        },

        /** Abre la historia desde la portada. */
        start() {
            this.index = 0;
            window.scrollTo(0, 0);
            this.enter();
            this.scene?.focus({ preventScroll: true });
        },

        get scene() {
            return scenes[this.index];
        },

        enter() {
            this.on = true;
            root.classList.add('inv-story-on');
            root.classList.toggle('inv-story-invitation', optIn);
            if (!optIn) rememberScrollMode(false);
            this.render(null);
        },

        /** «Ver todo»: la página completa con scroll, parada en la escena que se estaba viendo. */
        leave() {
            const current = this.scene;

            this.on = false;
            root.classList.remove('inv-story-on', 'inv-story-invitation');

            if (optIn) {
                // Sin ?historias: recargar la página no vuelve a abrir la historia
                if (askedForStory()) {
                    const url = new URL(location.href);
                    url.searchParams.delete('historias');
                    if (url.hash === '#historias') url.hash = '';
                    history.replaceState(history.state, '', url);
                }
            } else {
                rememberScrollMode(true);
            }

            scenes.forEach((scene) => {
                scene.classList.remove('is-active', 'is-past', 'is-future', 'is-dragging');
                scene.inert = false;
            });

            requestAnimationFrame(() => current?.scrollIntoView({ block: 'start', behavior: 'instant' }));
        },

        /** Desde el scroll vuelve a la historia en la parte que ocupa la pantalla. */
        resume() {
            const found = scenes.findIndex((scene) => scene.getBoundingClientRect().bottom > window.innerHeight * 0.35);

            this.index = Math.max(found, 0);
            window.scrollTo(0, 0);
            this.enter();
            this.scene?.focus({ preventScroll: true });
        },

        go(target, { focus = false } = {}) {
            const next = Math.min(Math.max(target, 0), this.count - 1);

            if (next === this.index) {
                return;
            }

            const direction = next > this.index ? 'forward' : 'back';

            this.index = next;
            this.moved = true;
            this.render(direction, focus);
        },

        next(focus = false) {
            if (this.openGate()) {
                return;
            }

            // En la última escena, «siguiente» vuelve a empezar
            this.index === this.count - 1 ? this.go(0, { focus }) : this.go(this.index + 1, { focus });
        },

        prev(focus = false) {
            this.go(this.index - 1, { focus });
        },

        /** Si la escena tiene una puerta cerrada (sello, raspe…), la abre y no avanza. */
        openGate() {
            const gate = this.scene?.querySelector('[data-story-gate]:not([data-gate-done])');

            if (!gate) {
                return false;
            }

            gate.dispatchEvent(new CustomEvent('story-open'));
            gate.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

            return true;
        },

        render(direction, focus = false) {
            scenes.forEach((scene, index) => {
                const active = index === this.index;

                if (active && !scene.classList.contains('is-active')) {
                    scene.scrollTop = 0;
                }

                scene.classList.toggle('is-active', active);
                scene.classList.toggle('is-past', index < this.index);
                scene.classList.toggle('is-future', index > this.index);
                scene.inert = !active;
            });

            root.dataset.storyDirection = direction ?? 'none';
            root.dataset.storyIndex = String(this.index);

            // Hasta dónde llegó el recorrido (0 a 1): cada tema lo usa, p. ej. para que el jardín crezca
            visited = Math.max(visited, this.index);
            root.style.setProperty('--story-visited', (visited / Math.max(this.count - 1, 1)).toFixed(3));

            // Durante el cambio de escena el tema puede dibujar su transición (html.is-turning)
            if (direction) {
                root.classList.remove('is-turning');
                void root.offsetWidth;
                root.classList.add('is-turning');
                clearTimeout(turnTimer);
                turnTimer = setTimeout(() => root.classList.remove('is-turning'), TURN_MS);
            }

            const scene = this.scene;
            this.announce = `${this.index + 1} de ${this.count}: ${scene.dataset.sceneTitle}`;

            // El menú resalta la escena visible (en modo historia no hay scroll que lo indique)
            window.dispatchEvent(new CustomEvent('inv-story-change', { detail: { id: scene.id, index: this.index } }));

            if (focus) {
                scene.focus({ preventScroll: true });
            }
        },

        // ── Gestos ──────────────────────────────────────────────────────────

        onPointerDown(event) {
            if (!this.on || isBlocked() || !event.isPrimary || event.button > 0) {
                return;
            }

            const scene = event.target.closest?.('.inv-scene.is-active');

            if (!scene || event.target.closest(IGNORE)) {
                return;
            }

            pointer = { x: event.clientX, y: event.clientY, t: performance.now(), dragging: false, scene };
        },

        onPointerMove(event) {
            if (!pointer) {
                return;
            }

            const dx = event.clientX - pointer.x;
            const dy = event.clientY - pointer.y;

            if (!pointer.dragging && Math.abs(dx) > DRAG_START && Math.abs(dx) > Math.abs(dy) * 1.4) {
                pointer.dragging = true;
                pointer.scene.classList.add('is-dragging');
            }

            if (pointer.dragging) {
                // La escena sigue al dedo con resistencia, sin pasar de un tercio de pantalla
                const limited = Math.max(Math.min(dx * 0.45, window.innerWidth / 3), -window.innerWidth / 3);

                pointer.scene.style.setProperty('--story-drag', `${limited.toFixed(1)}px`);
                pointer.scene.style.setProperty('--story-drag-ratio', (limited / window.innerWidth).toFixed(3));
            }
        },

        onPointerUp(event) {
            if (!pointer) {
                return;
            }

            const { x, y, t, dragging, scene } = pointer;
            const dx = event.clientX - x;
            const dy = event.clientY - y;

            this.cancelPointer();

            if (dragging) {
                if (Math.abs(dx) >= SWIPE_DISTANCE) {
                    dx < 0 ? this.next() : this.prev();
                }

                return;
            }

            const isTap = Math.hypot(dx, dy) < TAP_TOLERANCE && performance.now() - t < TAP_MAX_MS;

            if (!isTap || window.getSelection?.().toString()) {
                return;
            }

            // El tercio izquierdo de la escena vuelve; el resto avanza
            const box = scene.getBoundingClientRect();
            event.clientX - box.left < box.width * 0.3 ? this.prev() : this.next();
        },

        cancelPointer() {
            if (pointer?.scene) {
                pointer.scene.classList.remove('is-dragging');
                pointer.scene.style.removeProperty('--story-drag');
                pointer.scene.style.removeProperty('--story-drag-ratio');
            }

            pointer = null;
        },

        onKey(event) {
            if (!this.on || isBlocked() || event.altKey || event.ctrlKey || event.metaKey) {
                return;
            }

            if (event.target.closest?.(FIELDS)) {
                return;
            }

            const actions = {
                // En las invitaciones, Escape cierra la historia y vuelve a la página
                ...(optIn ? { Escape: () => this.leave() } : {}),
                ArrowRight: () => this.next(true),
                PageDown: () => this.next(true),
                ArrowLeft: () => this.prev(true),
                PageUp: () => this.prev(true),
                Home: () => this.go(0, { focus: true }),
                End: () => this.go(this.count - 1, { focus: true }),
            };

            if (actions[event.key]) {
                event.preventDefault();
                actions[event.key]();
            }
        },

        /** Con ratón o trackpad: al llegar al final de la escena, seguir girando la rueda avanza. */
        onWheel(event) {
            if (!this.on || isBlocked() || event.target.closest?.(FIELDS)) {
                return;
            }

            const scene = this.scene;
            const atEnd = scene.scrollTop + scene.clientHeight >= scene.scrollHeight - 2;
            const atStart = scene.scrollTop <= 0;

            if ((event.deltaY > 0 && !atEnd) || (event.deltaY < 0 && !atStart)) {
                wheelTotal = 0;
                return;
            }

            wheelTotal += event.deltaY;

            if (Math.abs(wheelTotal) < WHEEL_THRESHOLD || performance.now() < wheelPausedUntil) {
                return;
            }

            wheelTotal > 0 ? this.next() : this.prev();
            wheelTotal = 0;
            wheelPausedUntil = performance.now() + WHEEL_PAUSE_MS;
        },

        /** Los enlaces internos (menú, «Leer la carta», «Volver al inicio») llevan a su escena. */
        onLinkClick(event) {
            if (!this.on) {
                return;
            }

            const link = event.target.closest?.('a[href^="#"]');
            const target = link && link.getAttribute('href').length > 1
                ? document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1)))
                : null;

            if (!target) {
                return;
            }

            const index = scenes.findIndex((scene) => scene === target || scene.contains(target) || target.contains(scene));

            if (index < 0) {
                return;
            }

            event.preventDefault();
            this.go(index, { focus: event.detail === 0 });
        },
    };
}
