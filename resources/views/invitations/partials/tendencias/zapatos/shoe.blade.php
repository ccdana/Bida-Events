{{--
    Un tacón de «El cambio de zapatos» visto desde arriba, como se ve dentro de la caja: la punta
    almendrada arriba, la capellada de satén (principal) con su brillo y su moño, la boca con la
    plantilla clara (acento), su pespunte y el nombre grabado a lo largo, y abajo, asomando por detrás
    del talón, el tacón aguja con su tapita. Parámetros: id (único en la página, para los degradados),
    name (opcional: lo que va grabado en la plantilla), class.
--}}
<svg class="zp-shoe {{ $class ?? '' }}" viewBox="0 0 60 198" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $id }}-satin" x1="0" y1="0" x2="1" y2="0">
            <stop class="zp-satin__edge" offset="0"/>
            <stop class="zp-satin__light" offset="0.38"/>
            <stop class="zp-satin__mid" offset="0.64"/>
            <stop class="zp-satin__edge" offset="1"/>
        </linearGradient>
        <linearGradient id="{{ $id }}-insole" x1="0" y1="0" x2="0" y2="1">
            <stop class="zp-insole__light" offset="0"/>
            <stop class="zp-insole__dark" offset="1"/>
        </linearGradient>
        <linearGradient id="{{ $id }}-heel" x1="0" y1="0" x2="1" y2="0">
            <stop class="zp-heel__dark" offset="0"/>
            <stop class="zp-heel__light" offset="0.45"/>
            <stop class="zp-heel__dark" offset="1"/>
        </linearGradient>
    </defs>
    {{-- El tacón aguja, que asoma por detrás del talón, con su tapita --}}
    <path d="M26.6 158 L33.4 158 L31.5 192 L28.5 192 Z" fill="url(#{{ $id }}-heel)"/>
    <rect class="zp-shoe__tip" x="27.9" y="190.5" width="4.2" height="5" rx="1.2"/>
    {{-- El canto de la suela, apenas más grande que la capellada --}}
    <path class="zp-shoe__sole" d="M30 2.5 C40 5.5 52 25 53 48 C54 71 46 89 43 104 C41 116 45 134 45 147 C45 160 38 166 30 166 C22 166 15 160 15 147 C15 134 19 116 17 104 C14 89 6 71 7 48 C8 25 20 5.5 30 2.5 Z"/>
    {{-- La capellada de satén --}}
    <path d="M30 4 C39 7 50 26 51 48 C52 70 44 88 41 104 C39 116 43 134 43 146 C43 158 37 164 30 164 C23 164 17 158 17 146 C17 134 21 116 19 104 C16 88 8 70 9 48 C10 26 21 7 30 4 Z" fill="url(#{{ $id }}-satin)"/>
    <path class="zp-shoe__shine" d="M26.5 11 C21 22 17 35 17.5 50 C21 39 25 26 30 13 Z"/>
    {{-- La boca: la plantilla, su pespunte y la sombra del talón --}}
    <path class="zp-shoe__mouth" d="M30 58 C39 58 43 73 41 92 C40 106 41 130 41 142 C41 155 36 160 30 160 C24 160 19 155 19 142 C19 130 20 106 19 92 C17 73 21 58 30 58 Z"/>
    <path d="M30 62 C37 62 40 75 38.5 92 C37.5 106 38.5 130 38.5 141 C38.5 151 35 156 30 156 C25 156 21.5 151 21.5 141 C21.5 130 22.5 106 21.5 92 C20 75 23 62 30 62 Z" fill="url(#{{ $id }}-insole)"/>
    <path class="zp-shoe__stitch" d="M30 66 C35.5 66 37 77 36 92 C35 106 36 130 36 140 C36 148 33.5 152 30 152 C26.5 152 24 148 24 140 C24 130 25 106 24 92 C23 77 24.5 66 30 66 Z"/>
    <path class="zp-shoe__cup" d="M21.5 134 C21.5 151 25 156 30 156 C35 156 38.5 151 38.5 134 C35 145 25 145 21.5 134 Z"/>
    @if(!empty($name))
        <text class="zp-shoe__name" x="30" y="108" transform="rotate(-90 30 108)" text-anchor="middle" dominant-baseline="central">{{ $name }}</text>
    @endif
    {{-- El moño de satén en el borde de la capellada --}}
    <g class="zp-shoe__bow">
        <path d="M30 59 C25 52 17 51.5 18 57.5 C19 63 26 62 30 59 C34 62 41 63 42 57.5 C43 51.5 35 52 30 59 Z"/>
        <path class="zp-shoe__bow-fold" d="M29 58 C25.5 55.5 21.5 55 19.5 57 M31 58 C34.5 55.5 38.5 55 40.5 57"/>
        <circle cx="30" cy="58.6" r="2.4"/>
    </g>
</svg>
