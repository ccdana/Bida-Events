<?php

// Invitación de muestra "cumple-daniela-stickers": los 30 de Daniela en un álbum de stickers.
// Mismo contenido que "cumple-daniela-30", con la plantilla «Álbum de stickers» y su paleta.

use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('cumple-daniela-30', 'cumple-daniela-stickers', 'Cumpleaños de Daniela', InvitationTemplates::CUMPLE_STICKERS);
