{{--
    Pastel de apertura: las velas titilan hasta que el invitado toca el pastel; al soplarlas sale humo,
    explota el confeti, aparece «¡A celebrar!» y el pastel se va (lógica en cumple-fiesta.blade.php).
    Si el subtítulo trae la edad, las velas tienen forma de número.
--}}
@php
    $introAge = $page->age();
    $introCandles = $introAge ? str_split((string) $introAge) : [null, null, null];
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

        <button type="button" class="inv-cumple-cake" @click="blow()" aria-label="Soplar las velas y abrir la invitación">
            <span class="inv-cumple-cake__candles">
                @foreach($introCandles as $digit)
                    <span class="inv-cumple-candle {{ $digit !== null ? 'is-digit' : '' }}" style="--i: {{ $loop->index }}">
                        <i class="inv-cumple-candle__flame"></i>
                        <i class="inv-cumple-candle__smoke"></i>
                        <span class="inv-cumple-candle__body">{{ $digit }}</span>
                    </span>
                @endforeach
            </span>
            <span class="inv-cumple-cake__frosting"></span>
            <span class="inv-cumple-cake__tier"></span>
            <span class="inv-cumple-cake__plate"></span>
        </button>

        <p class="inv-cumple-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el pastel para soplar las velas' }}</p>
    </div>

    <p class="inv-cumple-intro__cheer" x-show="blown" x-cloak>{{ $invCopy['intro_cheer'] ?? '¡A celebrar!' }}</p>
</div>
