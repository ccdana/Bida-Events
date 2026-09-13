import lottie from 'lottie-web';

// Cada ícono se importa bajo demanda: la página solo descarga los JSON que usa.
// La clave es el nombre sin sufijo, p. ej. 'clock' para clock-loop-icon.json.
const ICON_LOADERS = Object.fromEntries(
    Object.entries(import.meta.glob('../lottie-icons/*-loop-icon.json', { import: 'default' }))
        .map(([path, loader]) => [path.match(/([^/]+)-loop-icon\.json$/)[1], loader]),
);

const sources = new Map();
const instances = new WeakMap();
const loadTokens = new WeakMap();

const loadIcon = (name) => {
    if (!sources.has(name)) {
        sources.set(name, ICON_LOADERS[name]());
    }

    return sources.get(name);
};

const prefersReducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

const hexToLottieRgb = (hex) => {
    let normalized = hex.replace('#', '').trim();

    if (normalized.length === 3) {
        normalized = normalized.split('').map((char) => char + char).join('');
    }

    if (normalized.length !== 6) {
        return [0, 0, 0];
    }

    return [
        parseInt(normalized.slice(0, 2), 16) / 255,
        parseInt(normalized.slice(2, 4), 16) / 255,
        parseInt(normalized.slice(4, 6), 16) / 255,
    ];
};

const parseCssColor = (value) => {
    const color = value.trim();

    if (!color) {
        return hexToLottieRgb('#C9A96E');
    }

    if (color.startsWith('#')) {
        return hexToLottieRgb(color);
    }

    const rgbMatch = color.match(/rgba?\(([^)]+)\)/i);

    if (rgbMatch) {
        const [red, green, blue] = rgbMatch[1].split(',').map((part) => parseFloat(part.trim()));

        return [red / 255, green / 255, blue / 255];
    }

    return hexToLottieRgb('#C9A96E');
};

const getPrimaryColor = () => getComputedStyle(document.documentElement)
    .getPropertyValue('--primary-color')
    .trim() || '#C9A96E';

/**
 * Escribe el color primario (y opcionalmente el grosor de trazo 1–3) en la capa
 * `control`. Todas las formas leen esos valores por expresión.
 */
const applyControlValues = (animationData, colorValue, strokeWeight) => {
    const data = structuredClone(animationData);
    const rgb = parseCssColor(colorValue);
    const stroke = Number(strokeWeight);

    const walkLayers = (layers) => {
        if (!Array.isArray(layers)) {
            return;
        }

        layers.forEach((layer) => {
            if (layer.nm !== 'control' || !Array.isArray(layer.ef)) {
                return;
            }

            const colorEffect = layer.ef.find((effect) => effect.nm === 'primary')?.ef?.find((effect) => effect.nm === 'Color');

            if (colorEffect?.v) {
                colorEffect.v.k = rgb;
            }

            const strokeEffect = layer.ef.find((effect) => effect.nm === 'stroke')?.ef?.find((effect) => effect.nm === 'Menu');

            if (strokeEffect?.v && stroke >= 1 && stroke <= 3) {
                strokeEffect.v.k = stroke;
            }
        });
    };

    walkLayers(data.layers);

    (data.assets ?? []).forEach((asset) => walkLayers(asset.layers));

    return data;
};

// Las animaciones fuera de pantalla se pausan para ahorrar CPU y batería en móviles
let visibilityObserver = null;

const getVisibilityObserver = () => {
    if (visibilityObserver || typeof IntersectionObserver === 'undefined') {
        return visibilityObserver;
    }

    visibilityObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const animation = instances.get(entry.target);

            if (!animation || prefersReducedMotion()) {
                return;
            }

            if (entry.isIntersecting) {
                animation.play();
            } else {
                animation.pause();
            }
        });
    }, { rootMargin: '100px 0px' });

    return visibilityObserver;
};

const destroyLottie = (element) => {
    const instance = instances.get(element);

    if (!instance) {
        return;
    }

    visibilityObserver?.unobserve(element);
    instance.destroy();
    instances.delete(element);
    element.replaceChildren();
};

const initLottieElement = async (element) => {
    const iconName = element.dataset.lottieIcon;

    if (!ICON_LOADERS[iconName]) {
        return;
    }

    // Un token por carga evita que una respuesta tardía pise a una más reciente
    const token = Symbol(iconName);
    loadTokens.set(element, token);
    element.dataset.lottieInitialized = 'true';

    const source = await loadIcon(iconName);

    if (loadTokens.get(element) !== token || !element.isConnected) {
        return;
    }

    destroyLottie(element);

    const color = getPrimaryColor();
    const reducedMotion = prefersReducedMotion();
    const animation = lottie.loadAnimation({
        container: element,
        renderer: 'svg',
        loop: !reducedMotion,
        autoplay: false,
        animationData: applyControlValues(source, color, element.dataset.lottieStroke),
    });

    instances.set(element, animation);
    element.dataset.lottieColor = color;

    if (reducedMotion) {
        animation.addEventListener('DOMLoaded', () => animation.goToAndStop(0, true));

        return;
    }

    const observer = getVisibilityObserver();

    if (observer) {
        observer.observe(element);
    } else {
        animation.play();
    }
};

export const initLottieIcons = () => {
    document.querySelectorAll('[data-lottie-icon]').forEach((element) => {
        if (element.dataset.lottieInitialized === 'true') {
            return;
        }

        initLottieElement(element);
    });
};

export const refreshLottieIconColors = () => {
    document.querySelectorAll('[data-lottie-icon]').forEach((element) => {
        delete element.dataset.lottieInitialized;
        initLottieElement(element);
    });
};

// Window globals are set by app.js after the dynamic import resolves
// to avoid race conditions with components that call window.initLottieIcons().
