{{--
    Plantilla «Noche de gala»: el gran salón de los XV. Se entra encendiendo la araña de cristal; la
    foto va en el espejo ovalado de marco dorado, el nombre en su placa grabada y cada sección es un
    panel de la pared con su moldura. Al fondo, la luz de los caireles sobre las paredes.
    Armado común en partials/shell/themed; lo propio en partials/gala y en themes/gala.css (se carga
    solo en esta invitación, con la base adaptable de css/invitation/tendencias/_base.css).
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::XV_PREMIUM,
    'parts' => 'gala',
    'styles' => ['resources/css/invitation/themes/gala.css'],
    'headExtra' => 'invitations.partials.tendencias.head',
    'bodyClass' => 'inv-trend',
    'footerDate' => 'l j \d\e F \d\e Y',
])
