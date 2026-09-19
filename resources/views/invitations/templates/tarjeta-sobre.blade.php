{{--
    Tarjeta "Sobre lacrado" (Día del Amor y la Amistad, 21 de septiembre): de una persona a otra.
    «Una carta que llega por correo»: un sobre rojo con sello de lacre que se rompe al tocarlo y
    suelta una lluvia de flores. Se recorre en modo historia (partials/story/chrome,
    resources/js/story) y cada escena es una pieza del sobre: la foto de portada, el mensaje en un
    marco de luz, el tiempo juntos en una etiqueta, los recuerdos en polaroids, la película de sus
    videos, la carta escrita a mano en dos hojas, el álbum que se da vuelta y deja ver el vinilo con
    su canción, y la respuesta lacrada de vuelta.

    Comparte perfil y módulos con la carta de amor (App\EventProfiles\LoveCardProfile); vistas
    propias en partials/sobre (InvitationTemplates, «partials»); estilos en
    resources/css/cards/sobre.css; JS en resources/js/cards/sobre.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::TARJETA_SOBRE);
    $invCopy = $page->copy;
    $dedication = $modulos['dedicatoria'] ?? [];
    $cardTo = trim((string) ($dedication['para'] ?? '')) ?: ($guest?->name ?? '');
    $cardFrom = trim((string) ($dedication['de'] ?? ''));
    // El sobre se abre en cada visita; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @vite(['resources/css/invitation/story.css', 'resources/css/cards/sobre.css'])
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-sobre-intro'])
    @endif
</head>
<body class="inv-page inv-amor inv-sobre overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" data-card="sobre" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">Saltar al contenido</a>

    @if($showIntro)
        @include('invitations.partials.sobre.intro')
    @endif

    @include('invitations.partials.shell.nav')

    {{-- Se recorre por escenas (toque o deslizar); «Ver todo» vuelve a la página con scroll --}}
    @include('invitations.partials.story.chrome', ['storyHint' => 'Toca para seguir leyendo'])

    {{-- Lluvia de flores: cae al abrir el sobre y en cada cambio de escena --}}
    @include('invitations.partials.sobre.petals')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.sobre.hero')

    <main id="contenido">
        {{-- Mensaje corto de la portada, en un marco de luz entre dos polaroids --}}
        @include('invitations.partials.sobre.note')

        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-sobre-footer',
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
