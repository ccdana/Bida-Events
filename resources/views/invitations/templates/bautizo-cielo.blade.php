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
        <script>
            // Solo en la primera visita de la sesión y nunca dentro de un iframe (teléfono de la home)
            (function () {
                let skip = window.self !== window.top;

                try {
                    skip = skip || sessionStorage.getItem(@js($introKey)) === '1';
                    sessionStorage.setItem(@js($introKey), '1');
                } catch (error) {}

                document.documentElement.classList.add(skip ? 'inv-intro-skip' : 'inv-intro-active');

                // Con la apertura, la invitación empieza siempre en la portada aunque sea una recarga
                if (!skip && 'scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                    window.scrollTo(0, 0);
                }
            })();
        </script>
        <noscript><style>.inv-bautizo-intro { display: none !important; }</style></noscript>
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

    <footer class="inv-footer inv-bautizo-footer">
        @include('invitations.partials.bautizo.dove', ['class' => 'inv-bautizo-footer__dove'])
        <p class="inv-bautizo-footer__name">{{ $page->displayName }}</p>
        <p class="inv-bautizo-footer__date">{{ \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')) }}</p>
        <p class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">Bida Events</span></p>
    </footer>

    @include('invitations.partials.shell.scripts')
</body>
</html>
