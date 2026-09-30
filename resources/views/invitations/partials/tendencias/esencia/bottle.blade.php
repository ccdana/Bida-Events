{{--
    El frasco de «Esencia XV»: la tapa (secundario), el collar nacarado, el cristal facetado con la
    fragancia adentro (principal) y la etiqueta con la inicial. Lo usan la apertura y la portada.
    Parámetros: id (único en la página, para los degradados), initial, class.
--}}
<svg class="ez-bottle {{ $class ?? '' }}" viewBox="0 0 100 170" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $id }}-glass" x1="0" y1="0" x2="1" y2="0">
            <stop class="ez-bottle__glass-edge" offset="0"/>
            <stop class="ez-bottle__glass-mid" offset="0.5"/>
            <stop class="ez-bottle__glass-edge" offset="1"/>
        </linearGradient>
        <linearGradient id="{{ $id }}-juice" x1="0" y1="0" x2="0" y2="1">
            <stop class="ez-bottle__juice-top" offset="0"/>
            <stop class="ez-bottle__juice-low" offset="1"/>
        </linearGradient>
    </defs>
    <rect class="ez-bottle__cap" x="34" y="2" width="32" height="30" rx="3"/>
    <path class="ez-bottle__cap-shine" d="M40 7 V27"/>
    <rect class="ez-bottle__collar" x="39" y="31" width="22" height="8" rx="1.5"/>
    <rect class="ez-bottle__neck" x="44" y="38" width="12" height="9"/>
    <rect x="9" y="46" width="82" height="121" rx="11" fill="url(#{{ $id }}-glass)" class="ez-bottle__glass"/>
    <rect x="16" y="62" width="68" height="98" rx="6" fill="url(#{{ $id }}-juice)"/>
    <path class="ez-bottle__meniscus" d="M17 64 H83"/>
    <rect class="ez-bottle__bevel" x="14" y="51" width="72" height="111" rx="8"/>
    <path class="ez-bottle__shine" d="M22 56 V154 M78 70 V120"/>
    <rect class="ez-bottle__label" x="27" y="94" width="46" height="30" rx="1.5"/>
    <text class="ez-bottle__initial" x="50" y="116" text-anchor="middle">{{ $initial }}</text>
</svg>
