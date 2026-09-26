{{--
    Plantilla «Carta de baile»: la invitación es la carta de baile de la noche, atada con un cordón y con el programa adentro.
    Armado común en partials/shell/themed; lo propio en partials/carta y en themes/carta.css.
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::XV_CARTA_DE_BAILE,
    'parts' => 'carta',
    'footerDate' => 'l j \d\e F \d\e Y',
])
