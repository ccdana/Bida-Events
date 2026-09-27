{{--
    Apertura de «Próxima salida»: la puerta de embarque al atardecer. Por el ventanal se ve el avión
    esperando en la pista, con sus luces; arriba de la puerta, el cartel con el número (el día del
    evento) y el estado, y en primer plano el pase de abordar a nombre del invitado. Al tocarlo, el
    lector lo escanea, el cartel pasa a «Embarcando», el talón se corta por la línea perforada y cae,
    y el avión despega mientras el pase sube y aparece el panel de salidas.
    Lógica en shell/cover-component; estilos en themes/salidas.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j M Y'));
    $gate = $page->eventDate->format('j');
@endphp

<div class="inv-themed-intro ps-intro"
    x-data="invitationCover({ part: 1150, reveal: 1750, close: 2650 })"
    x-show="!closed"
    :class="{ 'is-torn': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la graduación de {{ $page->displayName }}">
    <div class="ps-gate" aria-hidden="true">
        <div class="ps-gate__window">
            <span class="ps-gate__sky"></span>
            <span class="ps-gate__sun"></span>
            <span class="ps-gate__runway"></span>
            <svg class="ps-gate__plane" viewBox="0 0 120 40" focusable="false">
                <path class="ps-gate__plane-tail" d="M16 19 L8 3 H17 L31 19 Z"/>
                <path class="ps-gate__plane-body" d="M4 22 C10 18 20 17 34 17 H94 C105 17 114 19 118 22 C114 25 105 27 94 27 H34 C20 27 10 26 4 22 Z"/>
                <path class="ps-gate__plane-wing" d="M50 24 L70 37 H79 L67 24 Z"/>
                <path class="ps-gate__plane-windows" d="M40 20.5 H92"/>
                <circle class="ps-gate__plane-light" cx="10" cy="4" r="1.6"/>
            </svg>
            <span class="ps-gate__mullion" style="--m: 33%"></span>
            <span class="ps-gate__mullion" style="--m: 66%"></span>
        </div>

        <div class="ps-gate__board">
            <span class="ps-gate__number">{{ $invCopy['board_gate'] ?? 'Puerta' }} {{ $gate }}</span>
            <span class="ps-gate__status">
                <span class="ps-gate__status-now">{{ $invCopy['board_on_time'] ?? 'A tiempo' }}</span>
                <span class="ps-gate__status-next">{{ $invCopy['board_today'] ?? 'Embarcando hoy' }}</span>
            </span>
        </div>
    </div>

    <p class="ps-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Tienes un asiento reservado' }}</p>

    <button type="button" class="ps-pass" data-cover-trigger @click="open()" aria-label="Abordar y abrir la invitación">
        <span class="ps-pass__main">
            <span class="ps-pass__title">
                {{ $invCopy['pass_title'] ?? 'Pase de abordar' }}
                <span class="ps-pass__led" aria-hidden="true"></span>
            </span>
            <span class="ps-pass__field">
                <small>{{ $invCopy['pass_passenger'] ?? 'Pasajero' }}</small>
                <b>{{ $guest?->name ?? ($invCopy['pass_guest'] ?? 'Invitado especial') }}</b>
            </span>
            <span class="ps-pass__field">
                <small>{{ $invCopy['board_destination'] ?? 'Destino' }}</small>
                <b>{{ $page->displayName }}</b>
            </span>
            <span class="ps-pass__row">
                <span class="ps-pass__field">
                    <small>{{ $invCopy['board_date'] ?? 'Fecha' }}</small>
                    <b>{{ $introDate }}</b>
                </span>
                <span class="ps-pass__field">
                    <small>{{ $invCopy['board_time'] ?? 'Hora' }}</small>
                    <b>{{ $page->eventDate->format('H:i') }}</b>
                </span>
            </span>
            <span class="ps-pass__barcode" aria-hidden="true"><span class="ps-pass__scan"></span></span>
        </span>
        <span class="ps-pass__stub" aria-hidden="true">
            <span class="ps-pass__stamp">{{ $page->eventDate->format('Y') }}</span>
        </span>
    </button>

    <p class="ps-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el pase para abordar' }}</p>
</div>
