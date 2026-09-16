{{-- Cabecera común de las plantillas: metadatos, assets, fuentes y paleta del evento --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
{{-- La invitación es privada: no debe aparecer en buscadores --}}
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="{{ $page->colors['background'] }}">
<title>{{ $page->displayName }}</title>
<script>
    // Dentro de un iframe (vista previa del editor o teléfono de la home) se oculta la barra de scroll
    if (window.self !== window.top) {
        document.documentElement.classList.add('inv-embedded');
    }

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
