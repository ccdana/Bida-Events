<?php

// Invitación de muestra "xv-isabella-carta": los XV de Isabella como carta de baile.
// Mismo contenido que "xv-isabella", con la plantilla «Carta de baile» y su paleta.

use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('xv-isabella', 'xv-isabella-carta', 'XV de Isabella', InvitationTemplates::XV_CARTA_DE_BAILE);
