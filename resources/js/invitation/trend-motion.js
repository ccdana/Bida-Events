/**
 * Movimiento de la colección «tendencias» (templates/tendencia, App\Support\TrendTemplates).
 *
 * [data-fit]: el texto llena el ancho de su caja con la tipografía que haya elegido el cliente (el
 * nombre de la revista en «Edición especial», el nombre estampado en el enterito de «Tendedero»).
 * Se mide después de que cargan las fuentes y otra vez si cambia el ancho. data-fit-max y
 * data-fit-min limitan el tamaño (en px) para que un nombre corto no quede gigante ni uno largo,
 * ilegible: si ni al mínimo entra, vuelve a partirse en renglones.
 */
function fitText() {
    const elements = [...document.querySelectorAll('[data-fit]')];

    if (!elements.length) {
        return () => {};
    }

    const fit = (element) => {
        const box = element.parentElement;
        const style = getComputedStyle(box);
        const available = box.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight);

        if (available <= 0) {
            return;
        }

        // Se mide a un tamaño conocido y se escala: una sola medida por elemento
        element.style.setProperty('--fit-size', '100px');
        element.classList.add('is-fitted');

        const width = element.scrollWidth;
        const max = Number(element.dataset.fitMax) || 480;
        const min = Number(element.dataset.fitMin) || 18;
        const size = Math.max(min, Math.min(max, (100 * available) / Math.max(width, 1)));

        element.style.setProperty('--fit-size', `${size.toFixed(2)}px`);
        element.classList.toggle('is-fitted', size > min || element.scrollWidth <= available);
    };

    const fitAll = () => elements.forEach(fit);

    (document.fonts?.ready ?? Promise.resolve()).then(fitAll);

    if ('ResizeObserver' in window) {
        let queued = false;
        const observer = new ResizeObserver(() => {
            if (!queued) {
                queued = true;
                requestAnimationFrame(() => {
                    queued = false;
                    fitAll();
                });
            }
        });

        elements.forEach((element) => observer.observe(element.parentElement));
    } else {
        window.addEventListener('resize', fitAll, { passive: true });
    }

    return fitAll;
}

const refit = fitText();

// Se mide de nuevo cuando la apertura se retira: algunas aperturas escalan lo que hay debajo
document.addEventListener('click', (event) => {
    if (event.target.closest?.('[data-cover-trigger]')) {
        setTimeout(refit, 900);
        setTimeout(refit, 2600);
    }
});
