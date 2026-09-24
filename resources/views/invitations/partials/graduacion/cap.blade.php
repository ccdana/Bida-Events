{{--
    Birrete con su borla. Tablero y copa en el color secundario, cordón y borla en el del evento
    (themes/graduacion.css). El cordón y la borla van juntos para mecerse desde el botón del tablero.
--}}
<svg class="inv-grad-cap {{ $class ?? '' }}" viewBox="0 0 64 50" aria-hidden="true" focusable="false">
    <path class="inv-grad-cap__base" d="M15 20 V31 C15 38 49 38 49 31 V20 L32 27 Z"/>
    <path class="inv-grad-cap__board" d="M32 3 L62 15 L32 27 L2 15 Z"/>
    <path class="inv-grad-cap__shine" d="M32 7 L50 14"/>
    <g class="inv-grad-cap__swing">
        <path class="inv-grad-cap__cord" d="M32 15 L55 21 V38"/>
        <path class="inv-grad-cap__tassel" d="M52 37 H58 L59.5 47 H50.5 Z"/>
        <path class="inv-grad-cap__fringe" d="M52.5 40 V46.5 M54.5 40 V46.5 M56.5 40 V46.5 M58 40 V46.5"/>
    </g>
    <circle class="inv-grad-cap__button" cx="32" cy="15" r="2.4"/>
</svg>
