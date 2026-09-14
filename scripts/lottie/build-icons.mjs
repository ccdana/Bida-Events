/**
 * Generador de íconos Lottie propios de Bida Events.
 *
 * Produce JSON con la misma estructura que los íconos Lordicon "doodle" que ya
 * usa la invitación: una capa raíz `control` con los efectos `primary › Color`
 * y `stroke › Menu`, y trazos cuyo color y grosor se leen por expresión desde
 * esa capa. Así `resources/js/lottie-icons.js` los recolorea en tiempo de
 * ejecución cambiando un único valor.
 *
 * Cada ícono tiene dos precomposiciones: `in-reveal` (dibujado con Trim Paths)
 * y `loop-*`, que empieza y termina en la misma pose. La raíz reproduce solo
 * el segmento de loop, igual que los íconos existentes.
 *
 * Uso:
 *   node scripts/lottie/build-icons.mjs          → genera resources/lottie-icons/*-loop-icon.json
 *   node scripts/lottie/build-icons.mjs --check  → valida todos los JSON de resources/lottie-icons
 */
import { readdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const OUT_DIR = join(dirname(fileURLToPath(import.meta.url)), '..', '..', 'resources', 'lottie-icons');

const FR = 60;
const SIZE = 192;
const REVEAL = 60;
const LOOP = 120;
const STROKE = 6.5;

// ─── Geometría ──────────────────────────────────────────────────────────────

const round = (n) => Math.round(n * 100) / 100;

const roundShape = (shape) => ({
    i: shape.i.map(([x, y]) => [round(x), round(y)]),
    o: shape.o.map(([x, y]) => [round(x), round(y)]),
    v: shape.v.map(([x, y]) => [round(x), round(y)]),
    c: shape.c,
});

/** Convierte un path estilo SVG (comandos absolutos M, L, C, Z) a trazados Lottie. */
const svg = (d) => {
    const tokens = d.match(/[MLCZ]|-?\d*\.?\d+/gi);
    const shapes = [];
    let shape = null;
    let cursor = 0;
    const num = () => Number(tokens[cursor++]);
    const last = () => shape.v[shape.v.length - 1];

    while (cursor < tokens.length) {
        const command = tokens[cursor++].toUpperCase();

        if (command === 'M') {
            shape = { v: [[num(), num()]], i: [[0, 0]], o: [[0, 0]], c: false };
            shapes.push(shape);
        } else if (command === 'L') {
            shape.v.push([num(), num()]);
            shape.i.push([0, 0]);
            shape.o.push([0, 0]);
        } else if (command === 'C') {
            const [x1, y1, x2, y2, x, y] = [num(), num(), num(), num(), num(), num()];
            const [px, py] = last();
            shape.o[shape.o.length - 1] = [x1 - px, y1 - py];
            shape.v.push([x, y]);
            shape.i.push([x2 - x, y2 - y]);
            shape.o.push([0, 0]);
        } else if (command === 'Z') {
            const [fx, fy] = shape.v[0];
            const [lx, ly] = last();

            // Si el último vértice repite el primero, se fusionan para que la esquina cierre limpia
            if (shape.v.length > 1 && Math.abs(fx - lx) < 0.01 && Math.abs(fy - ly) < 0.01) {
                shape.i[0] = shape.i.pop();
                shape.v.pop();
                shape.o.pop();
            }

            shape.c = true;
        }
    }

    return shapes.map(roundShape);
};

/** Arco elíptico con béziers; 360° o más produce una elipse cerrada. */
const ellipse = (cx, cy, rx, ry, startDeg = -90, endDeg = 270) => {
    const segments = Math.max(1, Math.ceil(Math.abs(endDeg - startDeg) / 90));
    const step = (endDeg - startDeg) / segments;
    const k = (4 / 3) * Math.tan((step * Math.PI) / 180 / 4);
    const shape = { v: [], i: [], o: [], c: false };

    for (let s = 0; s <= segments; s++) {
        const angle = ((startDeg + step * s) * Math.PI) / 180;
        const dx = -rx * Math.sin(angle) * k;
        const dy = ry * Math.cos(angle) * k;
        shape.v.push([cx + rx * Math.cos(angle), cy + ry * Math.sin(angle)]);
        shape.i.push([-dx, -dy]);
        shape.o.push([dx, dy]);
    }

    if (Math.abs(endDeg - startDeg) >= 360) {
        shape.v.pop();
        shape.o.pop();
        shape.i[0] = shape.i.pop();
        shape.c = true;
    }

    return [roundShape(shape)];
};

const circle = (cx, cy, r, startDeg, endDeg) => ellipse(cx, cy, r, r, startDeg, endDeg);

// ─── Animación ──────────────────────────────────────────────────────────────

const EASE = {
    inOut: { o: { x: 0.45, y: 0 }, i: { x: 0.55, y: 1 } },
    out: { o: { x: 0.18, y: 0.6 }, i: { x: 0.3, y: 1 } },
    in: { o: { x: 0.6, y: 0 }, i: { x: 0.85, y: 0.45 } },
    linear: { o: { x: 0, y: 0 }, i: { x: 1, y: 1 } },
};

const stat = (k) => ({ a: 0, k });

/** frames: [[t, valor, easing?], ...]; el easing de cada tramo se toma del keyframe de salida. */
const anim = (frames) => ({
    a: 1,
    k: frames.map(([t, value, easing = 'inOut'], index) => {
        const s = Array.isArray(value) ? value : [value];

        if (index === frames.length - 1) {
            return { t, s };
        }

        const ease = EASE[easing];

        return { i: { ...ease.i }, o: { ...ease.o }, t, s };
    }),
});

const prop = (frames, fallback, map = (value) => value) => {
    if (!frames || frames.length === 0) {
        return stat(fallback);
    }

    if (frames.length === 1) {
        return stat(map(frames[0][1]));
    }

    return anim(frames.map(([t, value, easing]) => [t, map(value), easing]));
};

const firstValue = (frames, fallback) => (frames && frames.length ? frames[0][1] : fallback);

// ─── Estructura Lottie ──────────────────────────────────────────────────────

const colorExpression = (nm) => `var $bm_rt;\n$bm_rt = comp('${nm}').layer('control').effect('primary')('Color');`;
const strokeExpression = (nm) => `var $bm_rt;\n$bm_rt = $bm_mul($bm_div(value, 3), comp('${nm}').layer('control').effect('stroke')('Menu'));`;

const controlLayer = (op) => ({
    ddd: 0,
    ind: 1,
    ty: 3,
    nm: 'control',
    sr: 1,
    ks: {
        o: stat(0),
        r: stat(0),
        p: stat([0, 0]),
        a: stat([0, 0, 0]),
        s: stat([100, 100, 100]),
    },
    ao: 0,
    ef: [
        {
            ty: 5,
            nm: 'stroke',
            np: 3,
            mn: 'Pseudo/@@TIH9TRtySqK9417f7Gf0eQ',
            ix: 1,
            en: 1,
            ef: [{ ty: 7, nm: 'Menu', mn: 'Pseudo/@@TIH9TRtySqK9417f7Gf0eQ-0001', ix: 1, v: stat(3) }],
        },
        {
            ty: 5,
            nm: 'primary',
            np: 3,
            mn: 'ADBE Color Control',
            ix: 2,
            en: 1,
            ef: [{ ty: 2, nm: 'Color', mn: 'ADBE Color Control-0001', ix: 1, v: stat([0, 0, 0]) }],
        },
    ],
    ip: 0,
    op,
    st: 0,
    bm: 0,
});

const precompLayer = (ind, nm, refId, ip, op) => ({
    ddd: 0,
    ind,
    ty: 0,
    nm,
    refId,
    sr: 1,
    ks: {
        o: stat(100),
        r: stat(0),
        p: stat([SIZE / 2, SIZE / 2, 0]),
        a: stat([SIZE / 2, SIZE / 2, 0]),
        s: stat([100, 100, 100]),
    },
    ao: 0,
    w: SIZE,
    h: SIZE,
    ip,
    op,
    st: ip,
    bm: 0,
});

const shapeGroup = (compName, part, pathProps, trim) => {
    const items = pathProps.map((ks, index) => ({
        ind: index,
        ty: 'sh',
        ix: index + 1,
        ks,
        nm: `Path ${index + 1}`,
        hd: false,
    }));

    items.push(part.fill
        ? {
            ty: 'fl',
            c: { a: 0, k: [0, 0, 0, 1], x: colorExpression(compName) },
            o: stat(100),
            r: 1,
            bm: 0,
            nm: '.primary',
            cl: 'primary',
            hd: false,
        }
        : {
            ty: 'st',
            c: { a: 0, k: [0, 0, 0, 1], x: colorExpression(compName) },
            o: stat(100),
            w: { a: 0, k: STROKE, x: strokeExpression(compName) },
            lc: 2,
            lj: 2,
            bm: 0,
            nm: '.primary',
            cl: 'primary',
            hd: false,
        });

    if (trim) {
        items.push({ ty: 'tm', s: trim.s, e: trim.e, o: stat(0), m: 1, nm: 'Trim Paths 1', hd: false });
    }

    items.push({
        ty: 'tr',
        p: stat([0, 0]),
        a: stat([0, 0]),
        s: stat([100, 100]),
        r: stat(0),
        o: stat(100),
        sk: stat(0),
        sa: stat(0),
        nm: 'Transform',
    });

    return { ty: 'gr', it: items, nm: part.nm, np: items.length, cix: 2, bm: 0, ix: 1, hd: false };
};

/**
 * Construye las capas de una precomposición.
 * mode 'loop'   → usa las animaciones declaradas en part.loop.
 * mode 'reveal' → pose inicial del loop, dibujada con Trim Paths escalonado.
 */
const buildLayers = (compName, parts, mode) => {
    const indexOf = new Map(parts.map((part, index) => [part.nm, index + 1]));

    return parts.map((part, index) => {
        const loop = part.loop ?? {};
        const anchor = part.anchor ?? [SIZE / 2, SIZE / 2];
        const toPosition = ([dx, dy]) => [anchor[0] + dx, anchor[1] + dy, 0];
        const toScale = ([sx, sy]) => [sx, sy, 100];
        const pick = (frames, fallback, map) => (mode === 'loop'
            ? prop(frames, map ? map(fallback) : fallback, map)
            : stat(map ? map(firstValue(frames, fallback)) : firstValue(frames, fallback)));

        const ks = {
            o: pick(loop.opacity, 100),
            r: pick(loop.rotation, 0),
            p: pick(loop.position, [0, 0], toPosition),
            a: stat([anchor[0], anchor[1], 0]),
            s: pick(loop.scale, [100, 100], toScale),
        };

        const layer = {
            ddd: 0,
            ind: index + 1,
            ty: part.null ? 3 : 4,
            nm: part.nm,
            sr: 1,
            ks,
            ao: 0,
        };

        if (part.parent) {
            layer.parent = indexOf.get(part.parent);
        }

        if (!part.null) {
            const pathProps = part.paths.map((shape, pathIndex) => (mode === 'loop' && loop.morph
                ? prop(loop.morph.map(([t, shapes, easing]) => [t, shapes[pathIndex], easing]), shape)
                : stat(mode === 'reveal' && loop.morph ? loop.morph[0][1][pathIndex] : shape)));

            let trim = null;

            if (mode === 'loop' && (loop.trimStart || loop.trimEnd)) {
                trim = { s: prop(loop.trimStart, 0), e: prop(loop.trimEnd, 100) };
            } else if (mode === 'reveal') {
                const start = 4 + index * 5;
                trim = {
                    s: stat(firstValue(loop.trimStart, 0)),
                    e: anim([[start, 0, 'out'], [start + 34, firstValue(loop.trimEnd, 100)]]),
                };
            }

            layer.shapes = [shapeGroup(compName, part, pathProps, trim)];
        }

        return { ...layer, ip: 0, op: (mode === 'loop' ? LOOP : REVEAL) + 1, st: 0, bm: 0 };
    });
};

const buildIcon = (icon) => {
    const nm = `bida-line-${icon.slug}`;

    return {
        v: '5.12.1',
        fr: FR,
        ip: REVEAL,
        op: REVEAL + LOOP,
        w: SIZE,
        h: SIZE,
        nm,
        ddd: 0,
        assets: [
            { id: 'comp_0', nm: 'in-reveal', fr: FR, layers: buildLayers(nm, icon.parts, 'reveal') },
            { id: 'comp_1', nm: icon.loop, fr: FR, layers: buildLayers(nm, icon.parts, 'loop') },
        ],
        layers: [
            controlLayer(REVEAL + LOOP + 1),
            precompLayer(2, 'in-reveal', 'comp_0', 0, REVEAL),
            precompLayer(3, icon.loop, 'comp_1', REVEAL, REVEAL + LOOP + 1),
        ],
        markers: [
            { tm: 0, cm: 'in-reveal', dr: REVEAL },
            { tm: REVEAL, cm: `default:${icon.loop}`, dr: LOOP },
        ],
    };
};

// ─── Íconos ─────────────────────────────────────────────────────────────────
// Lienzo de 192×192. Posiciones de loop expresadas como desplazamiento desde el anchor.

const barPath = (x, top, width = 26, bottom = 150, r = 6) => svg(
    `M${x} ${bottom} L${x} ${top + r} C${x} ${top + r * 0.45} ${x + r * 0.45} ${top} ${x + r} ${top} `
    + `L${x + width - r} ${top} C${x + width - r * 0.45} ${top} ${x + width} ${top + r * 0.45} ${x + width} ${top + r} `
    + `L${x + width} ${bottom}`,
)[0];

/** Rectángulo de esquinas redondeadas como trazado cerrado. */
const roundRect = (x, y, w, h, r) => svg(
    `M${x + r} ${y} L${x + w - r} ${y} C${x + w - r * 0.45} ${y} ${x + w} ${y + r * 0.45} ${x + w} ${y + r} `
    + `L${x + w} ${y + h - r} C${x + w} ${y + h - r * 0.45} ${x + w - r * 0.45} ${y + h} ${x + w - r} ${y + h} `
    + `L${x + r} ${y + h} C${x + r * 0.45} ${y + h} ${x} ${y + h - r * 0.45} ${x} ${y + h - r} `
    + `L${x} ${y + r} C${x} ${y + r * 0.45} ${x + r * 0.45} ${y} ${x + r} ${y} Z`,
);

/** Falda acampanada con borde ondulado; `d` desplaza el ruedo para simular la tela que sigue al balanceo. */
const dressSkirt = (d) => svg(
    `M85 96 C77 118 ${62 + d * 0.4} 140 ${42 + d} 162 `
    + `C${54 + d} 170 ${64 + d} 162 ${75 + d} 168 `
    + `C${86 + d} 174 ${96 + d} 164 ${107 + d} 170 `
    + `C${118 + d} 176 ${130 + d} 164 ${150 + d} 162 `
    + `C${132 + d * 0.4} 140 115 118 107 96`,
);

/** Pliegues de la falda, desde la cintura hasta los valles del ruedo. */
const dressFolds = (d) => svg(
    `M93 104 C${89 + d * 0.2} 124 ${83 + d * 0.5} 146 ${78 + d} 165 `
    + `M100 104 C${104 + d * 0.2} 124 ${110 + d * 0.5} 146 ${116 + d} 167`,
);

const ICONS = [
    {
        slug: 'location',
        loop: 'loop-bounce',
        parts: [
            {
                nm: 'spark right',
                anchor: [158, 50],
                paths: svg('M150 46 L160 38 M154 62 L166 60'),
                loop: {
                    opacity: [[0, 0], [50, 0], [56, 100], [84, 100], [96, 0], [120, 0]],
                    scale: [[0, [80, 80]], [50, [80, 80], 'out'], [64, [105, 105]], [96, [105, 105]], [97, [80, 80]], [120, [80, 80]]],
                },
            },
            {
                nm: 'spark left',
                anchor: [34, 50],
                paths: svg('M42 46 L32 38 M38 62 L26 60'),
                loop: {
                    opacity: [[0, 0], [50, 0], [56, 100], [84, 100], [96, 0], [120, 0]],
                    scale: [[0, [80, 80]], [50, [80, 80], 'out'], [64, [105, 105]], [96, [105, 105]], [97, [80, 80]], [120, [80, 80]]],
                },
            },
            { nm: 'pin dot', parent: 'pin', anchor: [96, 80], paths: circle(96, 80, 13) },
            {
                nm: 'pin',
                anchor: [96, 150],
                paths: svg('M96 150 C84 138 58 110 58 80 C58 58 75 40 96 40 C117 40 134 58 134 80 C134 110 108 138 96 150 Z'),
                loop: {
                    position: [[0, [0, 0], 'out'], [28, [0, -16], 'in'], [54, [0, 0], 'out'], [64, [0, -5], 'in'], [74, [0, 0]], [120, [0, 0]]],
                    scale: [[0, [100, 100]], [50, [100, 100], 'out'], [56, [108, 92]], [72, [100, 100]], [120, [100, 100]]],
                },
            },
            {
                nm: 'ground',
                anchor: [96, 160],
                paths: [...ellipse(96, 160, 40, 9, 20, 160), ...ellipse(96, 160, 40, 9, 200, 236), ...ellipse(96, 160, 40, 9, 304, 340)],
                loop: {
                    scale: [[0, [100, 100], 'out'], [28, [72, 100], 'in'], [54, [100, 100]], [120, [100, 100]]],
                },
            },
        ],
    },
    {
        slug: 'crown',
        loop: 'loop-tilt',
        parts: [
            {
                nm: 'sparkle right',
                anchor: [166, 32],
                paths: svg('M166 20 L166 44 M154 32 L178 32'),
                loop: {
                    scale: [[0, [0, 0]], [24, [0, 0], 'out'], [40, [100, 100], 'in'], [58, [0, 0]], [120, [0, 0]]],
                    rotation: [[0, 0], [24, 0], [58, 45], [59, 0], [120, 0]],
                },
            },
            {
                nm: 'sparkle left',
                anchor: [26, 34],
                paths: svg('M26 24 L26 44 M16 34 L36 34'),
                loop: {
                    scale: [[0, [0, 0]], [64, [0, 0], 'out'], [80, [100, 100], 'in'], [98, [0, 0]], [120, [0, 0]]],
                    rotation: [[0, 0], [64, 0], [98, -45], [99, 0], [120, 0]],
                },
            },
            {
                nm: 'jewels',
                parent: 'crown',
                anchor: [96, 146],
                paths: [...circle(34, 60, 7), ...circle(96, 40, 7), ...circle(158, 60, 7), ...circle(96, 114, 6)],
            },
            {
                nm: 'crown',
                anchor: [96, 146],
                paths: [...svg('M44 128 L34 70 L70 98 L96 50 L122 98 L158 70 L148 128 Z'), ...svg('M44 146 L148 146')],
                loop: {
                    rotation: [[0, 0], [30, -6], [72, 5], [104, 0], [120, 0]],
                    position: [[0, [0, 0]], [30, [0, -4]], [72, [0, -2]], [104, [0, 0]], [120, [0, 0]]],
                },
            },
        ],
    },
    {
        // Vestido de gala en su percha: tirantes, escote corazón, cintura y falda que ondea con el balanceo
        slug: 'dress',
        loop: 'loop-sway',
        parts: [
            {
                nm: 'folds',
                parent: 'rig',
                paths: dressFolds(-4),
                loop: {
                    morph: [[0, dressFolds(-4)], [40, dressFolds(5)], [100, dressFolds(-5)], [120, dressFolds(-4)]],
                },
            },
            {
                nm: 'skirt',
                parent: 'rig',
                paths: dressSkirt(-4),
                loop: {
                    morph: [[0, dressSkirt(-4)], [40, dressSkirt(5)], [100, dressSkirt(-5)], [120, dressSkirt(-4)]],
                },
            },
            {
                nm: 'bodice',
                parent: 'rig',
                paths: svg(
                    'M80 37 L81 62 M112 37 L111 62 '
                    + 'M78 66 C82 58 90 58 96 67 C102 58 110 58 114 66 '
                    + 'M78 66 C77 76 80 86 85 94 M114 66 C115 76 112 86 107 94 '
                    + 'M85 94 C92 98 100 98 107 94',
                ),
            },
            { nm: 'hanger', parent: 'rig', paths: svg('M58 46 L96 30 L134 46') },
            {
                nm: 'rig',
                null: true,
                anchor: [96, 30],
                loop: {
                    rotation: [[0, 0], [30, 6], [60, 0], [90, -6], [120, 0]],
                },
            },
            { nm: 'hook', paths: svg('M96 30 L96 24 C96 19 102 17 102 12 C102 7 98 4 94 4 C90 4 87 7 87 10') },
        ],
    },
    {
        slug: 'hashtag',
        loop: 'loop-redraw',
        parts: [
            {
                nm: 'tilt',
                null: true,
                anchor: [96, 96],
                loop: {
                    rotation: [[0, 0], [60, -8], [120, 0]],
                    scale: [[0, [100, 100]], [60, [94, 94]], [120, [100, 100]]],
                },
            },
            {
                nm: 'bar top',
                parent: 'tilt',
                paths: svg('M44 76 L156 76'),
                loop: { trimEnd: [[0, 100], [14, 100, 'in'], [30, 0], [31, 0, 'out'], [50, 100], [120, 100]] },
            },
            {
                nm: 'bar bottom',
                parent: 'tilt',
                paths: svg('M36 116 L148 116'),
                loop: { trimEnd: [[0, 100], [26, 100, 'in'], [42, 0], [43, 0, 'out'], [62, 100], [120, 100]] },
            },
            { nm: 'bar left', parent: 'tilt', paths: svg('M80 38 L66 154') },
            { nm: 'bar right', parent: 'tilt', paths: svg('M128 38 L114 154') },
        ],
    },
    {
        slug: 'poll',
        loop: 'loop-bars',
        parts: [
            { nm: 'base', paths: svg('M32 158 L160 158') },
            {
                nm: 'bar 1',
                paths: [barPath(46, 104)],
                loop: { morph: [[0, [barPath(46, 104)]], [40, [barPath(46, 70)]], [80, [barPath(46, 112)]], [120, [barPath(46, 104)]]] },
            },
            {
                nm: 'bar 2',
                paths: [barPath(83, 64)],
                loop: { morph: [[0, [barPath(83, 64)]], [40, [barPath(83, 100)]], [80, [barPath(83, 80)]], [120, [barPath(83, 64)]]] },
            },
            {
                nm: 'bar 3',
                paths: [barPath(120, 88)],
                loop: { morph: [[0, [barPath(120, 88)]], [40, [barPath(120, 110)]], [80, [barPath(120, 56)]], [120, [barPath(120, 88)]]] },
            },
        ],
    },
    {
        slug: 'music',
        loop: 'loop-float',
        parts: [
            {
                nm: 'small note',
                anchor: [160, 96],
                paths: [...svg('M166 98 L166 66 C172 70 178 74 180 82'), ...ellipse(158, 100, 9, 7, 0, 360)],
                loop: {
                    opacity: [[0, 0], [20, 100], [80, 100], [108, 0], [120, 0]],
                    position: [[0, [0, 14], 'linear'], [110, [0, -22]], [111, [0, 14]], [120, [0, 14]]],
                    rotation: [[0, -8], [60, 8], [120, -8]],
                },
            },
            {
                nm: 'float',
                null: true,
                anchor: [100, 90],
                loop: {
                    position: [[0, [0, 0]], [60, [0, -8]], [120, [0, 0]]],
                    rotation: [[0, 0], [60, -4], [120, 0]],
                },
            },
            { nm: 'beams', parent: 'float', paths: svg('M76 54 L138 42 M76 72 L138 60') },
            { nm: 'stems', parent: 'float', paths: svg('M76 130 L76 54 M138 118 L138 42') },
            { nm: 'heads', parent: 'float', paths: [...ellipse(62, 132, 15, 11, 0, 360), ...ellipse(124, 120, 15, 11, 0, 360)] },
        ],
    },
    {
        slug: 'gift',
        loop: 'loop-open',
        parts: [
            {
                nm: 'confetti',
                anchor: [96, 50],
                paths: svg('M60 40 L52 30 M96 34 L96 20 M132 40 L140 30'),
                loop: {
                    opacity: [[0, 0], [18, 0], [28, 100], [48, 100], [58, 0], [120, 0]],
                    position: [[0, [0, 6]], [18, [0, 6], 'out'], [40, [0, -4]], [58, [0, -4]], [59, [0, 6]], [120, [0, 6]]],
                },
            },
            {
                nm: 'bow',
                parent: 'lid',
                anchor: [96, 74],
                paths: svg('M96 74 C86 56 64 52 66 66 C68 76 84 78 96 74 C108 78 124 76 126 66 C128 52 106 56 96 74'),
                loop: {
                    scale: [[0, [100, 100]], [14, [100, 100], 'out'], [30, [112, 112]], [56, [100, 100]], [120, [100, 100]]],
                },
            },
            {
                nm: 'lid',
                anchor: [96, 94],
                paths: svg('M36 74 L156 74 L156 94 L36 94 Z M96 74 L96 94'),
                loop: {
                    position: [[0, [0, 0], 'out'], [26, [0, -20]], [50, [0, -16], 'in'], [64, [0, 0], 'out'], [120, [0, 0]]],
                    rotation: [[0, 0, 'out'], [26, -7], [50, 4, 'in'], [64, 0], [120, 0]],
                },
            },
            {
                nm: 'box',
                anchor: [96, 156],
                paths: svg('M46 94 L46 156 L146 156 L146 94 M96 94 L96 156'),
                loop: {
                    scale: [[0, [100, 100]], [62, [100, 100], 'out'], [68, [105, 95]], [82, [100, 100]], [120, [100, 100]]],
                },
            },
        ],
    },
    {
        slug: 'rsvp',
        loop: 'loop-confirm',
        parts: [
            {
                nm: 'check',
                anchor: [150, 148],
                paths: svg('M140 149 L147 156 L161 141'),
                loop: { trimEnd: [[0, 100], [78, 100], [79, 0, 'out'], [98, 100], [120, 100]] },
            },
            {
                nm: 'badge',
                anchor: [150, 148],
                paths: circle(150, 148, 20),
                loop: {
                    scale: [[0, [100, 100]], [80, [100, 100], 'out'], [90, [112, 112]], [104, [100, 100]], [120, [100, 100]]],
                },
            },
            {
                nm: 'flap',
                parent: 'envelope',
                anchor: [90, 52],
                paths: svg('M30 52 L90 94 L150 52'),
                loop: {
                    morph: [
                        [0, svg('M30 52 L90 94 L150 52')],
                        [22, svg('M30 52 L90 16 L150 52')],
                        [56, svg('M30 52 L90 16 L150 52')],
                        [76, svg('M30 52 L90 94 L150 52')],
                        [120, svg('M30 52 L90 94 L150 52')],
                    ],
                },
            },
            {
                nm: 'envelope',
                anchor: [90, 128],
                paths: svg('M30 52 L150 52 L150 128 L30 128 Z M30 128 L74 90 M150 128 L106 90'),
                loop: {
                    position: [[0, [0, 0], 'out'], [12, [0, -6], 'in'], [26, [0, 0]], [120, [0, 0]]],
                },
            },
        ],
    },
    {
        slug: 'camera',
        loop: 'loop-shutter',
        parts: [
            {
                nm: 'flash',
                anchor: [150, 30],
                paths: svg('M150 30 L150 14 M162 38 L175 30 M138 38 L125 30'),
                loop: {
                    opacity: [[0, 0], [40, 0], [44, 100], [62, 100], [72, 0], [120, 0]],
                    scale: [[0, [70, 70]], [40, [70, 70], 'out'], [56, [110, 110]], [72, [110, 110]], [73, [70, 70]], [120, [70, 70]]],
                },
            },
            {
                nm: 'rig',
                null: true,
                anchor: [96, 110],
                loop: {
                    scale: [[0, [100, 100]], [40, [100, 100], 'in'], [46, [95, 95]], [58, [100, 100], 'out'], [120, [100, 100]]],
                },
            },
            {
                nm: 'lens inner',
                parent: 'rig',
                anchor: [96, 110],
                paths: circle(96, 110, 11),
                loop: {
                    scale: [[0, [100, 100]], [40, [100, 100], 'in'], [48, [20, 20]], [54, [20, 20], 'out'], [70, [100, 100]], [120, [100, 100]]],
                },
            },
            { nm: 'lens', parent: 'rig', paths: circle(96, 110, 28) },
            {
                nm: 'body',
                parent: 'rig',
                paths: [
                    ...svg('M40 70 L62 70 L74 52 L118 52 L130 70 L152 70 C160 70 166 76 166 84 L166 140 C166 148 160 154 152 154 L40 154 C32 154 26 148 26 140 L26 84 C26 76 32 70 40 70 Z'),
                    ...circle(146, 90, 4),
                ],
            },
        ],
    },
    {
        slug: 'heart',
        loop: 'loop-beat',
        parts: [
            {
                nm: 'pulse lines',
                paths: svg('M166 60 L178 54 M168 84 L182 84 M26 60 L14 54 M24 84 L10 84'),
                loop: {
                    opacity: [[0, 0], [6, 0], [12, 100], [40, 100], [52, 0], [120, 0]],
                    scale: [[0, [80, 80]], [6, [80, 80], 'out'], [30, [110, 110]], [52, [110, 110]], [53, [80, 80]], [120, [80, 80]]],
                },
            },
            { nm: 'shine', parent: 'heart', anchor: [96, 100], paths: svg('M56 72 C56 62 62 56 70 56') },
            {
                nm: 'heart',
                anchor: [96, 100],
                paths: svg('M96 150 C60 124 34 102 34 74 C34 54 50 40 68 40 C80 40 90 46 96 58 C102 46 112 40 124 40 C142 40 158 54 158 74 C158 102 132 124 96 150 Z'),
                loop: {
                    scale: [[0, [100, 100], 'out'], [10, [112, 112], 'in'], [20, [100, 100], 'out'], [30, [108, 108], 'in'], [46, [100, 100]], [120, [100, 100]]],
                },
            },
        ],
    },
    {
        slug: 'rings',
        loop: 'loop-clink',
        parts: [
            {
                nm: 'sparkle',
                anchor: [150, 38],
                paths: svg('M150 24 L150 52 M136 38 L164 38'),
                loop: {
                    opacity: [[0, 0], [48, 0], [56, 100], [82, 100], [96, 0], [120, 0]],
                    scale: [[0, [60, 60]], [48, [60, 60], 'out'], [64, [110, 110]], [96, [100, 100]], [97, [60, 60]], [120, [60, 60]]],
                },
            },
            {
                nm: 'ring right',
                anchor: [118, 110],
                paths: [...circle(118, 110, 38), ...svg('M106 64 L118 52 L130 64 L118 74 Z')],
                loop: {
                    position: [[0, [0, 0]], [30, [8, 0]], [54, [-3, 0], 'out'], [68, [0, 0]], [120, [0, 0]]],
                    rotation: [[0, 0], [30, 9], [54, -3, 'out'], [68, 0], [120, 0]],
                },
            },
            {
                nm: 'ring left',
                anchor: [74, 120],
                paths: circle(74, 120, 38),
                loop: {
                    position: [[0, [0, 0]], [30, [-8, 0]], [54, [3, 0], 'out'], [68, [0, 0]], [120, [0, 0]]],
                },
            },
        ],
    },
    {
        // Galería: fotos apiladas; la de adelante se inclina como al barajarlas y la de atrás asoma
        slug: 'gallery',
        loop: 'loop-shuffle',
        parts: [
            {
                nm: 'sun',
                parent: 'front photo',
                anchor: [116, 80],
                paths: circle(116, 80, 9),
                loop: {
                    scale: [[0, [100, 100]], [34, [100, 100], 'out'], [50, [128, 128]], [74, [100, 100]], [120, [100, 100]]],
                },
            },
            { nm: 'mountains', parent: 'front photo', paths: svg('M50 138 L78 108 L96 126 L112 112 L130 138') },
            {
                nm: 'front photo',
                anchor: [90, 152],
                paths: roundRect(40, 52, 100, 100, 9),
                loop: {
                    rotation: [[0, 0, 'out'], [26, -7], [52, 2], [68, 0], [120, 0]],
                    position: [[0, [0, 0], 'out'], [26, [-4, -6]], [52, [0, 1]], [68, [0, 0]], [120, [0, 0]]],
                },
            },
            {
                nm: 'back photo',
                anchor: [108, 88],
                paths: svg('M58 52 L58 47 C58 42 62 38 67 38 L149 38 C154 38 158 42 158 47 L158 129 C158 134 154 138 149 138 L140 138'),
                loop: {
                    position: [[0, [0, 0], 'out'], [26, [7, -4]], [52, [-1, 1]], [68, [0, 0]], [120, [0, 0]]],
                },
            },
        ],
    },
    {
        // Itinerario: línea de tiempo con tres momentos; un punto la recorre y cada momento se destaca al llegar
        slug: 'itinerary',
        loop: 'loop-travel',
        parts: [
            {
                nm: 'traveler',
                anchor: [62, 48],
                paths: circle(62, 48, 3),
                loop: {
                    position: [[0, [0, 0]], [14, [0, 0]], [36, [0, 48]], [56, [0, 48]], [78, [0, 96]], [102, [0, 96]], [103, [0, 0]], [120, [0, 0]]],
                    opacity: [[0, 100], [96, 100], [102, 0], [110, 0], [118, 100], [120, 100]],
                },
            },
            {
                nm: 'moment 1',
                anchor: [90, 49],
                paths: svg('M90 44 L150 44 M90 55 L126 55'),
                loop: {
                    scale: [[0, [100, 100]], [4, [108, 100], 'out'], [16, [100, 100]], [120, [100, 100]]],
                },
            },
            {
                nm: 'moment 2',
                anchor: [90, 97],
                paths: svg('M90 92 L156 92 M90 103 L134 103'),
                loop: {
                    scale: [[0, [100, 100]], [34, [100, 100], 'out'], [42, [110, 100]], [58, [100, 100]], [120, [100, 100]]],
                },
            },
            {
                nm: 'moment 3',
                anchor: [90, 145],
                paths: svg('M90 140 L146 140 M90 151 L120 151'),
                loop: {
                    scale: [[0, [100, 100]], [76, [100, 100], 'out'], [84, [110, 100]], [100, [100, 100]], [120, [100, 100]]],
                },
            },
            { nm: 'nodes', paths: [...circle(62, 48, 11), ...circle(62, 96, 11), ...circle(62, 144, 11)] },
            { nm: 'spine', paths: svg('M62 24 L62 37 M62 59 L62 85 M62 107 L62 133 M62 155 L62 168') },
        ],
    },
    {
        // Paloma del bautizo: aletea dos veces, planea y flota entre destellos
        slug: 'dove',
        loop: 'loop-flap',
        parts: [
            {
                nm: 'rays',
                anchor: [96, 52],
                paths: svg('M96 36 L96 50 M68 44 L75 55 M124 44 L117 55'),
                loop: {
                    opacity: [[0, 35], [30, 100], [60, 35], [90, 100], [120, 35]],
                },
            },
            {
                nm: 'wing',
                parent: 'rig',
                anchor: [114, 116],
                paths: svg('M114 114 C112 88 96 62 70 52 C74 70 74 80 80 90 C66 84 54 86 46 90 C58 108 86 118 114 120 Z'),
                loop: {
                    rotation: [[0, 0], [14, -38], [28, 2], [42, -38], [56, 0], [120, 0]],
                },
            },
            { nm: 'eye', parent: 'rig', paths: circle(152, 84, 2) },
            {
                nm: 'body',
                parent: 'rig',
                paths: svg('M164 88 C162 74 146 68 136 76 C130 81 128 90 122 96 C110 106 90 106 70 104 L40 96 L50 110 L34 118 L60 122 C70 142 96 152 122 148 C148 144 164 126 166 104 L178 98 Z'),
            },
            {
                nm: 'rig',
                null: true,
                anchor: [100, 120],
                loop: {
                    position: [[0, [0, 0]], [30, [0, -6]], [60, [0, 0]], [90, [0, -6]], [120, [0, 0]]],
                },
            },
        ],
    },
    {
        // Pastel de cumpleaños: tres velas cuyas llamas titilan
        slug: 'cake',
        loop: 'loop-flicker',
        parts: [
            {
                nm: 'flame 1',
                anchor: [72, 56],
                paths: svg('M72 56 C64 48 68 38 72 32 C76 38 80 48 72 56 Z'),
                loop: { scale: [[0, [100, 100]], [20, [86, 114]], [40, [108, 94]], [60, [92, 110]], [80, [106, 96]], [100, [90, 112]], [120, [100, 100]]] },
            },
            {
                nm: 'flame 2',
                anchor: [96, 50],
                paths: svg('M96 50 C88 42 92 32 96 26 C100 32 104 42 96 50 Z'),
                loop: { scale: [[0, [100, 100]], [20, [108, 94]], [40, [88, 114]], [60, [106, 96]], [80, [90, 112]], [100, [104, 98]], [120, [100, 100]]] },
            },
            {
                nm: 'flame 3',
                anchor: [120, 56],
                paths: svg('M120 56 C112 48 116 38 120 32 C124 38 128 48 120 56 Z'),
                loop: { scale: [[0, [100, 100]], [20, [92, 110]], [40, [106, 96]], [60, [88, 114]], [80, [104, 98]], [100, [94, 108]], [120, [100, 100]]] },
            },
            { nm: 'candles', paths: svg('M72 64 L72 84 M96 58 L96 84 M120 64 L120 84') },
            { nm: 'frosting', paths: svg('M48 104 C54 112 62 112 66 104 C70 112 80 112 84 104 C88 112 98 112 102 104 C106 112 116 112 120 104 C124 112 134 112 138 104 C142 112 148 110 148 104') },
            { nm: 'cake', paths: [...roundRect(44, 84, 104, 70, 12), ...svg('M30 162 L162 162')] },
        ],
    },
    {
        // Globo que se balancea y flota con un par de destellos
        slug: 'balloon',
        loop: 'loop-float',
        parts: [
            {
                nm: 'sparks',
                anchor: [152, 46],
                paths: svg('M146 44 L158 36 M150 60 L164 60'),
                loop: { opacity: [[0, 0], [30, 0], [42, 100], [70, 100], [82, 0], [120, 0]] },
            },
            { nm: 'shine', parent: 'rig', paths: svg('M72 60 C74 48 82 40 92 38') },
            {
                nm: 'balloon',
                parent: 'rig',
                paths: [...svg('M96 22 C122 22 140 44 140 70 C140 100 118 122 96 126 C74 122 52 100 52 70 C52 44 70 22 96 22 Z'), ...svg('M90 126 L102 126 L96 134 Z')],
            },
            { nm: 'string', parent: 'rig', paths: svg('M96 134 C86 146 106 156 96 170') },
            {
                nm: 'rig',
                null: true,
                anchor: [96, 134],
                loop: {
                    rotation: [[0, -6], [60, 6], [120, -6]],
                    position: [[0, [0, 0]], [60, [0, -6]], [120, [0, 0]]],
                },
            },
        ],
    },
];

// ─── Validación ─────────────────────────────────────────────────────────────

const walk = (node, visit) => {
    if (Array.isArray(node)) {
        node.forEach((child) => walk(child, visit));
    } else if (node && typeof node === 'object') {
        visit(node);
        Object.values(node).forEach((child) => walk(child, visit));
    }
};

const check = () => {
    let failures = 0;

    for (const file of readdirSync(OUT_DIR).filter((name) => name.endsWith('.json'))) {
        const errors = [];
        let data = null;

        try {
            data = JSON.parse(readFileSync(join(OUT_DIR, file), 'utf8'));
        } catch (error) {
            errors.push(`JSON inválido: ${error.message}`);
        }

        if (data) {
            const control = data.layers?.find((layer) => layer.nm === 'control');
            const hasEffect = (name, sub) => control?.ef?.find((effect) => effect.nm === name)?.ef?.some((effect) => effect.nm === sub);

            if (!hasEffect('primary', 'Color')) errors.push('falta control › primary › Color');
            if (!hasEffect('stroke', 'Menu')) errors.push('falta control › stroke › Menu');
            if (!(data.ip < data.op)) errors.push('ip debe ser menor que op');

            const references = [...JSON.stringify(data).matchAll(/comp\('([^']+)'\)/g)].map((match) => match[1]);

            if (references.length === 0) errors.push('ninguna forma lee el color desde la capa control');
            if (references.some((name) => name !== data.nm)) errors.push('hay expresiones que no apuntan a la composición raíz');

            // Solo los íconos propios garantizan que el loop cierre en la misma pose
            if (data.nm?.startsWith('bida-line-')) {
                const loopLayer = data.layers.find((layer) => layer.ty === 0 && layer.ip === data.ip);
                const loopComp = data.assets.find((asset) => asset.id === loopLayer?.refId);

                if (!loopComp) {
                    errors.push('no se encontró la precomposición del loop');
                } else {
                    walk(loopComp.layers, (node) => {
                        if (node.a === 1 && Array.isArray(node.k) && node.k.length > 1) {
                            const first = JSON.stringify(node.k[0].s);
                            const last = JSON.stringify(node.k[node.k.length - 1].s);

                            if (first !== last) errors.push(`el loop no cierra: ${first} ≠ ${last}`);
                        }
                    });
                }
            }
        }

        if (errors.length) {
            failures += 1;
            console.error(`✗ ${file}\n  - ${errors.join('\n  - ')}`);
        } else {
            console.log(`✓ ${file}`);
        }
    }

    process.exitCode = failures ? 1 : 0;
};

if (process.argv.includes('--check')) {
    check();
} else {
    for (const icon of ICONS) {
        const file = join(OUT_DIR, `${icon.slug}-loop-icon.json`);
        writeFileSync(file, JSON.stringify(buildIcon(icon)));
        console.log(`→ ${icon.slug}-loop-icon.json`);
    }
}
