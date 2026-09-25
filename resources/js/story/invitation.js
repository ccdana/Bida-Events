/**
 * Invitaciones en modo historia: el mismo recorrido por escenas de las tarjetas (story.js), pero a
 * pedido y avanzando solo cuando el invitado toca. Se carga aparte para no sumar a las invitaciones
 * lo propio de las tarjetas (inclinación del teléfono, pétalos, gestos).
 */
import { invitationStory } from './story.js';

window.invitationStory = invitationStory;
