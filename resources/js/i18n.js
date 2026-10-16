/**
 * Textos de los módulos de JavaScript en el idioma de la invitación. Las vistas los dejan en
 * window.bidaStrings (partials/shell/scripts); si una clave no llega, queda el texto en español que
 * se pasa como respaldo. «:nombre» se llena con los valores que se pasen.
 */
export function t(key, fallback, values = {}) {
    const text = window.bidaStrings?.[key] ?? fallback;

    return text.replace(/:(\w+)/g, (match, name) => values[name] ?? match);
}
