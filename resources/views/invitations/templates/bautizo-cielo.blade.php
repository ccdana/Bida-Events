{{--
    Plantilla "Bautizo Cielo": mismos módulos que las demás con un lenguaje de bautizo.
    Nubes que se abren al entrar, foto en medallón con halo y paloma, destellos, burbujas y
    secciones separadas por olas. Estilos en resources/css/invitation/themes/bautizo.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::BAUTIZO_CIELO);
    $invCopy = $page->copy;
    // La apertura con nubes no aparece en la vista previa del editor ni cuando el bautizo ya pasó
    $showIntro = ! $page->isPostEvent && empty($isPreview);
    $introKey = 'inv-intro-'.$invitation->slug;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.intro-script', ['introSelector' => '.inv-bautizo-intro'])
    @endif
</head>
<body class="inv-page inv-bautizo overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">

    @if($showIntro)
        @include('invitations.partials.bautizo.intro')
    @endif

    @include('invitations.partials.bautizo.ambient')

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.bautizo.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-bautizo-footer',
        'footerName' => $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')),
        'footerOrnament' => 'dove',
    ])

    @include('invitations.partials.shell.scripts')
</body>
</html>
