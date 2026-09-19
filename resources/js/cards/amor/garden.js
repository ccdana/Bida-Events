/**
 * Detalles vivos de la tarjeta de amor:
 * - butterflyHunt: cuenta las mariposas escondidas (partials/amor/butterfly). Cada una avisa con el
 *   evento «butterfly-caught»; se recuerdan en esta pestaña para no contarlas dos veces.
 * - inkLetter: prepara la carta para que, al romper el sello, las palabras aparezcan una a una como
 *   tinta que se asienta (el texto ya está en el HTML: solo se envuelve cada palabra).
 */
import { celebrate, vibrate } from '../../story/effects.js';

const storageKey = () => `inv-butterflies:${location.pathname}`;

const readFound = () => {
    try {
        return JSON.parse(sessionStorage.getItem(storageKey()) || '[]');
    } catch {
        return [];
    }
};

const saveFound = (ids) => {
    try {
        sessionStorage.setItem(storageKey(), JSON.stringify(ids));
    } catch {
        // Sin almacenamiento: se cuentan solo mientras la página esté abierta
    }
};

export function butterflyHunt({ total = 3, message = '' } = {}) {
    let timer = null;

    return {
        found: [],
        toast: '',

        init() {
            this.found = readFound();
            this.found.forEach((id) => document.querySelector(`[data-butterfly="${id}"]`)?.classList.add('is-caught'));

            window.addEventListener('butterfly-caught', (event) => this.catch(event.detail ?? {}));
        },

        catch({ id, rect }) {
            const butterfly = document.querySelector(`[data-butterfly="${id}"]`);

            if (!id || this.found.includes(id)) {
                return;
            }

            this.found.push(id);
            saveFound(this.found);
            butterfly?.classList.add('is-flying');
            setTimeout(() => butterfly?.classList.add('is-caught'), 1400);
            vibrate(12);

            const complete = this.found.length >= total;
            celebrate({ rect, amount: complete ? 34 : 10, variant: 'love' });
            this.say(complete ? message : `Mariposa ${this.found.length} de ${total}`, complete ? 5200 : 2200);
        },

        say(text, duration) {
            clearTimeout(timer);
            this.toast = text;
            timer = setTimeout(() => { this.toast = ''; }, duration);
        },
    };
}

export function inkLetter() {
    document.querySelectorAll('.inv-amor .inv-letter').forEach((letter) => {
        let index = 0;

        letter.querySelectorAll('.inv-letter__body p').forEach((block) => {
            const walker = document.createTreeWalker(block, NodeFilter.SHOW_TEXT);
            const nodes = [];

            while (walker.nextNode()) {
                nodes.push(walker.currentNode);
            }

            nodes.forEach((node) => {
                const fragment = document.createDocumentFragment();

                node.textContent.split(/(\s+)/).forEach((part) => {
                    if (part === '' || /^\s+$/.test(part)) {
                        fragment.appendChild(document.createTextNode(part));
                        return;
                    }

                    const word = document.createElement('span');
                    word.className = 'inv-ink';
                    word.style.setProperty('--w', String(index++));
                    word.textContent = part;
                    fragment.appendChild(word);
                });

                node.replaceWith(fragment);
            });
        });

        letter.classList.add('is-inked');
        letter.style.setProperty('--words', String(index));
    });
}
