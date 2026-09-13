{{-- Sobre de apertura: se abre al tocarlo y revela la invitación (lógica en boda-jardin.blade.php) --}}
<div class="inv-boda-cover"
    x-data="weddingCover(@js($coverKey))"
    x-show="!closed"
    :class="{ 'is-opening': opening }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación de {{ implode(' y ', $coupleNames) }}">
    <div class="inv-boda-cover__content">
        <p class="inv-boda-cover__eyebrow">
            @if($guest)
                Para {{ $guest->name }}
            @else
                {{ $invCopy['cover_eyebrow'] ?? 'Tienes una invitación' }}
            @endif
        </p>

        <button type="button" class="inv-boda-envelope" @click="open()" aria-label="Abrir la invitación">
            <span class="inv-boda-envelope__back"></span>
            <span class="inv-boda-envelope__letter">
                <span class="inv-boda-envelope__letter-names">{{ implode(' & ', $coupleNames) }}</span>
                <span class="inv-boda-envelope__letter-date">{{ $page->eventDate->format('d · m · Y') }}</span>
            </span>
            <span class="inv-boda-envelope__front"></span>
            <span class="inv-boda-envelope__flap"></span>
            <span class="inv-boda-envelope__seal">{{ str_replace(' ', '', $page->initials()) }}</span>
        </button>

        <p class="inv-boda-cover__names">{{ implode(' & ', $coupleNames) }}</p>
        <p class="inv-boda-cover__hint">Toca el sobre para abrir</p>
    </div>
</div>
