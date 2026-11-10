{{--
    Un tacón de «El cambio de zapatos» visto de perfil, mirando a la derecha: el stiletto clásico de
    punta cerrada. La capellada de satén (principal) con su brillo en la puntera, el ribete de la boca,
    el forro claro (acento) que se ve por la abertura, el moño en el empeine, el canto de la suela y el
    taco aguja con su tapita. El par va en espejo (el de la derecha con scale -1 1). Parámetros: id
    (único en la página, para los degradados), class.
--}}
<svg class="zp-heel {{ $class ?? '' }}" viewBox="0 14 162 94" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $id }}-satin" x1="0" y1="0" x2="0" y2="1">
            <stop class="zp-satin__light" offset="0"/>
            <stop class="zp-satin__mid" offset="0.5"/>
            <stop class="zp-satin__edge" offset="1"/>
        </linearGradient>
        <linearGradient id="{{ $id }}-spike" x1="0" y1="0" x2="1" y2="0">
            <stop class="zp-heel__dark" offset="0"/>
            <stop class="zp-heel__light" offset="0.5"/>
            <stop class="zp-heel__dark" offset="1"/>
        </linearGradient>
    </defs>
    {{-- El taco aguja y su tapita --}}
    <path d="M20 44 L26.5 102 L32 102 C32.5 84 34.5 63 41 52.5 C33.5 49.5 26 46.5 20 44 Z" fill="url(#{{ $id }}-spike)"/>
    <rect class="zp-heel__tip" x="25.6" y="100.5" width="7.4" height="4.2" rx="1.3"/>
    {{-- La capellada de satén: contrafuerte alto, arco pronunciado, empeine y puntera afinada --}}
    <path d="M21 18 C13.5 25 13 37 20 44 C27 47 34 50 41 52.5 C59 58.5 80 86 104 93.5 C122 98 142 99.5 153 96.5 C158.5 95 161 91 157 88.5 C147 83 129 78.5 113 74.5 C105 72.5 99 70 94 66.5 C76 58.5 44 40 21 18 Z" fill="url(#{{ $id }}-satin)"/>
    {{-- El forro claro que se ve por la boca --}}
    <path class="zp-heel__lining" d="M21 18 C44 40 76 58.5 94 66.5 C80 66.5 60 59 41.5 48 C32 42.5 24.5 33 21 18 Z"/>
    {{-- El ribete de la boca --}}
    <path class="zp-heel__piping" d="M21 18 C44 40 76 58.5 94 66.5"/>
    {{-- El canto de la suela --}}
    <path class="zp-heel__sole" d="M41.5 53 C59 59 80 86.5 104 94.2 C122 98.7 142 100.2 153.5 97.2"/>
    {{-- El brillo del satén en la puntera y en el contrafuerte --}}
    <path class="zp-heel__shine" d="M114 78 C130 81.5 144 86 153 91"/>
    <path class="zp-heel__shine zp-heel__shine--back" d="M18.5 27 C16.8 33 17.2 38.5 19.8 42"/>
    {{-- El moño de satén en el empeine --}}
    <g class="zp-heel__bow">
        <path d="M95 66 C90 59.5 83 60.5 85 65.5 C86.5 69.5 91.5 68.5 95 66 C98.5 68.5 103.5 69.5 105 65.5 C107 60.5 100 59.5 95 66 Z"/>
        <circle cx="95" cy="66.1" r="2.3"/>
    </g>
</svg>
