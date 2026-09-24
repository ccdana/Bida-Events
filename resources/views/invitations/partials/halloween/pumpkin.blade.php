{{--
    Calabaza tallada: gajos en el color del evento, tallo y la cara. La luz de adentro (inv-hw-pumpkin__light)
    se enciende con la clase is-lit en el contenedor (themes/halloween.css).
--}}
<svg class="inv-hw-pumpkin {{ $class ?? '' }}" viewBox="0 0 120 110" aria-hidden="true" focusable="false">
    <path class="inv-hw-pumpkin__stem" d="M57 22 C56 14 58 8 64 4 L69 8 C65 11 63 16 64 23 Z"/>
    <path class="inv-hw-pumpkin__leaf" d="M64 12 C72 4 84 6 88 12 C80 12 74 14 68 18 Z"/>
    <ellipse class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--side" cx="34" cy="64" rx="28" ry="38"/>
    <ellipse class="inv-hw-pumpkin__lobe inv-hw-pumpkin__lobe--side" cx="86" cy="64" rx="28" ry="38"/>
    <ellipse class="inv-hw-pumpkin__lobe" cx="60" cy="64" rx="32" ry="42"/>
    <path class="inv-hw-pumpkin__groove" d="M46 26 C38 46 38 84 46 104"/>
    <path class="inv-hw-pumpkin__groove" d="M74 26 C82 46 82 84 74 104"/>
    {{-- Cara: ojos triangulares, nariz y sonrisa con dientes --}}
    <g class="inv-hw-pumpkin__face">
        <path d="M38 52 L50 52 L44 40 Z"/>
        <path d="M70 52 L82 52 L76 40 Z"/>
        <path d="M56 64 L64 64 L60 57 Z"/>
        <path d="M32 72 Q60 98 88 72 L80 74 L76 80 L70 76 L64 83 L58 77 L52 83 L46 76 L40 80 Z"/>
    </g>
</svg>
