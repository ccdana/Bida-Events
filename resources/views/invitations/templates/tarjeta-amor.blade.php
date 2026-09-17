{{--
    Tarjeta "Carta de amor" (Día del Amor, 21 de septiembre): de una persona a otra.
    Se abre como una carta atada con una cinta; adentro va la foto, la dedicatoria escrita a mano,
    el tiempo que llevan juntos, recuerdos y la respuesta del destinatario.
    Módulos y vocabulario en App\EventProfiles\LoveCardProfile; estilos en resources/css/cards/amor.css.
    Se recorre en modo historia (partials/story/chrome, resources/js/story): portada, carta con sello
    que se abre manteniendo presionado, tiempo juntos que se raspa, recuerdos y respuesta con celebración.
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
<body class="inv-page inv-amor overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">Saltar al contenido</a>

    @if($showIntro)
        @include('invitations.partials.amor.intro')
    @endif

    @include('invitations.partials.shell.nav')

    {{-- Se recorre por escenas (toque o deslizar); «Ver todo» vuelve a la página con scroll --}}
    @include('invitations.partials.story.chrome', ['storyHint' => 'Toca para seguir leyendo'])

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.amor.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
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
