{{--
    Tarjeta «Libro de aventuras» (Día del Amor, 21 de septiembre): un cuaderno de recortes con tapa
    de cuero, hojas de papel, cinta washi y flores amarillas que se hojea pasando las páginas.
    Módulos y vocabulario en App\EventProfiles\AdventureBookProfile; estilos en resources/css/cards/aventura.css;
    la vuelta de página en resources/js/aventura (librería page-flip).
    Sin JavaScript, con «reducir movimiento» o con «Ver todas las hojas» las páginas quedan apiladas.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::TARJETA_AVENTURA);
    $invCopy = $page->copy;
    $dedication = $modulos['dedicatoria'] ?? [];
    $cardTo = trim((string) ($dedication['para'] ?? '')) ?: ($guest?->name ?? '');
    $cardFrom = trim((string) ($dedication['de'] ?? ''));
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    {{-- Letras propias del cuaderno: manuscrita y de máquina de escribir --}}
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;700&family=Special+Elite&display=swap" rel="stylesheet">
    @vite(['resources/css/invitation/story.css', 'resources/css/cards/aventura.css'])
</head>
<body class="inv-page inv-aventura overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">{{ $invCopy['skip_link'] ?? 'Saltar al contenido' }}</a>

    @include('invitations.partials.aventura.sprite')

    {{-- Luces y destellos suaves sobre el escritorio (por encima del fondo, sin tapar toques) --}}
    @include('invitations.partials.drift', ['kind' => 'bokeh', 'count' => 8, 'mobile' => 5, 'seed' => 3, 'class' => 'inv-drift--suave inv-drift--above'])
    @include('invitations.partials.drift', ['kind' => 'star', 'count' => 10, 'mobile' => 6, 'seed' => 8, 'class' => 'inv-drift--suave inv-drift--above'])

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    <main id="contenido" class="nb-desk">
        @include('invitations.partials.aventura.book')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-aventura-footer',
        'footerName' => $cardTo !== '' ? 'Para '.$cardTo : $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')),
        'footerOrnament' => null,
    ])

    @include('invitations.partials.shell.scripts')
</body>
</html>
