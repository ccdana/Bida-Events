{{--
    El tejado de la casa de muñecas, de frente y simétrico: tejas de escama, el alero con su
    moldura, la ventana redonda de la buhardilla (su vidrio se enciende y adentro se asoma el
    fantasma) y el gato sentado en la cumbrera. id: prefijo único para el patrón y el recorte.
    Colores en tendencias/casona.css.
--}}
@php($roofId = $id ?? 'cs-roof')
<svg class="cs-roof" viewBox="0 0 400 176" aria-hidden="true" focusable="false">
    <defs>
        <pattern id="{{ $roofId }}-tiles" width="22" height="13" patternUnits="userSpaceOnUse">
            <path class="cs-roof__tile" d="M0 13 A11 11 0 0 1 22 13"/>
            <path class="cs-roof__tile" d="M-11 6.5 A11 11 0 0 1 11 6.5 M11 6.5 A11 11 0 0 1 33 6.5"/>
        </pattern>
        <clipPath id="{{ $roofId }}-window">
            <circle cx="200" cy="112" r="22"/>
        </clipPath>
    </defs>
    {{-- El gato en la cumbrera, con la cola colgando --}}
    <g class="cs-roof__cat">
        <path class="cs-roof__cat-tail" d="M211 26 C222 30 224 40 216 46"/>
        <path class="cs-roof__cat-body" d="M190 30 C186 24 187 14 191 9 C189 5 189 0 190 -6 L194 -1 C196 -2 204 -2 206 -1 L210 -6 C211 0 211 5 209 9 C213 14 214 24 210 30 Z"/>
        <ellipse class="cs-roof__cat-eye" cx="196.5" cy="3.5" rx="1.6" ry="2"/>
        <ellipse class="cs-roof__cat-eye" cx="203.5" cy="3.5" rx="1.6" ry="2"/>
    </g>
    <path class="cs-roof__body" d="M10 170 L200 26 L390 170 Z"/>
    <path class="cs-roof__tiles" d="M10 170 L200 26 L390 170 Z" fill="url(#{{ $roofId }}-tiles)"/>
    <path class="cs-roof__eave" d="M2 172 L200 22 L398 172"/>
    {{-- La buhardilla: el marco, el vidrio que se enciende, el fantasma que se asoma y la cruz --}}
    <circle class="cs-roof__frame" cx="200" cy="112" r="28"/>
    <circle class="cs-roof__glass" cx="200" cy="112" r="22"/>
    <circle class="cs-roof__glow" cx="200" cy="112" r="22"/>
    <g clip-path="url(#{{ $roofId }}-window)">
        <g class="cs-roof__ghost">
            <path class="cs-ghost__sheet" d="M200 102 C191 102 186 109 186 118 V140 H214 V118 C214 109 209 102 200 102 Z"/>
            <ellipse class="cs-ghost__eye" cx="196" cy="116" rx="1.8" ry="2.5"/>
            <ellipse class="cs-ghost__eye" cx="204" cy="116" rx="1.8" ry="2.5"/>
        </g>
    </g>
    <path class="cs-roof__muntin" d="M200 90 V134 M178 112 H222"/>
</svg>
