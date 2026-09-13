@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::XV_PREMIUM);
    $invCopy = $page->copy;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    @include('invitations.partials.shell.head')
</head>
<body class="inv-page overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">

    @include('invitations.partials.particles')

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.hero', [
        'invitation' => $invitation,
        'bienvenida' => $page->welcome,
        'heroImage' => $page->heroImage,
        'hasHeroImage' => (bool) $page->heroImage,
    ])

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    <footer class="inv-footer">
        <p class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">Bida Events</span></p>
    </footer>

    @include('invitations.partials.shell.scripts')
</body>
</html>
