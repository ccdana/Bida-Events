{{--
    Plantilla «Álbum de stickers»: se entra abriendo el sobre y cada dato es un sticker pegado en su casilla del álbum; desde los 18 años los stickers van derechos.
    Armado común en partials/shell/themed; lo propio en partials/stickers y en themes/stickers.css.
--}}
@include('invitations.partials.shell.themed', [
    'template' => \App\Support\InvitationTemplates::CUMPLE_STICKERS,
    'parts' => 'stickers',
    'footerDate' => 'l j \d\e F',
])
