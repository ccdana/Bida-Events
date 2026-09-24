{{--
    Degradados compartidos de la plantilla de Halloween: el volumen de la calabaza (gajos con luz
    arriba a la izquierda), el tallo y la luz de la vela que se ve por la cara tallada. Se definen
    una sola vez por página y los usan todas las calabazas (intro, portada y pie).
    Los colores salen de la paleta del evento (clases hw-stop-* en themes/halloween.css).
--}}
<svg class="inv-hw-defs" width="0" height="0" aria-hidden="true" focusable="false">
    <defs>
        <radialGradient id="hw-pk-center" cx="0.38" cy="0.3" r="0.8">
            <stop offset="0" class="hw-stop-hi"/>
            <stop offset="0.45" class="hw-stop-base"/>
            <stop offset="1" class="hw-stop-deep"/>
        </radialGradient>
        <radialGradient id="hw-pk-side" cx="0.5" cy="0.32" r="0.85">
            <stop offset="0" class="hw-stop-base"/>
            <stop offset="0.6" class="hw-stop-mid"/>
            <stop offset="1" class="hw-stop-darkest"/>
        </radialGradient>
        <linearGradient id="hw-pk-stem" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#3f4a24"/>
            <stop offset="0.5" stop-color="#6f7d3a"/>
            <stop offset="1" stop-color="#2f3719"/>
        </linearGradient>
        <radialGradient id="hw-pk-glow" cx="0.5" cy="0.55" r="0.6">
            <stop offset="0" stop-color="#fff6cf"/>
            <stop offset="0.45" stop-color="#ffd166"/>
            <stop offset="1" class="hw-stop-base"/>
        </radialGradient>
        <linearGradient id="hw-pk-shine" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#fff" stop-opacity="0.55"/>
            <stop offset="1" stop-color="#fff" stop-opacity="0"/>
        </linearGradient>
    </defs>
</svg>
