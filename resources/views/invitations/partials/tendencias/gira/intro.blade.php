{{--
    Apertura de «Gira mundial»: el show está por empezar. Arriba, la estructura con los focos todavía
    apagados y la pantalla del escenario; abajo, el público esperando. En primer plano, la pulsera de
    tela del festival con el nombre del invitado impreso y su código. Al tocarla, el lector la escanea
    (una línea de luz la recorre), cae el sello y arranca el show: se encienden los focos y barren el
    escenario, la pantalla muestra la gira, saltan papelitos y el público levanta las manos.
    Lógica en shell/cover-component; estilos en tendencias/gira.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::upper($page->eventDate->locale('es')->translatedFormat('j M'));
    $age = $page->age();
    // El público: cabezas y hombros, y algunos con los brazos listos para subir
    $crowd = [];
    for ($person = 0; $person < 12; $person++) {
        $crowd[] = [14 + $person * 34 + ($person % 3) * 4, 40 + ($person % 2) * 6, $person % 3 !== 1];
    }
@endphp

<div class="inv-themed-intro gr-intro"
    x-data="invitationCover({ part: 1750, reveal: 2150, close: 2950 })"
    x-show="!closed"
    :class="{ 'is-scanned': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al cumpleaños de {{ $page->displayName }}">
    <div class="gr-rig" aria-hidden="true">
        <span class="gr-rig__truss"></span>
        @foreach([12, 36, 64, 88] as $index => $spot)
            <span class="gr-rig__light" style="--x: {{ $spot }}%; --i: {{ $index }}"><i></i></span>
        @endforeach
        <span class="gr-rig__screen">
            <span>{{ $invCopy['tour_name'] ?? 'Gira' }} {{ $age ?? $page->eventDate->format('Y') }}</span>
        </span>
    </div>

    <p class="gr-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Tu acceso está listo' }}</p>

    <button type="button" class="gr-band" data-cover-trigger @click="open()" aria-label="Escanear la pulsera y entrar al show">
        <span class="gr-band__fabric">
            <span class="gr-band__lines">
                <span class="gr-band__print">{{ $invCopy['tour_wristband'] ?? 'Acceso general' }} · {{ $invCopy['tour_name'] ?? 'Gira' }} {{ $age ?? $page->eventDate->format('Y') }} · {{ $introDate }}</span>
                <span class="gr-band__name">{{ $guest?->name ?? $page->displayName }}</span>
            </span>
            <span class="gr-band__code" aria-hidden="true"></span>
        </span>
        <span class="gr-band__clip" aria-hidden="true"></span>
        <span class="gr-band__scan" aria-hidden="true"></span>
        <span class="gr-band__stamp" aria-hidden="true">{{ $invCopy['tour_only'] ?? 'Fecha única' }}</span>
    </button>

    <p class="gr-intro__doors">{{ $invCopy['tour_doors'] ?? 'Puertas' }} · {{ $page->eventDate->format('H:i') }}</p>
    <p class="gr-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca para escanear tu pulsera' }}</p>

    {{-- Papelitos que disparan los cañones de los costados --}}
    @for($i = 0; $i < 18; $i++)
        <span class="gr-intro__confetti" style="--side: {{ $i % 2 ? 1 : -1 }}; --a: {{ 55 + ($i * 7) % 30 }}deg; --d: {{ 0.9 + ($i % 6) * 0.03 }}s; --h: {{ 48 + ($i * 13) % 40 }}svh; --r: {{ ($i * 97) % 360 }}deg" aria-hidden="true"></span>
    @endfor

    <svg class="gr-crowd" viewBox="0 0 420 90" preserveAspectRatio="xMidYMax slice" aria-hidden="true" focusable="false">
        @foreach($crowd as [$x, $y, $cheers])
            <g class="gr-crowd__person" style="--p: {{ $loop->index }}">
                @if($cheers)
                    <path class="gr-crowd__arm" d="M{{ $x - 12 }} {{ $y + 14 }} L{{ $x - 20 }} {{ $y - 12 }} M{{ $x + 12 }} {{ $y + 14 }} L{{ $x + 20 }} {{ $y - 12 }}"/>
                @endif
                <circle cx="{{ $x }}" cy="{{ $y }}" r="11"/>
                <path d="M{{ $x - 20 }} 90 V{{ $y + 22 }} C{{ $x - 20 }} {{ $y + 13 }} {{ $x - 12 }} {{ $y + 11 }} {{ $x }} {{ $y + 11 }} C{{ $x + 12 }} {{ $y + 11 }} {{ $x + 20 }} {{ $y + 13 }} {{ $x + 20 }} {{ $y + 22 }} V90 Z"/>
            </g>
        @endforeach
    </svg>
</div>
