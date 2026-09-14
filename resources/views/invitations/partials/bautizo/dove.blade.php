{{-- Paloma de la paz en vuelo con rama de olivo: silueta estilizada, plumas largas y trazo fino (estilos en themes/bautizo.css) --}}
@php($doveGradient = 'inv-dove-'.\Illuminate\Support\Str::random(6))
<svg class="inv-bautizo-dove {{ $class ?? '' }}" viewBox="-2 0 240 126" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $doveGradient }}" x1="0" y1="0" x2="0" y2="1">
            <stop class="inv-bautizo-dove__stop" offset="0"/>
            <stop class="inv-bautizo-dove__stop inv-bautizo-dove__stop--low" offset="1"/>
        </linearGradient>
    </defs>

    {{-- Ala trasera, un tono más oscura para dar profundidad --}}
    <path class="inv-bautizo-dove__wing inv-bautizo-dove__wing--back inv-bautizo-dove__shade" d="M100 82 C94 58 98 30 114 8 C118 24 124 32 136 36 C124 48 118 64 120 84 Z"/>

    <path class="inv-bautizo-dove__body" fill="url(#{{ $doveGradient }})" d="M30 78 L44 72 C48 62 60 58 70 62 C80 66 86 76 98 82 C116 90 140 92 162 90 C182 88 204 78 226 64 C214 80 206 88 196 92 C212 94 222 98 232 106 C210 110 186 110 166 106 C146 118 118 124 94 116 C74 110 58 98 50 86 C48 83 46 82 44 82 Z"/>
    <path class="inv-bautizo-dove__line" d="M170 96 C188 92 206 84 222 70 M170 101 C190 101 210 102 228 105"/>
    <path class="inv-bautizo-dove__belly" d="M64 100 C82 114 110 120 136 116 C150 114 160 110 166 106 C148 110 126 111 104 108 C88 106 76 104 64 100 Z"/>

    {{-- Ala delantera con plumas largas --}}
    <g class="inv-bautizo-dove__wing inv-bautizo-dove__wing--front">
        <path class="inv-bautizo-dove__body" fill="url(#{{ $doveGradient }})" d="M92 84 C94 60 108 34 132 18 C150 6 172 2 196 4 C184 12 176 18 170 24 C184 22 194 24 204 30 C188 36 176 40 166 46 C176 48 184 52 190 60 C172 62 156 64 146 70 C150 74 152 78 152 82 C142 82 132 86 124 90 C112 92 100 90 92 84 Z"/>
        <path class="inv-bautizo-dove__line" d="M108 80 C126 56 150 34 188 10 M120 86 C138 68 160 52 196 32 M134 88 C148 78 164 68 182 60"/>
    </g>

    <path class="inv-bautizo-dove__beak" d="M30 78 L44 72 L44 82 Z"/>
    <circle class="inv-bautizo-dove__eye" cx="59" cy="70" r="2.3"/>

    {{-- Rama de olivo en el pico --}}
    <path class="inv-bautizo-dove__stem" d="M34 80 C28 90 20 98 10 104"/>
    <path class="inv-bautizo-dove__leaf" d="M26 90 C18 85 11 87 8 92 C15 95 22 94 26 90 Z"/>
    <path class="inv-bautizo-dove__leaf" d="M21 95 C26 102 26 109 22 113 C17 107 17 100 21 95 Z"/>
    <path class="inv-bautizo-dove__leaf" d="M13 101 C6 99 1 102 0 107 C6 109 11 107 13 101 Z"/>
</svg>
