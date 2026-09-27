{{--
    Cabecera de la colección «tendencias»: la tinta que se lee sobre cada color de la paleta. Las
    temáticas pintan piezas enteras con el principal, el secundario o el acento (el botón de
    terciopelo de la galería, la etiqueta de la gira, el sticker de la revista…) y con esto el texto
    encima siempre llega a AA, sea cual sea la paleta que elija el cliente
    (App\Support\ColorContrast::inkOn). Necesita $page.
--}}
@php
    $trendInk = fn (string $role) => \App\Support\ColorContrast::inkOn($page->colors[$role], $page->colors['text'], $page->colors['background']);
@endphp
<style>
    :root {
        --tr-ink-primary: {{ $trendInk('primary') }};
        --tr-ink-secondary: {{ $trendInk('secondary') }};
        --tr-ink-accent: {{ $trendInk('accent') }};
    }
</style>
