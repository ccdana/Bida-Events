{{--
    Plantilla «La gota»: el agua del bautizo. Se entra vertiendo la jarra sobre la pila; la portada es
    una gota grande con la foto adentro, suspendida sobre el agua donde van a caer sus ondas.
    Armado común en partials/shell/themed; lo propio en partials/gota y en themes/gota.css (se carga
    solo en esta invitación, con la base adaptable de css/invitation/tendencias/_base.css).
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::BAUTIZO_LA_GOTA,
    'parts' => 'gota',
    'styles' => ['resources/css/invitation/themes/gota.css'],
    'headExtra' => 'invitations.partials.tendencias.head',
    'bodyClass' => 'inv-trend',
    'footerDate' => 'j \d\e F \d\e Y',
])
