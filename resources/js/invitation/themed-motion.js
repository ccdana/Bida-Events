/**
 * Movimiento de las plantillas de la colección «nueva» (partials/shell/themed).
 *
 * - Parallax: cada elemento con data-parallax="0.12" se mueve a esa fracción del scroll. Las capas
 *   de fondo son fijas (se desplazan con el scroll de la página) y las de adentro de la portada se
 *   mueven respecto del centro de la pantalla. Un solo requestAnimationFrame por cuadro y solo
 *   transform, así no hay saltos ni en celulares modestos.
 * - Stickers: al tocar uno ([data-sticker]) se estira como goma y vuelve a su lugar.
 * - Toques con gesto ([data-poke="swing|pop|tip|spin|spritz"]): la etiqueta que se mece en su hilo,
 *   el medallón que salta, la tarjeta de lugar que se tambalea, la joya que gira, el frasco que
 *   rocía. Van con la Web Animations API y se suman (composite: add) a lo que la pieza ya tenga:
 *   no pisan su entrada CSS ni el parallax. Si se toca otra vez a mitad del gesto, empieza de nuevo.
 *
 * Con movimiento reducido no hay parallax ni gestos; en modo historia, tampoco parallax.
 */
const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

function parallax() {
    const layers = [...document.querySelectorAll('[data-parallax]')].map((element) => ({
        element,
        speed: Number(element.dataset.parallax) || 0,
        // Texturas que se repiten: se mueven dentro de una baldosa y nunca se les ve el borde
        loop: Number(element.dataset.parallaxLoop) || 0,
        fixed: getComputedStyle(element).position === 'fixed',
        visible: true,
    }));

    if (!layers.length) {
        return;
    }

    // Las capas de adentro de la página solo se calculan mientras se ven
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
            const layer = layers.find((item) => item.element === entry.target);

            if (layer) {
                layer.visible = entry.isIntersecting;
            }
        }), { rootMargin: '20% 0px' });

        layers.filter((layer) => !layer.fixed).forEach((layer) => observer.observe(layer.element));
    }

    let queued = false;

    const paint = () => {
        queued = false;

        const still = reduced.matches || document.documentElement.classList.contains('inv-story-on');
        const middle = window.innerHeight / 2;

        layers.forEach((layer) => {
            if (still) {
                layer.element.style.transform = '';
                return;
            }

            if (layer.fixed) {
                const shift = window.scrollY * layer.speed;
                const offset = layer.loop ? shift % layer.loop : shift;

                layer.element.style.transform = `translate3d(0, ${(-offset).toFixed(1)}px, 0)`;
                return;
            }

            if (layer.visible) {
                const box = layer.element.parentElement.getBoundingClientRect();
                const offset = (box.top + box.height / 2 - middle) * layer.speed;

                layer.element.style.transform = `translate3d(0, ${offset.toFixed(1)}px, 0)`;
            }
        });
    };

    const request = () => {
        if (!queued) {
            queued = true;
            requestAnimationFrame(paint);
        }
    };

    window.addEventListener('scroll', request, { passive: true });
    window.addEventListener('resize', request, { passive: true });
    reduced.addEventListener?.('change', request);
    request();
}

function stickers() {
    document.addEventListener('click', (event) => {
        const sticker = event.target.closest?.('[data-sticker]');

        if (!sticker || reduced.matches) {
            return;
        }

        sticker.classList.remove('is-poked');
        // Reinicia la animación si se toca otra vez antes de que termine
        void sticker.offsetWidth;
        sticker.classList.add('is-poked');
        sticker.addEventListener('animationend', () => sticker.classList.remove('is-poked'), { once: true });
    });
}

// Los gestos de cada toque, en el sistema de la pieza (su transform-origin lo pone la hoja del tema)
const POKES = {
    // Cuelga de su hilo: se mece y se va calmando
    swing: {
        keyframes: [0, 11, -7, 4, -2, 0].map((angle) => ({ transform: `rotate(${angle}deg)` })),
        options: { duration: 1400, easing: 'ease-out' },
    },
    // Salta y vuelve a su lugar
    pop: {
        keyframes: [
            { transform: 'scale(1)' },
            { transform: 'translateY(-6%) scale(1.14)', offset: 0.35 },
            { transform: 'scale(0.95)', offset: 0.7 },
            { transform: 'scale(1)' },
        ],
        options: { duration: 620, easing: 'ease-out' },
    },
    // Una tarjeta doblada en carpa que se tambalea hacia atrás y vuelve
    tip: {
        keyframes: [0, -22, 9, -4, 0].map((angle) => ({ transform: `perspective(600px) rotateX(${angle}deg)` })),
        options: { duration: 1100, easing: 'ease-out' },
    },
    // Una moneda que da la vuelta completa
    spin: {
        keyframes: [{ transform: 'rotateY(0deg)' }, { transform: 'rotateY(360deg)' }],
        options: { duration: 900, easing: 'cubic-bezier(0.34, 1.3, 0.64, 1)' },
    },
    // Se aprieta el atomizador: la pieza baja un poco y sus nubecitas ([data-poke-puff]) salen
    spritz: {
        keyframes: [
            { transform: 'translateY(0) scale(1)' },
            { transform: 'translateY(4%) scale(0.97, 1.02)', offset: 0.3 },
            { transform: 'translateY(0) scale(1)' },
        ],
        options: { duration: 520, easing: 'ease-out' },
        // Cada nubecita se abre hacia su lado (--puff-x, en la hoja del tema)
        puff: (drift) => [
            { opacity: 0, transform: 'translate(0, 0) scale(0.2)' },
            { opacity: 0.85, offset: 0.2 },
            { opacity: 0, transform: `translate(${drift}, -140%) scale(1.7)` },
        ],
        puffOptions: { duration: 1500, easing: 'cubic-bezier(0.2, 0.7, 0.3, 1)' },
    },
};

function pokes() {
    if (typeof Element.prototype.animate !== 'function') {
        return;
    }

    document.addEventListener('click', (event) => {
        const piece = event.target.closest?.('[data-poke]');
        const gesture = POKES[piece?.dataset.poke];

        if (!gesture || reduced.matches) {
            return;
        }

        piece.getAnimations?.().filter((animation) => animation.id === 'poke').forEach((animation) => animation.cancel());
        piece.animate(gesture.keyframes, { ...gesture.options, composite: 'add', id: 'poke' });

        if (gesture.puff) {
            piece.querySelectorAll('[data-poke-puff]').forEach((puff, index) => {
                const drift = getComputedStyle(puff).getPropertyValue('--puff-x').trim() || '0px';

                puff.animate(gesture.puff(drift), { ...gesture.puffOptions, delay: 120 + index * 90 });
            });
        }
    });
}

parallax();
stickers();
pokes();
