{{--
    Apertura de «Próxima salida»: un pase de abordar a nombre del invitado. Al tocarlo, el talón se
    corta por la línea perforada y cae; el pase sube y deja ver el panel de salidas.
    Lógica en shell/cover-component; estilos en themes/salidas.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j M Y'));
@endphp

<div class="inv-themed-intro ps-intro"
    x-data="invitationCover({ part: 650, reveal: 1400, close: 2300 })"
    x-show="!closed"
    :class="{ 'is-torn': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la graduación de {{ $page->displayName }}">
    <p class="ps-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Tienes un asiento reservado' }}</p>

    <button type="button" class="ps-pass" data-cover-trigger @click="open()" aria-label="Abordar y abrir la invitación">
        <span class="ps-pass__main">
            <span class="ps-pass__title">{{ $invCopy['pass_title'] ?? 'Pase de abordar' }}</span>
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
            <span class="ps-pass__barcode" aria-hidden="true"></span>
        </span>
        <span class="ps-pass__stub" aria-hidden="true">
            <span class="ps-pass__stamp">{{ $page->eventDate->format('Y') }}</span>
        </span>
    </button>

    <p class="ps-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el pase para abordar' }}</p>
</div>
