{{--
    Apertura de «Móvil de cuna»: el cuarto del bebé en penumbra, con el móvil quieto sobre la
    baranda de la cuna. Al tocarlo el móvil da una vuelta, las figuras se mecen y se enciende la luz
    del cuarto. Lógica en shell/cover-component; estilos en tendencias/movil.css.
--}}
<div class="inv-themed-intro mv-intro"
    x-data="invitationCover({ part: 1300, reveal: 1700, close: 2500 })"
    x-show="!closed"
    :class="{ 'is-spinning': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    <p class="mv-intro__eyebrow">
        @if($guest)
            Para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Un día muy especial' }}
        @endif
    </p>

    <button type="button" class="mv-intro__trigger" data-cover-trigger @click="open()" aria-label="Hacer girar el móvil y abrir la invitación">
        @include('invitations.partials.tendencias.movil.mobile', ['class' => 'mv-mobile--intro'])
    </button>

    {{-- La baranda de la cuna --}}
    <svg class="mv-crib" viewBox="0 0 320 70" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <rect x="0" y="4" width="320" height="10" rx="5"/>
        @for($bar = 0; $bar < 11; $bar++)
            <rect x="{{ 14 + $bar * 29 }}" y="12" width="7" height="58" rx="3"/>
        @endfor
    </svg>

    <p class="mv-intro__name">{{ $page->displayName }}</p>
    <p class="mv-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el móvil para que gire' }}</p>
</div>
