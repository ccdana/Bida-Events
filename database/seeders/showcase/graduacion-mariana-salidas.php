<?php

// Invitación de muestra "graduacion-mariana-salidas": la graduación de Mariana como panel de salidas.
// Mismo contenido que "graduacion-mariana", con la plantilla «Próxima salida» y su paleta.

use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('graduacion-mariana', 'graduacion-mariana-salidas', 'Graduación de Mariana', InvitationTemplates::GRADUACION_PROXIMA_SALIDA);
