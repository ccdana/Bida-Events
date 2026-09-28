{{--
    Apertura de «Encomienda especial»: la caja de cartón vista desde arriba, cerrada con una cinta
    impresa («frágil · con mucho amor»), la guía del envío pegada en una solapa con el nombre del
    invitado y el sello de «frágil» en la otra. Al tocarla, la cinta se despega de abajo hacia arriba,
    las solapas se abren hacia los costados, el papel de seda se corre y sube la tarjeta con su nombre,
    con papel picado. Después la tarjeta se acerca y queda la portada.
    Lógica en shell/cover-component; estilos en css/invitation/tendencias/encomienda.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F'));
    $tapeText = mb_strtoupper(($invCopy['parcel_fragile'] ?? 'Frágil').' · '.($invCopy['parcel_care'] ?? 'Con mucho amor').' · ');
@endphp

<div class="inv-themed-intro en-intro"
    x-data="invitationCover({ part: 2350, reveal: 2900, close: 3650 })"
    x-show="!closed"
    :class="{ 'is-unsealed': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al baby shower de {{ $page->displayName }}">
    <p class="en-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Llegó una encomienda muy especial' }}</p>

    <div class="en-box">
        {{-- Adentro: el cartón, el papel de seda y la tarjeta con su nombre --}}
        <span class="en-box__inside" aria-hidden="true">
            <span class="en-tissue en-tissue--left"></span>
            <span class="en-tissue en-tissue--right"></span>
        </span>
        <span class="en-card">
            <span class="en-card__kicker">{{ $invCopy['hero_eyebrow'] ?? 'Baby shower' }}</span>
            <span class="en-card__name">{{ $page->displayName }}</span>
            <span class="en-card__date">{{ $introDate }}</span>
        </span>

        {{-- Las dos solapas: en una la guía del envío, en la otra el sello --}}
        <span class="en-box__flap en-box__flap--left" aria-hidden="true">
            <span class="en-rubber">{{ $invCopy['parcel_fragile'] ?? 'Frágil' }}</span>
            <span class="en-arrows"><i></i><i></i></span>
        </span>
        <span class="en-box__flap en-box__flap--right">
            <span class="en-label">
                <span class="en-label__head">{{ $invCopy['parcel_title'] ?? 'Encomienda especial' }}</span>
                <span class="en-label__row">
                    <small>Para</small>
                    <b>{{ $guest?->name ?? 'Nuestra familia y amigos' }}</b>
                </span>
                <span class="en-label__row">
                    <small>{{ $invCopy['parcel_arrival'] ?? 'Llega' }}</small>
                    <b>{{ $introDate }}</b>
                </span>
                <span class="en-label__bars" aria-hidden="true"></span>
            </span>
        </span>

        {{-- La cinta que cierra la caja --}}
        <button type="button" class="en-tape" data-cover-trigger aria-label="Despegar la cinta y abrir la caja">
            <span class="en-tape__print" aria-hidden="true">{{ str_repeat($tapeText, 4) }}</span>
        </button>
    </div>

    <span class="en-confetti" aria-hidden="true">
        @for($piece = 0; $piece < 14; $piece++)
            <i style="--i: {{ $piece }}; --a: {{ ($piece * 137) % 360 }}deg; --d: {{ 5 + ($piece * 7) % 6 }}rem"></i>
        @endfor
    </span>

    <p class="en-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la cinta para abrir la caja' }}</p>
</div>
