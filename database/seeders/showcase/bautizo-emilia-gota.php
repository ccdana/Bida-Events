<?php

// Invitación de muestra "bautizo-emilia-gota": el bautizo de Emilia con un texto neutral.
// Mismo contenido que "bautizo-emilia", con la plantilla «La gota» y su paleta.

use App\Support\InvitationTemplates;
use Database\Seeders\ShowcaseVariant;

return ShowcaseVariant::of('bautizo-emilia', 'bautizo-emilia-gota', 'Bautizo de Emilia', InvitationTemplates::BAUTIZO_LA_GOTA, [
    // Texto neutral: la plantilla no da por hecho un credo
    'modules.bienvenida.mensaje' => 'Con la alegría de mis papás, Gabriela y Rodrigo, te invito a acompañarme en el día de mi bautizo.',
    'modules.ubicacion.nota' => 'Después de la ceremonia te esperamos en el Salón Jardín Las Lilas, a tres cuadras.',
    'modules.itinerario.eventos.0.titulo' => 'Ceremonia de bautizo',
    'modules.destacados.padrinos.0.mensaje' => 'Gracias por aceptar acompañarme en cada paso, toda la vida.',
]);
