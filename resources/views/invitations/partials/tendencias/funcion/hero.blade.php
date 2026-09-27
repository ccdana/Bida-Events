{{--
    Portada de «Función de medianoche»: el afiche de la película de estreno, enmarcado con las luces
    de la marquesina. Arriba «Función de estreno»; el título (el nombre de la fiesta) en relieve; la
    imagen del afiche (la foto con tono de cine antiguo o, sin foto, un fantasmita simpático bajo la
    luna llena: es apta para todos); la frase, el sello de la clasificación y, abajo, el bloque de
    créditos de los afiches con los datos de verdad: estreno, función y sala. Estilos en
    tendencias/funcion.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Fiesta de Halloween');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
    $cssText = fn (string $text) => trim(str_replace(['<', '>', '"', '\\'], '', $text));
@endphp

<style>
    .inv-funcion { --fn-intermission: "{{ $cssText($invCopy['film_intermission'] ?? 'Intermedio') }}"; }
</style>

<header id="inicio" class="inv-hero fn-hero">
    <div class="fn-poster">
        <span class="fn-bulbs" aria-hidden="true"></span>

        <p class="fn-poster__presents inv-fade-up">{{ $invCopy['intro_eyebrow'] ?? 'Función de estreno' }}</p>
        <h1 class="fn-poster__title inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>

        <figure class="fn-poster__art inv-fade-up inv-fade-up--2">
            @if($page->heroImage)
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1080], 'width' => 1080, 'sizes' => '(min-width: 640px) 24rem, 82vw', 'class' => 'fn-poster__photo'])
            @else
                {{-- Sin foto: un fantasmita simpático que se asoma bajo la luna --}}
                <svg class="fn-poster__scene" viewBox="0 0 300 360" aria-hidden="true" focusable="false">
                    <circle class="fn-scene__moon" cx="210" cy="92" r="58"/>
                    <g class="fn-scene__stars">
                        <circle cx="48" cy="54" r="2.4"/><circle cx="92" cy="30" r="1.6"/><circle cx="130" cy="78" r="2"/>
                        <circle cx="262" cy="190" r="1.8"/><circle cx="36" cy="150" r="1.6"/>
                    </g>
                    <path class="fn-scene__hill" d="M0 300 C 70 250 140 262 190 282 C 236 300 270 276 300 262 V360 H0 Z"/>
                    <g class="fn-scene__ghost">
                        <path d="M104 290 V190 C104 146 134 118 160 118 C186 118 216 146 216 190 V290 C206 280 196 280 188 292 C180 280 168 280 160 292 C152 280 140 280 132 292 C124 280 114 280 104 290 Z"/>
                        <ellipse class="fn-scene__eye" cx="144" cy="186" rx="8" ry="11"/>
                        <ellipse class="fn-scene__eye" cx="178" cy="186" rx="8" ry="11"/>
                        <path class="fn-scene__smile" d="M146 214 Q161 228 176 214"/>
                    </g>
                </svg>
            @endif
        </figure>

        <p class="fn-poster__tagline inv-fade-up inv-fade-up--2">{{ $heroEyebrow }}</p>

        <div class="fn-rating inv-fade-up inv-fade-up--3">
            <span class="fn-rating__badge">{{ $invCopy['film_rating'] ?? 'Clasificación A' }}</span>
            <span class="fn-rating__text">{{ $invCopy['film_rating_text'] ?? 'Apta para valientes de todas las edades' }}</span>
        </div>

        @if($heroMessage !== '')
            <p class="fn-poster__message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
        @endif

        {{-- El bloque de créditos del afiche, con los datos de la fiesta --}}
        <dl class="fn-billing inv-fade-up inv-fade-up--3">
            <div><dt>{{ $invCopy['film_premiere'] ?? 'Estreno' }}</dt><dd>{{ $heroDay }}</dd></div>
            <div><dt>{{ $invCopy['film_show'] ?? 'Función' }}</dt><dd>{{ $date->format('H:i') }}</dd></div>
            @if($page->placeName)
                <div><dt>{{ $invCopy['film_theater'] ?? 'Sala' }}</dt><dd><a href="#ubicacion">{{ $page->placeName }}</a></dd></div>
            @endif
        </dl>
    </div>

    <a href="#contenido" class="inv-hero__scroll fn-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
