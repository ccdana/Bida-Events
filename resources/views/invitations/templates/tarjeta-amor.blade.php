{{--
    Tarjeta "Carta de amor" (Día del Amor, 21 de septiembre): de una persona a otra.
    «Un jardín que florece», por el Día del Amor y la primavera. Se recorre en modo historia
    (partials/story/chrome, resources/js/story) y cada escena es un gesto distinto:
    regar un capullo para abrirla, la foto que se revela, la carta con sello (mantener presionado),
    la margarita que se deshoja, los recuerdos en un tendedero, responder con una flor y soplar un
    diente de león. Tres mariposas escondidas, pasto que florece al avanzar y ráfagas de pétalos.
    Módulos y vocabulario en App\EventProfiles\LoveCardProfile; vistas propias en partials/amor
    (InvitationTemplates, «partials»); estilos en resources/css/cards/amor.css; JS en resources/js/cards/amor.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::TARJETA_AMOR);
    $invCopy = $page->copy;
    $dedication = $modulos['dedicatoria'] ?? [];
    $cardTo = trim((string) ($dedication['para'] ?? '')) ?: ($guest?->name ?? '');
    $cardFrom = trim((string) ($dedication['de'] ?? ''));
    // La carta se abre en cada visita; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @vite(['resources/css/invitation/story.css', 'resources/css/cards/amor.css'])
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-amor-intro'])
    @endif
</head>
<body class="inv-page inv-amor overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" data-card="amor" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">Saltar al contenido</a>

    @if($showIntro)
        @include('invitations.partials.amor.intro')
    @endif

    @include('invitations.partials.shell.nav')

    {{-- Se recorre por escenas (toque o deslizar); «Ver todo» vuelve a la página con scroll --}}
    @include('invitations.partials.story.chrome', ['storyHint' => 'Toca para seguir leyendo'])

    {{-- Jardín vivo: pasto que florece al avanzar, ráfaga de pétalos y mariposas escondidas --}}
    @include('invitations.partials.amor.garden')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.amor.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')

        {{-- Cierre: un diente de león para pedir un deseo --}}
        @include('invitations.partials.amor.wish')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-amor-footer',
        'footerName' => $cardTo !== '' ? 'Para '.$cardTo : $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')),
        'footerOrnament' => null,
    ])

    @include('invitations.partials.shell.scripts')

    @if($showIntro)
        @include('invitations.partials.shell.cover-component')
    @endif
</body>
</html>
