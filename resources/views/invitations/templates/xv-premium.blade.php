@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::XV_PREMIUM);
    $invCopy = $page->copy;
    // El telón aparece en cada visita, también después de la fiesta; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-xv-intro'])
    @endif
</head>
<body class="inv-page inv-xv overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">{{ $invCopy['skip_link'] ?? 'Saltar al contenido' }}</a>

    @if($showIntro)
        @include('invitations.partials.xv.intro')
    @endif

    @include('invitations.partials.particles')
    {{-- Destellos que suben junto a las luces --}}
    @include('invitations.partials.drift', ['kind' => 'star', 'count' => 14, 'mobile' => 8, 'seed' => 3])

    @include('invitations.partials.xv.glints')

    @include('invitations.partials.shell.nav')
    {{-- También se puede ver como historias de Instagram (el círculo de la esquina o ?historias) --}}
    @include('invitations.partials.story.instagram')

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

    @if($showIntro)
        @include('invitations.partials.shell.cover-component')
    @endif
</body>
</html>
