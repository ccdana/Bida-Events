<?php

// Invitación de muestra "halloween-expediente-diego": la fiesta de Diego como un expediente.
// Mismo contenido que "halloween-noche-diego", con la plantilla «Expediente abierto» y su paleta.

use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('halloween-noche-diego', 'halloween-expediente-diego', 'Fiesta de Halloween de Diego', InvitationTemplates::HALLOWEEN_EXPEDIENTE);
