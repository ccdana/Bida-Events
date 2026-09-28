{{--
    Portada de «Mapa de estrellas»: el mapa del cielo del día del bautizo, como las cartas celestes.
    Un círculo con sus marcas de grados, la grilla de las coordenadas, los puntos cardinales y las
    estrellas —distintas para cada invitación: salen de la fecha y el nombre— con un par de
    constelaciones. La foto es la luna, en el centro. Debajo, el nombre, la fecha, el lugar y sus
    coordenadas. Estilos en css/invitation/tendencias/estrellas.css.
--}}
@php
    $heroSky = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['sky_label'] ?? 'El cielo del día de mi bautizo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y'));

    // Las coordenadas del lugar, como en las cartas del cielo
    $lat = $modulos['ubicacion']['lat'] ?? null;
    $lng = $modulos['ubicacion']['lng'] ?? null;
    $coordinates = is_numeric($lat) && is_numeric($lng)
        ? sprintf('%.2f° %s · %.2f° %s', abs($lat), $lat < 0 ? 'S' : 'N', abs($lng), $lng < 0 ? 'O' : 'E')
        : null;

    // Las estrellas del mapa: siempre las mismas para esta invitación (salen de la fecha y el nombre)
    $seed = crc32($page->eventDate->format('Y-m-d').$page->displayName);
    $next = function () use (&$seed): float {
        $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;

        return $seed / 0x7fffffff;
    };
    $stars = [];
    while (count($stars) < 74) {
        $angle = $next() * M_PI * 2;
        $radius = 64 + sqrt($next()) * 84;
        $stars[] = [round(160 + cos($angle) * $radius, 1), round(160 + sin($angle) * $radius, 1), round(0.6 + $next() * $next() * 2.6, 2)];
    }
    // Dos constelaciones chicas: las estrellas más brillantes unidas de a tres o cuatro
    $bright = collect($stars)->sortByDesc(fn (array $star) => $star[2])->take(7)->values()->all();
    $constellations = [array_slice($bright, 0, 4), array_slice($bright, 4, 3)];
@endphp

<header id="inicio" class="inv-hero es-hero">
    <p class="es-kicker inv-fade-up">{{ $heroSky }}</p>

    <div class="es-chart inv-fade-up inv-fade-up--1">
        <svg class="es-chart__map" viewBox="0 0 320 320" aria-hidden="true" focusable="false">
            <circle class="es-chart__ring" cx="160" cy="160" r="156"/>
            <circle class="es-chart__ring es-chart__ring--inner" cx="160" cy="160" r="150"/>
            @for($tick = 0; $tick < 72; $tick++)
                @php($long = $tick % 6 === 0)
                <line class="es-chart__tick {{ $long ? 'is-long' : '' }}" x1="160" y1="{{ $long ? 4 : 6 }}" x2="160" y2="10" transform="rotate({{ $tick * 5 }} 160 160)"/>
            @endfor
            @foreach([48, 96, 132] as $radius)
                <circle class="es-chart__grid" cx="160" cy="160" r="{{ $radius }}"/>
            @endforeach
            @foreach([0, 60, 120] as $angle)
                <line class="es-chart__grid" x1="160" y1="10" x2="160" y2="310" transform="rotate({{ $angle }} 160 160)"/>
            @endforeach
            @foreach($constellations as $group)
                @foreach($group as $index => [$x, $y])
                    @if(isset($group[$index + 1]))
                        <line class="es-chart__line" x1="{{ $x }}" y1="{{ $y }}" x2="{{ $group[$index + 1][0] }}" y2="{{ $group[$index + 1][1] }}"/>
                    @endif
                @endforeach
            @endforeach
            @foreach($stars as $index => [$x, $y, $size])
                <circle class="es-chart__star {{ $size > 2.2 ? 'is-bright' : '' }}" style="--d: {{ 2 + ($index % 5) }}s; --delay: -{{ ($index * 0.37) % 5 }}s" cx="{{ $x }}" cy="{{ $y }}" r="{{ $size }}"/>
            @endforeach
            @foreach(['N' => [160, 26], 'E' => [292, 164], 'S' => [160, 302], 'O' => [28, 164]] as $letter => [$x, $y])
                <text class="es-chart__cardinal" x="{{ $x }}" y="{{ $y }}" text-anchor="middle">{{ $letter }}</text>
            @endforeach
        </svg>

        {{-- La luna: la foto en el centro del mapa, con su halo --}}
        <div class="es-chart__moon">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 12rem, 40vw'])
        </div>
    </div>

    <h1 class="es-name inv-fade-up inv-fade-up--2">{{ $page->displayName }}</h1>

    <p class="es-when inv-fade-up inv-fade-up--3">{{ $heroDay }} · {{ $page->eventDate->format('H:i') }}</p>
    @if($page->placeName)
        <p class="es-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
    @endif
    @if($coordinates)
        <p class="es-coords inv-fade-up inv-fade-up--3">{{ $coordinates }}</p>
    @endif

    @if(!empty($heroMessage))
        <p class="es-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll es-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
