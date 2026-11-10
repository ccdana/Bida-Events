{{--
    Apertura de «Esencia XV»: el tocador, de frente. Contra la pared, el espejo ovalado con su marco de
    laca y un moño de satén arriba; sobre la mesa del tocador, al medio, el perfume antiguo de cristal
    tallado con su atomizador de perilla (la pera de tela con su borla), a un lado el florero y al otro
    el collar de perlas. Al tocar la perilla, se aprieta dos veces, la borla se mece y del pico sale la
    bruma: sube, se junta en una nube frente al espejo y en ella aparece su nombre. Todo con la paleta
    del editor (la fragancia, la perilla y las flores con el principal; la laca con el secundario; el
    cristal y la bruma con el acento). Solo transform y opacity. Lógica en shell/cover-component;
    estilos en css/invitation/tendencias/esencia.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F · H:i')));
    $initial = mb_strtoupper(mb_substr(trim($page->displayName), 0, 1));
    // Las perlas del collar, a lo largo de su caída
    $pearls = collect(range(0, 16))->map(fn ($i) => [round(6 + $i * 6.1, 1), round(9 + 24 * sin(M_PI * $i / 16), 1)]);
@endphp

<div class="inv-themed-intro ez-intro"
    x-data="invitationCover({ part: 1150, reveal: 3400, close: 4250 })"
    x-show="!closed"
    :class="{ 'is-sprayed': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="{{ __('Invitación al lanzamiento de :displayName', ['displayName' => $page->displayName]) }}">
    <p class="ez-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? __('Lanzamiento exclusivo') }}</p>

    <div class="ez-vanity">
        {{-- El espejo ovalado, con su marco de laca, el moño de arriba y el vidrio que se empaña --}}
        <div class="ez-mirror" aria-hidden="true">
            <span class="ez-mirror__glass">
                <i class="ez-mirror__shine"></i>
                <i class="ez-mirror__fog"></i>
            </span>
            <svg class="ez-mirror__bow" viewBox="0 0 80 40" focusable="false">
                <path d="M40 18 C33 6 16 2 12 11 C8 20 26 24 40 18 C54 24 72 20 68 11 C64 2 47 6 40 18 Z"/>
                <path d="M37 20 L28 38 L35 34 L38 40 L40 21 Z M43 20 L52 38 L45 34 L42 40 L40 21 Z"/>
                <circle cx="40" cy="18" r="4.5"/>
            </svg>
        </div>

        {{-- La mesa del tocador: la tapa, el faldón con su cajón y las patas --}}
        <div class="ez-table" aria-hidden="true">
            <span class="ez-table__top"></span>
            <span class="ez-table__apron"><i class="ez-table__drawer"></i></span>
            <span class="ez-table__leg ez-table__leg--left"></span>
            <span class="ez-table__leg ez-table__leg--right"></span>
        </div>

        {{-- El florero, a la izquierda --}}
        <svg class="ez-vase" viewBox="0 0 60 110" aria-hidden="true" focusable="false">
            <path class="ez-vase__stem" d="M30 66 C29 50 22 36 14 26 M30 66 C30 46 31 30 32 14 M30 66 C33 52 40 42 48 34"/>
            <path class="ez-vase__leaf" d="M30 52 C22 50 18 44 18 40 C24 41 29 45 30 52 Z M31 44 C37 41 42 41 45 43 C41 47 36 47 31 44 Z"/>
            <g class="ez-vase__rose">
                <circle cx="14" cy="24" r="8"/>
                <path d="M14 18 C10 19 9 25 13 27 C17 28 19 23 16 21"/>
            </g>
            <g class="ez-vase__rose">
                <circle cx="32" cy="12" r="9"/>
                <path d="M32 5 C27 6 26 13 31 15 C36 16 38 10 34 8"/>
            </g>
            <g class="ez-vase__bud">
                <ellipse cx="49" cy="32" rx="5" ry="6.5"/>
            </g>
            <path class="ez-vase__glass" d="M17 66 C9 78 10 104 30 106 C50 104 51 78 43 66 C41 62 38 61 38 57 H22 C22 61 19 62 17 66 Z"/>
            <path class="ez-vase__water" d="M13 82 C20 84 40 84 47 82 C49 94 44 103 30 104 C16 103 11 94 13 82 Z"/>
            <path class="ez-vase__shine" d="M16 74 C14 82 14 90 17 97"/>
        </svg>

        {{-- El collar de perlas, a la derecha --}}
        <svg class="ez-pearls" viewBox="0 0 110 40" aria-hidden="true" focusable="false">
            @foreach($pearls as [$x, $y])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="3.2"/>
            @endforeach
            <rect class="ez-pearls__clasp" x="1" y="6" width="6" height="5" rx="1.5"/>
        </svg>

        {{-- El perfume: cristal tallado, la fragancia, el cuello con su collar de metal, el pico y el tubito --}}
        <div class="ez-flacon" aria-hidden="true">
            <svg class="ez-flacon__svg" viewBox="0 0 120 160" focusable="false">
                <defs>
                    <linearGradient id="ez-glass" x1="0" y1="0" x2="1" y2="0">
                        <stop class="ez-glass__edge" offset="0"/>
                        <stop class="ez-glass__light" offset="0.3"/>
                        <stop class="ez-glass__mid" offset="0.7"/>
                        <stop class="ez-glass__edge" offset="1"/>
                    </linearGradient>
                    <linearGradient id="ez-scent" x1="0" y1="0" x2="0" y2="1">
                        <stop class="ez-scent__light" offset="0"/>
                        <stop class="ez-scent__deep" offset="1"/>
                    </linearGradient>
                </defs>
                <path class="ez-flacon__tube" d="M68 31 C86 31 98 40 106 54 C111 64 115 76 120 84"/>
                <rect class="ez-flacon__metal" x="34" y="27" width="20" height="5" rx="2.5"/>
                <path class="ez-flacon__metal" d="M49 37 C49 25 71 25 71 37 Z"/>
                <rect class="ez-flacon__metal" x="45" y="36" width="30" height="10" rx="2"/>
                <rect x="51" y="45" width="18" height="11" fill="url(#ez-glass)"/>
                <path d="M36 56 H84 L100 74 V136 L84 154 H36 L20 136 V74 Z" fill="url(#ez-glass)"/>
                <path d="M24 86 H96 V134 L82 149 H38 L24 134 Z" fill="url(#ez-scent)" class="ez-flacon__scent"/>
                <path class="ez-flacon__facet" d="M42 64 H78 L90 78 V132 L78 146 H42 L30 132 V78 Z M36 56 L42 64 M84 56 L78 64 M100 74 L90 78 M20 74 L30 78 M20 136 L30 132 M100 136 L90 132 M36 154 L42 146 M84 154 L78 146"/>
                <ellipse class="ez-flacon__label" cx="60" cy="108" rx="15" ry="12"/>
                <text class="ez-flacon__initial" x="60" y="109" text-anchor="middle" dominant-baseline="central">{{ $initial }}</text>
                <path class="ez-flacon__shine" d="M27 80 V128"/>
            </svg>
            {{-- El destello que cruza el cristal --}}
            <span class="ez-flacon__glint"></span>
        </div>

        {{-- La perilla del atomizador, con su red y su borla: se aprieta para rociar --}}
        <button type="button" class="ez-bulb" data-cover-trigger @click.stop="open()" aria-label="{{ __('Apretar la perilla del perfume') }}">
            <svg class="ez-bulb__svg" viewBox="0 0 44 96" aria-hidden="true" focusable="false">
                <defs>
                    <pattern id="ez-bulb-net" width="5" height="5" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                        <path class="ez-bulb__net" d="M0 0 H5 M0 0 V5"/>
                    </pattern>
                </defs>
                <rect class="ez-flacon__metal" x="18" y="0" width="8" height="9" rx="1.5"/>
                <g class="ez-bulb__ball">
                    <ellipse class="ez-bulb__fabric" cx="22" cy="31" rx="16" ry="21"/>
                    <ellipse cx="22" cy="31" rx="16" ry="21" fill="url(#ez-bulb-net)"/>
                    <path class="ez-bulb__shine" d="M13 22 C12 28 12 34 14 39"/>
                </g>
                <g class="ez-bulb__tassel">
                    <rect class="ez-flacon__metal" x="18" y="51" width="8" height="5" rx="1.5"/>
                    <path class="ez-bulb__threads" d="M19 56 L14 90 M21 56 L19 92 M23 56 L25 92 M25 56 L30 90 M22 56 L22 93"/>
                </g>
            </svg>
        </button>

        {{-- La bruma del atomizador y la nube frente al espejo --}}
        <span class="ez-mist" aria-hidden="true">
            @foreach([[0, 0], [1, 0.08], [2, 0.16], [3, 0.24], [4, 0.32], [5, 0.4]] as [$n, $d])
                <i style="--n: {{ $n }}; --d: {{ $d }}s"></i>
            @endforeach
        </span>
        <span class="ez-cloud" aria-hidden="true"></span>

        {{-- Su nombre, que aparece en la nube --}}
        <p class="ez-intro__reveal" aria-hidden="true">
            <small>{{ $invCopy['scent_line'] ?? __('Eau de Quince') }}</small>
            {{ $page->displayName }}
        </p>
    </div>

    <div class="ez-intro__meta">
        <p class="ez-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="ez-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? __('Invitación para') }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="ez-intro__hint">{{ $invCopy['intro_hint'] ?? __('Aprieta la perilla para perfumar') }}</p>
</div>
