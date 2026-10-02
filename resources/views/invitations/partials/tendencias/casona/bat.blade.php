{{--
    Murciélago de «Casa de muñecas de medianoche», de frente con las alas abiertas; las alas se
    pliegan y se abren con CSS (cs-bat__wing). class: clases extra.
--}}
<svg class="cs-bat {{ $class ?? '' }}" viewBox="0 0 64 30" aria-hidden="true" focusable="false">
    <path class="cs-bat__wing cs-bat__wing--left" d="M30 12 C26 6 20 3 13 4 C9 6 4 6 1 4 C4 10 6 16 4 23 C10 19 15 19 19 23 C21 17 25 15 30 18 Z"/>
    <path class="cs-bat__wing cs-bat__wing--right" d="M34 12 C38 6 44 3 51 4 C55 6 60 6 63 4 C60 10 58 16 60 23 C54 19 49 19 45 23 C43 17 39 15 34 18 Z"/>
    <path class="cs-bat__body" d="M32 8 L29.5 4 L29 10 C27 13 27.5 19 32 22 C36.5 19 37 13 35 10 L34.5 4 Z"/>
</svg>
