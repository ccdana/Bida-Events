{{--
    Portada de «Galería Quince»: la pared principal de la exposición. Arriba, la rotulación de la
    muestra (el nombre como título); al centro, el retrato colgado de su alambre bajo un foco, con
    marco y paspartú; al lado, la cédula del museo con la ficha de la obra y los datos de la
    inauguración (fecha, hora y sala). Al pie, el zócalo y el cordón de terciopelo que ordena la
    fila. Todo lee la paleta y las letras del editor. Estilos en tendencias/galeria.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
    // La obra va de su nacimiento a la noche de la inauguración: «Retrato, 2011–2026»
    $bornYear = (int) $date->format('Y') - ($page->age() ?? 15);
    // El número de sala de cada sección (CSS counter) lleva la palabra de la plantilla
    $roomLabel = trim(str_replace(['<', '>', '"', '\\'], '', (string) ($invCopy['room_label'] ?? 'Sala')));
@endphp

<style>
    .inv-galeria { --gq-room-label: "{{ $roomLabel }}"; }
</style>

<header id="inicio" class="inv-hero gq-hero">
    <span class="gq-hero__spot" aria-hidden="true"></span>

    <div class="gq-hero__lettering">
        <p class="gq-kicker inv-fade-up">{{ $invCopy['exhibit_label'] ?? 'Exposición' }}</p>
        <h1 class="gq-title inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>
        <p class="gq-subtitle inv-fade-up inv-fade-up--1">{{ $heroEyebrow }}</p>
    </div>

    <figure class="gq-artwork inv-fade-up inv-fade-up--2">
        {{-- Alambre y clavo de los que cuelga el cuadro --}}
        <svg class="gq-artwork__wire" viewBox="0 0 200 60" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <path d="M18 60 L100 8 L182 60"/>
            <circle cx="100" cy="7" r="4"/>
        </svg>
        <span class="gq-frame">
            <span class="gq-mat">
                <span class="gq-canvas">
                    @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1080], 'width' => 1080, 'sizes' => '(min-width: 768px) 22rem, 70vw', 'parallax' => '0.06'])
                </span>
            </span>
        </span>
    </figure>

    {{-- La cédula: la ficha de la obra y, como en toda inauguración, cuándo y dónde --}}
    <aside class="gq-label inv-fade-up inv-fade-up--3" aria-label="Ficha de la obra">
        <p class="gq-label__artist">{{ $page->displayName }}</p>
        <p class="gq-label__work"><em>{{ $invCopy['exhibit_title'] ?? 'Quince' }}</em>, {{ $bornYear }}–{{ $date->format('Y') }}</p>
        <p class="gq-label__medium">{{ $invCopy['exhibit_piece'] ?? 'Retrato' }}</p>
        @if($heroMessage !== '')
            <p class="gq-label__text">{{ $heroMessage }}</p>
        @endif
        <dl class="gq-label__facts">
            <div>
                <dt>{{ $invCopy['exhibit_opening'] ?? 'Inauguración' }}</dt>
                <dd>{{ $heroDay }} · {{ $date->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div>
                    <dt>{{ $invCopy['exhibit_room'] ?? 'Sala principal' }}</dt>
                    <dd><a href="#ubicacion">{{ $page->placeName }}</a></dd>
                </div>
            @endif
        </dl>
        <p class="gq-label__foot">
            <span>
                @if($guest)
                    {{ $invCopy['exhibit_guest'] ?? 'Invitación de honor para' }} <strong>{{ $guest->name }}</strong>
                @else
                    {{ $invCopy['exhibit_free'] ?? 'Entrada con invitación' }}
                @endif
            </span>
            <span class="gq-label__no" aria-hidden="true">N.º {{ $page->age() ?? 15 }}</span>
        </p>
    </aside>

    {{-- Zócalo y cordón de la fila --}}
    <div class="gq-floor" aria-hidden="true">
        <svg viewBox="0 0 320 90" preserveAspectRatio="xMidYMax meet" focusable="false">
            <g class="gq-post">
                <ellipse cx="34" cy="84" rx="20" ry="5"/>
                <rect x="31" y="22" width="6" height="62" rx="2"/>
                <circle cx="34" cy="19" r="7"/>
            </g>
            <g class="gq-post">
                <ellipse cx="286" cy="84" rx="20" ry="5"/>
                <rect x="283" y="22" width="6" height="62" rx="2"/>
                <circle cx="286" cy="19" r="7"/>
            </g>
            <path class="gq-rope__cord" d="M40 27 C 110 70, 210 70, 280 27"/>
            <path class="gq-rope__sheen" d="M40 27 C 110 70, 210 70, 280 27"/>
        </svg>
    </div>

    <a href="#contenido" class="inv-hero__scroll gq-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
