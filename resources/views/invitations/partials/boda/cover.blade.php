{{--
    Sobre de apertura: papel con forro estampado, sello de cera con las iniciales, ramas y pétalos alrededor.
    Al tocarlo el sello se rompe, la solapa gira, la tarjeta sale y crece hacia la invitación
    (lógica en boda-jardin.blade.php, estilos en themes/boda.css).
--}}
@php
    $coverNames = implode(' & ', $coupleNames);
    $coverSeal = str_replace(' ', '', $page->initials());
@endphp

<div class="inv-boda-cover"
    x-data="weddingCover()"
    x-show="!closed"
    :class="{ 'is-opening': opening }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación de {{ implode(' y ', $coupleNames) }}">
    <div class="inv-boda-cover__glow" aria-hidden="true"></div>

    @include('invitations.partials.boda.branch', ['class' => 'inv-boda-cover__branch inv-boda-cover__branch--top'])
    @include('invitations.partials.boda.branch', ['class' => 'inv-boda-cover__branch inv-boda-cover__branch--bottom'])

    <div class="inv-boda-petals inv-boda-cover__petals" aria-hidden="true">
        @for($i = 0; $i < 9; $i++)
            <span style="{{ sprintf('--x:%.1f%%;--s:%.2f;--o:%.2f;--dx:%dpx;--d:%ds;--delay:-%.1fs', fmod($i * 23.7 + 6, 100), 0.8 + ($i % 3) / 10, 0.7, (($i * 29) % 70) - 35, 9 + $i % 4, $i * 1.3) }}"></span>
        @endfor
    </div>

    <div class="inv-boda-cover__content">
        <p class="inv-boda-cover__eyebrow">
            @if($guest)
                Para {{ $guest->name }}
            @else
                {{ $invCopy['cover_eyebrow'] ?? 'Tienes una invitación' }}
            @endif
        </p>
        <p class="inv-boda-cover__names">{{ $coverNames }}</p>

        <button type="button" class="inv-boda-envelope" @click="open()" aria-label="Abrir la invitación">
            <span class="inv-boda-envelope__back"></span>

            <span class="inv-boda-envelope__letter">
                <span class="inv-boda-envelope__letter-eyebrow">{{ $invCopy['hero_eyebrow'] ?? 'Nos casamos' }}</span>
                <span class="inv-boda-envelope__letter-names">{{ $coverNames }}</span>
                <span class="inv-boda-envelope__letter-rule"></span>
                <span class="inv-boda-envelope__letter-date">{{ $page->eventDate->format('d · m · Y') }}</span>
            </span>

            <span class="inv-boda-envelope__pocket"></span>

            <span class="inv-boda-envelope__flap">
                <span class="inv-boda-envelope__flap-front"></span>
                <span class="inv-boda-envelope__liner"></span>
            </span>

            <span class="inv-boda-envelope__seal"><span>{{ $coverSeal }}</span></span>
        </button>

        <p class="inv-boda-cover__hint">
            <span class="inv-boda-cover__hint-dot" aria-hidden="true"></span>
            Toca el sello para abrir
        </p>
    </div>
</div>
