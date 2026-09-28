{{--
    Florcita bordada de «Bordado a mano»: cinco pétalos en punto margarita, el centro de nuditos y,
    si se pide, dos hojas en punto satén. Va dentro de un <svg>; los pétalos y las hojas miden 1 de
    largo (pathLength) para poder «bordarlos» con stroke-dashoffset.
    Parámetros: x, y, size (escala), rotate (grados), leaves (bool), class y step (orden en que se borda).
--}}
<g transform="translate({{ $x }} {{ $y }}) rotate({{ $rotate ?? 0 }}) scale({{ $size ?? 1 }})">
    <g class="bd-flower {{ $class ?? '' }}" style="--i: {{ $step ?? 0 }}">
        @if($leaves ?? false)
            <path class="bd-flower__leaf" d="M9 3 C13 -1 20 -1 25 4 C20 8 13 8 9 3 Z" pathLength="1"/>
            <path class="bd-flower__leaf" d="M-9 3 C-13 -1 -20 -1 -25 4 C-20 8 -13 8 -9 3 Z" pathLength="1"/>
        @endif
        @foreach([0, 72, 144, 216, 288] as $angle)
            <path class="bd-flower__petal" d="M0 -2.5 C-3.6 -6 -3.4 -11.5 0 -13 C3.4 -11.5 3.6 -6 0 -2.5 Z" transform="rotate({{ $angle }})" pathLength="1"/>
        @endforeach
        <g class="bd-flower__knots">
            <circle r="1.8" cx="-1.1" cy="-0.7"/>
            <circle r="1.6" cx="1.3" cy="-0.5"/>
            <circle r="1.6" cx="0" cy="1.4"/>
        </g>
    </g>
</g>
