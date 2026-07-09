import lottie from 'lottie-web';
import invitationLoopIcon from '../lottie-icons/invitation-loop-icon.json';
import clockLoopIcon from '../lottie-icons/clock-loop-icon.json';
import calendarLoopIcon from '../lottie-icons/calendar-loop-icon.json';
import videoLoopIcon from '../lottie-icons/video-loop-icon.json';
import eyeImageLoopIcon from '../lottie-icons/eye-image-loop-icon.json';

const ICONS = {
    invitation: invitationLoopIcon,
    clock: clockLoopIcon,
    calendar: calendarLoopIcon,
    video: videoLoopIcon,
    'eye-image': eyeImageLoopIcon,
};

const instances = new WeakMap();

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

const applyPrimaryColorToLottie = (animationData, colorValue) => {
    const data = structuredClone(animationData);
    const rgb = parseCssColor(colorValue);

    const walkLayers = (layers) => {
        if (!Array.isArray(layers)) {
            return;
        }

        layers.forEach((layer) => {
            if (layer.nm !== 'control' || !Array.isArray(layer.ef)) {
                return;
            }

            const primaryEffect = layer.ef.find((effect) => effect.nm === 'primary');
            const colorEffect = primaryEffect?.ef?.find((effect) => effect.nm === 'Color');

            if (colorEffect?.v) {
                colorEffect.v.k = rgb;
            }
        });
    };

    walkLayers(data.layers);

    (data.assets ?? []).forEach((asset) => walkLayers(asset.layers));

    return data;
};

const destroyLottie = (element) => {
    const instance = instances.get(element);

    if (!instance) {
        return;
    }

    instance.destroy();
    instances.delete(element);
    element.replaceChildren();
};

const initLottieElement = (element) => {
    const iconName = element.dataset.lottieIcon;
    const source = ICONS[iconName];

    if (!source) {
        return;
    }

    destroyLottie(element);

    const animation = lottie.loadAnimation({
        container: element,
        renderer: 'svg',
        loop: true,
        autoplay: true,
        animationData: applyPrimaryColorToLottie(source, getPrimaryColor()),
    });

    instances.set(element, animation);
    element.dataset.lottieInitialized = 'true';
    element.dataset.lottieColor = getPrimaryColor();
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

window.initLottieIcons = initLottieIcons;
window.refreshLottieIconColors = refreshLottieIconColors;
