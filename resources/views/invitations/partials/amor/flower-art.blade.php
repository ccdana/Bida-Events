{{--
    Dibujo de una flor para responder la tarjeta de amor. Parámetro: $flower (rosa, girasol,
    tulipan, margarita u otra: se usa la flor genérica #amor-flower de partials/amor/garden).
    Cada una tiene su forma: rosa en copa con espiral, girasol de dos coronas con semillas,
    tulipán de tres pétalos y margarita de pétalos finos. Colores en resources/css/cards/amor/reply.css.
--}}
<svg class="inv-flower inv-flower--{{ $flower }}" viewBox="0 0 60 90" aria-hidden="true">
    <path class="inv-flower__stem" d="M30 88 C 27 72, 33 58, 30 40" />
    <g class="inv-flower__leaf">
        <path d="M29 70 C 22 70, 14 68, 9 60 C 17 55, 25 60, 29 70 Z" />
        <path class="inv-flower__leaf-vein" d="M28 69 C 22 65, 16 62, 11 60" />
    </g>
    <g class="inv-flower__leaf">
        <path d="M31 60 C 38 60, 46 57, 51 49 C 43 44, 35 50, 31 60 Z" />
        <path class="inv-flower__leaf-vein" d="M32 59 C 38 55, 44 52, 49 49" />
    </g>

    <g class="inv-flower__head">
        @switch($flower)
            @case('rosa')
                <path class="inv-flower__calyx" d="M24 39 L 30 45 L 36 39 Q 30 42 24 39 Z" />
                <path class="inv-flower__rose-back" d="M13 25 C 11 12, 21 5, 30 8 C 39 5, 49 12, 47 25 C 45 36, 38 41, 30 41 C 22 41, 15 36, 13 25 Z" />
                <path class="inv-flower__rose-mid" d="M19 20 C 20 11, 28 9, 30 12 C 32 9, 40 11, 41 20 C 41 26, 36 30, 30 30 C 24 30, 19 26, 19 20 Z" />
                <path class="inv-flower__rose-spiral" d="M25 19 C 26 14, 34 14, 35 19 C 35 23, 29 24, 28 20 C 28 18, 31 17, 32 19" />
                <path class="inv-flower__rose-front" d="M13 24 C 15 35, 22 41, 30 41 C 25 36, 21 30, 21 22 C 18 22, 15 23, 13 24 Z" />
                <path class="inv-flower__rose-front" d="M47 24 C 45 35, 38 41, 30 41 C 35 36, 39 30, 39 22 C 42 22, 45 23, 47 24 Z" />
                <path class="inv-flower__rose-lip" d="M19 31 C 24 38, 36 38, 41 31 C 36 34, 24 34, 19 31 Z" />
                @break

            @case('tulipan')
                <path class="inv-flower__calyx" d="M26 42 Q 30 46 34 42 L 30 47 Z" />
                <path class="inv-flower__tulip-back" d="M17 12 C 15 27, 19 40, 30 43 C 41 40, 45 27, 43 12 C 39 20, 35 23, 30 17 C 25 23, 21 20, 17 12 Z" />
                <path class="inv-flower__tulip-front" d="M30 15 C 22 23, 21 36, 30 43 C 39 36, 38 23, 30 15 Z" />
                <path class="inv-flower__tulip-shine" d="M26 24 C 25 30, 26 35, 28 38" />
                @break

            @case('girasol')
                @foreach(range(0, 336, 24) as $angle)
                    <path class="inv-flower__sun-petal" d="M30 25 C 27.6 20, 27.4 13, 30 8 C 32.6 13, 32.4 20, 30 25 Z" transform="rotate({{ $angle }} 30 25)" />
                @endforeach
                @foreach(range(12, 348, 24) as $angle)
                    <path class="inv-flower__sun-petal inv-flower__sun-petal--inner" d="M30 25 C 28.2 21, 28 16, 30 12 C 32 16, 31.8 21, 30 25 Z" transform="rotate({{ $angle }} 30 25)" />
                @endforeach
                <circle class="inv-flower__sun-disc" cx="30" cy="25" r="8.2" />
                @foreach([[0, 0], [2.6, 0], [-2.6, 0], [1.3, 2.3], [-1.3, 2.3], [1.3, -2.3], [-1.3, -2.3], [3.9, 2.3], [-3.9, 2.3], [3.9, -2.3], [-3.9, -2.3], [0, 4.6], [0, -4.6], [2.6, 4.6], [-2.6, 4.6], [2.6, -4.6], [-2.6, -4.6], [5.2, 0], [-5.2, 0]] as [$dx, $dy])
                    <circle class="inv-flower__seed" cx="{{ 30 + $dx }}" cy="{{ 25 + $dy }}" r="0.8" />
                @endforeach
                @break

            @case('margarita')
                @foreach(range(0, 337.5, 22.5) as $angle)
                    <path class="inv-flower__daisy-petal" d="M30 25 C 28.3 20, 28.2 12, 30 7.5 C 31.8 12, 31.7 20, 30 25 Z" transform="rotate({{ $angle }} 30 25)" />
                @endforeach
                <circle class="inv-flower__daisy-disc" cx="30" cy="25" r="5.4" />
                @foreach(range(0, 300, 60) as $angle)
                    <circle class="inv-flower__seed" cx="{{ round(30 + 2.8 * cos(deg2rad($angle)), 2) }}" cy="{{ round(25 + 2.8 * sin(deg2rad($angle)), 2) }}" r="0.6" />
                @endforeach
                @break

            @default
                <svg x="6" y="1" width="48" height="48" viewBox="0 0 40 40"><use href="#amor-flower" /></svg>
        @endswitch
    </g>
</svg>
