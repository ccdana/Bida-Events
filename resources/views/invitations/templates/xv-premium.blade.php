{{--
    Plantilla «Atelier» (clave xv-premium, la de la antigua «Noche de gala»): el taller de alta costura
    la semana del desfile. Se entra abriendo la funda del vestido; la portada es el tablero de
    inspiración con la foto, la etiqueta tejida con su nombre y la ficha del desfile, y cada sección es
    una pieza de molde. Armado común en partials/shell/themed; lo propio en partials/atelier y en
    themes/atelier.css (se carga solo en esta invitación, con la base adaptable de la colección
    tendencias: css/invitation/tendencias/_base.css).
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::XV_PREMIUM,
    'parts' => 'atelier',
    'styles' => ['resources/css/invitation/themes/atelier.css'],
    'headExtra' => 'invitations.partials.tendencias.head',
    'bodyClass' => 'inv-trend',
    'footerDate' => 'l j \d F \d Y',
])
