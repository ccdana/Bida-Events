{{--
    Apertura de «Partitura a dos voces»: el atril antes del concierto, con la partitura cerrada (la
    obra y los nombres en la tapa) y la batuta del director apoyada encima. Al tocarla, la batuta se
    levanta y marca la entrada, «1, 2, 3, 4»; la tapa se abre y, al paso de una luz que recorre el
    sistema, las dos voces se escriben a la par —una sube, la otra baja— hasta terminar en la misma
    nota, sus ligaduras se dibujan y al final aparece un corazón con su calderón. Después la
    partitura se acerca y queda la portada. Lógica en shell/cover-component; estilos en
    tendencias/partitura.css.
--}}
@php
    $voices = array_slice($page->names(), 0, 2);
    // Las dos melodías sobre el sistema (x, y): la primera sube, la segunda baja; terminan en la misma nota
    $upper = [[74, 79], [106, 74], [138, 69], [170, 64], [202, 59], [234, 64], [266, 64]];
    $lower = [[74, 139], [106, 144], [138, 149], [170, 154], [202, 159], [234, 154], [266, 154]];
@endphp

<div class="inv-themed-intro pt-intro"
    x-data="invitationCover({ part: 2650, reveal: 3150, close: 3950 })"
    x-show="!closed"
    :class="{ 'is-counting': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación de {{ $page->displayName }}">
    <p class="pt-intro__eyebrow">
        @if($guest)
            Para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Tienes una invitación' }}
        @endif
    </p>

    <div class="pt-stand">
        {{-- El atril: la tabla, su repisa, la vara y las tres patas --}}
        <svg class="pt-stand__frame" viewBox="0 0 320 330" aria-hidden="true" focusable="false">
            <rect class="pt-stand__desk" x="6" y="6" width="308" height="226" rx="6"/>
            <rect class="pt-stand__ledge" x="0" y="226" width="320" height="12" rx="4"/>
            <rect class="pt-stand__pole" x="155" y="238" width="10" height="70" rx="3"/>
            <path class="pt-stand__legs" d="M160 304 L96 328 M160 304 L224 328 M160 304 L160 326"/>
        </svg>

        <button type="button" class="pt-score" data-cover-trigger @click="open()" aria-label="Marcar la entrada y abrir la invitación">
            {{-- La primera página: el sistema de dos voces, vacío hasta que suena --}}
            <span class="pt-score__page" aria-hidden="true">
                <svg viewBox="0 0 320 210" focusable="false">
                    <rect class="pt-score__playhead" x="0" y="36" width="26" height="148"/>
                    @foreach([44, 134] as $staffIndex => $top)
                        <g class="pt-score__lines">
                            @foreach([0, 10, 20, 30, 40] as $offset)
                                <line x1="26" y1="{{ $top + $offset }}" x2="314" y2="{{ $top + $offset }}"/>
                            @endforeach
                        </g>
                        <text class="pt-staff__meter" x="40" y="{{ $top + 17 }}" text-anchor="middle">4</text>
                        <text class="pt-staff__meter" x="40" y="{{ $top + 37 }}" text-anchor="middle">4</text>
                    @endforeach
                    <text class="pt-score__voice" x="28" y="32">{{ $voices[0] ?? '' }}</text>
                    <text class="pt-score__voice" x="28" y="124">{{ $voices[1] ?? '' }}</text>
                    <path class="pt-score__brace" d="M20 44 C 10 52, 16 96, 8 109 C 16 122, 10 166, 20 174"/>
                    <line class="pt-score__system" x1="26" y1="44" x2="26" y2="174"/>
                    <g class="pt-score__final">
                        <line x1="308" y1="44" x2="308" y2="174"/>
                        <rect x="311" y="44" width="4" height="130"/>
                    </g>

                    {{-- Las ligaduras de cada voz --}}
                    <path class="pt-score__slur pt-score__slur--1" d="M74 70 C 130 40, 220 38, 266 54" pathLength="1"/>
                    <path class="pt-score__slur pt-score__slur--2" d="M74 150 C 130 180, 220 182, 266 164" pathLength="1"/>

                    @foreach([$upper, $lower] as $voice => $notes)
                        @foreach($notes as $index => [$x, $y])
                            <g class="pt-score__note pt-score__note--{{ $voice + 1 }}" style="--n: {{ $index }}">
                                <ellipse cx="{{ $x }}" cy="{{ $y }}" rx="5.2" ry="3.8" transform="rotate(-22 {{ $x }} {{ $y }})"/>
                                @if($voice === 0)
                                    <line x1="{{ $x + 4.6 }}" y1="{{ $y - 1 }}" x2="{{ $x + 4.6 }}" y2="{{ $y - 26 }}"/>
                                @else
                                    <line x1="{{ $x - 4.6 }}" y1="{{ $y + 1 }}" x2="{{ $x - 4.6 }}" y2="{{ $y + 26 }}"/>
                                @endif
                            </g>
                        @endforeach
                    @endforeach

                    {{-- Al final, las dos voces se encuentran: un corazón con su calderón --}}
                    <path class="pt-score__heart" d="M287 124 C 268 111, 270 94, 281 95 C 285 95.5, 287 99, 287 101 C 287 99, 289 95.5, 293 95 C 304 94, 306 111, 287 124 Z" pathLength="1"/>
                    <g class="pt-score__fermata">
                        <path d="M275 90 C 278 78, 296 78, 299 90"/>
                        <circle cx="287" cy="87" r="2"/>
                    </g>
                </svg>
            </span>

            {{-- La tapa de la partitura --}}
            <span class="pt-score__cover">
                <span class="pt-score__opus">{{ $invCopy['score_opus'] ?? 'Op. 1' }}</span>
                <span class="pt-score__work">{{ $invCopy['score_title'] ?? 'Concierto para dos voces' }}</span>
                <span class="pt-score__names">{{ $page->displayName }}</span>
            </span>
        </button>

        {{-- La batuta del director, apoyada sobre la partitura --}}
        <span class="pt-baton" aria-hidden="true">
            <svg viewBox="0 0 12 150" focusable="false">
                <path class="pt-baton__shaft" d="M5.2 4 L6.8 4 L7.4 112 L4.6 112 Z"/>
                <path class="pt-baton__grip" d="M3 108 C 2 118, 1.5 134, 6 146 C 10.5 134, 10 118, 9 108 Z"/>
            </svg>
        </span>

        {{-- La cuenta del director: se ve un número a la vez --}}
        <p class="pt-count" aria-hidden="true">
            <span>1</span><span>2</span><span>3</span><span>4</span>
        </p>
    </div>

    <p class="pt-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la batuta para empezar' }}</p>
</div>
