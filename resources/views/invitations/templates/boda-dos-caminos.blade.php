{{--
    Plantilla «Dos caminos»: un mapa de curvas de nivel donde el camino de cada uno llega al lugar de la boda.
    Armado común en partials/shell/themed; lo propio en partials/caminos y en themes/caminos.css.
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::BODA_DOS_CAMINOS,
    'parts' => 'caminos',
    'footerDate' => 'j \d\e F \d\e Y',
])
