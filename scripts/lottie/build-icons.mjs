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
        slug: 'dress',
        loop: 'loop-sway',
        parts: [
            {
                nm: 'hook',
                anchor: [96, 46],
                paths: svg('M96 54 L96 44 C96 38 106 36 106 28 C106 20 100 16 93 16 C86 16 82 21 82 27'),
            },
            {
                nm: 'dress',
                anchor: [96, 50],
                paths: [
                    ...svg('M82 66 L96 54 L110 66'),
                    ...svg('M82 66 L74 100 C64 122 52 142 42 160 C78 168 114 168 150 160 C140 142 128 122 118 100 L110 66'),
                    ...svg('M74 100 C88 108 104 108 118 100'),
                    ...svg('M88 126 L80 150 M104 126 L112 150'),
                ],
                loop: {
                    rotation: [[0, 0], [30, 7], [60, 0], [90, -7], [120, 0]],
                },
            },
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
