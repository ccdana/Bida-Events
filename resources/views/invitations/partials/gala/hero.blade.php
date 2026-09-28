{{--
    Portada de «Noche de gala»: la pared del salón con su papel labrado. Arriba cuelga la araña,
    encendida; debajo, el espejo ovalado con marco dorado —coronado, con cuentas y hojas— y la foto
    adentro, por el que cruza un reflejo cada tanto. El nombre va grabado en la placa del marco y
    debajo, la fecha y el lugar entre adornos. Estilos en themes/gala.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $frameId = 'ga-frame-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));
@endphp

<header id="inicio" class="inv-hero ga-hero">
    <div class="ga-hero__ceiling" aria-hidden="true">
        @include('invitations.partials.gala.chandelier', ['lit' => true, 'class' => 'ga-hero__chandelier'])
    </div>

    <p class="ga-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="ga-mirror inv-fade-up inv-fade-up--1">
        <div class="ga-mirror__glass">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 16rem, 58vw'])
            <span class="ga-mirror__shine" aria-hidden="true"></span>
        </div>

        {{-- El marco: óvalo dorado con su filete de cuentas, hojas a los lados y la corona arriba --}}
        <svg class="ga-mirror__frame" viewBox="0 0 240 300" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="{{ $frameId }}" x1="0" y1="0" x2="1" y2="1">
                    <stop class="ga-ch__metal-hi" offset="0"/>
                    <stop class="ga-ch__metal-mid" offset="0.45"/>
                    <stop class="ga-ch__metal-low" offset="0.7"/>
                    <stop class="ga-ch__metal-hi" offset="1"/>
                </linearGradient>
            </defs>
            <path class="ga-frame__ring" fill="url(#{{ $frameId }})" fill-rule="evenodd"
                d="M120 30 A94 122 0 1 1 119.9 30 Z M120 44 A80 108 0 1 0 120.1 44 Z"/>
            <ellipse class="ga-frame__beads" cx="120" cy="152" rx="87" ry="115"/>
            <ellipse class="ga-frame__edge" cx="120" cy="152" rx="80" ry="108"/>
            {{-- Hojas a los costados y abajo --}}
            @foreach([[20, 152, -90], [220, 152, 90], [120, 270, 180]] as [$x, $y, $angle])
                <g transform="translate({{ $x }} {{ $y }}) rotate({{ $angle }})" class="ga-frame__leaf" fill="url(#{{ $frameId }})">
                    <path d="M0 -4 C-10 -14 -22 -12 -26 -4 C-18 -6 -10 -4 0 4 Z"/>
                    <path d="M0 -4 C10 -14 22 -12 26 -4 C18 -6 10 -4 0 4 Z"/>
                    <circle cx="0" cy="0" r="4.5"/>
                </g>
            @endforeach
            {{-- La corona del remate --}}
            <g class="ga-frame__crown" fill="url(#{{ $frameId }})">
                <path d="M98 30 L100 12 L109 22 L120 6 L131 22 L140 12 L142 30 Z"/>
                <rect x="96" y="29" width="48" height="5" rx="2"/>
                <circle cx="100" cy="10" r="2.6"/>
                <circle cx="120" cy="4" r="3"/>
                <circle cx="140" cy="10" r="2.6"/>
            </g>
        </svg>
    </div>

    {{-- La placa grabada con el nombre --}}
    <div class="ga-plaque inv-fade-up inv-fade-up--2">
        <h1 class="ga-plaque__name">{{ $page->displayName }}</h1>
    </div>

    <p class="ga-when inv-fade-up inv-fade-up--3">
        <span class="ga-when__ornament" aria-hidden="true"></span>
        <span>{{ $heroDay }} · {{ $page->eventDate->format('H:i') }}</span>
        <span class="ga-when__ornament" aria-hidden="true"></span>
    </p>
    @if($page->placeName)
        <p class="ga-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
    @endif

    @if(!empty($heroMessage))
        <p class="ga-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll ga-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
