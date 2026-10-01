{{--
    Apertura de «Atelier»: la entrega del vestido. Bajo la luz del taller, la funda de tela cuelga de
    una percha de madera torneada con gancho de bronce; de la percha cuelga la tarjeta de la casa con
    su nombre. La funda tiene su caída (pliegues de luz y sombra), su ribete cosido y un cierre de
    dientes finos con tirador de cuero que se mece. Al tocarlo, el tirador baja y la funda se afloja;
    después las dos hojas se abren como puertas (se ve su forro) y aparece el vestido: corpiño de
    satén con su bordado, el lazo de la cintura y la falda con su caída; un brillo recorre la tela y
    destellan las piedras. Todo se dibuja con la paleta del editor (la funda y el lazo con el
    principal, el vestido con el acento). Solo transform y opacity. Lógica en
    shell/cover-component; estilos en css/invitation/themes/atelier.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    // Media funda (la izquierda; la derecha es la misma en espejo): hombro, costado y ruedo redondeado
    $bagHalf = 'M100 4 C78 6 40 18 16 40 C9 47 8 56 8 66 L6 306 C6 318 14 326 26 326 L100 326 Z';
    // El ribete cosido, un poco hacia adentro del borde
    $bagPiping = 'M100 11 C80 13 45 24 23 44 C16 51 15 58 15 68 L13 303 C13 312 19 318 28 318 L100 318';
    // Las piedras del bordado y de la falda: siempre en el mismo lugar
    $gownStones = [[86, 70], [100, 74], [114, 70], [92, 86], [108, 86], [100, 100], [80, 100], [120, 100],
        [62, 196], [138, 196], [84, 228], [116, 228], [48, 262], [152, 262], [100, 252], [72, 292], [128, 292]];
@endphp

<div class="inv-themed-intro at-intro"
    x-data="invitationCover({ part: 1150, reveal: 2750, close: 3650 })"
    x-show="!closed"
    :class="{ 'is-unzipped': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la colección de {{ $page->displayName }}">
    <span class="at-intro__light" aria-hidden="true"></span>
    <p class="at-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Colección XV' }}</p>

    <div class="at-bag">
        {{-- Los degradados de la tela, el forro y el satén (los colores salen de la paleta, en la hoja) --}}
        <svg class="at-defs" aria-hidden="true" focusable="false">
            <defs>
                <linearGradient id="at-fabric" x1="0" y1="0" x2="1" y2="0">
                    <stop class="at-fabric__edge" offset="0"/>
                    <stop class="at-fabric__mid" offset="0.55"/>
                    <stop class="at-fabric__seam" offset="1"/>
                </linearGradient>
                <linearGradient id="at-fold" x1="0" y1="0" x2="1" y2="0">
                    <stop class="at-fold__clear" offset="0"/>
                    <stop class="at-fold__light" offset="0.5"/>
                    <stop class="at-fold__clear" offset="1"/>
                </linearGradient>
                <linearGradient id="at-hem" x1="0" y1="0" x2="0" y2="1">
                    <stop class="at-fold__clear" offset="0.82"/>
                    <stop class="at-hem__shade" offset="1"/>
                </linearGradient>
                <linearGradient id="at-lining" x1="0" y1="0" x2="1" y2="0">
                    <stop class="at-lining__edge" offset="0"/>
                    <stop class="at-lining__mid" offset="1"/>
                </linearGradient>
                <linearGradient id="at-satin" x1="0" y1="0" x2="1" y2="0">
                    <stop class="at-satin__shade" offset="0"/>
                    <stop class="at-satin__base" offset="0.3"/>
                    <stop class="at-satin__light" offset="0.5"/>
                    <stop class="at-satin__base" offset="0.7"/>
                    <stop class="at-satin__shade" offset="1"/>
                </linearGradient>
                <linearGradient id="at-wood" x1="0" y1="0" x2="0" y2="1">
                    <stop class="at-wood__light" offset="0"/>
                    <stop class="at-wood__dark" offset="1"/>
                </linearGradient>
                {{-- Los dientes del cierre; los de la otra hoja, medio diente más abajo (se entrelazan) --}}
                <pattern id="at-teeth" width="2.8" height="3" patternUnits="userSpaceOnUse">
                    <rect class="at-teeth__metal" width="2.8" height="1.7" rx="0.4"/>
                </pattern>
                <pattern id="at-teeth-b" y="1.5" width="2.8" height="3" patternUnits="userSpaceOnUse">
                    <rect class="at-teeth__metal" width="2.8" height="1.7" rx="0.4"/>
                </pattern>
                <linearGradient id="at-brass-line" x1="0" y1="0" x2="1" y2="1">
                    <stop class="at-brass__light" offset="0"/>
                    <stop class="at-brass__dark" offset="1"/>
                </linearGradient>
            </defs>
        </svg>

        {{-- El riel del taller --}}
        <span class="at-bag__rail" aria-hidden="true"></span>

        {{-- Adentro: el fondo de la funda y el vestido colgado --}}
        <svg class="at-gown" viewBox="0 0 200 330" aria-hidden="true" focusable="false">
            <path class="at-gown__back" d="{{ $bagHalf }} M100 4 C122 6 160 18 184 40 C191 47 192 56 192 66 L194 306 C194 318 186 326 174 326 L100 326 Z"/>
            {{-- Las cintas que lo sostienen de la percha --}}
            <path class="at-gown__loop" d="M66 38 C70 48 72 56 74 64 M134 38 C130 48 128 56 126 64"/>
            {{-- La falda, con su caída: pliegues de luz y de sombra --}}
            <g class="at-gown__skirt">
                <path class="at-gown__fill" d="M80 122 C56 150 28 216 14 314 C40 326 70 318 100 324 C130 318 160 326 186 314 C172 216 144 150 120 122 Z"/>
                <path class="at-gown__shadow" d="M84 126 C66 172 44 244 30 318 L46 321 C58 248 76 178 89 126 Z"/>
                <path class="at-gown__shadow" d="M116 126 C134 172 156 244 170 318 L154 321 C142 248 124 178 111 126 Z"/>
                <path class="at-gown__shadow at-gown__shadow--deep" d="M80 124 C60 160 38 226 26 314 L18 314 C30 222 54 156 79 123 Z M120 124 C140 160 162 226 174 314 L182 314 C170 222 146 156 121 123 Z"/>
                <path class="at-gown__shine" d="M96 126 C92 186 84 254 76 322 L92 324 C96 256 100 188 101 126 Z"/>
                <path class="at-gown__shine at-gown__shine--soft" d="M108 126 C116 186 126 254 136 322 L124 323 C116 256 108 188 104 126 Z"/>
                {{-- La capa de tul, más corta, con su ruedo ondulado --}}
                <path class="at-gown__tulle" d="M84 124 C62 156 40 216 30 298 Q44 308 58 300 Q72 310 86 302 Q100 312 114 302 Q128 310 142 300 Q156 308 170 298 C160 216 138 156 116 124 Z"/>
                <path class="at-gown__hemline" d="M14 314 C40 326 70 318 100 324 C130 318 160 326 186 314"/>
            </g>
            {{-- El corpiño de escote corazón --}}
            <path class="at-gown__fill" d="M72 64 C78 55 92 55 100 65 C108 55 122 55 128 64 C126 84 124 104 121 122 L79 122 C76 104 74 84 72 64 Z"/>
            <path class="at-gown__seam" d="M100 66 V120 M86 62 C88 82 88 102 90 121 M114 62 C112 82 112 102 110 121"/>
            {{-- El lazo de la cintura --}}
            <path class="at-gown__sash" d="M78 118 C92 121 108 121 122 118 L123 128 C108 131 92 131 77 128 Z"/>
            <path class="at-gown__sash at-gown__bow" d="M100 123 C92 116 84 116 85 123 C86 130 94 129 100 123 C106 129 114 130 115 123 C116 116 108 116 100 123 Z M98 124 L94 140 L99 137 Z M102 124 L106 140 L101 137 Z"/>
            {{-- Las piedras del bordado, que destellan al abrirse la funda --}}
            <g class="at-gown__stones">
                @foreach($gownStones as [$x, $y])
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ $y < 110 ? 0.65 : 1 }}" style="--n: {{ $loop->index }}"/>
                @endforeach
            </g>
            {{-- El brillo que recorre el satén, recortado a la forma del vestido --}}
            <clipPath id="at-gown-clip">
                <path d="M80 122 C56 150 28 216 14 314 C40 326 70 318 100 324 C130 318 160 326 186 314 C172 216 144 150 120 122 Z"/>
                <path d="M72 64 C78 55 92 55 100 65 C108 55 122 55 128 64 C126 84 124 104 121 122 L79 122 C76 104 74 84 72 64 Z"/>
            </clipPath>
            <g clip-path="url(#at-gown-clip)">
                <g transform="rotate(14 100 190)">
                    <rect class="at-gown__sheen" x="-70" y="20" width="56" height="340"/>
                </g>
            </g>
        </svg>

        {{-- Las dos hojas de la funda: por fuera la tela, por dentro el forro --}}
        @foreach(['left', 'right'] as $side)
            <span class="at-bag__panel at-bag__panel--{{ $side }}" aria-hidden="true">
                <span class="at-bag__tilt">
                    <svg class="at-bag__face" viewBox="0 0 100 330" focusable="false">
                        <path class="at-bag__fabric" d="{{ $bagHalf }}"/>
                        <path class="at-bag__drape" d="M38 30 C30 120 26 220 24 322 L44 324 C46 220 50 120 54 22 Z"/>
                        <path class="at-bag__drape at-bag__drape--soft" d="M74 12 C70 110 70 220 72 326 L84 326 C82 220 82 110 86 8 Z"/>
                        <path class="at-bag__hem" d="{{ $bagHalf }}"/>
                        <path class="at-bag__piping" d="{{ $bagPiping }}"/>
                        {{-- Media cinta del cierre, con sus dientes --}}
                        <rect class="at-bag__tape" x="94" y="98" width="6" height="218"/>
                        <rect class="at-bag__teeth" x="97.2" y="100" width="2.8" height="214"/>
                    </svg>
                    <svg class="at-bag__lining" viewBox="0 0 100 330" focusable="false">
                        <path d="{{ $bagHalf }}"/>
                    </svg>
                </span>
            </span>
        @endforeach

        {{-- La percha: el gancho de bronce y la madera torneada --}}
        <svg class="at-bag__hanger" viewBox="0 0 160 60" aria-hidden="true" focusable="false">
            <path class="at-hanger__hook" d="M80 34 V24 C80 15 92 13 92 6 C92 -1 82 -3 77 3"/>
            <path class="at-hanger__wood" d="M14 54 C40 42 64 32 80 30 C96 32 120 42 146 54 C150 56 150 60 145 59 C120 50 98 42 80 41 C62 42 40 50 15 59 C10 60 10 56 14 54 Z"/>
            <circle class="at-hanger__nut" cx="80" cy="34" r="3"/>
        </svg>

        {{-- La tarjeta de la casa, colgada de la percha --}}
        <div class="at-tag-card">
            <span class="at-tag-card__cord" aria-hidden="true"></span>
            <span class="at-tag-card__eyelet" aria-hidden="true"></span>
            <span class="at-tag-card__house">{{ $invCopy['atelier_house'] ?? 'Maison' }}</span>
            <span class="at-tag-card__name">{{ $page->displayName }}</span>
            <span class="at-tag-card__line">{{ $invCopy['atelier_collection'] ?? 'Colección XV' }} · {{ $page->eventDate->format('Y') }}</span>
        </div>

        {{-- El cierre: la parte que todavía no se abrió y el tirador --}}
        <span class="at-zip__closed" aria-hidden="true"></span>
        <button type="button" class="at-zip" data-cover-trigger @click="open()" aria-label="Bajar el cierre y abrir la funda del vestido">
            <span class="at-zip__pull" aria-hidden="true">
                <span class="at-zip__slider"></span>
                <span class="at-zip__tab"><i></i></span>
            </span>
        </button>
    </div>

    <div class="at-intro__meta">
        <p class="at-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="at-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Invitación para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="at-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el cierre para abrir la funda' }}</p>
</div>
