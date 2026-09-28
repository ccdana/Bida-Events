{{--
    Botón de costura de «Bordado a mano»: el borde, el hundido del centro, los cuatro agujeros y el
    hilo en cruz que lo sujeta. Parámetros opcionales: class, style.
--}}
<svg class="bd-button {{ $class ?? '' }}" @isset($style) style="{{ $style }}" @endisset viewBox="0 0 40 40" aria-hidden="true" focusable="false">
    <circle class="bd-button__face" cx="20" cy="20" r="19"/>
    <circle class="bd-button__rim" cx="20" cy="20" r="14.5"/>
    @foreach([[15, 15], [25, 15], [15, 25], [25, 25]] as [$x, $y])
        <circle class="bd-button__hole" cx="{{ $x }}" cy="{{ $y }}" r="2.2"/>
    @endforeach
    <path class="bd-button__thread" d="M15 15 L25 25 M25 15 L15 25"/>
</svg>
