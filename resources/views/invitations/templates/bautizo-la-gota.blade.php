{{--
    Plantilla «La gota»: una gota cae sobre el agua; el nombre queda al centro y los padrinos en el primer anillo.
    Armado común en partials/shell/themed; lo propio en partials/gota y en themes/gota.css.
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::BAUTIZO_LA_GOTA,
    'parts' => 'gota',
    'footerDate' => 'j \d\e F \d\e Y',
])
