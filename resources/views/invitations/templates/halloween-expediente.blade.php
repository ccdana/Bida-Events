{{--
    Plantilla «Expediente abierto»: el archivo de un caso que se lee con una linterna, o con las luces encendidas.
    Armado común en partials/shell/themed; lo propio en partials/expediente y en themes/expediente.css.
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::HALLOWEEN_EXPEDIENTE,
    'parts' => 'expediente',
    'footerDate' => 'l j \d\e F \d\e Y',
])
