{{--
    Apertura de «Atelier»: la funda del vestido colgada de su percha en el riel del taller. Sobre la
    funda, la etiqueta tejida con su nombre. Mientras se espera, el tirador del cierre se mece y cada
    tanto da un tirón. Al tocarlo baja el cierre y la funda se abre en una V; después las dos hojas se
    abren como puertas, el croquis del vestido se dibuja en tinta sobre el papel de molde y la
    etiqueta de la colección se cose con su pespunte. Solo se animan transform, opacity y el trazo del
    croquis. Lógica en shell/cover-component; estilos en css/invitation/themes/atelier.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    $firstName = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first();
@endphp

<div class="inv-themed-intro at-intro"
    x-data="invitationCover({ part: 1150, reveal: 2650, close: 3600 })"
    x-show="!closed"
    :class="{ 'is-unzipped': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la colección de {{ $page->displayName }}">
    <p class="at-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Colección XV' }}</p>

    <div class="at-bag">
        {{-- El riel del taller, del que cuelga la percha --}}
        <span class="at-bag__rail" aria-hidden="true"></span>
        {{-- La percha: el gancho y los hombros de madera --}}
        <svg class="at-bag__hanger" viewBox="0 0 120 56" aria-hidden="true" focusable="false">
            <path class="at-hanger__hook" d="M60 30 V20 C60 12 70 10 70 4 C70 -2 60 -3 56 3"/>
            <path class="at-hanger__bar" d="M8 52 L60 28 L112 52"/>
        </svg>

        {{-- Lo que hay adentro: el croquis sobre el papel de molde y la etiqueta de la colección --}}
        <div class="at-bag__inside" aria-hidden="true">
            <svg class="at-sketch" viewBox="0 0 200 300" focusable="false">
                {{-- El maniquí --}}
                <g class="at-sketch__form">
                    <path pathLength="1" d="M94 34 V22 H106 V34"/>
                    <path pathLength="1" d="M74 60 C76 42 90 34 100 34 C110 34 124 42 126 60"/>
                    <path pathLength="1" d="M100 272 V292 M72 294 H128"/>
                </g>
                {{-- El vestido: corpiño de escote corazón, el lazo de la cintura y la falda con sus pliegues --}}
                <g class="at-sketch__gown">
                    <path pathLength="1" d="M80 60 C86 51 94 53 100 61 C106 53 114 51 120 60"/>
                    <path pathLength="1" d="M80 60 C77 82 81 100 86 113"/>
                    <path pathLength="1" d="M120 60 C123 82 119 100 114 113"/>
                    <path pathLength="1" d="M86 113 Q100 118 114 113"/>
                    <path pathLength="1" d="M86 113 C70 152 46 220 28 262"/>
                    <path pathLength="1" d="M114 113 C130 152 154 220 172 262"/>
                    <path pathLength="1" d="M28 262 C58 274 78 266 100 274 C122 266 142 274 172 262"/>
                    <path class="at-sketch__tulle" pathLength="1" d="M90 116 C78 150 62 196 50 236 C70 246 86 240 100 247 C114 240 130 246 150 236 C138 196 122 150 110 116"/>
                    <path class="at-sketch__fold" pathLength="1" d="M88 122 C76 172 60 218 46 264"/>
                    <path class="at-sketch__fold" pathLength="1" d="M112 122 C124 172 140 218 154 264"/>
                    <path class="at-sketch__fold" pathLength="1" d="M90 66 C92 84 94 98 96 112 M110 66 C108 84 106 98 104 112"/>
                    <path class="at-sketch__fold" pathLength="1" d="M95 120 C89 170 81 220 72 266"/>
                    <path class="at-sketch__fold" pathLength="1" d="M105 120 C111 170 119 220 128 266"/>
                    <path class="at-sketch__fold" pathLength="1" d="M100 119 V272"/>
                    <path pathLength="1" d="M100 115 C92 108 86 112 90 118 C94 121 98 118 100 115 C102 118 106 121 110 118 C114 112 108 108 100 115"/>
                </g>
                {{-- Las marcas de tiza: cintura y ruedo --}}
                <g class="at-sketch__chalk">
                    <path pathLength="1" d="M64 113 H136"/>
                    <path pathLength="1" d="M20 282 C60 294 140 294 180 282"/>
                </g>
            </svg>
            <span class="at-sketch__sign">{{ $firstName }}</span>
        </div>

        {{-- Las dos hojas de la funda --}}
        <span class="at-bag__half at-bag__half--left" aria-hidden="true"></span>
        <span class="at-bag__half at-bag__half--right" aria-hidden="true"></span>
        <span class="at-bag__gap" aria-hidden="true"></span>

        {{-- La etiqueta tejida cosida en la funda --}}
        <div class="at-bag__label">
            <span class="at-bag__house">{{ $invCopy['atelier_house'] ?? 'Maison' }}</span>
            <span class="at-bag__name">{{ $page->displayName }}</span>
        </div>

        <button type="button" class="at-zip" data-cover-trigger @click="open()" aria-label="Abrir la funda del vestido">
            <span class="at-zip__teeth" aria-hidden="true"></span>
            <span class="at-zip__pull" aria-hidden="true"><span class="at-zip__tab"><i></i></span></span>
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
