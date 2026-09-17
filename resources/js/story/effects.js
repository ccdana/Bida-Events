/**
 * Efectos compartidos de las tarjetas:
 * - inclinación: el giroscopio del teléfono (o el ratón en computadora) escribe --tilt-x / --tilt-y
 *   entre -1 y 1 en <html>, y el CSS mueve la foto, sus brillos y los pétalos;
 * - celebración: una lluvia de pétalos desde un punto y una vibración corta. Se dispara con
 *   window.dispatchEvent(new CustomEvent('inv-celebrate', { detail: { rect, amount } })).
 * Todo se apaga con «reducir movimiento».
 */

export const prefersReducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

export function vibrate(pattern) {
    try {
        navigator.vibrate?.(pattern);
    } catch {
        // Navegadores que no lo permiten sin interacción: se ignora
    }
}

const clamp = (value) => Math.min(Math.max(value, -1), 1);

export function initTilt() {
    if (prefersReducedMotion()) {
        return;
    }

    const root = document.documentElement;
    const target = { x: 0, y: 0 };
    const current = { x: 0, y: 0 };
    let frame = null;

    const step = () => {
        current.x += (target.x - current.x) * 0.12;
        current.y += (target.y - current.y) * 0.12;
        root.style.setProperty('--tilt-x', current.x.toFixed(3));
        root.style.setProperty('--tilt-y', current.y.toFixed(3));

        frame = Math.abs(target.x - current.x) + Math.abs(target.y - current.y) > 0.002
            ? requestAnimationFrame(step)
            : null;
    };

    const set = (x, y) => {
        target.x = clamp(x);
        target.y = clamp(y);
        frame ??= requestAnimationFrame(step);
    };

    if (window.matchMedia?.('(pointer: fine)').matches) {
        window.addEventListener('pointermove', (event) => {
            set((event.clientX / window.innerWidth) * 2 - 1, (event.clientY / window.innerHeight) * 2 - 1);
        }, { passive: true });

        return;
    }

    const listen = () => {
        window.addEventListener('deviceorientation', (event) => {
            if (event.gamma === null || event.beta === null) {
                return;
            }

            // Teléfono en la mano: unos 45° hacia atrás es la posición neutra
            set(event.gamma / 28, (event.beta - 45) / 28);
        }, { passive: true });
    };

    // iPhone pide permiso, y solo dentro de un toque: el primero es el que abre la carta
    if (typeof window.DeviceOrientationEvent?.requestPermission === 'function') {
        document.addEventListener('click', () => {
            window.DeviceOrientationEvent.requestPermission()
                .then((state) => state === 'granted' && listen())
                .catch(() => {});
        }, { once: true, capture: true });
    } else if ('DeviceOrientationEvent' in window) {
        listen();
    }
}

export function celebrate({ rect = null, amount = 26 } = {}) {
    vibrate([20, 50, 20, 50, 40]);

    if (prefersReducedMotion()) {
        return;
    }

    const x = rect ? rect.left + rect.width / 2 : window.innerWidth / 2;
    const y = rect ? rect.top + rect.height / 2 : window.innerHeight / 2;

    const burst = document.createElement('div');
    burst.className = 'inv-burst';
    burst.setAttribute('aria-hidden', 'true');
    burst.style.left = `${x}px`;
    burst.style.top = `${y}px`;

    for (let i = 0; i < amount; i++) {
        const petal = document.createElement('i');
        const angle = (Math.PI * 2 * i) / amount + (Math.random() - 0.5) * 0.6;
        const distance = 70 + Math.random() * 130;

        petal.style.setProperty('--dx', `${(Math.cos(angle) * distance).toFixed(1)}px`);
        petal.style.setProperty('--dy', `${(Math.sin(angle) * distance - 40).toFixed(1)}px`);
        petal.style.setProperty('--turn', `${Math.round(Math.random() * 540 - 270)}deg`);
        petal.style.setProperty('--size', `${(0.55 + Math.random() * 0.65).toFixed(2)}rem`);
        petal.style.setProperty('--delay', `${Math.round(Math.random() * 120)}ms`);
        petal.dataset.tone = String(i % 3);
        burst.appendChild(petal);
    }

    document.body.appendChild(burst);
    setTimeout(() => burst.remove(), 2200);
}

export function initCelebrations() {
    window.addEventListener('inv-celebrate', (event) => celebrate(event.detail ?? {}));
}
