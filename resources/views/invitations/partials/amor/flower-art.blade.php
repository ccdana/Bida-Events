{{--
    Dibujo de una flor para responder la tarjeta de amor. Parámetro: $flower (rosa, girasol,
    tulipan, margarita u otra: se dibuja una flor genérica con los colores de la tarjeta).
    Reusa #amor-flower de partials/amor/garden para las flores de pétalos redondos.
--}}
<svg class="inv-flower inv-flower--{{ $flower }}" viewBox="0 0 60 90" aria-hidden="true">
    <path class="inv-flower__stem" d="M30 88 C 28 70, 32 56, 30 40" />
    <path class="inv-flower__leaf" d="M30 70 C 20 66, 14 70, 10 62 C 18 56, 26 60, 30 70 Z" />
    <path class="inv-flower__leaf" d="M30 60 C 40 55, 46 58, 50 50 C 42 45, 34 49, 30 60 Z" />

    @switch($flower)
        @case('rosa')
            <g class="inv-flower__head">
                <circle cx="30" cy="26" r="15" class="inv-flower__rose-base" />
                <path class="inv-flower__rose-fold" d="M30 26 m-3 0 a3 3 0 1 1 6 0 a6 6 0 1 1 -12 0 a9 9 0 1 1 18 0 a12 12 0 0 1 -21 7" />
            </g>
            @break
        @case('tulipan')
            <g class="inv-flower__head">
                <path class="inv-flower__tulip" d="M16 16 C 16 32, 22 40, 30 40 C 38 40, 44 32, 44 16 L 37 24 L 30 12 L 23 24 Z" />
                <path class="inv-flower__tulip-shade" d="M30 12 L 23 24 C 24 34, 27 39, 30 40 C 33 39, 36 34, 37 24 Z" />
            </g>
            @break
        @default
            {{-- Girasol, margarita y cualquier otra: pétalos redondos en doble rueda --}}
            <g class="inv-flower__head">
                <svg x="6" y="2" width="48" height="48" viewBox="0 0 40 40"><use href="#amor-flower" /></svg>
                <g transform="rotate(30 30 26)"><svg x="6" y="2" width="48" height="48" viewBox="0 0 40 40"><use href="#amor-flower" /></svg></g>
            </g>
    @endswitch
</svg>
