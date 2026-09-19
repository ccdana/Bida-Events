/**
 * «The Story We Write Together»: lo carga app.js solo si la página tiene [data-card="historia"],
 * antes de arrancar Alpine (pondCover tiene que existir cuando Alpine lea la apertura).
 */
import { pondCover } from './cover.js';
import { initShootingStar, initStage, initSurfacing } from './stage.js';

window.pondCover = pondCover;

initStage();
initSurfacing();
initShootingStar();
