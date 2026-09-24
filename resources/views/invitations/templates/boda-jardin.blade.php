{{--
    Plantilla "Promesa en el jardín": mismos módulos que «Noche de gala» con un lenguaje propio de boda.
    Sobre de apertura, foto en arco con ramas que crecen, pétalos, títulos caligráficos y
    secciones separadas por ornamentos. Estilos en resources/css/invitation/themes/boda.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::BODA_JARDIN);
    $invCopy = $page->copy;
    $coupleNames = $page->names();
    // El sobre se muestra en cada visita, también después de la boda; solo se omite en la vista previa del editor
    $showCover = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showCover)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-boda-cover'])
    @endif
</head>
<body class="inv-page inv-boda overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">{{ $invCopy['skip_link'] ?? 'Saltar al contenido' }}</a>

    @if($showCover)
        @include('invitations.partials.boda.cover')
    @endif

    @include('invitations.partials.boda.ambient')
    {{-- Hojas del jardín que caen junto a los pétalos --}}
    @include('invitations.partials.drift', ['kind' => 'leaf', 'count' => 12, 'mobile' => 7, 'seed' => 2, 'class' => 'inv-drift--jardin'])
    @include('invitations.partials.drift', ['kind' => 'bokeh', 'count' => 10, 'mobile' => 6, 'seed' => 5])

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.boda.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-boda-footer',
        'footerName' => implode(' & ', $coupleNames),
        'footerDate' => $page->eventDate->format('d · m · Y'),
        'footerOrnament' => null,
    ])

    @include('invitations.partials.shell.scripts')

    @if($showCover)
        <script>
        // Sobre de apertura: el toque que lo abre también desbloquea la música si tiene autoplay
        function weddingCover() {
            return {
                // 0 cerrado · 1 sello y solapa · 2 sale la tarjeta · 3 el sobre se hunde · 4 se desvanece
                stage: 0,
                closed: false,
                init() {
                    const root = document.documentElement;

                    if (root.classList.contains('inv-cover-skip')) {
                        this.closed = true;
                        return;
                    }

                    window.scrollTo(0, 0);
                    root.classList.add('inv-lock');
                },
                open() {
                    if (this.stage > 0) {
                        return;
                    }

                    const root = document.documentElement;
                    const finish = () => {
                        this.closed = true;
                        root.classList.remove('inv-lock');
                    };

                    if (window.matchMedia?.('(prefers-reduced-motion: reduce)').matches) {
                        root.classList.remove('inv-cover-waiting');
                        finish();
                        return;
                    }

                    // Cada etapa se activa por tiempo: así la tarjeta siempre queda por delante de la solapa ya abierta
                    this.stage = 1;
                    setTimeout(() => { this.stage = 2; }, 800);
                    setTimeout(() => { this.stage = 3; }, 1650);
                    setTimeout(() => {
                        this.stage = 4;
                        root.classList.remove('inv-cover-waiting');
                    }, 2300);
                    setTimeout(finish, 2950);
                },
            };
        }
        </script>
    @endif
</body>
</html>
