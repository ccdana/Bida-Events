{{--
    Plantilla "Boda Jardín": mismos módulos que XV Premium con un lenguaje propio de boda.
    Sobre de apertura, foto en arco con ramas que crecen, pétalos, títulos caligráficos y
    secciones separadas por ornamentos. Estilos en resources/css/invitation/themes/boda.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::BODA_JARDIN);
    $invCopy = $page->copy;
    $coupleNames = $page->names();
    // El sobre no aparece en la vista previa del editor ni cuando la boda ya pasó
    $showCover = ! $page->isPostEvent && empty($isPreview);
    $coverKey = 'inv-opened-'.$invitation->slug;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    @include('invitations.partials.shell.head')
    @if($showCover)
        <script>
            // Sin sobre dentro de iframes (teléfono de la home) ni si ya se abrió en esta visita
            (function () {
                let skip = window.self !== window.top;

                try {
                    skip = skip || sessionStorage.getItem(@js($coverKey)) === '1';
                } catch (error) {}

                document.documentElement.classList.add(skip ? 'inv-cover-skip' : 'inv-cover-waiting');
            })();
        </script>
        <noscript><style>.inv-boda-cover { display: none !important; }</style></noscript>
    @endif
</head>
<body class="inv-page inv-boda overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">

    @if($showCover)
        @include('invitations.partials.boda.cover')
    @endif

    @include('invitations.partials.boda.ambient')

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.boda.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    <footer class="inv-footer inv-boda-footer">
        <p class="inv-boda-footer__names">{{ implode(' & ', $coupleNames) }}</p>
        <p class="inv-boda-footer__date">{{ $page->eventDate->format('d · m · Y') }}</p>
        <p class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">Bida Events</span></p>
    </footer>

    @include('invitations.partials.shell.scripts')

    @if($showCover)
        <script>
        // Sobre de apertura: el toque que lo abre también desbloquea la música si tiene autoplay
        function weddingCover(storageKey) {
            return {
                opening: false,
                closed: false,
                init() {
                    const root = document.documentElement;

                    if (root.classList.contains('inv-cover-skip')) {
                        this.closed = true;
                        return;
                    }

                    // Al abrir el sobre la invitación empieza siempre en la portada, aunque sea una recarga
                    if ('scrollRestoration' in history) {
                        history.scrollRestoration = 'manual';
                    }

                    window.scrollTo(0, 0);
                    root.classList.add('inv-lock');
                },
                open() {
                    if (this.opening) {
                        return;
                    }

                    this.opening = true;

                    try {
                        sessionStorage.setItem(storageKey, '1');
                    } catch (error) {}

                    const root = document.documentElement;
                    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

                    // La portada empieza a animarse mientras el sobre se desvanece
                    setTimeout(() => root.classList.remove('inv-cover-waiting'), reduced ? 0 : 1250);
                    setTimeout(() => {
                        this.closed = true;
                        root.classList.remove('inv-lock');
                    }, reduced ? 150 : 2150);
                },
            };
        }
        </script>
    @endif
</body>
</html>
