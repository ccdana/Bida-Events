{{--
    Plantilla «Próxima salida»: el panel de salidas de una terminal, con pase de abordar y horario en tablero.
    Armado común en partials/shell/themed; lo propio en partials/salidas y en themes/salidas.css.
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::GRADUACION_PROXIMA_SALIDA,
    'parts' => 'salidas',
    'footerDate' => 'l j \d\e F \d\e Y',
])
