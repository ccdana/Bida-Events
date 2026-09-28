{{--
    Apertura de «Galería Quince»: la entrada de la galería la noche de la inauguración, armada en
    espejo sobre el eje del medio. Sobre la puerta, el rótulo de la muestra; por el vidrio de las dos
    hojas se ve la sala a oscuras con la obra principal al centro y una más chica a cada lado. Delante,
    dos postes de latón con el cordón de terciopelo cerrado al medio por su mosquetón, y la alfombra
    que llega hasta la puerta. Mientras se espera, un reflejo cruza el vidrio, el cordón se mece y la
    prensa prueba sus flashes a los dos lados, a destiempo.
    Al tocar el cordón se abre el mosquetón y cada mitad cae colgando de su poste; los focos de la sala
    se encienden titilando (primero el de la obra principal, después los dos de los costados), se
    abren las puertas, los postes quedan atrás y se camina hacia la obra principal hasta que la luz de
    la sala llena la pantalla y aparece la portada. Solo se animan transform y opacity: nada de
    filtros ni fondos que obliguen a repintar. Lógica en shell/cover-component (origin: el centro de
    la obra principal, hacia donde se camina); estilos en css/invitation/tendencias/galeria.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
@endphp

<div class="inv-themed-intro gq-intro"
    x-data="invitationCover({ part: 1000, reveal: 2300, close: 3300, origin: '.gq-door__art--main' })"
    x-show="!closed"
    :class="{ 'is-unhooked': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la exposición de {{ $page->displayName }}">
    {{-- La prensa, a los dos lados de la alfombra --}}
    <span class="gq-press gq-press--left" aria-hidden="true"></span>
    <span class="gq-press gq-press--right" aria-hidden="true"></span>

    <div class="gq-sign">
        <p class="gq-sign__kicker">{{ $invCopy['intro_eyebrow'] ?? 'Inauguración' }}</p>
        <p class="gq-sign__name">{{ $page->displayName }}</p>
        <p class="gq-sign__title">{{ $invCopy['exhibit_title'] ?? 'Quince' }}</p>
        <p class="gq-sign__date">{{ $introDate }}</p>
        @if($guest)
            <p class="gq-sign__guest">{{ $invCopy['exhibit_guest'] ?? 'Invitación de honor para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <div class="gq-entrance">
        <div class="gq-door">
            {{-- La sala que se ve por el vidrio: la obra principal y una a cada lado, cada una con su foco --}}
            <span class="gq-door__room" aria-hidden="true">
                <span class="gq-door__art gq-door__art--side gq-door__art--left"><i></i><b></b></span>
                <span class="gq-door__art gq-door__art--main"><i></i><b></b></span>
                <span class="gq-door__art gq-door__art--side gq-door__art--right"><i></i><b></b></span>
            </span>
            <span class="gq-door__panel gq-door__panel--left" aria-hidden="true"></span>
            <span class="gq-door__panel gq-door__panel--right" aria-hidden="true"></span>
        </div>
        <span class="gq-carpet" aria-hidden="true"></span>

        <button type="button" class="gq-barrier" data-cover-trigger @click="open()" aria-label="Abrir el cordón y entrar a la galería">
            <svg class="gq-barrier__svg" viewBox="0 0 320 150" focusable="false" aria-hidden="true">
                {{-- Postes de latón con su base y su remate --}}
                <g class="gq-post">
                    <ellipse cx="34" cy="143" rx="24" ry="6"/>
                    <rect x="30" y="34" width="8" height="107" rx="3"/>
                    <circle cx="34" cy="30" r="9"/>
                </g>
                <g class="gq-post">
                    <ellipse cx="286" cy="143" rx="24" ry="6"/>
                    <rect x="282" y="34" width="8" height="107" rx="3"/>
                    <circle cx="286" cy="30" r="9"/>
                </g>
                {{-- El cordón: dos mitades que se cierran al medio con el mosquetón --}}
                <g class="gq-ropes">
                    <g class="gq-rope gq-rope--left">
                        <path class="gq-rope__cord" d="M42 40 C 82 88, 124 98, 160 98"/>
                        <path class="gq-rope__sheen" d="M42 40 C 82 88, 124 98, 160 98"/>
                    </g>
                    <g class="gq-rope gq-rope--right">
                        <path class="gq-rope__cord" d="M278 40 C 238 88, 196 98, 160 98"/>
                        <path class="gq-rope__sheen" d="M278 40 C 238 88, 196 98, 160 98"/>
                    </g>
                    <circle class="gq-rope__clip" cx="42" cy="40" r="5"/>
                    <circle class="gq-rope__clip" cx="278" cy="40" r="5"/>
                    <g class="gq-clasp">
                        <circle cx="160" cy="98" r="7"/>
                        <circle class="gq-clasp__hole" cx="160" cy="98" r="3.2"/>
                    </g>
                </g>
                {{-- Ya abierto: cada mitad cuelga de su poste y la punta queda en el piso --}}
                <g class="gq-rope-down gq-rope-down--left">
                    <path class="gq-rope__cord" d="M42 40 C 40 96, 46 134, 76 139 S 118 140, 128 136"/>
                    <path class="gq-rope__sheen" d="M42 40 C 40 96, 46 134, 76 139 S 118 140, 128 136"/>
                    <circle class="gq-rope__clip" cx="128" cy="136" r="4.5"/>
                </g>
                <g class="gq-rope-down gq-rope-down--right">
                    <path class="gq-rope__cord" d="M278 40 C 280 96, 274 134, 244 139 S 202 140, 192 136"/>
                    <path class="gq-rope__sheen" d="M278 40 C 280 96, 274 134, 244 139 S 202 140, 192 136"/>
                    <circle class="gq-rope__clip" cx="192" cy="136" r="4.5"/>
                </g>
            </svg>
        </button>
    </div>

    {{-- El aviso, en una cartela de museo --}}
    <p class="gq-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el cordón para entrar' }}</p>

    {{-- La luz de la sala que llena la pantalla al final del recorrido --}}
    <span class="gq-intro__flood" aria-hidden="true"></span>
</div>
