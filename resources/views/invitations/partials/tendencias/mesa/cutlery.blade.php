{{--
    Los cubiertos de «Mesa de honor», vistos desde arriba: el tenedor, el cuchillo o la cuchara, en el
    oro de la vajilla. Parámetros: piece (fork, knife, spoon), class.
--}}
<svg class="ms-cutlery {{ $class ?? '' }}" viewBox="0 0 24 140" aria-hidden="true" focusable="false">
    @switch($piece)
        @case('fork')
            <path d="M5 4 V30 C5 38 9 42 10 44 V132 C10 136.5 14 136.5 14 132 V44 C15 42 19 38 19 30 V4 H17 V28 H15 V4 H13 V28 H11 V4 H9 V28 H7 V4 Z"/>
            @break
        @case('knife')
            <path d="M14.5 4 C7.5 12 6.5 30 8.5 56 L8.5 132 C8.5 137 14.5 137 14.5 132 Z"/>
            <path class="ms-cutlery__line" d="M8.8 58 H14.2"/>
            @break
        @default
            <ellipse cx="12" cy="21" rx="7.5" ry="12"/>
            <path d="M10.4 32 H13.6 V132 C13.6 136.5 10.4 136.5 10.4 132 Z"/>
    @endswitch
</svg>
