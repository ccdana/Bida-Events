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

        // Teléfonos del sitio: la apertura se luce un momento y se abre sola. Con ?reel=1 la muestra se
        // cargó oculta detrás de otra (resources/js/site.js) y espera la señal de que ya se ve.
        if (window.invCoverAutoplay) {
            const standby = new URLSearchParams(window.location.search).has('reel');

            window.invCoverPlay = new Promise((resolve) => {
                if (!standby) {
                    window.addEventListener('load', () => resolve(), { once: true });
                    return;
                }

                window.addEventListener('message', (event) => {
                    if (event.origin === window.location.origin && event.data === 'bida:cover-play') {
                        resolve();
                    }
                });
            });

            window.invCoverPlay.then(() => {
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
