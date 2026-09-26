{{--
    Apertura de «Carta de baile»: la carta cerrada sobre terciopelo, atada con un cordón y su borla.
    Al tocarla, el cordón se suelta y cae, la tapa se abre como un librito y adentro espera el lugar
    reservado a nombre del invitado. Lógica en shell/cover-component; estilos en themes/carta.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F'));
@endphp

<div class="inv-themed-intro cb-intro"
    x-data="invitationCover({ part: 700, reveal: 1500, close: 2400 })"
    x-show="!closed"
    :class="{ 'is-untied': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los XV años de {{ $page->displayName }}">
    <p class="cb-intro__eyebrow">
        @if($guest)
            Para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Tienes un lugar en el baile' }}
        @endif
    </p>

    <button type="button" class="cb-closed" data-cover-trigger @click="open()" aria-label="Desatar el cordón y abrir la carta">
        {{-- Lo que se ve al abrir la tapa: la primera hoja, con el lugar reservado --}}
        <span class="cb-closed__inside" aria-hidden="true">
            @if($guest)
                <span class="cb-closed__reserved">{{ $invCopy['card_reserved'] ?? 'Reservado para' }}</span>
                <span class="cb-closed__guest">{{ $guest->name }}</span>
            @else
                <span class="cb-closed__guest">{{ $invCopy['card_reserved_any'] ?? 'Un lugar reservado para ti' }}</span>
            @endif
        </span>

        <span class="cb-closed__cover">
            <span class="cb-closed__title">{{ $invCopy['card_title'] ?? 'Carta de baile' }}</span>
            <span class="cb-closed__name">{{ $page->displayName }}</span>
            <span class="cb-closed__date">{{ $introDate }}</span>
        </span>

        {{-- Cordón que abraza la carta, con su nudo y la borla colgando --}}
        <span class="cb-closed__cord" aria-hidden="true">
            <svg viewBox="0 0 220 120" preserveAspectRatio="none" focusable="false">
                <path class="cb-cord-line" d="M0 40 H220"/>
                <path class="cb-cord-line cb-cord-line--under" d="M0 46 H220"/>
            </svg>
        </span>
        <span class="cb-closed__tassel" aria-hidden="true">
            <svg viewBox="0 0 40 110" focusable="false">
                <path class="cb-tassel-string" d="M20 0 C14 16 26 26 20 44"/>
                <ellipse class="cb-tassel-knot" cx="20" cy="46" rx="6" ry="5"/>
                <path class="cb-tassel-cap" d="M13 50 H27 L25 58 H15 Z"/>
                <path class="cb-tassel-fringe" d="M15 58 L11 104 M18 58 L16 106 M20 58 L20 107 M22 58 L24 106 M25 58 L29 104"/>
            </svg>
        </span>
    </button>

    <p class="cb-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la borla para desatar el cordón' }}</p>
</div>
