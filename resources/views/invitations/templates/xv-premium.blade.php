@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::XV_PREMIUM);
    $invCopy = $page->copy;
    // El telón de apertura no aparece en la vista previa del editor ni cuando la fiesta ya pasó
    $showIntro = ! $page->isPostEvent && empty($isPreview);
    $introKey = 'inv-intro-'.$invitation->slug;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.intro-script', ['introSelector' => '.inv-xv-intro'])
    @endif
</head>
<body class="inv-page inv-xv overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">

    @if($showIntro)
        @include('invitations.partials.xv.intro')
    @endif

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

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-xv-footer',
        'footerName' => $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y')),
        'footerOrnament' => 'crown',
    ])

    @include('invitations.partials.shell.scripts')
</body>
</html>
