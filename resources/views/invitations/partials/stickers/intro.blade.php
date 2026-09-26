{{--
    Apertura de «Álbum de stickers»: un sobre de stickers cerrado. Se abre tirando de la tira de arriba
    con el dedo: la tira sigue el arrastre y se estira un poco; si se suelta antes de tiempo vuelve a su
    lugar con un rebote y, pasado el punto, se arranca y el sticker brillante sube desde el sobre. Un
    toque también lo abre. Lógica en shell/cover-component y en stickerPeel (abajo); estilos en
    themes/stickers.css.
--}}
@php
    $introAge = $page->age();
@endphp

<div class="inv-themed-intro st-intro"
    x-data="{ ...invitationCover({ part: 750, reveal: 1500, close: 2400 }), ...stickerPeel() }"
    x-show="!closed"
    :class="{ 'is-peeling': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al cumpleaños de {{ $page->displayName }}">
    <p class="st-intro__eyebrow">
        @if($guest)
            {{ $guest->name }}, {{ mb_strtolower($invCopy['intro_eyebrow'] ?? '¡Estás invitado!') }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? '¡Estás invitado!' }}
        @endif
    </p>

    <button type="button" class="st-pack" data-cover-trigger
        @click="tap()"
        @pointerdown="grab($event)"
        @pointermove.window="drag($event)"
        @pointerup.window="release()"
        @pointercancel.window="release()"
        :class="{ 'is-dragging': dragging, 'is-springing': springing }"
        :style="`--peel: ${peel.toFixed(3)}`"
        aria-label="Abrir el sobre y entrar a la invitación">
        {{-- El sticker que espera dentro: sube cuando se arranca la tira --}}
        <span class="st-pack__card" aria-hidden="true">
            <b>{{ $introAge ?? $page->initials() }}</b>
            <small>{{ $page->displayName }}</small>
        </span>

        <span class="st-pack__body" aria-hidden="true">
            <span class="st-pack__label">
                <small>{{ $invCopy['intro_pack'] ?? 'Stickers de' }}</small>
                <strong>{{ $page->displayName }}</strong>
                @if($introAge !== null)
                    <em>{{ $introAge }} {{ $invCopy['sticker_age'] ?? 'años' }}</em>
                @endif
            </span>
        </span>

        <span class="st-pack__strip" aria-hidden="true"><i></i></span>
    </button>

    <p class="st-intro__hint">{{ $invCopy['intro_hint'] ?? 'Arranca la tira para abrir el sobre' }}</p>
</div>

<script>
// Tira del sobre con el dedo: la distancia arrastrada es cuánto se levanta (0 a 1)
function stickerPeel() {
    return {
        peel: 0,
        dragging: false,
        springing: false,
        moved: false,
        origin: null,
        grab(event) {
            if (this.stage > 0) {
                return;
            }

            this.dragging = true;
            this.moved = false;
            this.origin = { x: event.clientX, y: event.clientY };
        },
        drag(event) {
            if (!this.dragging) {
                return;
            }

            const distance = Math.hypot(event.clientX - this.origin.x, event.clientY - this.origin.y);

            this.moved = this.moved || distance > 6;
            this.peel = Math.min(1, distance / ((this.$el.offsetWidth || 240) * 0.7));
        },
        release() {
            if (!this.dragging) {
                return;
            }

            this.dragging = false;

            // Pasado un tercio se arranca sola; antes, vuelve a su lugar con un rebote
            if (this.peel > 0.34) {
                this.open();
                return;
            }

            this.springing = true;
            this.peel = 0;
            setTimeout(() => { this.springing = false; }, 650);
        },
        tap() {
            if (this.moved) {
                this.moved = false;
                return;
            }

            this.open();
        },
    };
}
</script>
