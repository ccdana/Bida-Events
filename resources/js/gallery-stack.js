import { animate } from 'motion';

// Pose de cada carta según su profundidad en la pila. La de arriba es neutra.
const DEPTH_PRESETS = [
    { x: 0, y: 0, rotate: 0, scale: 1 },
    { x: -6, y: 10, rotate: -2.6, scale: 0.955 },
    { x: 7, y: 19, rotate: 2.2, scale: 0.91 },
    { x: -3, y: 27, rotate: -1.4, scale: 0.87 },
];

const SWIPE_DISTANCE_RATIO = 0.28;   // fracción del ancho de la carta
const SWIPE_VELOCITY = 480;          // px/s
const VELOCITY_WINDOW_MS = 90;       // la velocidad se mide sobre los últimos ms del gesto
const TAP_TOLERANCE = 6;             // px antes de considerar que hubo arrastre
const DRAG_ROTATION = 11;            // grados al arrastrar un ancho de carta
const EXIT_ROTATION = 16;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
const lerp = (from, to, t) => from + (to - from) * t;
const presetAt = (depth) => DEPTH_PRESETS[Math.min(depth, DEPTH_PRESETS.length - 1)];
const prefersReducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

const velocityOf = (samples) => {
    if (samples.length < 2) {
        return 0;
    }

    const last = samples[samples.length - 1];
    const first = samples.find((sample) => last.t - sample.t <= VELOCITY_WINDOW_MS) ?? samples[0];
    const seconds = (last.t - first.t) / 1000;

    return seconds > 0 ? (last.x - first.x) / seconds : 0;
};

export function galleryStack(initialPhotos = [], initialSrcsets = []) {
    // ═══════════════════════════════════════════════════════════════════════
    //  Estado del gesto y referencias DOM fuera de la reactividad de Alpine:
    //  las transformaciones se escriben directo en style por cada frame, así
    //  un arrastre no re-renderiza el componente.
    // ═══════════════════════════════════════════════════════════════════════
    let cards = [];
    let states = [];
    let animations = [];
    let order = [];
    let drag = null;
    let paintQueued = false;
    const exits = new Map();   // carta → token de su salida en curso
    let proxy = null;

    const depthOf = (index) => order.indexOf(index);

    const paint = (index) => {
        const card = cards[index];
        const state = states[index];
        const depth = depthOf(index);

        card.style.transform = `translate3d(${state.x.toFixed(2)}px, ${state.y.toFixed(2)}px, 0) rotate(${state.rotate.toFixed(3)}deg) scale(${state.scale.toFixed(4)})`;
        card.style.opacity = state.opacity.toFixed(3);
        card.style.zIndex = exits.has(index) ? cards.length + 1 : cards.length - depth;
    };

    const paintAll = () => {
        paintQueued = false;
        cards.forEach((_, index) => paint(index));
    };

    const queuePaint = () => {
        if (!paintQueued) {
            paintQueued = true;
            requestAnimationFrame(paintAll);
        }
    };

    const stopAnimation = (index) => {
        animations[index]?.stop();
        animations[index] = null;
    };

    const syncTop = () => {
        cards.forEach((card, index) => card.classList.toggle('is-top', order[0] === index));

        if (proxy) {
            proxy.current = order[0] ?? 0;
        }
    };

    const restingPose = (index) => {
        const depth = depthOf(index);

        return { ...presetAt(depth), opacity: depth < DEPTH_PRESETS.length ? 1 : 0 };
    };

    /** Anima una carta desde su estado actual hacia `target` con un resorte. */
    const springCard = (index, target, { stiffness = 240, damping = 26, mass = 1, velocity = 0 } = {}) => {
        stopAnimation(index);

        const from = { ...states[index] };
        const controls = animate(0, 1, {
            type: 'spring',
            stiffness,
            damping,
            mass,
            velocity,
            onUpdate: (progress) => {
                const state = states[index];
                state.x = lerp(from.x, target.x, progress);
                state.y = lerp(from.y, target.y, progress);
                state.rotate = lerp(from.rotate, target.rotate, progress);
                state.scale = lerp(from.scale, target.scale, progress);
                state.opacity = clamp(lerp(from.opacity, target.opacity, progress), 0, 1);
                queuePaint();
            },
        });

        animations[index] = controls;

        return controls;
    };

    const settleStack = (exclude = null) => {
        order.forEach((index) => {
            // Las cartas que siguen saliendo terminan su vuelo antes de volver a la pila
            if (index !== exclude && !exits.has(index)) {
                springCard(index, restingPose(index));
            }
        });
    };

    /** Mientras se arrastra, las cartas de atrás avanzan en proporción al gesto. */
    const followDrag = (progress) => {
        order.forEach((index, depth) => {
            if (depth === 0 || exits.has(index)) {
                return;
            }

            stopAnimation(index);

            const from = presetAt(depth);
            const to = presetAt(depth - 1);
            const state = states[index];
            state.x = lerp(from.x, to.x, progress);
            state.y = lerp(from.y, to.y, progress);
            state.rotate = lerp(from.rotate, to.rotate, progress);
            state.scale = lerp(from.scale, to.scale, progress);
            state.opacity = depth - 1 < DEPTH_PRESETS.length ? 1 : 0;
        });
    };

    const exitDistance = (index) => (cards[index].offsetWidth || 320) * 0.6 + window.innerWidth * 0.6;

    /** Lanza la carta superior hacia `direction` y la devuelve al fondo de la pila. */
    const flingTop = (direction, velocity = 0) => {
        if (order.length <= 1) {
            return;
        }

        const index = order.shift();
        order.push(index);
        syncTop();

        if (prefersReducedMotion()) {
            cards.forEach((_, cardIndex) => {
                stopAnimation(cardIndex);
                Object.assign(states[cardIndex], restingPose(cardIndex));
            });
            states[order[0]].opacity = 0;
            springCard(order[0], restingPose(order[0]), { stiffness: 400, damping: 40 });
            paintAll();

            return;
        }

        stopAnimation(index);
        const exitToken = Symbol('exit');
        exits.set(index, exitToken);

        const from = { ...states[index] };
        const distance = direction * exitDistance(index);
        const travel = Math.abs(distance - from.x) || 1;
        let returned = false;

        const returnToBack = () => {
            if (returned) {
                return;
            }

            returned = true;

            // Si la carta se recuperó con "anterior" antes de terminar de salir, ya no vuelve al fondo
            if (exits.get(index) !== exitToken) {
                return;
            }

            exits.delete(index);
            stopAnimation(index);

            // Reaparece detrás, ligeramente abajo, y se acomoda en su lugar
            const pose = restingPose(index);
            Object.assign(states[index], { x: pose.x, y: pose.y + 16, rotate: pose.rotate, scale: pose.scale * 0.94, opacity: 0 });
            springCard(index, pose, { stiffness: 210, damping: 26 });
        };

        animations[index] = animate(0, 1, {
            type: 'spring',
            stiffness: 170,
            damping: 24,
            velocity: Math.abs(velocity) / travel,
            onUpdate: (progress) => {
                const state = states[index];
                state.x = lerp(from.x, distance, progress);
                state.y = lerp(from.y, from.y - 12, progress);
                state.rotate = lerp(from.rotate, from.rotate + direction * EXIT_ROTATION, progress);
                state.scale = from.scale;
                state.opacity = 1 - clamp((progress - 0.55) / 0.4, 0, 1);
                queuePaint();

                if (progress >= 0.96) {
                    returnToBack();
                }
            },
        });

        animations[index].finished?.then(returnToBack);
        settleStack(index);
    };

    /** Trae la última carta del fondo al frente, entrando desde `direction`. */
    const bringBack = (direction = -1) => {
        if (order.length <= 1) {
            return;
        }

        const index = order.pop();
        order.unshift(index);
        syncTop();
        stopAnimation(index);
        exits.delete(index);

        if (prefersReducedMotion()) {
            Object.assign(states[index], restingPose(index), { opacity: 0 });
        } else {
            Object.assign(states[index], {
                x: direction * exitDistance(index),
                y: -12,
                rotate: direction * EXIT_ROTATION,
                scale: 1,
                opacity: 0,
            });
        }

        springCard(index, restingPose(index), { stiffness: 190, damping: 24 });
        settleStack(index);
    };

    const onPointerMove = (event) => {
        if (!drag || event.pointerId !== drag.pointerId) {
            return;
        }

        const dx = event.clientX - drag.startX;
        const dy = event.clientY - drag.startY;

        if (!drag.moved && Math.hypot(dx, dy) > TAP_TOLERANCE) {
            drag.moved = true;
            cards[drag.index].classList.add('is-dragging');
        }

        const state = states[drag.index];
        state.x = drag.originX + dx;
        state.y = drag.originY + dy * 0.25;
        state.rotate = (state.x / drag.width) * DRAG_ROTATION * drag.lever;

        const now = performance.now();
        drag.samples.push({ t: now, x: event.clientX });

        while (drag.samples.length > 2 && now - drag.samples[0].t > VELOCITY_WINDOW_MS * 2) {
            drag.samples.shift();
        }

        followDrag(clamp(Math.abs(state.x) / (drag.width * 0.9), 0, 1));
        queuePaint();
    };

    const endDrag = (event) => {
        if (!drag || event.pointerId !== drag.pointerId) {
            return;
        }

        const { index, width, moved, samples } = drag;
        const card = cards[index];

        card.removeEventListener('pointermove', onPointerMove);
        card.removeEventListener('pointerup', endDrag);
        card.removeEventListener('pointercancel', endDrag);
        card.classList.remove('is-dragging');
        drag = null;

        if (!moved && event.type === 'pointerup') {
            flingTop(1);

            return;
        }

        const x = states[index].x;
        const velocity = velocityOf(samples);
        const fastEnough = Math.abs(velocity) > SWIPE_VELOCITY;
        const farEnough = Math.abs(x) > width * SWIPE_DISTANCE_RATIO;
        const cancelled = event.type === 'pointercancel';

        if (!cancelled && (farEnough || fastEnough)) {
            const direction = fastEnough ? Math.sign(velocity) : Math.sign(x);
            flingTop(direction || 1, velocity);

            return;
        }

        // Rebote subamortiguado: vuelve con un pequeño overshoot
        springCard(index, restingPose(index), {
            stiffness: 320,
            damping: 19,
            velocity: Math.abs(x) > 1 ? -velocity / x : 0,
        });
        settleStack(index);
    };

    return {
        photos: Array.isArray(initialPhotos) ? initialPhotos.filter(Boolean) : [],
        // srcset por foto (mismo índice que photos); null cuando la imagen no está en Cloudinary
        srcsets: Array.isArray(initialSrcsets) ? initialSrcsets : [],
        current: 0,
        interacted: false,

        init() {
            proxy = this;

            const stack = this.$refs.stack;

            if (!stack) {
                return;
            }

            cards = [...stack.querySelectorAll('[data-gallery-card]')];
            order = cards.map((_, index) => index);
            animations = cards.map(() => null);
            states = cards.map((_, index) => ({ ...restingPose(index) }));

            cards.forEach((card, index) => {
                card.addEventListener('pointerdown', (event) => this.onPointerDown(event, index));
            });

            paintAll();
            this.$nextTick(() => window.initLottieIcons?.());
        },

        destroy() {
            cards.forEach((_, index) => stopAnimation(index));
            proxy = null;
        },

        onPointerDown(event, index) {
            if (order[0] !== index || cards.length <= 1 || (event.pointerType === 'mouse' && event.button !== 0)) {
                return;
            }

            const card = cards[index];
            const rect = card.getBoundingClientRect();
            stopAnimation(index);

            drag = {
                index,
                pointerId: event.pointerId,
                startX: event.clientX,
                startY: event.clientY,
                originX: states[index].x,
                originY: states[index].y,
                width: rect.width || 320,
                // Tomar la carta por arriba o por abajo invierte el giro, como una palanca
                lever: event.clientY - rect.top < rect.height / 2 ? 1 : -1,
                samples: [{ t: performance.now(), x: event.clientX }],
                moved: false,
            };

            try {
                card.setPointerCapture?.(event.pointerId);
            } catch {
                // Algunos punteros (sintéticos o ya liberados) no admiten captura; el gesto sigue funcionando
            }
            card.addEventListener('pointermove', onPointerMove);
            card.addEventListener('pointerup', endDrag);
            card.addEventListener('pointercancel', endDrag);
            this.interacted = true;
        },

        swipeNext() {
            this.interacted = true;
            flingTop(1);
        },

        swipePrev() {
            this.interacted = true;
            bringBack(-1);
        },
    };
}

window.galleryStack = galleryStack;
