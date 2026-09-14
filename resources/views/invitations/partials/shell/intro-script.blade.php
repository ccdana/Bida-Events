{{--
    Apertura animada de las plantillas (XV y bautizo): solo en la primera visita de la sesión y nunca
    dentro de un iframe (teléfono de la home). Parámetros: introKey e introSelector.
--}}
<script>
    (function () {
        let skip = window.self !== window.top;

        try {
            skip = skip || sessionStorage.getItem(@js($introKey)) === '1';
            sessionStorage.setItem(@js($introKey), '1');
        } catch (error) {}

        document.documentElement.classList.add(skip ? 'inv-intro-skip' : 'inv-intro-active');

        // Con apertura, la invitación empieza siempre en la portada aunque sea una recarga
        if (!skip && 'scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
            window.scrollTo(0, 0);
        }
    })();
</script>
<noscript><style>{{ $introSelector }} { display: none !important; }</style></noscript>
