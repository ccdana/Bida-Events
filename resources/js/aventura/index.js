/**
 * Tarjeta «Libro de aventuras»: la vuelta de página del cuaderno, el juego de memoria y la lluvia de
 * pétalos. Lo carga app.js solo si la página tiene [data-notebook], antes de arrancar Alpine, así las
 * hojas ya están en su lugar cuando Alpine las recorre.
 */
import { initCelebrations } from '../story/effects.js';
import { initNotebook } from './book.js';
import { initFreeCollages } from './collage.js';
import { memoryGame } from './memory-game.js';

window.memoryGame = memoryGame;

initCelebrations();
// El collage libre se acomoda cuando se conoce la forma de sus fotos (no frena al libro)
initFreeCollages();
document.querySelectorAll('[data-notebook]').forEach(initNotebook);
