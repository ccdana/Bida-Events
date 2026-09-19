/**
 * Juego de memoria del «Libro de aventuras» (Alpine): cada foto está dos veces boca abajo y hay que
 * encontrar los pares. Las cartas vienen del servidor (se ven sin JavaScript) y aquí se mezclan.
 * Al completar todos los pares caen pétalos y aparece el mensaje final.
 */
import { prefersReducedMotion, vibrate } from '../story/effects.js';

export function memoryGame(pairs) {
    return {
        pairs,
        found: 0,
        moves: 0,
        won: false,
        open: [],
        locked: false,

        init() {
            this.shuffle();
        },

        cards() {
            return [...this.$refs.grid.querySelectorAll('.nb-card')];
        },

        label(card, position) {
            const photo = card.querySelector('.nb-card__face img')?.alt || 'foto';

            if (card.classList.contains('is-matched')) {
                card.setAttribute('aria-label', `Carta ${position}: ${photo}, par encontrado`);
            } else if (card.classList.contains('is-open')) {
                card.setAttribute('aria-label', `Carta ${position}: ${photo}`);
            } else {
                card.setAttribute('aria-label', `Carta ${position}, boca abajo`);
            }
        },

        relabel() {
            this.cards().forEach((card, index) => this.label(card, index + 1));
        },

        shuffle() {
            const grid = this.$refs.grid;
            const cards = this.cards();

            for (let i = cards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [cards[i], cards[j]] = [cards[j], cards[i]];
            }

            cards.forEach((card) => {
                card.classList.remove('is-open', 'is-matched', 'is-miss');
                card.disabled = false;
                grid.appendChild(card);
            });

            this.relabel();
        },

        flip(card) {
            if (this.locked || this.won || card.classList.contains('is-open') || card.classList.contains('is-matched')) {
                return;
            }

            card.classList.add('is-open');
            this.open.push(card);
            this.relabel();

            if (this.open.length < 2) {
                return;
            }

            this.moves++;
            const [first, second] = this.open;

            if (first.dataset.pair === second.dataset.pair) {
                [first, second].forEach((match) => {
                    match.classList.add('is-matched');
                    match.disabled = true;
                });
                this.open = [];
                this.found++;
                this.relabel();
                vibrate(15);

                if (this.found === this.pairs) {
                    this.finish();
                }

                return;
            }

            this.locked = true;
            [first, second].forEach((miss) => miss.classList.add('is-miss'));

            setTimeout(() => {
                [first, second].forEach((miss) => miss.classList.remove('is-open', 'is-miss'));
                this.open = [];
                this.locked = false;
                this.relabel();
            }, prefersReducedMotion() ? 500 : 950);
        },

        finish() {
            // Se deja ver el último par antes de celebrar
            setTimeout(() => {
                this.won = true;
                window.dispatchEvent(new CustomEvent('inv-celebrate', {
                    detail: { rect: this.$refs.grid.getBoundingClientRect(), amount: 34 },
                }));
            }, 450);
        },

        restart() {
            this.found = 0;
            this.moves = 0;
            this.won = false;
            this.open = [];
            this.locked = false;
            this.shuffle();
        },
    };
}
