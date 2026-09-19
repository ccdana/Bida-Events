/**
 * Apertura de «Nuestra historia»: un estanque con la luna reflejada (partials/historia/cover).
 * Las ondas nacen donde cae el dedo; con teclado, desde el centro. Se apoya en invitationCover
 * (shell/cover-component) para bloquear la página, esperar la portada y retirarse.
 */
import { vibrate } from '../../story/effects.js';

export function pondCover(timing) {
    const cover = window.invitationCover(timing);

    return {
        ...cover,

        touch(event) {
            if (this.stage > 0 || this.closed) {
                return;
            }

            const pond = event.currentTarget;
            const rect = pond.getBoundingClientRect();
            // detail === 0: Enter/Espacio o el clic automático de la muestra
            const fromPointer = event.detail > 0 && event.clientX > 0;

            pond.style.setProperty('--rx', `${fromPointer ? event.clientX - rect.left : rect.width / 2}px`);
            pond.style.setProperty('--ry', `${fromPointer ? event.clientY - rect.top : rect.height / 2}px`);

            vibrate(12);
            this.open();
        },
    };
}
