{{-- Corona de línea: se traza y sus joyas aparecen una tras otra (estilos en themes/xv.css) --}}
<svg class="inv-xv-crown {{ $class ?? '' }}" viewBox="0 0 120 84" fill="none" aria-hidden="true" focusable="false">
    <path class="inv-xv-crown__line" pathLength="1" d="M16 64 L10 26 L38 46 L60 14 L82 46 L110 26 L104 64 Z"/>
    <path class="inv-xv-crown__line" pathLength="1" d="M18 74 L102 74"/>
    @foreach([[10, 21, 4.5], [60, 8, 4.5], [110, 21, 4.5], [40, 56, 3], [60, 56, 3], [80, 56, 3]] as $index => [$x, $y, $r])
        <circle class="inv-xv-crown__gem" style="--i: {{ $index }}" cx="{{ $x }}" cy="{{ $y }}" r="{{ $r }}"/>
    @endforeach
</svg>
