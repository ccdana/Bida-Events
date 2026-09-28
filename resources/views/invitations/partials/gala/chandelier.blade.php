{{--
    La araña de cristal de «Noche de gala»: cadena, florón, columna torneada, cuatro brazos con sus
    velas, guirnaldas de cuentas y caireles que cuelgan. El metal es el color principal (dorado, rosa
    o plata según la paleta); las llamas y los destellos se ven encendidos (lit o la apertura al
    tocarla). Parámetros: class, lit (encendida de entrada).
--}}
@php
    $candles = [[30, 118], [74, 106], [166, 106], [210, 118]];
    $gradient = 'ga-metal-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));
@endphp
<svg class="ga-ch {{ !empty($lit) ? 'is-lit' : '' }} {{ $class ?? '' }}" viewBox="0 0 240 200" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="{{ $gradient }}" x1="0" y1="0" x2="1" y2="1">
            <stop class="ga-ch__metal-hi" offset="0"/>
            <stop class="ga-ch__metal-mid" offset="0.5"/>
            <stop class="ga-ch__metal-low" offset="1"/>
        </linearGradient>
    </defs>

    {{-- El resplandor de cada vela, detrás de todo --}}
    @foreach($candles as $index => [$x, $y])
        <circle class="ga-ch__glow" style="--c: {{ $index }}" cx="{{ $x }}" cy="{{ $y - 26 }}" r="22"/>
    @endforeach

    {{-- Cadena y florón --}}
    @for($link = 0; $link < 5; $link++)
        <ellipse class="ga-ch__link" cx="120" cy="{{ 4 + $link * 7 }}" rx="{{ $link % 2 ? 1.6 : 3 }}" ry="3.6"/>
    @endfor
    <path class="ga-ch__metal" fill="url(#{{ $gradient }})" d="M104 38 H136 L130 47 H110 Z"/>

    {{-- Guirnaldas de cuentas entre los brazos --}}
    <path class="ga-ch__beads" d="M30 122 Q52 148 74 110"/>
    <path class="ga-ch__beads" d="M74 110 Q120 166 166 110"/>
    <path class="ga-ch__beads" d="M166 110 Q188 148 210 122"/>

    {{-- Brazos en S hasta cada vela --}}
    <path class="ga-ch__arm" d="M100 124 C76 140 34 138 30 120"/>
    <path class="ga-ch__arm" d="M108 120 C96 118 76 116 74 108"/>
    <path class="ga-ch__arm" d="M132 120 C144 118 164 116 166 108"/>
    <path class="ga-ch__arm" d="M140 124 C164 140 206 138 210 120"/>

    {{-- Columna torneada y el plato donde nacen los brazos --}}
    <path class="ga-ch__metal" fill="url(#{{ $gradient }})" d="M116 47 H124 V58 C132 62 134 72 128 78 C136 84 136 98 125 104 V116 H115 V104 C104 98 104 84 112 78 C106 72 108 62 116 58 Z"/>
    <ellipse class="ga-ch__metal" fill="url(#{{ $gradient }})" cx="120" cy="122" rx="27" ry="8"/>

    {{-- Velas: el platillo, la vela y la llama --}}
    @foreach($candles as $index => [$x, $y])
        <path class="ga-ch__metal" fill="url(#{{ $gradient }})" d="M{{ $x - 8 }} {{ $y - 2 }} H{{ $x + 8 }} L{{ $x + 5 }} {{ $y + 4 }} H{{ $x - 5 }} Z"/>
        <rect class="ga-ch__candle" x="{{ $x - 3 }}" y="{{ $y - 18 }}" width="6" height="16" rx="1.5"/>
        <path class="ga-ch__flame" style="--c: {{ $index }}" d="M{{ $x }} {{ $y - 32 }} C{{ $x + 3 }} {{ $y - 27 }} {{ $x + 5 }} {{ $y - 24 }} {{ $x + 5 }} {{ $y - 21 }} A5 5 0 0 1 {{ $x - 5 }} {{ $y - 21 }} C{{ $x - 5 }} {{ $y - 24 }} {{ $x - 3 }} {{ $y - 27 }} {{ $x }} {{ $y - 32 }} Z"/>
        {{-- El cairel que cuelga bajo cada vela --}}
        <path class="ga-ch__prism" style="--c: {{ $index }}" d="M{{ $x }} {{ $y + 6 }} L{{ $x + 3.5 }} {{ $y + 13 }} L{{ $x }} {{ $y + 23 }} L{{ $x - 3.5 }} {{ $y + 13 }} Z"/>
    @endforeach

    {{-- El remate: una esfera y el cairel grande del centro --}}
    <circle class="ga-ch__metal" fill="url(#{{ $gradient }})" cx="120" cy="133" r="5.5"/>
    <path class="ga-ch__prism ga-ch__prism--big" style="--c: 4" d="M120 140 L129 158 L120 186 L111 158 Z"/>
    <path class="ga-ch__prism-line" d="M120 140 V186 M111 158 H129"/>

    {{-- Destellos que saltan de los cristales al encenderse --}}
    @foreach([[30, 134], [74, 122], [120, 170], [166, 122], [210, 134], [96, 146], [144, 146]] as $index => [$x, $y])
        <path class="ga-ch__spark" style="--c: {{ $index }}" d="M{{ $x }} {{ $y - 6 }} L{{ $x + 1.5 }} {{ $y - 1.5 }} L{{ $x + 6 }} {{ $y }} L{{ $x + 1.5 }} {{ $y + 1.5 }} L{{ $x }} {{ $y + 6 }} L{{ $x - 1.5 }} {{ $y + 1.5 }} L{{ $x - 6 }} {{ $y }} L{{ $x - 1.5 }} {{ $y - 1.5 }} Z"/>
    @endforeach
</svg>
