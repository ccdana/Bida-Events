{{--
    Portada de «Atelier»: el tablero de inspiración del taller. La foto va sujeta con dos alfileres,
    con muestras de tela asomando detrás; encima de su borde, la etiqueta tejida con su nombre como
    si fuera su propia casa de moda. Debajo, la ficha del desfile (fecha, hora y pasarela) con la firma
    de la diseñadora, y al pie la cinta métrica. Estilos en themes/atelier.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $firstName = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first();
    // El número de la ficha sale de la fecha: siempre el mismo para esta invitación
    $sheetCode = $page->eventDate->format('y').'·'.$page->eventDate->format('md');
    // La palabra antes del número de cada pieza (CSS counter)
    $pieceLabel = trim(str_replace(['<', '>', '"', '\\'], '', (string) ($invCopy['atelier_piece'] ?? 'Pieza')));
@endphp

<style>
    .inv-atelier { --at-piece-label: "{{ $pieceLabel }}"; }
</style>

<header id="inicio" class="inv-hero at-hero">
    <p class="at-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="at-board inv-fade-up inv-fade-up--1">
        {{-- Muestras de tela que asoman detrás de la foto, una a cada lado --}}
        <span class="at-board__swatch at-board__swatch--left" aria-hidden="true"></span>
        <span class="at-board__swatch at-board__swatch--right" aria-hidden="true"></span>

        <figure class="at-photo">
            <span class="at-photo__frame">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 18rem, 66vw'])
            </span>
            <span class="at-pin at-pin--left" aria-hidden="true"></span>
            <span class="at-pin at-pin--right" aria-hidden="true"></span>
        </figure>

        {{-- La etiqueta tejida: su nombre es la casa --}}
        <div class="at-label">
            <span class="at-label__house">{{ $invCopy['atelier_house'] ?? 'Maison' }}</span>
            <h1 class="at-label__name">{{ $page->displayName }}</h1>
            <span class="at-label__line">{{ $invCopy['atelier_collection'] ?? 'Colección XV' }} · {{ $page->eventDate->format('Y') }}</span>
        </div>
    </div>

    {{-- La ficha del desfile --}}
    <div class="at-sheet inv-fade-up inv-fade-up--2">
        <p class="at-sheet__title">
            <span>{{ $invCopy['atelier_sheet'] ?? 'Ficha del desfile' }}</span>
            <span class="at-sheet__code">N.º {{ $sheetCode }}</span>
        </p>
        <dl class="at-sheet__fields">
            <div class="at-sheet__field">
                <dt>{{ $invCopy['atelier_date'] ?? 'Fecha' }}</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div class="at-sheet__field">
                <dt>{{ $invCopy['atelier_time'] ?? 'Hora' }}</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div class="at-sheet__field at-sheet__field--wide">
                    <dt>{{ $invCopy['atelier_place'] ?? 'Pasarela' }}</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>
        <span class="at-sheet__sign" aria-hidden="true">{{ $firstName }}</span>
    </div>

    @if(!empty($heroMessage))
        <p class="at-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <span class="at-tape at-tape--hero" aria-hidden="true"></span>

    <a href="#contenido" class="inv-hero__scroll at-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
