<?php

// Invitación de muestra "boda-camila-andres-caminos": la boda de Camila y Andrés en un mapa.
// Mismo contenido que "boda-camila-andres", con la plantilla «Dos caminos» y su paleta.

use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('boda-camila-andres', 'boda-camila-andres-caminos', 'Boda de Camila y Andrés', InvitationTemplates::BODA_DOS_CAMINOS);
