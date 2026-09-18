/**
 * Margarita para deshojar (partials/amor/daisy-milestone): cada pétalo tocado cae y alterna
 * «me quiere» / «no me quiere». Con un número impar de pétalos, el último siempre es «¡me quiere!».
 * Es una puerta del modo historia: «siguiente» (story-open) la deshoja sola, pétalo tras pétalo.
 */
import { celebrate, vibrate } from '../../story/effects.js';
import { countUp } from '../../story/gestures.js';

const insideEditor = () => window.self !== window.top && !window.invDemo;

export function daisyGate({ petals = 9, yes = 'Me quiere', no = 'No me quiere', answer = '¡Me quiere!' } = {}) {
    let autoTimer = null;

    return {
        total: petals,
        plucked: [],
        count: 0,
        word: '',
        done: false,

        init() {
            this.$el.addEventListener('story-open', () => this.pluckAll());

            if (insideEditor()) {
                this.plucked = [...Array(petals).keys()];
                this.count = petals;
                this.done = true;
            }
        },

        isPlucked(index) {
            return this.plucked.includes(index);
        },

        pluck(index) {
            if (this.isPlucked(index) || this.count >= this.total) {
                return;
            }

            this.plucked.push(index);
            this.count++;

            const last = this.count === this.total;
            this.word = last ? answer : (this.count % 2 === 1 ? yes : no);
            vibrate(last ? [16, 40, 16, 40, 30] : 8);

            if (last) {
                this.finish();
            }
        },

        /** Deshoja los pétalos que quedan, uno tras otro. */
        pluckAll() {
            if (this.count >= this.total) {
                return;
            }

            clearTimeout(autoTimer);
            const remaining = [...Array(this.total).keys()].filter((index) => !this.isPlucked(index));

            remaining.forEach((index, position) => {
                autoTimer = setTimeout(() => this.pluck(index), position * 140);
            });
        },

        finish() {
            // El centro late, se vuelve corazón y aparece cuánto tiempo llevan juntos
            setTimeout(() => {
                this.done = true;
                countUp(this.$el);
                celebrate({ rect: this.$refs.center?.getBoundingClientRect(), amount: 32, variant: 'love' });
            }, 900);
        },
    };
}
