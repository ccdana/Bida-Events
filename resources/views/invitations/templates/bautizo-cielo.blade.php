{{--
    Plantilla «Entre nubes»: el cielo del bautizo. Se entra abriendo las capas de nubes; la foto es el
    sol entre las nubes y cada sección flota como una nube sobre el cielo, que se mueve al fondo.
    Armado común en partials/shell/themed; lo propio en partials/nubes y en themes/nubes.css (se carga
    solo en esta invitación, con la base adaptable de css/invitation/tendencias/_base.css).
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::BAUTIZO_CIELO,
    'parts' => 'nubes',
    'styles' => ['resources/css/invitation/themes/nubes.css'],
    'headExtra' => 'invitations.partials.tendencias.head',
    'bodyClass' => 'inv-trend',
    'footerDate' => 'j \d\e F \d\e Y',
])
