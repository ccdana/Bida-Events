{{--
    Paisaje en silueta al pie de la noche: una loma lejana, dos árboles secos, una reja de hierro y la
    loma de adelante. Se estira a lo ancho sin deformarse (se recorta a los costados en el celular).
    Colores en themes/halloween.css (inv-hw-land__*).
--}}
<svg class="inv-hw-land {{ $class ?? '' }}" viewBox="0 0 400 120" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
    <path class="inv-hw-land__far" d="M0 120 L0 84 C50 70 96 74 140 82 C186 90 222 96 262 88 C310 78 356 70 400 78 L400 120 Z"/>

    {{-- Árbol seco grande a la izquierda --}}
    <path class="inv-hw-land__tree" d="M52 118 C54 100 52 88 47 77 C41 71 31 69 20 72 C29 64 39 64 45 69 C43 58 37 50 27 45 C37 45 44 52 48 61 C50 50 48 40 42 31 C50 36 55 47 55 59 C59 50 66 43 76 40 C69 47 62 56 60 67 C65 62 72 60 80 61 C70 64 63 71 60 81 C58 94 60 106 64 118 Z"/>
    <path class="inv-hw-land__tree" d="M27 45 C22 42 18 38 16 32 M42 31 C40 25 41 19 44 14 M76 40 C82 36 86 31 88 25 M80 61 C86 60 91 57 95 52" fill="none" stroke-width="2" stroke-linecap="round"/>

    {{-- Árbol seco chico a la derecha --}}
    <path class="inv-hw-land__tree" d="M352 118 C353 106 352 98 349 90 C345 86 339 85 333 87 C338 82 344 82 348 85 C347 78 343 73 337 70 C344 70 348 75 351 81 C352 74 351 67 347 61 C353 65 356 72 356 80 C359 74 364 70 370 69 C365 73 360 79 359 86 C358 96 359 107 362 118 Z"/>

    {{-- Reja de hierro con puntas --}}
    <g class="inv-hw-land__fence">
        <rect x="214" y="92" width="120" height="2.2" rx="1"/>
        <rect x="214" y="104" width="120" height="2.2" rx="1"/>
        @for($post = 0; $post < 11; $post++)
            @php($postX = 216 + $post * 11.4)
            <path d="M{{ $postX }} 116 L{{ $postX }} 90 L{{ $postX + 1.3 }} 85 L{{ $postX + 2.6 }} 90 L{{ $postX + 2.6 }} 116 Z"/>
        @endfor
    </g>

    <path class="inv-hw-land__near" d="M0 120 L0 100 C44 90 96 92 150 99 C204 106 246 110 300 102 C340 96 372 94 400 98 L400 120 Z"/>
</svg>
