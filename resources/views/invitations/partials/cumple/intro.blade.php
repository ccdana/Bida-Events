{{--
    Pastel de apertura: dos pisos con glaseado que chorrea, perlas de crema y chispas, sobre una base.
    Las velas titilan con su halo hasta que el invitado toca el pastel; al soplarlas sale humo,
    explota el confeti, aparece «¡A celebrar!» y el pastel se va (lógica en cumple-fiesta.blade.php).
    Si el subtítulo trae la edad, las velas tienen forma de número.
--}}
@php
    $introAge = $page->age();
    $introCandles = $introAge ? str_split((string) $introAge) : [null, null, null];

    // Glaseado con gotas de distinto largo: se dibuja de derecha a izquierda por el borde inferior
    $cakeDrip = function (float $x0, float $x1, float $yTop, float $yBase, array $lengths, float $radius = 10): string {
        $step = ($x1 - $x0) / count($lengths);
        $path = sprintf('M%.1f %.1f Q%.1f %.1f %.1f %.1f H%.1f Q%.1f %.1f %.1f %.1f V%.1f',
            $x0, $yTop + $radius, $x0, $yTop, $x0 + $radius, $yTop, $x1 - $radius, $x1, $yTop, $x1, $yTop + $radius, $yBase);

        foreach ($lengths as $i => $length) {
            $right = $x1 - $i * $step;
            $left = $right - $step;
            $center = $right - $step / 2;
            $half = $step * 0.26;
            $path .= sprintf(' Q%.1f %.1f %.1f %.1f V%.1f A%.1f %.1f 0 0 1 %.1f %.1f V%.1f Q%.1f %.1f %.1f %.1f',
                ($right + $center + $half) / 2, $yBase + 3, $center + $half, $yBase + 2,
                $yBase + $length, $half, $half, $center - $half, $yBase + $length,
                $yBase + 2, ($center - $half + $left) / 2, $yBase + 3, $left, $yBase);
        }

        return $path.' Z';
    };
@endphp

<div class="inv-cumple-intro"
    x-data="birthdayIntro()"
    x-show="!closed"
    :class="{ 'is-blown': blown }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al cumpleaños de {{ $page->displayName }}">
    <div class="inv-cumple-intro__confetti" aria-hidden="true">
        @for($i = 0; $i < 36; $i++)
            @php
                $angle = deg2rad(-90 + (($i * 47) % 150) - 75);
                $radius = 110 + ($i * 53) % 150;
            @endphp
            <span style="{{ sprintf('--x:%dpx;--y:%dpx;--r:%ddeg;--delay:%.2fs', round(cos($angle) * $radius * 1.4), round(sin($angle) * $radius), 180 + ($i * 97) % 540, (($i * 7) % 10) / 100) }}"></span>
        @endfor
    </div>

    <div class="inv-cumple-intro__content">
        <p class="inv-cumple-ribbon inv-cumple-intro__eyebrow">
            @if($guest)
                ¡{{ $guest->name }}, estás invitado!
            @else
                {{ $invCopy['intro_eyebrow'] ?? '¡Estás invitado!' }}
            @endif
        </p>
        <p class="inv-cumple-intro__name">{{ $page->displayName }}</p>

        <button type="button" class="inv-cumple-cake" data-cover-trigger @click="blow()" aria-label="Soplar las velas y abrir la invitación">
            <span class="inv-cumple-cake__glow" aria-hidden="true"></span>
            @foreach([[4, 14, 16, '0s', 'c2'], [88, 8, 12, '-0.7s', 'c1'], [-2, 58, 11, '-1.2s', 'c1'], [94, 50, 17, '-0.4s', 'c2'], [14, 40, 9, '-1.5s', 'c3']] as [$left, $top, $size, $delay, $color])
                <span class="inv-cumple-cake__sparkle" style="--l: {{ $left }}%; --t: {{ $top }}%; --s: {{ $size }}px; --d: {{ $delay }}; --c: var(--inv-cumple-{{ $color }})" aria-hidden="true"></span>
            @endforeach

            <span class="inv-cumple-cake__body">
                <span class="inv-cumple-cake__candles">
                    @foreach($introCandles as $digit)
                        <span class="inv-cumple-candle {{ $digit !== null ? 'is-digit' : '' }}" style="--i: {{ $loop->index }}">
                            <i class="inv-cumple-candle__glow"></i>
                            <i class="inv-cumple-candle__flame"></i>
                            <i class="inv-cumple-candle__smoke"></i>
                            <span class="inv-cumple-candle__body">{{ $digit }}</span>
                        </span>
                    @endforeach
                </span>

                <svg class="inv-cumple-cake__art" viewBox="0 0 240 200" aria-hidden="true" focusable="false">
                    {{-- Base con pedestal --}}
                    <path class="inv-cumple-cake__stand" d="M102 182 H138 L152 197 H88 Z"/>
                    <rect class="inv-cumple-cake__stand" x="8" y="172" width="224" height="11" rx="5.5"/>

                    {{-- Piso de abajo: bizcocho, franja de relleno, chispas y glaseado --}}
                    <rect class="inv-cumple-cake__tier" x="26" y="100" width="188" height="73" rx="10"/>
                    <rect class="inv-cumple-cake__band" x="27.5" y="144" width="185" height="11"/>
                    <g class="inv-cumple-cake__sprinkles">
                        <path class="inv-cumple-cake__sprinkle" d="M44 136 l7 -3"/>
                        <path class="inv-cumple-cake__sprinkle" d="M70 132 l5 5"/>
                        <path class="inv-cumple-cake__sprinkle" d="M150 134 l7 2"/>
                        <path class="inv-cumple-cake__sprinkle" d="M182 131 l4 -6"/>
                        <path class="inv-cumple-cake__sprinkle" d="M58 163 l7 1"/>
                        <path class="inv-cumple-cake__sprinkle" d="M96 166 l5 -5"/>
                        <path class="inv-cumple-cake__sprinkle" d="M140 162 l7 3"/>
                        <path class="inv-cumple-cake__sprinkle" d="M190 165 l5 -4"/>
                        <path class="inv-cumple-cake__sprinkle" d="M118 137 l6 -3"/>
                    </g>
                    <path class="inv-cumple-cake__frosting" d="{{ $cakeDrip(24, 216, 100, 116, [9, 17, 7, 22, 11, 15, 6, 19]) }}"/>
                    <path class="inv-cumple-cake__shine" d="M38 107 H84"/>
                    <g>
                        @for($x = 34; $x <= 206; $x += 12)
                            <circle class="inv-cumple-cake__pipe" cx="{{ $x }}" cy="171" r="4.6"/>
                        @endfor
                    </g>

                    {{-- Piso de arriba --}}
                    <rect class="inv-cumple-cake__tier inv-cumple-cake__tier--top" x="60" y="44" width="120" height="60" rx="8"/>
                    <g class="inv-cumple-cake__dots">
                        <circle cx="80" cy="88" r="3.2"/>
                        <circle cx="102" cy="95" r="3.2"/>
                        <circle cx="124" cy="86" r="3.2"/>
                        <circle cx="146" cy="95" r="3.2"/>
                        <circle cx="164" cy="85" r="3.2"/>
                    </g>
                    <path class="inv-cumple-cake__frosting inv-cumple-cake__frosting--top" d="{{ $cakeDrip(57, 183, 44, 58, [7, 15, 9, 18, 6, 13], 8) }}"/>
                    <path class="inv-cumple-cake__shine" d="M70 50 H102"/>
                    <g>
                        @for($x = 66; $x <= 174; $x += 12)
                            <circle class="inv-cumple-cake__pipe" cx="{{ $x }}" cy="103" r="4"/>
                        @endfor
                    </g>
                </svg>

                <span class="inv-cumple-cake__tap" aria-hidden="true"></span>
            </span>
        </button>

        <p class="inv-cumple-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el pastel para soplar las velas' }}</p>
    </div>

    <p class="inv-cumple-intro__cheer" x-show="blown" x-cloak>{{ $invCopy['intro_cheer'] ?? '¡A celebrar!' }}</p>
</div>
