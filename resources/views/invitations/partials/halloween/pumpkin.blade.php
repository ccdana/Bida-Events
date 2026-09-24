{{--
    Calabaza tallada ilustrada: cinco gajos con volumen (degradados de halloween/defs), un brillo
    arriba, tallo con zarcillo y hoja, y la cara con el borde de pulpa claro. Apagada, la cara es un
    hueco oscuro; con la clase is-lit en un contenedor (o en la loma y el pie) la vela la ilumina
    por dentro y titila (themes/halloween.css).
--}}
<svg class="inv-hw-pumpkin {{ $class ?? '' }}" viewBox="0 0 120 110" aria-hidden="true" focusable="false">
    {{-- Gajos, de atrás hacia adelante --}}
    <path class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--outer" d="M60 28 C36 20 8 34 7 64 C6 92 30 108 58 103 C44 92 40 44 60 28 Z"/>
    <path class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--outer" d="M60 28 C84 20 112 34 113 64 C114 92 90 108 62 103 C76 92 80 44 60 28 Z"/>
    <path class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--inner" d="M60 26 C44 24 26 40 26 66 C26 92 44 106 60 104 C51 90 49 42 60 26 Z"/>
    <path class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--inner" d="M60 26 C76 24 94 40 94 66 C94 92 76 106 60 104 C69 90 71 42 60 26 Z"/>
    <path class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--center" d="M60 25 C49 25 42 42 42 65 C42 90 49 105 60 105 C71 105 78 90 78 65 C78 42 71 25 60 25 Z"/>

    {{-- Hundido donde nace el tallo y brillo del gajo del centro --}}
    <path class="inv-hw-pumpkin__dimple" d="M49 28 C53 23 67 23 71 28 C66 31 54 31 49 28 Z"/>
    <path class="inv-hw-pumpkin__shine" d="M50 34 C46 42 45 52 46 60 C49 52 51 43 55 36 C54 34 52 33 50 34 Z"/>

    {{-- Tallo, zarcillo y hoja --}}
    <path class="inv-hw-pumpkin__stem" d="M56 28 C55 19 57 11 62 5 C64 3 68 4 69 6 C66 11 64 19 65 28 C62 30 58 30 56 28 Z"/>
    <path class="inv-hw-pumpkin__vine" d="M65 13 C73 7 82 10 80 17 C78 22 71 20 73 15"/>
    <path class="inv-hw-pumpkin__leaf" d="M67 16 C75 9 88 10 93 17 C85 20 76 20 67 16 Z"/>
    <path class="inv-hw-pumpkin__vein" d="M69 16 C77 15 85 16 91 17"/>

    {{-- Cara tallada: el trazo claro es el borde de pulpa; el relleno, el hueco o la luz --}}
    <g class="inv-hw-pumpkin__face">
        <path d="M34 52 L43 38 L52 51 C46 48.5 40 48.5 34 52 Z"/>
        <path d="M68 51 L77 38 L86 52 C80 48.5 74 48.5 68 51 Z"/>
        <path d="M56 63 L60 56 L64 63 C61.5 62 58.5 62 56 63 Z"/>
        <path d="M30 70 Q42 74 49 73.5 L52 79 L56 74 Q60 74.4 64 74 L68 79 L71 73.5 Q78 74 90 70 Q84 86 70 89 L65 83 L60 89.6 Q40 88 30 70 Z"/>
    </g>
</svg>
