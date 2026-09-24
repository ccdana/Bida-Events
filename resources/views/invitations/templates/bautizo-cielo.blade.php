{{--
    Plantilla "Entre nubes": mismos módulos que las demás con un lenguaje de bautizo.
    Pila bautismal y jarra de agua que se toca para entrar, foto en medallón con halo y paloma, destellos, burbujas y
    secciones separadas por olas. Estilos en resources/css/invitation/themes/bautizo.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::BAUTIZO_CIELO);
    $invCopy = $page->copy;
    // Las nubes aparecen en cada visita, también después del bautizo; solo se omiten en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-bautizo-intro'])
    @endif
</head>
<body class="inv-page inv-bautizo overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">{{ $invCopy['skip_link'] ?? 'Saltar al contenido' }}</a>

    @if($showIntro)
        @include('invitations.partials.bautizo.intro')
    @endif

    @include('invitations.partials.bautizo.ambient')
    {{-- Plumas blancas que bajan meciéndose, como de las palomas --}}
    @include('invitations.partials.drift', ['kind' => 'feather', 'count' => 10, 'mobile' => 6, 'seed' => 3, 'class' => 'inv-drift--plumas'])
    @include('invitations.partials.drift', ['kind' => 'star', 'count' => 12, 'mobile' => 7, 'seed' => 4])

    @include('invitations.partials.shell.nav')
    {{-- También se puede ver como historias de Instagram (el círculo de la esquina o ?historias) --}}
    @include('invitations.partials.story.instagram')

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

    @if($showIntro)
        @include('invitations.partials.shell.cover-component')
    @endif
</body>
</html>
