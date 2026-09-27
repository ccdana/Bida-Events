{{--
    Plantilla «Birrete al aire»: se entra pasando la borla al otro lado, como en la ceremonia, y el
    birrete sale volando. La portada es la medalla de la promoción con laureles; el día baja por el
    cordón de la borla y quienes acompañaron reciben su medalla. Al fondo, birretes en el aire.
    Armado común en partials/shell/themed; lo propio en partials/birrete y en themes/birrete.css (se
    carga solo en esta invitación, con la base adaptable de css/invitation/tendencias/_base.css).
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::GRADUACION_BIRRETE,
    'parts' => 'birrete',
    'styles' => ['resources/css/invitation/themes/birrete.css'],
    'headExtra' => 'invitations.partials.tendencias.head',
    'bodyClass' => 'inv-trend',
    'footerDate' => 'j \d\e F \d\e Y',
])
