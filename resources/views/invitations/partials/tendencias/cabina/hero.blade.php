{{--
    Portada de «Cabina de fotos»: entre las dos cortinas de terciopelo de la cabina, el nombre en grande
    y, colgada de su pinza, la tira recién salida: tres poses con el sello naranja de la fecha y, al pie,
    el nombre, el día, la hora y el lugar impresos. Debajo, el mensaje. El sello de la fecha también lo
    llevan todas las secciones (--cb-stamp). Estilos en css/invitation/tendencias/cabina.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? '¡Celebremos juntos!');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
@endphp
{{-- El sello de la fecha de todas las secciones y la tinta que se lee sobre el papel fotográfico --}}
<style>
    :root {
        --cb-stamp: "{{ $date->format('d m') }} '{{ $date->format('y') }}";
        --cb-paper-ink: {{ \App\Support\ColorContrast::inkOn('#FBF8F3', $page->colors['text'], $page->colors['background']) }};
    }
</style>

<header id="inicio" class="inv-hero cb-hero">
    <span class="cb-hero__curtain cb-hero__curtain--left" aria-hidden="true"></span>
    <span class="cb-hero__curtain cb-hero__curtain--right" aria-hidden="true"></span>

    <p class="cb-kicker inv-fade-up">{{ $heroEyebrow }}</p>
    <h1 class="cb-hero__name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>

    <div class="cb-hang inv-fade-up inv-fade-up--2">
        <span class="cb-hang__clip" aria-hidden="true"></span>
        @include('invitations.partials.tendencias.cabina.strip', ['class' => 'cb-strip--hero'])
    </div>

    @if($heroMessage !== '')
        <p class="cb-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll cb-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
