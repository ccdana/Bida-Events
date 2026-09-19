/**
 * Tarjeta del Día del Amor: «un jardín que florece». Lo carga app.js solo si la página tiene
 * [data-card="amor"], antes de arrancar Alpine. Usa el modo historia y los efectos de resources/js/story.
 */
import { clothesline } from './clothesline.js';
import { bloomCover } from './cover.js';
import { daisyGate } from './daisy.js';
import { butterflyHunt, inkLetter } from './garden.js';
import { flowerReply } from './reply.js';
import { dandelionWish } from './wish.js';

window.bloomCover = bloomCover;
window.daisyGate = daisyGate;
window.clothesline = clothesline;
window.flowerReply = flowerReply;
window.dandelionWish = dandelionWish;
window.butterflyHunt = butterflyHunt;

inkLetter();
