{{--
    Apertura de la tarjeta de amor: un capullo dormido en la tierra. Cada toque deja caer una gota:
    1 brota el tallo, 2 se abren las hojas, 3 florece. Entonces la flor crece, suelta sus pétalos y
    deja ver la portada. «Abrir sin esperar» la abre de una vez.
    La flor es de pétalos con muesca en la punta, en dos coronas, con nervadura, estambres y cáliz.
    Lógica en resources/js/cards/amor/cover.js (bloomCover, sobre invitationCover de shell/cover-component);
    estilos en resources/css/cards/amor/cover.css.
--}}
@php
    $introTo = $cardTo !== '' ? $cardTo : null;
    $introFrom = $cardFrom !== '' ? $cardFrom : null;
    // Corona de afuera (8 pétalos grandes) y de adentro (8 más chicos, entre medio)
    $petals = [];
    foreach (range(0, 315, 45) as $angle) {
        $petals[] = ['angle' => $angle, 'scale' => 1, 'ring' => 'outer'];
    }
    foreach (range(22.5, 337.5, 45) as $angle) {
        $petals[] = ['angle' => $angle, 'scale' => 0.74, 'ring' => 'inner'];
    }
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
                    <linearGradient id="amor-bloom-petal" x1="0" y1="1" x2="0" y2="0">
                        <stop offset="0" style="stop-color: var(--amor-petal-deep)" />
                        <stop offset="0.55" style="stop-color: var(--amor-petal-mid)" />
                        <stop offset="1" style="stop-color: var(--amor-petal-light)" />
                    </linearGradient>
                    <linearGradient id="amor-bloom-petal-inner" x1="0" y1="1" x2="0" y2="0">
                        <stop offset="0" style="stop-color: var(--amor-petal-deep)" />
                        <stop offset="1" style="stop-color: var(--amor-petal-mid)" />
                    </linearGradient>
                    <linearGradient id="amor-bloom-stem" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0" style="stop-color: var(--amor-leaf-dark)" />
                        <stop offset="0.45" style="stop-color: var(--amor-leaf)" />
                        <stop offset="1" style="stop-color: var(--amor-leaf-dark)" />
                    </linearGradient>
                    <linearGradient id="amor-bloom-leaf" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" style="stop-color: var(--amor-leaf-light)" />
                        <stop offset="1" style="stop-color: var(--amor-leaf-dark)" />
                    </linearGradient>
                    <radialGradient id="amor-bloom-heart" cx="45%" cy="40%" r="60%">
                        <stop offset="0" style="stop-color: #fff1b8" />
                        <stop offset="0.5" style="stop-color: var(--amor-sun)" />
                        <stop offset="1" style="stop-color: #d98a2b" />
                    </radialGradient>
                    <radialGradient id="amor-bloom-earth" cx="50%" cy="20%" r="80%">
                        <stop offset="0" style="stop-color: #a4755a" />
                        <stop offset="1" style="stop-color: #6f4a37" />
                    </radialGradient>
                    <path id="amor-bloom-petal-shape" d="M120 108 C 105 98, 99 79, 105 64 C 108 56, 113 57, 117 53 C 119 56, 121 56, 123 53 C 127 57, 132 56, 135 64 C 141 79, 135 98, 120 108 Z" />
                </defs>

                <g clip-path="url(#amor-bloom-soil)">
                    <g class="inv-bloom__plant">
                        <path class="inv-bloom__stem" d="M120 300 C 113 252, 129 192, 120 112" />

                        <g class="inv-bloom__leaf inv-bloom__leaf--left">
                            <path class="inv-bloom__leaf-blade" d="M119 216 C 104 214, 86 212, 72 196 C 90 184, 108 190, 119 216 Z" />
                            <path class="inv-bloom__leaf-vein" d="M118 213 C 104 204, 92 200, 76 197 M100 206 L 94 199 M108 209 L 103 201" />
                        </g>
                        <g class="inv-bloom__leaf inv-bloom__leaf--right">
                            <path class="inv-bloom__leaf-blade" d="M122 186 C 136 184, 154 180, 170 162 C 150 152, 132 160, 122 186 Z" />
                            <path class="inv-bloom__leaf-vein" d="M123 183 C 138 173, 150 168, 166 164 M140 176 L 145 168 M132 180 L 136 171" />
                        </g>

                        <g class="inv-bloom__flower">
                            @foreach($petals as $petal)
                                <g class="inv-bloom__petal inv-bloom__petal--{{ $petal['ring'] }}"
                                    style="--a: {{ $petal['angle'] }}deg; --s: {{ $petal['scale'] }}; --i: {{ $loop->index }}">
                                    <use href="#amor-bloom-petal-shape" />
                                    <path class="inv-bloom__petal-vein" d="M120 104 C 119 91, 119 77, 120 62" />
                                </g>
                            @endforeach

                            {{-- Cáliz: dos hojitas que abrazan el capullo y se abren al florecer --}}
                            <path class="inv-bloom__sepal inv-bloom__sepal--left" d="M120 114 C 108 111, 101 99, 104 84 C 110 93, 116 102, 120 114 Z" />
                            <path class="inv-bloom__sepal inv-bloom__sepal--right" d="M120 114 C 132 111, 139 99, 136 84 C 130 93, 124 102, 120 114 Z" />

                            <g class="inv-bloom__heart">
                                <circle cx="120" cy="108" r="11.5" fill="url(#amor-bloom-heart)" />
                                @foreach(range(0, 330, 30) as $angle)
                                    <circle class="inv-bloom__stamen" cx="{{ round(120 + 8.2 * cos(deg2rad($angle)), 2) }}" cy="{{ round(108 + 8.2 * sin(deg2rad($angle)), 2) }}" r="1.35" />
                                @endforeach
                                <circle class="inv-bloom__stamen-core" cx="118" cy="106" r="3" />
                            </g>
                        </g>
                    </g>
                </g>

                <path class="inv-bloom__soil" d="M16 263 C 50 246, 90 240, 120 240 C 152 240, 192 246, 224 263 C 228 274, 218 284, 204 285 L 36 285 C 22 284, 12 274, 16 263 Z" />
                <path class="inv-bloom__soil-shade" d="M30 272 C 70 262, 170 262, 210 272" />
                <g class="inv-bloom__pebbles">
                    <ellipse cx="64" cy="270" rx="4" ry="2.4" />
                    <ellipse cx="170" cy="274" rx="3.2" ry="2" />
                    <ellipse cx="146" cy="268" rx="2.4" ry="1.6" />
                </g>
                <path class="inv-bloom__tuft" d="M40 262 q2 -14 6 -18 M46 262 q1 -10 -3 -15 M58 256 q3 -12 8 -14 M180 254 q-2 -13 -7 -16 M186 256 q2 -12 7 -15 M198 260 q1 -9 -3 -12" />
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
