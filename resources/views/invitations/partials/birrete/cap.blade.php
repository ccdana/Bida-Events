{{--
    Birrete de «Birrete al aire» visto de tres cuartos: el tablero con su canto, la copa, el botón y la
    borla colgando de un costado. El tablero y la copa toman el color secundario; el cordón, el botón y
    la borla, el principal (themes/birrete.css). side: right (por defecto), left o both (las dos borlas:
    la apertura muestra una y la pasa al otro lado). class: clases extra.
--}}
@php
    $capSide = in_array($side ?? 'right', ['left', 'both'], true) ? $side : 'right';
    $capTassels = $capSide === 'both' ? ['right', 'left'] : [$capSide];
    // Por lado: punto del canto donde cae el cordón y la borla que cuelga de ahí
    $capPoints = ['right' => [204, 'C150 82 184 86 204 88'], 'left' => [36, 'C90 82 56 86 36 88']];
@endphp
<svg class="br-cap br-cap--{{ $capSide }} {{ $class ?? '' }}" viewBox="0 0 240 190" aria-hidden="true" focusable="false">
    {{-- Copa: la parte que va en la cabeza --}}
    <path class="br-cap__skull" d="M66 96 V132 C66 150 174 150 174 132 V96 C150 106 90 106 66 96 Z"/>
    <path class="br-cap__skull-shade" d="M66 124 C66 132 70 138 80 142 C100 148 140 148 160 142 C170 138 174 132 174 124 C150 134 90 134 66 124 Z"/>
    {{-- Tablero: el cuadrado de arriba, con su canto --}}
    <path class="br-cap__edge" d="M20 78 L120 116 L220 78 V85 L120 123 L20 85 Z"/>
    <path class="br-cap__board" d="M120 40 L220 78 L120 116 L20 78 Z"/>
    <path class="br-cap__sheen" d="M120 48 L196 77"/>
    {{-- Cordón sobre el tablero y la borla que cuelga del canto --}}
    @foreach($capTassels as $tassel)
        @php([$x, $curve] = $capPoints[$tassel])
        <g class="br-cap__tassel br-cap__tassel--{{ $tassel }}">
            <path class="br-cap__cord br-cap__cord--board" pathLength="1" d="M120 78 {{ $curve }}"/>
            <g class="br-cap__hang" style="transform-origin: {{ $x }}px 88px">
                <path class="br-cap__cord" d="M{{ $x }} 88 V142"/>
                <path class="br-cap__knob" d="M{{ $x - 5 }} 140 H{{ $x + 5 }} L{{ $x + 7 }} 148 H{{ $x - 7 }} Z"/>
                <path class="br-cap__fringe" d="M{{ $x - 7 }} 148 H{{ $x + 7 }} L{{ $x + 10 }} 176 H{{ $x - 10 }} Z"/>
                <path class="br-cap__threads" d="M{{ $x - 6 }} 152 V174 M{{ $x - 2 }} 152 V175 M{{ $x + 2 }} 152 V175 M{{ $x + 6 }} 152 V174"/>
            </g>
        </g>
    @endforeach
    <circle class="br-cap__button" cx="120" cy="78" r="6"/>
</svg>
