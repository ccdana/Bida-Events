{{--
    Apertura de «Mapa de estrellas»: el cielo de noche lleno de estrellas que titilan y, en el medio,
    diez estrellas más grandes, todavía sueltas: su constelación, con forma de corazón. Al tocar la
    más brillante, una línea de luz va uniendo las estrellas una por una hasta cerrar el corazón, que
    se enciende; cruza una estrella fugaz y el cielo se acerca hasta la portada.
    Lógica en shell/cover-component; estilos en css/invitation/tendencias/estrellas.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y'));
    // La constelación: diez estrellas en forma de corazón, en el orden en que se unen
    $constellation = [[150, 74], [118, 40], [76, 46], [52, 90], [78, 146], [150, 214], [222, 146], [248, 90], [224, 46], [182, 40]];
    // El cielo de fondo: estrellas chicas en lugares fijos
    $seed = crc32('estrellas-intro');
    $next = function () use (&$seed): float {
        $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;

        return $seed / 0x7fffffff;
    };
    $sky = [];
    for ($star = 0; $star < 46; $star++) {
        $sky[] = [round($next() * 100, 1), round($next() * 100, 1), round(1 + $next() * 2.2, 1), round($next() * 4, 1)];
    }
@endphp

<div class="inv-themed-intro es-intro"
    x-data="invitationCover({ part: 2000, reveal: 2500, close: 3300 })"
    x-show="!closed"
    :class="{ 'is-joining': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    <div class="es-intro__sky" aria-hidden="true">
        @foreach($sky as [$x, $y, $size, $delay])
            <span style="--x: {{ $x }}%; --y: {{ $y }}%; --s: {{ $size }}px; --delay: -{{ $delay }}s"></span>
        @endforeach
    </div>
    <span class="es-intro__shooting" aria-hidden="true"></span>

    <p class="es-intro__eyebrow">
        @if($guest)
            {{ $invCopy['intro_eyebrow'] ?? 'Mira el cielo' }}, {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Mira el cielo' }}
        @endif
    </p>

    <button type="button" class="es-constellation" data-cover-trigger aria-label="Unir las estrellas y abrir la invitación">
        <svg viewBox="0 0 300 250" aria-hidden="true" focusable="false">
            @foreach($constellation as $index => [$x, $y])
                @php([$toX, $toY] = $constellation[($index + 1) % count($constellation)])
                <line class="es-constellation__line" style="--i: {{ $index }}" x1="{{ $x }}" y1="{{ $y }}" x2="{{ $toX }}" y2="{{ $toY }}" pathLength="1"/>
            @endforeach
            @foreach($constellation as $index => [$x, $y])
                <g class="es-constellation__star {{ $index === 0 ? 'is-first' : '' }}" style="--i: {{ $index }}" transform="translate({{ $x }} {{ $y }})">
                    <circle class="es-constellation__halo" r="10"/>
                    <path d="M0 -8 L1.8 -1.8 L8 0 L1.8 1.8 L0 8 L-1.8 1.8 L-8 0 L-1.8 -1.8 Z"/>
                </g>
            @endforeach
        </svg>
    </button>

    <div class="es-intro__title">
        <p class="es-intro__label">{{ $invCopy['constellation_label'] ?? 'La constelación de' }}</p>
        <p class="es-intro__name">{{ $page->displayName }}</p>
        <p class="es-intro__date">{{ $introDate }}</p>
    </div>

    <p class="es-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la estrella más brillante para unir la constelación' }}</p>
</div>
