/**
 * Tarjetas interactivas: modo historia, gestos y efectos. Lo carga app.js solo si la página tiene
 * [data-story] o alguno de sus gestos, antes de arrancar Alpine.
 */
import { initCelebrations, initTilt } from './effects.js';
import { holdToOpen, scratchReveal } from './gestures.js';
import { invitationStory } from './story.js';

window.invitationStory = invitationStory;
window.holdToOpen = holdToOpen;
window.scratchReveal = scratchReveal;

initTilt();
initCelebrations();
