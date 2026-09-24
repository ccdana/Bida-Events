/**
 * Invitaciones «como historias de Instagram»: el mismo recorrido por escenas de las tarjetas
 * (story.js), pero a pedido y con avance automático. Se carga aparte para no sumar a las
 * invitaciones lo propio de las tarjetas (inclinación del teléfono, pétalos, gestos).
 */
import { invitationStory } from './story.js';

window.invitationStory = invitationStory;
