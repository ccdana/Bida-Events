{{--
    Apertura que se toca para entrar (sobre, pastel, telón o nubes): la portada espera sus animaciones
    hasta que el invitado abre la invitación. Dentro de un iframe (editor) se omite, salvo en las muestras de la home.
    Parámetro: coverSelector (se oculta sin JavaScript).
--}}
<script>
    (function () {
        const root = document.documentElement;

        if (window.self !== window.top && !window.invDemo) {
            root.classList.add('inv-cover-skip');
            return;
        }

        // Teléfono de la portada de la home: la apertura se luce un momento y se abre sola
        if (window.invCoverAutoplay) {
            window.addEventListener('load', () => {
                setTimeout(() => document.querySelector('[data-cover-trigger]')?.click(), 2200);
            });
        }

        root.classList.add('inv-cover-waiting');

        // La apertura se muestra siempre sobre la portada, aunque sea una recarga
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        window.scrollTo(0, 0);
    })();
</script>
<noscript><style>{{ $coverSelector }} { display: none !important; }</style></noscript>
