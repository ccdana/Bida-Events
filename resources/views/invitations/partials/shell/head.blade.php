{{-- Cabecera común de las plantillas: metadatos, assets, fuentes y paleta del evento --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- La invitación es privada: no debe aparecer en buscadores --}}
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="{{ $page->colors['background'] }}">
<title>{{ $page->displayName }}</title>
{{-- Vista previa al pegar el enlace en WhatsApp: nombre, fecha, lugar y foto de portada --}}
@include('layouts.partials.share-meta', ['share' => $page->share(url()->current(), empty($isDemo))])
<script>
    (function () {
        const root = document.documentElement;

        // Dentro de un iframe (vista previa del editor o teléfono de la home) se oculta la barra de scroll
        if (window.self !== window.top) {
            root.classList.add('inv-embedded');
        }

        // La invitación se sirve con la clase no-js: el HTML se lee plano y completo.
        // Al arrancar se quita, y un vigía la devuelve si Alpine nunca inicia (bundle
        // caído o red lenta), para que el invitado no se quede con la pantalla en blanco.
        root.classList.remove('no-js');

        const watchdog = setTimeout(function () {
            if (window.Alpine) return;
            root.classList.add('no-js', 'inv-cover-skip');
            root.classList.remove('inv-cover-waiting', 'inv-lock');
        }, 6000);

        document.addEventListener('alpine:init', function () {
            clearTimeout(watchdog);
        });
    })();

    // Muestra de la home: las respuestas se simulan en el navegador y la música nunca arranca sola
    window.invDemo = @js(! empty($isDemo));
    window.invCoverAutoplay = @js(! empty($coverAutoplay));
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?{{ $page->fontQuery() }}&display=swap" rel="stylesheet">
<style>
    html { scroll-behavior: smooth; }
    :root {
        --primary-color: {{ $page->colors['primary'] }};
        --secondary-color: {{ $page->colors['secondary'] }};
        --accent-color: {{ $page->colors['accent'] }};
        --text-color: {{ $page->colors['text'] }};
        --bg-color: {{ $page->colors['background'] }};
        --surface-color: color-mix(in srgb, var(--accent-color) 32%, var(--bg-color));
        --font-titles: '{{ $page->fonts['titulos'] }}', serif;
        --font-body: '{{ $page->fonts['cuerpo'] }}', sans-serif;
        --font-script: '{{ $page->fonts['script'] }}', cursive;
    }
</style>
