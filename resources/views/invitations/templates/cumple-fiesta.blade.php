{{--
    Plantilla "Sopla las velas": mismos módulos que las demás con un lenguaje de fiesta.
    Pastel con velas que se soplan al entrar, confeti, globos, banderines, la edad en grande y
    controles con bordes marcados y sombras de color. Estilos en resources/css/invitation/themes/cumple.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::CUMPLE_FIESTA);
    $invCopy = $page->copy;
    // El pastel aparece en cada visita, también después de la fiesta; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-cumple-intro'])
    @endif
</head>
<body class="inv-page inv-cumple overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">Saltar al contenido</a>

    @if($showIntro)
        @include('invitations.partials.cumple.intro')
    @endif

    @include('invitations.partials.cumple.ambient')

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.cumple.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-cumple-footer',
        'footerName' => $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y')),
        'footerOrnament' => 'balloons',
    ])

    @include('invitations.partials.shell.scripts')

    @if($showIntro)
        <script>
        // Pastel de apertura: al soplar las velas sale confeti; el toque también desbloquea la música si tiene autoplay
        function birthdayIntro() {
            return {
                blown: false,
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
                blow() {
                    if (this.blown) {
                        return;
                    }

                    this.blown = true;

                    const root = document.documentElement;
                    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

                    // La portada empieza a animarse mientras el confeti cae y el pastel se va
                    setTimeout(() => root.classList.remove('inv-cover-waiting'), reduced ? 0 : 1600);
                    setTimeout(() => {
                        this.closed = true;
                        root.classList.remove('inv-lock');
                    }, reduced ? 150 : 2400);
                },
            };
        }
        </script>
    @endif
</body>
</html>
