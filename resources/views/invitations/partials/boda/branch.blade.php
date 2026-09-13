{{-- Rama de hojas dibujada a mano: el tallo se traza y las hojas brotan en orden (estilos en themes/boda.css) --}}
<svg class="inv-boda-branch {{ $class ?? '' }}" viewBox="0 0 100 200" fill="none" aria-hidden="true" focusable="false">
    <path class="inv-boda-branch__stem" pathLength="1" d="M50 198 C44 160 58 128 50 96 C43 68 52 38 68 6"/>
    @foreach([[48, 176, -140, 1], [51, 152, -32, 1], [51, 124, -150, 0.95], [48, 98, -38, 0.95], [46, 72, -155, 0.85], [51, 48, -48, 0.8], [59, 26, -128, 0.7], [64, 16, -58, 0.6]] as $index => [$x, $y, $angle, $size])
        <g transform="translate({{ $x }} {{ $y }}) rotate({{ $angle }}) scale({{ $size }})">
            <path class="inv-boda-branch__leaf" style="--i: {{ $index }}" d="M0 0 C6 -7 18 -8 26 0 C18 8 6 7 0 0 Z M3 0 L21 0"/>
        </g>
    @endforeach
    @foreach([[40, 139], [61, 85], [38, 57], [68, 6]] as $index => [$x, $y])
        <circle class="inv-boda-branch__berry" style="--i: {{ $index * 2 + 1 }}" cx="{{ $x }}" cy="{{ $y }}" r="2.4"/>
    @endforeach
</svg>
