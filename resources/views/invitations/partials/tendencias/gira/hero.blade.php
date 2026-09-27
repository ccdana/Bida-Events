{{--
    Portada de «Gira mundial»: el afiche de la gira, impreso a dos tintas como en risografía, pegado
    con cinta. Arriba «En concierto»; el nombre como el del artista, con la segunda tinta corrida; la
    edad gigante sobreimpresa detrás; la foto a dos tintas con trama de puntos y, abajo, la lista de
    fechas: todas las ciudades de la gira tachadas y canceladas, menos una: la fiesta, con su sello
    de «Fecha única». Estilos en tendencias/gira.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $age = $page->age();
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? '¡Celebremos juntos!');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $cities = array_values(array_filter(array_map('trim', explode('·', (string) ($invCopy['tour_cities'] ?? 'Tokio · París · Nueva York · Londres')))));
    $shortDate = fn ($day) => \Illuminate\Support\Str::upper($day->translatedFormat('j M'));
    // Las ciudades canceladas son los días previos de la gira
    $cityCount = count($cities);
@endphp

<header id="inicio" class="inv-hero gr-hero">
    <div class="gr-poster">
        <span class="gr-tape gr-tape--left" aria-hidden="true"></span>
        <span class="gr-tape gr-tape--right" aria-hidden="true"></span>

        <p class="gr-poster__presents inv-fade-up">{{ $invCopy['tour_presents'] ?? 'En concierto' }}</p>

        <div class="gr-poster__headline inv-fade-up inv-fade-up--1">
            @if($age !== null)
                <span class="gr-poster__age" aria-hidden="true">{{ $age }}</span>
            @endif
            <h1 class="gr-poster__name" data-text="{{ $page->displayName }}">{{ $page->displayName }}</h1>
            <p class="gr-poster__tour">{{ $invCopy['tour_name'] ?? 'Gira' }} {{ $age ?? $date->format('Y') }}</p>
        </div>

        <figure class="gr-poster__photo inv-fade-up inv-fade-up--2">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1080], 'width' => 1080, 'sizes' => '(min-width: 640px) 26rem, 88vw'])
            <span class="gr-poster__dots" aria-hidden="true"></span>
        </figure>

        <p class="gr-poster__eyebrow inv-fade-up inv-fade-up--2">{{ $heroEyebrow }}</p>

        {{-- Las fechas de la gira: todas canceladas menos la fiesta --}}
        <ol class="gr-dates inv-fade-up inv-fade-up--3">
            @foreach($cities as $index => $city)
                <li class="gr-date is-cancelled">
                    <span class="gr-date__day">{{ $shortDate($date->copy()->subDays($cityCount - $index)) }}</span>
                    <span class="gr-date__city"><s>{{ $city }}</s></span>
                    <span class="gr-date__status">{{ $invCopy['tour_cancelled'] ?? 'Cancelado' }}</span>
                </li>
            @endforeach
            <li class="gr-date is-on">
                <span class="gr-date__day">{{ $shortDate($date) }}</span>
                <span class="gr-date__city">
                    @if($page->placeName)
                        <a href="#ubicacion">{{ $page->placeName }}</a>
                    @else
                        {{ $page->displayName }}
                    @endif
                </span>
                <span class="gr-date__status">{{ $date->format('H:i') }}</span>
                <span class="gr-date__stamp" aria-hidden="true">{{ $invCopy['tour_only'] ?? 'Fecha única' }}</span>
            </li>
        </ol>

        @if($heroMessage !== '')
            <p class="gr-poster__message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
        @endif

        <p class="gr-poster__doors">{{ $invCopy['tour_doors'] ?? 'Puertas' }} {{ $date->format('H:i') }} · {{ \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F')) }}</p>
    </div>

    <a href="#contenido" class="inv-hero__scroll gr-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
