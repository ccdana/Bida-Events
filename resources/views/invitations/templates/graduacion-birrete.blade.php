{{--
    Plantilla «Birrete al aire»: mismos módulos que las demás con un lenguaje de graduación.
    Diploma enrollado con cinta que se desata al entrar, birretes que vuelan, foto en arco con doble
    filete dorado y el pie de la portada como el de un diploma. Estilos en themes/graduacion.css.
--}}
@php
    $page = new \App\Support\InvitationPage($invitation, $modulos, $guest ?? null, \App\Support\InvitationTemplates::GRADUACION_BIRRETE);
    $invCopy = $page->copy;
    // El diploma aparece en cada visita, también después del acto; solo se omite en la vista previa del editor
    $showIntro = empty($isPreview);
@endphp
<!DOCTYPE html>
{{-- La clase no-js la quita el primer script de la cabecera (ver shell/head) --}}
<html lang="es" class="no-js">
<head>
    @include('invitations.partials.shell.head')
    @if($showIntro)
        @include('invitations.partials.shell.cover-script', ['coverSelector' => '.inv-grad-intro'])
    @endif
</head>
<body class="inv-page inv-graduacion overflow-x-hidden {{ $page->hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">
    <a class="inv-skip" href="#contenido">Saltar al contenido</a>

    @if($showIntro)
        @include('invitations.partials.graduacion.intro')
    @endif

    @include('invitations.partials.graduacion.ambient')

    @include('invitations.partials.shell.nav')

    @include('invitations.partials.music-player', ['musica' => $page->music, 'flags' => array_merge($page->flags, ['musica' => $page->visible('musica')])])

    @include('invitations.partials.graduacion.hero')

    <main id="contenido">
        @include('invitations.partials.shell.modules')
    </main>

    @include('invitations.partials.shell.footer', [
        'footerClass' => 'inv-grad-footer',
        'footerName' => $page->displayName,
        'footerDate' => \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y')),
        'footerOrnament' => 'cap',
    ])

    @include('invitations.partials.shell.scripts')

    @if($showIntro)
        <script>
        // Diploma de apertura: al tocar la cinta se suelta el lazo, el papel se despliega y vuelan los birretes
        function graduationIntro() {
            return {
                opened: false,
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
                    if (this.opened) {
                        return;
                    }

                    this.opened = true;

                    const root = document.documentElement;
                    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

                    // La portada empieza a animarse mientras el diploma se abre y los birretes vuelan
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
