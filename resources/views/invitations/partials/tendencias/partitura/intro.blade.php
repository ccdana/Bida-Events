{{--
    Apertura de «Partitura a dos voces»: el atril antes del concierto. Arriba la obra y los nombres;
    abajo un metrónomo que oscila despacio. Al tocarlo marca la entrada, como el director antes de
    empezar: «1, 2, 3, 4» y el concierto comienza. Lógica en shell/cover-component; estilos en
    tendencias/partitura.css.
--}}
<div class="inv-themed-intro pt-intro"
    x-data="invitationCover({ part: 1250, reveal: 1600, close: 2300 })"
    x-show="!closed"
    :class="{ 'is-counting': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación de {{ $page->displayName }}">
    <div class="pt-intro__head">
        <p class="pt-intro__eyebrow">
            @if($guest)
                Para {{ $guest->name }}
            @else
                {{ $invCopy['intro_eyebrow'] ?? 'Tienes una invitación' }}
            @endif
        </p>
        <p class="pt-intro__work">{{ $invCopy['score_title'] ?? 'Concierto para dos voces' }}</p>
        <p class="pt-intro__names">{{ $page->displayName }}</p>
    </div>

    <button type="button" class="pt-metronome" data-cover-trigger @click="open()" aria-label="Marcar la entrada y abrir la invitación">
        <svg viewBox="0 0 120 170" aria-hidden="true" focusable="false">
            {{-- Caja de madera con su escala --}}
            <path class="pt-metronome__body" d="M40 8 H80 L110 160 H10 Z"/>
            <path class="pt-metronome__face" d="M48 22 H72 L92 132 H28 Z"/>
            <g class="pt-metronome__scale">
                @for($tick = 0; $tick < 9; $tick++)
                    <line x1="{{ 52 - $tick * 1.6 }}" y1="{{ 34 + $tick * 11 }}" x2="{{ 58 - $tick * 1.4 }}" y2="{{ 34 + $tick * 11 }}"/>
                @endfor
            </g>
            <rect class="pt-metronome__base" x="4" y="156" width="112" height="10" rx="3"/>
            {{-- El péndulo con su pesa --}}
            <g class="pt-metronome__arm">
                <line x1="60" y1="128" x2="60" y2="20"/>
                <path class="pt-metronome__weight" d="M52 48 H68 L66 62 H54 Z"/>
            </g>
            <circle class="pt-metronome__pivot" cx="60" cy="128" r="5"/>
        </svg>
    </button>

    {{-- La cuenta del director: se ve un número a la vez --}}
    <p class="pt-count" aria-hidden="true">
        <span>1</span><span>2</span><span>3</span><span>4</span>
    </p>

    <p class="pt-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el metrónomo para empezar' }}</p>
</div>
