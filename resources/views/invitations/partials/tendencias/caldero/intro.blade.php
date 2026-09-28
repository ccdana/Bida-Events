{{--
    Apertura de «Caldero encantado»: el laboratorio de noche. Arriba, el estante con frascos que
    brillan (y uno de dulces); abajo, el caldero sobre el fuego con la poción que burbujea despacio,
    el cucharón adentro y la etiqueta de la receta colgada del asa. Al tocar, el cucharón revuelve,
    la poción da vueltas y cambia de color, burbujea con ganas y sube una nube de humo de la que sale
    el nombre; la nube crece hasta tapar todo y deja ver la portada. Para toda la familia: sin sustos.
    Lógica en shell/cover-component; estilos en css/invitation/tendencias/caldero.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F'));
    // Burbujas que revientan en la superficie de la poción: x, tamaño
    $surfaceBubbles = [[98, 5], [132, 7], [170, 4.5], [204, 6], [116, 3.5], [188, 3.5]];
    // Los dulces del frasco del estante: x, y, color (1 principal, 2 secundario, 3 acento)
    $candies = [[204, 84, 1], [215, 88, 3], [226, 84, 2], [209, 76, 2], [221, 76, 1], [215, 68, 3]];
@endphp

<div class="inv-themed-intro cl-intro"
    x-data="invitationCover({ part: 2600, reveal: 3100, close: 3900 })"
    x-show="!closed"
    :class="{ 'is-stirring': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a {{ $page->displayName }}">
    {{-- El estante del laboratorio --}}
    <svg class="cl-shelf" viewBox="0 0 320 112" aria-hidden="true" focusable="false">
        <g class="cl-shelf__glass">
            <path class="cl-shelf__liquid is-primary" d="M20 74 A20 20 0 0 0 60 74 Z"/>
            <circle cx="40" cy="74" r="20"/>
            <rect x="34" y="40" width="12" height="18" rx="2"/>
            <rect class="cl-shelf__cork" x="32" y="33" width="16" height="9" rx="2.5"/>

            <rect class="cl-shelf__liquid is-secondary" x="86" y="66" width="28" height="30" rx="4"/>
            <rect x="86" y="44" width="28" height="52" rx="5"/>
            <rect x="94" y="32" width="12" height="13" rx="2"/>
            <rect class="cl-shelf__cork" x="92" y="25" width="16" height="9" rx="2.5"/>

            <path class="cl-shelf__liquid is-accent" d="M134 76 H166 L172 94 H128 Z"/>
            <path d="M142 40 H158 V56 L172 94 H128 L142 56 Z"/>
            <rect class="cl-shelf__cork" x="140" y="33" width="20" height="8" rx="2.5"/>

            @foreach($candies as [$x, $y, $tone])
                <circle class="cl-shelf__candy is-tone-{{ $tone }}" cx="{{ $x }}" cy="{{ $y }}" r="5.5"/>
            @endforeach
            <rect x="196" y="58" width="38" height="38" rx="7"/>
            <rect class="cl-shelf__cork" x="193" y="51" width="44" height="9" rx="3"/>

            <path class="cl-shelf__liquid is-secondary" d="M262 80 A13 13 0 0 0 288 80 Z"/>
            <circle cx="275" cy="80" r="13"/>
            <rect x="271" y="54" width="8" height="15" rx="2"/>
            <rect class="cl-shelf__cork" x="269" y="48" width="12" height="7" rx="2"/>
        </g>
        <rect class="cl-shelf__plank" x="4" y="96" width="312" height="9" rx="2"/>
        <path class="cl-shelf__plank" d="M30 105 h10 l-10 10 Z M280 105 h10 v10 Z"/>
    </svg>

    <p class="cl-intro__eyebrow">
        @if($guest)
            {{ $guest->name }}: {{ \Illuminate\Support\Str::lcfirst($invCopy['intro_eyebrow'] ?? 'Se está preparando una fiesta') }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Se está preparando una fiesta' }}
        @endif
    </p>

    <div class="cl-stage">
        {{-- La nube de humo con el nombre: sube cuando se revuelve --}}
        <div class="cl-smoke" aria-hidden="true">
            @for($puff = 0; $puff < 6; $puff++)
                <i style="--i: {{ $puff }}"></i>
            @endfor
        </div>
        <p class="cl-intro__name">{{ $page->displayName }}</p>

        <span class="cl-rise" aria-hidden="true">
            @for($bubble = 0; $bubble < 8; $bubble++)
                <i style="--i: {{ $bubble }}"></i>
            @endfor
        </span>

        <button type="button" class="cl-cauldron" data-cover-trigger aria-label="Revolver la poción y abrir la invitación">
            <svg viewBox="0 0 300 250" aria-hidden="true" focusable="false">
                <g class="cl-fire">
                    <rect class="cl-fire__log" x="78" y="224" width="144" height="13" rx="6.5" transform="rotate(-7 150 230)"/>
                    <rect class="cl-fire__log" x="78" y="224" width="144" height="13" rx="6.5" transform="rotate(7 150 230)"/>
                    <path class="cl-flame cl-flame--1" d="M102 232 C92 212 106 200 104 182 C120 196 128 214 122 232 Z"/>
                    <path class="cl-flame cl-flame--2" d="M132 232 C120 206 138 190 136 166 C156 186 166 210 158 232 Z"/>
                    <path class="cl-flame cl-flame--3" d="M166 232 C158 210 172 196 172 176 C188 194 194 214 186 232 Z"/>
                    <path class="cl-flame cl-flame--4" d="M190 232 C184 218 194 208 194 194 C206 206 208 220 204 232 Z"/>
                </g>
                <g class="cl-pot">
                    <path class="cl-pot__leg" d="M80 212 L66 238 H82 L94 218 Z"/>
                    <path class="cl-pot__leg" d="M220 212 L234 238 H218 L206 218 Z"/>
                    <circle class="cl-pot__handle" cx="30" cy="136" r="12"/>
                    <circle class="cl-pot__handle" cx="270" cy="136" r="12"/>
                    <path class="cl-pot__body" d="M34 112 C22 196 84 230 150 230 C216 230 278 196 266 112 Z"/>
                    <path class="cl-pot__shine" d="M60 138 C58 172 78 198 104 210"/>
                    <ellipse class="cl-pot__rim" cx="150" cy="112" rx="122" ry="22"/>
                    <ellipse class="cl-potion" cx="150" cy="114" rx="106" ry="15"/>
                    <g transform="translate(150 114) scale(1 0.14)">
                        <path class="cl-swirl" d="M0 0 C20 -30 60 -10 55 25 C50 60 -10 70 -40 40 C-70 10 -60 -50 -10 -70 C40 -90 95 -40 90 20"/>
                    </g>
                    @foreach($surfaceBubbles as $index => [$x, $size])
                        <circle class="cl-pop" style="--i: {{ $index }}" cx="{{ $x }}" cy="{{ 114 - $size * 0.4 }}" r="{{ $size }}"/>
                    @endforeach
                    <g class="cl-spoon">
                        <g transform="translate(150 114) rotate(26)">
                            <rect class="cl-spoon__handle" x="-4.5" y="-122" width="9" height="126" rx="4.5"/>
                            <ellipse class="cl-spoon__ripple" cx="0" cy="2" rx="13" ry="4" transform="rotate(-26)"/>
                        </g>
                    </g>
                    <path class="cl-pot__lip" d="M28 112 A122 22 0 0 0 272 112"/>
                </g>
            </svg>
        </button>

        {{-- La etiqueta de la receta, colgada del asa --}}
        <span class="cl-tag">
            <span class="cl-tag__label">{{ $invCopy['potion_label'] ?? 'Receta secreta' }}</span>
            <span class="cl-tag__date">{{ $introDate }}</span>
        </span>
    </div>

    <p class="cl-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el caldero para revolver la poción' }}</p>
</div>
