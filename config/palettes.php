<?php

/*
|--------------------------------------------------------------------------
| Paletas listas del editor (pestaña «Estética»)
|--------------------------------------------------------------------------
|
| Una lista sola. Cada paleta dice para qué tipos de evento se pensó, con el código del perfil
| (config/event_profiles.php); con «events» vacío sirve para cualquiera. El editor muestra primero
| la paleta original de la plantilla elegida (InvitationTemplates, «palette»), después las de su
| tipo y al final las demás.
|
| Los cinco colores son los que usa la invitación:
|   primary    color del evento: botones, líneas y detalles
|   secondary  segundo tono de acento (algunas plantillas lo usan en fondos de sección)
|   accent     tono suave de recuadros y fondos de tarjeta
|   text       texto
|   background fondo de la página
| «mode»: light (fondo claro) o night (fondo oscuro).
|
| Reglas: nombres y combinaciones sin repetir, y todas pasan el contraste AA. Lo revisa
| EditorPanelsTest con App\Support\ColorContrast.
*/

return [

    // ── Fondo claro ─────────────────────────────────────────────────────────
    ['name' => 'Champagne y oro', 'description' => 'Dorado cálido sobre marfil', 'mode' => 'light', 'events' => ['xv', 'boda'], 'colors' => ['primary' => '#94712F', 'secondary' => '#3D3228', 'accent' => '#F3E8D8', 'text' => '#2A241E', 'background' => '#FBF7F0']],
    ['name' => 'Rosa de gala', 'description' => 'Rosa empolvado y blanco perla', 'mode' => 'light', 'events' => ['xv', 'amor'], 'colors' => ['primary' => '#B4586A', 'secondary' => '#5C3D42', 'accent' => '#F6DDE2', 'text' => '#3A2828', 'background' => '#FFF8F8']],
    ['name' => 'Lila soñado', 'description' => 'Lavanda suave con plata', 'mode' => 'light', 'events' => ['xv', 'cumple'], 'colors' => ['primary' => '#7A5BA8', 'secondary' => '#3E2F58', 'accent' => '#E8E0F4', 'text' => '#2C2438', 'background' => '#FBF9FE']],
    ['name' => 'Azul princesa', 'description' => 'Celeste hielo con plata', 'mode' => 'light', 'events' => ['xv', 'bautizo'], 'colors' => ['primary' => '#3F6E9E', 'secondary' => '#23374F', 'accent' => '#DDE9F5', 'text' => '#1E2A38', 'background' => '#F7FAFD']],

    ['name' => 'Salvia y marfil', 'description' => 'Verde natural, sereno', 'mode' => 'light', 'events' => ['boda', 'bautizo'], 'colors' => ['primary' => '#55775B', 'secondary' => '#2F4535', 'accent' => '#E2EDE4', 'text' => '#1E2E24', 'background' => '#F6FAF7']],
    ['name' => 'Terracota', 'description' => 'Cálido, de atardecer', 'mode' => 'light', 'events' => ['boda', 'aventura'], 'colors' => ['primary' => '#A85F42', 'secondary' => '#5C3A2E', 'accent' => '#F5E6DC', 'text' => '#342520', 'background' => '#FDF8F5']],
    ['name' => 'Perla costera', 'description' => 'Azul grisáceo sofisticado', 'mode' => 'light', 'events' => ['boda'], 'colors' => ['primary' => '#4A6A87', 'secondary' => '#2C3E50', 'accent' => '#DCE8F0', 'text' => '#1A2832', 'background' => '#F4F8FB']],
    ['name' => 'Borgoña', 'description' => 'Vino y crema, clásico', 'mode' => 'light', 'events' => ['boda', 'amor'], 'colors' => ['primary' => '#7E2E3E', 'secondary' => '#4A1C26', 'accent' => '#F2E1E0', 'text' => '#2E1E20', 'background' => '#FCF8F5']],

    ['name' => 'Cielo sereno', 'description' => 'Azul suave y blanco nube', 'mode' => 'light', 'events' => ['bautizo'], 'colors' => ['primary' => '#43729E', 'secondary' => '#C9A96E', 'accent' => '#DCEBF5', 'text' => '#2E3A46', 'background' => '#F7FBFE']],
    ['name' => 'Rosa bautizo', 'description' => 'Rosa delicado y marfil', 'mode' => 'light', 'events' => ['bautizo'], 'colors' => ['primary' => '#B25A7A', 'secondary' => '#C9A96E', 'accent' => '#F8E3EA', 'text' => '#46323A', 'background' => '#FFF9FB']],
    ['name' => 'Menta', 'description' => 'Verde agua fresco', 'mode' => 'light', 'events' => ['bautizo'], 'colors' => ['primary' => '#3F8575', 'secondary' => '#C9A96E', 'accent' => '#DDF1EB', 'text' => '#23372F', 'background' => '#F6FCFA']],
    ['name' => 'Lino', 'description' => 'Beige natural, sin género', 'mode' => 'light', 'events' => ['bautizo', 'aventura'], 'colors' => ['primary' => '#8A6C47', 'secondary' => '#C9A96E', 'accent' => '#EFE6D8', 'text' => '#352C22', 'background' => '#FBF8F3']],

    ['name' => 'Confeti coral', 'description' => 'Coral, amarillo sol y menta', 'mode' => 'light', 'events' => ['cumple'], 'colors' => ['primary' => '#C8433B', 'secondary' => '#F7B32B', 'accent' => '#CDEFE3', 'text' => '#2B2D42', 'background' => '#FFF8F0']],
    ['name' => 'Fiesta lila', 'description' => 'Lila vibrante y amarillo', 'mode' => 'light', 'events' => ['cumple'], 'colors' => ['primary' => '#6D4BD8', 'secondary' => '#FFC93C', 'accent' => '#E6DDFB', 'text' => '#2A2440', 'background' => '#FBF8FF']],
    ['name' => 'Mandarina', 'description' => 'Naranja alegre y azul', 'mode' => 'light', 'events' => ['cumple'], 'colors' => ['primary' => '#B85418', 'secondary' => '#2E6FB7', 'accent' => '#FFE4CF', 'text' => '#2E2218', 'background' => '#FFF9F3']],

    ['name' => 'Girasol', 'description' => 'Amarillo de primavera y verde hoja', 'mode' => 'light', 'events' => ['amor', 'aventura'], 'colors' => ['primary' => '#8F6A12', 'secondary' => '#4E6B35', 'accent' => '#FBEAB5', 'text' => '#2F2A1C', 'background' => '#FFFBEF']],
    ['name' => 'Lavanda', 'description' => 'Violeta suave y blanco', 'mode' => 'light', 'events' => ['amor', 'xv'], 'colors' => ['primary' => '#7650A8', 'secondary' => '#4B3470', 'accent' => '#ECE2F7', 'text' => '#2D2340', 'background' => '#FCF9FF']],
    ['name' => 'Durazno', 'description' => 'Melocotón tierno y coral', 'mode' => 'light', 'events' => ['amor'], 'colors' => ['primary' => '#B24E3C', 'secondary' => '#6B2E24', 'accent' => '#FDE2D6', 'text' => '#3A2420', 'background' => '#FFF8F4']],

    ['name' => 'Kraft y bosque', 'description' => 'Papel kraft y verde musgo', 'mode' => 'light', 'events' => ['aventura'], 'colors' => ['primary' => '#4F6B32', 'secondary' => '#3A2A18', 'accent' => '#E7D8B5', 'text' => '#2B2416', 'background' => '#F5EEDD']],
    ['name' => 'Bitácora azul', 'description' => 'Tinta azul y papel antiguo', 'mode' => 'light', 'events' => ['aventura'], 'colors' => ['primary' => '#2F5A87', 'secondary' => '#1E3550', 'accent' => '#F0D98A', 'text' => '#1F2530', 'background' => '#F6F1E4']],
    ['name' => 'Acuarela rosa', 'description' => 'Rosa acuarela y lápiz', 'mode' => 'light', 'events' => ['aventura', 'amor'], 'colors' => ['primary' => '#A84B62', 'secondary' => '#5A2A36', 'accent' => '#F6D9DF', 'text' => '#2F2226', 'background' => '#FBF3EF']],

    ['name' => 'Arena y tinta', 'description' => 'Neutro cálido, para cualquier evento', 'mode' => 'light', 'events' => [], 'colors' => ['primary' => '#6E6257', 'secondary' => '#38312A', 'accent' => '#EDE6DB', 'text' => '#2B2620', 'background' => '#FAF7F2']],
    ['name' => 'Jade suave', 'description' => 'Verde jade y blanco roto', 'mode' => 'light', 'events' => [], 'colors' => ['primary' => '#2F7A6B', 'secondary' => '#1B3D37', 'accent' => '#DCEFEA', 'text' => '#1C2E2A', 'background' => '#F5FAF9']],

    ['name' => 'Toga y oro', 'description' => 'Azul noche con dorado de medalla', 'mode' => 'light', 'events' => ['graduacion'], 'colors' => ['primary' => '#8A6A22', 'secondary' => '#1B2A4A', 'accent' => '#E6E0CF', 'text' => '#18213A', 'background' => '#FAF8F2']],
    ['name' => 'Borla verde', 'description' => 'Verde institucional y marfil', 'mode' => 'light', 'events' => ['graduacion'], 'colors' => ['primary' => '#2F6B4F', 'secondary' => '#1E3B2E', 'accent' => '#DCEBE2', 'text' => '#1B2C24', 'background' => '#F6FAF7']],
    ['name' => 'Papel y tinta', 'description' => 'Blanco puro y negro, sin adornos', 'mode' => 'light', 'events' => ['lienzo'], 'colors' => ['primary' => '#1F1F1F', 'secondary' => '#555555', 'accent' => '#EDEDED', 'text' => '#0F0F0F', 'background' => '#FCFCFC']],
    ['name' => 'Grafito', 'description' => 'Grises suaves y un azul de acento', 'mode' => 'light', 'events' => ['lienzo'], 'colors' => ['primary' => '#2D5B8C', 'secondary' => '#3A3F47', 'accent' => '#E7EAEE', 'text' => '#1D2127', 'background' => '#F8F9FA']],
    // ── Fondo oscuro ────────────────────────────────────────────────────────
    ['name' => 'Gala de medianoche', 'description' => 'Negro profundo con oro', 'mode' => 'night', 'events' => ['xv', 'boda', 'cumple'], 'colors' => ['primary' => '#D4AF37', 'secondary' => '#1A1814', 'accent' => '#3D3528', 'text' => '#F5F0E6', 'background' => '#0D0C0A']],
    ['name' => 'Esmeralda de noche', 'description' => 'Verde profundo con luz dorada', 'mode' => 'night', 'events' => ['boda'], 'colors' => ['primary' => '#7EC9A0', 'secondary' => '#0F1F18', 'accent' => '#1A3D2E', 'text' => '#E0F2E9', 'background' => '#051510']],
    ['name' => 'Pista de baile', 'description' => 'Neón fucsia sobre noche', 'mode' => 'night', 'events' => ['cumple'], 'colors' => ['primary' => '#FF6FB5', 'secondary' => '#14102A', 'accent' => '#2E2350', 'text' => '#F6EEFF', 'background' => '#0C0918']],
    ['name' => 'Rosa terciopelo', 'description' => 'Noche romántica profunda', 'mode' => 'night', 'events' => ['amor'], 'colors' => ['primary' => '#E8A0B4', 'secondary' => '#2A1520', 'accent' => '#4A2A38', 'text' => '#FCE8EE', 'background' => '#140A10']],

    // La tarjeta «Bajo la misma luna» siempre es de noche: lo que cambia es la luz de la luna
    ['name' => 'Luna plateada', 'description' => 'Luz fría sobre azul noche', 'mode' => 'night', 'events' => ['historia'], 'colors' => ['primary' => '#D6DEEA', 'secondary' => '#1C2B5A', 'accent' => '#3F6FA8', 'text' => '#F1F4FA', 'background' => '#0A1230']],
    ['name' => 'Luna rosada', 'description' => 'Un reflejo tibio, casi de amanecer', 'mode' => 'night', 'events' => ['historia'], 'colors' => ['primary' => '#F2B8C6', 'secondary' => '#2A1C4A', 'accent' => '#6A4C93', 'text' => '#F8EEF2', 'background' => '#120C28']],
    ['name' => 'Aurora', 'description' => 'Verde boreal sobre la noche', 'mode' => 'night', 'events' => ['historia'], 'colors' => ['primary' => '#8FE3C0', 'secondary' => '#0E2A33', 'accent' => '#2E6F73', 'text' => '#ECF7F3', 'background' => '#061A20']],

    ['name' => 'Zafiro medianoche', 'description' => 'Azul noche refinado', 'mode' => 'night', 'events' => [], 'colors' => ['primary' => '#7EB8DA', 'secondary' => '#0E1A2B', 'accent' => '#1E3A5F', 'text' => '#E3EEF8', 'background' => '#060D18']],
    ['name' => 'Gala amatista', 'description' => 'Púrpura lujoso', 'mode' => 'night', 'events' => [], 'colors' => ['primary' => '#B8A0D8', 'secondary' => '#1A1428', 'accent' => '#352850', 'text' => '#EDE6F8', 'background' => '#0A0812']],

    ['name' => 'Calabaza y luna', 'description' => 'Naranja encendido sobre noche violeta', 'mode' => 'night', 'events' => ['halloween'], 'colors' => ['primary' => '#FF9A3C', 'secondary' => '#5E3A99', 'accent' => '#2E2440', 'text' => '#F6F0E8', 'background' => '#16111F']],
    ['name' => 'Bosque encantado', 'description' => 'Verde ácido y negro de medianoche', 'mode' => 'night', 'events' => ['halloween'], 'colors' => ['primary' => '#A6E05A', 'secondary' => '#4B2A6B', 'accent' => '#22301E', 'text' => '#EEF4E6', 'background' => '#0E130C']],
    ['name' => 'Birrete de noche', 'description' => 'Azul profundo y dorado', 'mode' => 'night', 'events' => ['graduacion'], 'colors' => ['primary' => '#E0BE6A', 'secondary' => '#101A30', 'accent' => '#1F2C4A', 'text' => '#F2EEE4', 'background' => '#0B1222']],
    ['name' => 'Tinta sobre negro', 'description' => 'Negro y blanco, al revés', 'mode' => 'night', 'events' => ['lienzo'], 'colors' => ['primary' => '#F2F2F2', 'secondary' => '#BDBDBD', 'accent' => '#262626', 'text' => '#F7F7F7', 'background' => '#0D0D0D']],
];
