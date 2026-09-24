{{--
    Plantilla «Noche de calabazas»: invitación de temporada para una fiesta de Halloween, con los
    mismos módulos que las demás. Calabaza que se enciende al tocarla, luna llena con murciélagos,
    niebla y chispas de vela. Estilos en resources/css/invitation/themes/halloween.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::HALLOWEEN_CALABAZAS);
    $invCopy = $page->copy;
    // La calabaza aparece en cada visita, también después de la fiesta; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-hw-intro'])
    @endif
</head>
<body class="inv-page inv-halloween overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    @include('invitations.partials.halloween.defs')

    <a class="inv-skip" href="#contenido">{{ $invCopy['skip_link'] ?? 'Saltar al contenido' }}</a>

    @if($showIntro)
        @include('invitations.partials.halloween.intro')
    @endif

    @include('invitations.partials.halloween.ambient')
    {{-- Cielo con estrellas y hojas secas de otoño que caen --}}
    @include('invitations.partials.drift', ['kind' => 'twinkle', 'count' => 26, 'mobile' => 18, 'seed' => 2])
    @include('invitations.partials.drift', ['kind' => 'leaf', 'count' => 12, 'mobile' => 7, 'seed' => 4, 'class' => 'inv-drift--otono'])

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.halloween.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-hw-footer',
        'footerName' => $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y')),
        'footerOrnament' => 'pumpkin',
    ])

    @include('invitations.partials.shell.scripts')

    @if($showIntro)
        <script>
        // Calabaza de apertura: al tocarla se enciende la vela, salen los murciélagos y la noche se abre
        function halloweenIntro() {
            return {
                lit: false,
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
                light() {
                    if (this.lit) {
                        return;
                    }

                    this.lit = true;

                    const root = document.documentElement;
                    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

                    // La portada empieza a animarse mientras los murciélagos vuelan
                    setTimeout(() => root.classList.remove('inv-cover-waiting'), reduced ? 0 : 1700);
                    setTimeout(() => {
                        this.closed = true;
                        root.classList.remove('inv-lock');
                    }, reduced ? 150 : 2500);
                },
            };
        }
        </script>
    @endif
</body>
</html>
