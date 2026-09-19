{{--
    Apertura de la tarjeta de amor: un capullo dormido en la tierra. Cada toque deja caer una gota:
    1 brota el tallo, 2 se abren las hojas, 3 florece. Entonces la flor crece, suelta sus pétalos y
    deja ver la portada. «Abrir sin esperar» la abre de una vez.
    Lógica en resources/js/cards/amor/cover.js (bloomCover, sobre invitationCover de shell/cover-component);
    estilos en resources/css/cards/amor/cover.css.
--}}
@php
    $introTo = $cardTo !== '' ? $cardTo : null;
    $introFrom = $cardFrom !== '' ? $cardFrom : null;
    $petalAngles = range(0, 324, 36);
@endphp

<div class="inv-amor-intro"
    x-data="bloomCover({ part: 650, reveal: 1400, close: 2300 })"
    x-show="!closed"
    :class="{
        'is-drop-1': drops >= 1,
        'is-drop-2': drops >= 2,
        'is-bloomed': drops >= 3,
        'is-leaving': stage >= 1,
        'is-gone': stage >= 2,
    }"
    @click="water()"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $introTo ? 'Carta para '.$introTo : 'Una carta para ti' }}">
    <span class="inv-bloom__sun" aria-hidden="true"></span>

    <div class="inv-bloom">
        <p class="inv-bloom__eyebrow">{{ $introFrom ? 'Tienes una carta de '.$introFrom : 'Tienes una carta' }}</p>

        <button type="button" class="inv-bloom__stage" data-cover-trigger
            :aria-label="drops < 3 ? `Regar la flor (faltan ${3 - drops} toques)` : 'La flor se abrió'">
            <span class="inv-bloom__drops" x-ref="sky" aria-hidden="true"></span>

            <svg class="inv-bloom__art" viewBox="0 0 240 300" aria-hidden="true">
                <defs>
                    <clipPath id="amor-bloom-soil">
                        <rect x="0" y="0" width="240" height="258" />
                    </clipPath>
                    <radialGradient id="amor-bloom-petal" cx="50%" cy="85%" r="85%">
                        <stop offset="0" style="stop-color: var(--amor-petal-deep)" />
                        <stop offset="1" style="stop-color: var(--amor-petal-light)" />
                    </radialGradient>
                </defs>

                <g clip-path="url(#amor-bloom-soil)">
                    <g class="inv-bloom__plant">
                        <path class="inv-bloom__stem" d="M120 300 C 114 250, 128 190, 120 116" />
                        <path class="inv-bloom__leaf inv-bloom__leaf--left" d="M119 214 C 98 206, 84 214, 76 198 C 94 186, 110 194, 119 214 Z" />
                        <path class="inv-bloom__leaf inv-bloom__leaf--right" d="M122 184 C 142 174, 156 182, 166 166 C 146 156, 132 164, 122 184 Z" />

                        <g class="inv-bloom__flower">
                            @foreach($petalAngles as $angle)
                                <ellipse class="inv-bloom__petal" cx="120" cy="84" rx="11" ry="25" style="--a: {{ $angle }}deg; --i: {{ $loop->index }}" />
                            @endforeach
                            <path class="inv-bloom__sepal" d="M108 118 L120 100 L132 118 Q120 126 108 118 Z" />
                            <circle class="inv-bloom__center" cx="120" cy="108" r="12" />
                            <circle class="inv-bloom__center-dot" cx="116" cy="105" r="2.2" />
                            <circle class="inv-bloom__center-dot" cx="124" cy="106" r="1.8" />
                            <circle class="inv-bloom__center-dot" cx="119" cy="112" r="2" />
                        </g>
                    </g>
                </g>

                <path class="inv-bloom__soil" d="M18 262 Q120 236 222 262 Q226 276 214 284 L26 284 Q14 276 18 262 Z" />
                <path class="inv-bloom__tuft" d="M44 262 q4 -12 8 0 M60 258 q3 -10 7 0 M176 256 q4 -12 8 0 M192 260 q3 -9 6 0" />
            </svg>
        </button>

        <div class="inv-bloom__caption" aria-live="polite">
            <p class="inv-bloom__hint" x-show="drops < 3">
                <span class="inv-bloom__dots" aria-hidden="true">
                    @for($drop = 1; $drop <= 3; $drop++)
                        <i :class="{ 'is-full': drops >= {{ $drop }} }"></i>
                    @endfor
                </span>
                <span x-text="drops === 0 ? @js($invCopy['intro_hint'] ?? 'Riégalo con tres toques') : (drops === 1 ? 'Otra gotita…' : '¡Una más!')">{{ $invCopy['intro_hint'] ?? 'Riégalo con tres toques' }}</span>
            </p>
            <p class="inv-bloom__to" x-show="drops >= 3" x-cloak>{{ $introTo ? 'Para '.$introTo : 'Para ti' }}</p>
        </div>

        <button type="button" class="inv-bloom__skip" x-show="drops < 3" @click.stop="bloomNow()">Abrir sin esperar</button>
    </div>
</div>
