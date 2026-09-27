{{--
    Apertura de «Galería Quince»: la entrada de la galería la noche de la inauguración. Por el vidrio
    de la puerta se ve la sala a oscuras con tres cuadros colgados; en el vidrio está rotulada la
    inauguración, una alfombra llega hasta la puerta y, delante, un cordón de terciopelo entre dos
    postes de latón. Al tocar el cordón se suelta de un poste y cae, se encienden los focos de la sala
    uno por uno, las puertas se abren, saltan los flashes de la prensa y se entra. Lógica en
    shell/cover-component; estilos en css/invitation/tendencias/galeria.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
@endphp

<div class="inv-themed-intro gq-intro"
    x-data="invitationCover({ part: 950, reveal: 1800, close: 2700 })"
    x-show="!closed"
    :class="{ 'is-unhooked': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la exposición de {{ $page->displayName }}">
    <span class="gq-flash gq-flash--left" aria-hidden="true"></span>
    <span class="gq-flash gq-flash--right" aria-hidden="true"></span>

    <div class="gq-door">
        <span class="gq-door__light" aria-hidden="true"></span>

        {{-- La sala que se ve por el vidrio: tres cuadros colgados, cada uno con su foco --}}
        <span class="gq-door__room" aria-hidden="true">
            @foreach([1, 2, 3] as $art)
                <span class="gq-door__art gq-door__art--{{ $art }}" style="--a: {{ $loop->index }}"><i></i></span>
            @endforeach
        </span>
        <span class="gq-door__panel gq-door__panel--left" aria-hidden="true"></span>
        <span class="gq-door__panel gq-door__panel--right" aria-hidden="true"></span>
        <span class="gq-carpet" aria-hidden="true"></span>

        <div class="gq-sign">
            <p class="gq-sign__kicker">{{ $invCopy['intro_eyebrow'] ?? 'Inauguración' }}</p>
            <p class="gq-sign__name">{{ $page->displayName }}</p>
            <p class="gq-sign__title">{{ $invCopy['exhibit_title'] ?? 'Quince' }}</p>
            <p class="gq-sign__date">{{ $introDate }}</p>
            @if($guest)
                <p class="gq-sign__guest">{{ $invCopy['exhibit_guest'] ?? 'Invitación de honor para' }} {{ $guest->name }}</p>
            @endif
        </div>

        <button type="button" class="gq-barrier" data-cover-trigger @click="open()" aria-label="Soltar el cordón y entrar a la galería">
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
                {{-- El cordón cuelga del poste izquierdo; su otra punta se suelta del derecho --}}
                <g class="gq-rope">
                    <path class="gq-rope__cord" d="M42 40 C 110 104, 210 104, 278 40"/>
                    <path class="gq-rope__sheen" d="M42 40 C 110 104, 210 104, 278 40"/>
                    <circle class="gq-rope__clip" cx="42" cy="40" r="5"/>
                    <circle class="gq-rope__clip" cx="278" cy="40" r="5"/>
                </g>
                {{-- El mismo cordón ya suelto: cuelga del poste izquierdo y queda tendido en el piso --}}
                <g class="gq-rope-down">
                    <path class="gq-rope__cord" d="M42 40 C 40 96, 52 138, 96 140 S 170 139, 196 136"/>
                    <path class="gq-rope__sheen" d="M42 40 C 40 96, 52 138, 96 140 S 170 139, 196 136"/>
                    <circle class="gq-rope__clip" cx="42" cy="40" r="5"/>
                    <circle class="gq-rope__clip" cx="196" cy="136" r="5"/>
                </g>
            </svg>
        </button>
    </div>

    <p class="gq-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el cordón para entrar' }}</p>
</div>
