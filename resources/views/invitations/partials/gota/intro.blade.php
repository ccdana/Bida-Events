{{--
    Apertura de «La gota»: la pila con el agua quieta y una jarra de porcelana encima, bajo una luz
    suave. Al tocar, la jarra se inclina y vierte un chorro; el agua brilla, salpica y suelta destellos,
    y la invitación aparece dentro de la onda que se abre donde cayó el agua, con anillos que se
    expanden sobre la portada (lógica en shell/cover-component, estilos en themes/gota.css).
    Neutral: la pila lleva grabada una gota, sin símbolos de un credo.
--}}
@php
    $introEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $introDate = $page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y');
    $fontId = 'gt-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6));
@endphp

<div class="inv-themed-intro gt-intro"
    x-data="invitationCover({ part: 1150, reveal: 1350, close: 2750, origin: '.gt-font__impact' })"
    x-show="!closed"
    :class="{ 'is-opening': stage >= 1, 'is-parting': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    <div class="gt-intro__scene">
        <span class="gt-intro__light" aria-hidden="true"></span>

        @for($i = 0; $i < 8; $i++)
            <span class="gt-intro__sparkle" aria-hidden="true"
                style="{{ sprintf('--top:%.1f%%;--left:%.1f%%;--size:%dpx;--d:%.1fs;--delay:-%.1fs', fmod($i * 31.3 + 6, 70), fmod($i * 47.9 + 11, 94), 8 + ($i * 5) % 9, 2.4 + ($i % 4) * 0.5, fmod($i * 0.9, 3)) }}"></span>
        @endfor

        <div class="gt-intro__content">
            <p class="gt-intro__eyebrow">
                @if($guest)
                    Para {{ $guest->name }}
                @else
                    {{ $introEyebrow }}
                @endif
            </p>
            <p class="gt-intro__name">{{ $page->displayName }}</p>
            <p class="gt-intro__date">{{ $introDate }}</p>

            <button type="button" class="gt-font" data-cover-trigger aria-label="Verter el agua y abrir la invitación">
                <span class="gt-font__halo" aria-hidden="true"></span>

                <svg class="gt-font__art" viewBox="0 0 240 300" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="{{ $fontId }}-stone" x1="0" y1="0" x2="0" y2="1">
                            <stop class="gt-font__stone-top" offset="0"/>
                            <stop class="gt-font__stone-low" offset="1"/>
                        </linearGradient>
                        <linearGradient id="{{ $fontId }}-water" x1="0" y1="0" x2="0" y2="1">
                            <stop class="gt-font__water-top" offset="0"/>
                            <stop class="gt-font__water-deep" offset="1"/>
                        </linearGradient>
                        <radialGradient id="{{ $fontId }}-pearl" cx="0.35" cy="0.3" r="0.9">
                            <stop class="gt-font__pearl-hi" offset="0"/>
                            <stop class="gt-font__pearl-mid" offset="0.55"/>
                            <stop class="gt-font__pearl-low" offset="1"/>
                        </radialGradient>
                        <radialGradient id="{{ $fontId }}-glow">
                            <stop class="gt-font__glow-in" offset="0"/>
                            <stop class="gt-font__glow-out" offset="1"/>
                        </radialGradient>
                        <clipPath id="{{ $fontId }}-surface">
                            <ellipse cx="120" cy="191" rx="86" ry="14"/>
                        </clipPath>
                    </defs>

                    {{-- Pila: sombra, base, pie con nudo, copa con filete y una gota grabada, borde y agua --}}
                    <ellipse class="gt-font__shadow" cx="120" cy="293" rx="60" ry="4"/>
                    <rect class="gt-font__stone" fill="url(#{{ $fontId }}-stone)" x="82" y="280" width="76" height="10" rx="5"/>
                    <path class="gt-font__stone" fill="url(#{{ $fontId }}-stone)" d="M106 258 C110 266 110 274 100 281 H140 C130 274 130 266 134 258 Z"/>
                    <ellipse class="gt-font__stone gt-font__knot" cx="120" cy="267" rx="9" ry="3"/>
                    <path class="gt-font__stone" fill="url(#{{ $fontId }}-stone)" d="M24 190 C26 238 70 260 120 260 C170 260 214 238 216 190 Z"/>
                    <path class="gt-font__gold" d="M40 216 C76 236 164 236 200 216"/>
                    <path class="gt-font__gold" d="M120 229 C117 234 114 238 114 241.5 A6 6 0 0 0 126 241.5 C126 238 123 234 120 229 Z"/>
                    <ellipse class="gt-font__rim" cx="120" cy="190" rx="96" ry="19"/>
                    <ellipse cx="120" cy="191" rx="86" ry="14" fill="url(#{{ $fontId }}-water)"/>
                    <ellipse class="gt-font__water-edge" cx="120" cy="191" rx="86" ry="14"/>

                    <g clip-path="url(#{{ $fontId }}-surface)">
                        <path class="gt-font__shimmer" d="M60 188 q10 -2 20 0"/>
                        <path class="gt-font__shimmer gt-font__shimmer--2" d="M120 196 q8 -1.6 16 0"/>
                        @for($i = 0; $i < 3; $i++)
                            <ellipse class="gt-font__ripple" style="--i: {{ $i }}" cx="157" cy="191" rx="12" ry="2.2"/>
                        @endfor
                    </g>

                    <ellipse class="gt-font__glow" cx="157" cy="184" rx="74" ry="32" fill="url(#{{ $fontId }}-glow)"/>

                    @foreach([[-14, -22, 2], [-6, -32, 2.4], [4, -36, 1.8], [12, -27, 2.2], [21, -16, 1.8]] as [$dx, $dy, $r])
                        <circle class="gt-font__splash" style="--dx: {{ $dx }}px; --dy: {{ $dy }}px" cx="157" cy="188" r="{{ $r }}"/>
                    @endforeach

                    {{-- Destellos que suben del agua --}}
                    @foreach([[-38, -70], [-16, -96], [8, -84], [30, -104], [46, -62]] as $index => [$dx, $dy])
                        <g transform="translate(157 184)">
                            <path class="gt-font__star" style="--i: {{ $index }}; --dx: {{ $dx }}px; --dy: {{ $dy }}px" d="M0 -5 L1.3 -1.3 L5 0 L1.3 1.3 L0 5 L-1.3 1.3 L-5 0 L-1.3 -1.3 Z"/>
                        </g>
                    @endforeach

                    <circle class="gt-font__impact" cx="157" cy="191" r="1" fill="none"/>

                    {{-- Chorro de agua desde el pico de la jarra inclinada hasta la pila --}}
                    <path class="gt-font__stream" pathLength="1" d="M154 88 C161 90 159 140 157 190"/>
                    <path class="gt-font__stream gt-font__stream--shine" pathLength="1" d="M154.6 91.5 C160 94 158.2 140 156.2 184"/>

                    {{-- Jarra de porcelana: la boca se estira en un pico afilado; asa de oreja, cuello fino, cuerpo de pera y filetes --}}
                    <g transform="translate(12 0)">
                        <g class="gt-font__ewer">
                            <path class="gt-font__ewer-handle-edge" d="M97 47 C85 40 70 45 70 61 C70 73 76 82 83 88"/>
                            <path class="gt-font__ewer-handle" d="M97 47 C85 40 70 45 70 61 C70 73 76 82 83 88"/>
                            <path class="gt-font__ewer-body" fill="url(#{{ $fontId }}-pearl)" d="M102 125 H114 L112.5 131.5 H103.5 Z"/>
                            <path class="gt-font__ewer-body" fill="url(#{{ $fontId }}-pearl)" d="M92 138 C92 133.5 99 131 108 131 C117 131 124 133.5 124 138 Z"/>
                            <path class="gt-font__ewer-body" fill="url(#{{ $fontId }}-pearl)" d="M98 40 C104 38.5 110 38 114 38.2 C122 38.5 130 34 137 29.5 C138.6 28.6 140 30 139.2 31.4 C134 38.5 126 45 119.5 50.5 C117.3 53 116.6 56 117 60 C118 68 138 78 138 100 C138 116 126 125 116 126 H100 C90 125 78 116 78 100 C78 78 98 68 99 60 C99.4 55 98.6 48 96.3 44.2 C95.6 42.4 96.2 40.6 98 40 Z"/>
                            <path class="gt-font__ewer-mouth" d="M98.5 40.6 C104 39.2 110 38.9 114 39.1 C110 41.2 103 41.8 98.5 40.6 Z"/>
                            <path class="gt-font__ewer-channel" d="M114 39.4 C122 39.6 130 35.6 136.6 31"/>
                            <path class="gt-font__gold" d="M98 40 C104 38.5 110 38 114 38.2 C122 38.5 130 34 137 29.5"/>
                            <path class="gt-font__gold" d="M99.2 59 C104 60.6 112 60.6 116.8 59"/>
                            <path class="gt-font__gold" d="M79 96 C98 104 118 104 137 96"/>
                            <path class="gt-font__gold gt-font__gold--thin" d="M81 108 C99 115 117 115 135 108"/>
                            <path class="gt-font__gold" d="M92.5 137.5 H123.5"/>
                            <path class="gt-font__glint" d="M85 95 C85 85 90 77 96 73"/>
                            <path class="gt-font__glint gt-font__glint--sm" d="M101.6 47 C102.2 51 102.2 55 101.8 58"/>
                        </g>
                    </g>
                </svg>
            </button>

            <p class="gt-intro__hint">
                <span class="gt-intro__hint-dot" aria-hidden="true"></span>
                {{ $invCopy['intro_hint'] ?? 'Toca la jarra para verter el agua' }}
            </p>
        </div>
    </div>

    <div class="gt-intro__waves" aria-hidden="true"><span></span><span></span></div>
</div>
