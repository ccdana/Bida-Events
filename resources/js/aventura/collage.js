/**
 * Collage libre del «Libro de aventuras»: acomoda las fotos de la hoja según su forma (vertical,
 * horizontal o cuadrada) y las muestra enteras, sin recortar.
 *
 * Reparte las fotos en filas, sin cambiar su orden. En cada fila todas tienen la misma altura y juntas
 * llenan el ancho de la hoja. De todas las formas de armar las filas, elige la que mejor llena la hoja
 * sin pasarse; si ninguna entra, achica la mejor. Las medidas van en cqw (centésimas del ancho de la
 * hoja), así el acomodo es el mismo en el teléfono y en la computadora.
 * Mientras cargan las fotos, o sin JavaScript, se ve una grilla simple de dos columnas.
 */

// Medidas de la hoja en cqw (ver .nb-collage y .nb-page__inner en resources/css/cards/aventura.css)
const AREA = { width: 83.5, height: 94 };
const FRAME = 1.6; // marco blanco alrededor de cada foto
const GAP = 2.6; // espacio entre fotos y entre filas

/** Proporción (ancho / alto) real de una foto; 1 si no se puede leer. */
function aspectOf(img) {
    return new Promise((resolve) => {
        const probe = new Image();
        const done = () => resolve(probe.naturalWidth && probe.naturalHeight ? probe.naturalWidth / probe.naturalHeight : 1);

        probe.onload = done;
        probe.onerror = () => resolve(1);
        probe.src = img.currentSrc || img.src;

        if (probe.complete) {
            done();
        }
    });
}

/** Todas las formas de cortar la lista en filas seguidas: [[0,1],[2,3]], [[0],[1,2,3]]… */
function partitions(count) {
    const result = [];

    for (let mask = 0; mask < 2 ** (count - 1); mask++) {
        const rows = [[0]];

        for (let i = 1; i < count; i++) {
            if (mask & (1 << (i - 1))) {
                rows.push([i]);
            } else {
                rows[rows.length - 1].push(i);
            }
        }

        result.push(rows);
    }

    return result;
}

/** Alto de cada fila (solo la foto) para que llene el ancho. */
function measure(rows, aspects) {
    const heights = rows.map((row) => {
        const free = AREA.width - row.length * FRAME * 2 - (row.length - 1) * GAP;
        const ratio = row.reduce((sum, index) => sum + aspects[index], 0);

        return free / ratio;
    });

    const total = heights.reduce((sum, height) => sum + height + FRAME * 2, 0) + (rows.length - 1) * GAP;

    return { heights, total };
}

export function arrangeCollage(collage) {
    const items = [...collage.querySelectorAll('.nb-collage__item')];
    const images = items.map((item) => item.querySelector('img'));

    if (!items.length || images.some((img) => !img)) {
        return Promise.resolve();
    }

    return Promise.all(images.map(aspectOf)).then((aspects) => {
        let best = null;

        for (const rows of partitions(items.length)) {
            const { heights, total } = measure(rows, aspects);
            // Que ninguna foto quede diminuta ni gigante dentro de su fila
            const balanced = heights.every((height) => height >= 16);
            const fits = total <= AREA.height;
            const score = (fits ? total : AREA.height - (total - AREA.height) * 1.5) - (balanced ? 0 : 40);

            if (!best || score > best.score) {
                best = { rows, heights, total, score };
            }
        }

        // Si lo mejor se pasa del alto, se achica todo en la misma proporción
        const scale = Math.min(1, AREA.height / best.total);
        const used = best.total * scale;
        let top = (AREA.height - used) / 2;

        best.rows.forEach((row, rowIndex) => {
            const height = best.heights[rowIndex] * scale;
            const rowWidth = row.reduce((sum, index) => sum + aspects[index] * height + FRAME * 2, 0) + (row.length - 1) * GAP;
            let left = (AREA.width - rowWidth) / 2;

            row.forEach((index) => {
                const width = aspects[index] * height;
                const style = items[index].style;

                style.left = `${left.toFixed(2)}cqw`;
                style.top = `${top.toFixed(2)}cqw`;
                style.width = `${(width + FRAME * 2).toFixed(2)}cqw`;
                style.height = `${(height + FRAME * 2).toFixed(2)}cqw`;
                left += width + FRAME * 2 + GAP;
            });

            top += height + FRAME * 2 + GAP;
        });

        collage.classList.add('is-arranged');
    });
}

export function initFreeCollages(scope = document) {
    scope.querySelectorAll('[data-nb-free-collage]').forEach((collage) => {
        arrangeCollage(collage).catch(() => {
            // Si algo falla, queda la grilla simple
        });
    });
}
