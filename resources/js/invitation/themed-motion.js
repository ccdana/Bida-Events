/**
 * Movimiento de las plantillas de la colección «nueva» (partials/shell/themed).
 *
 * - Parallax: cada elemento con data-parallax="0.12" se mueve a esa fracción del scroll. Las capas
 *   de fondo son fijas (se desplazan con el scroll de la página) y las de adentro de la portada se
 *   mueven respecto del centro de la pantalla. Un solo requestAnimationFrame por cuadro y solo
 *   transform, así no hay saltos ni en celulares modestos.
 * - Stickers: al tocar uno ([data-sticker]) se estira como goma y vuelve a su lugar.
 *
 * Con movimiento reducido o en modo historia no hay parallax.
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

parallax();
stickers();
