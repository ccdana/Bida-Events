{{--
    Apertura de «Álbum de stickers»: el sobre cerrado apoyado sobre el álbum abierto, con sus casillas
    numeradas esperando. Se abre tirando de la tira de arriba con el dedo: la tira sigue el arrastre y
    se estira un poco; si se suelta antes de tiempo vuelve a su lugar con un rebote y, pasado el punto,
    se arranca. Del sobre salen los stickers en abanico (la fecha, la hora, el brillante con la edad,
    el lugar y una estrella), el sobre vacío se cae y cada sticker vuela a pegarse en su casilla. Un
    toque también lo abre. Lógica en shell/cover-component y en stickerPeel (abajo); estilos en
    themes/stickers.css.
--}}
@php
    $introAge = $page->age();
    // Cada sticker: su lugar en el abanico al salir del sobre y su casilla en el álbum
    $fanStickers = [
        ['date', '-6.8rem, -5.6rem, -18deg', '-8.4rem, -7.4rem, -4deg'],
        ['time', '-3.5rem, -8.2rem, -9deg', '8.4rem, -7.4rem, 3deg'],
        ['star', '6.8rem, -5.6rem, 18deg', '8.4rem, 7.6rem, -6deg'],
        ['place', '3.5rem, -8.2rem, 9deg', '-8.4rem, 7.6rem, 4deg'],
        ['shiny', '0rem, -9.6rem, 0deg', '0rem, -0.6rem, -2deg'],
    ];
@endphp

<div class="inv-themed-intro st-intro"
    x-data="{ ...invitationCover({ part: 1350, reveal: 2100, close: 3250 }), ...stickerPeel() }"
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

    <div class="st-stage">
        {{-- El álbum abierto debajo del sobre: casillas numeradas y alguno ya pegado --}}
        <span class="st-album" aria-hidden="true">
            <span class="st-album__page st-album__page--left"></span>
            <span class="st-album__page st-album__page--right"></span>
            @foreach($fanStickers as $index => [$kind, $fan, $slot])
                @php([$slotX, $slotY, $slotR] = array_map('trim', explode(',', $slot)))
                <span class="st-album__slot st-album__slot--{{ $kind }}" style="--sx: {{ $slotX }}; --sy: {{ $slotY }}"><i>{{ $index + 1 }}</i></span>
            @endforeach
            <span class="st-album__done" style="--sx: 0rem; --sy: 8.2rem"></span>
        </span>

        <button type="button" class="st-pack" data-cover-trigger
            @click="tap()"
            @pointerdown="grab($event)"
            @pointermove.window="drag($event)"
            @pointerup.window="release()"
            @pointercancel.window="release()"
            :class="{ 'is-dragging': dragging, 'is-springing': springing }"
            :style="`--peel: ${peel.toFixed(3)}`"
            aria-label="Abrir el sobre y entrar a la invitación">
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

        {{-- Los stickers que esperan dentro del sobre: salen en abanico y después vuelan a su casilla --}}
        @foreach($fanStickers as $index => [$kind, $fan, $slot])
            @php([$fanX, $fanY, $fanR] = array_map('trim', explode(',', $fan)))
            @php([$slotX, $slotY, $slotR] = array_map('trim', explode(',', $slot)))
            <span class="st-fan st-fan--{{ $kind }}" style="--fx: {{ $fanX }}; --fy: {{ $fanY }}; --fr: {{ $fanR }}; --sx: {{ $slotX }}; --sy: {{ $slotY }}; --sr: {{ $slotR }}; --i: {{ $index }}" aria-hidden="true">
                @switch($kind)
                    @case('shiny')
                        <b>{{ $introAge ?? $page->initials() }}</b>
                        <small>{{ $page->displayName }}</small>
                        @break
                    @case('date')
                        <b>{{ $page->eventDate->format('j') }}</b>
                        <small>{{ \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('M')) }}</small>
                        @break
                    @case('time')
                        <b class="is-small">{{ $page->eventDate->format('H:i') }}</b>
                        <small>{{ $invCopy['sticker_time'] ?? 'La hora' }}</small>
                        @break
                    @case('place')
                        <svg viewBox="0 0 24 24" focusable="false"><path d="M12 22 C12 22 4 14.5 4 9.5 A8 8 0 0 1 20 9.5 C20 14.5 12 22 12 22 Z M12 12.5 A3 3 0 1 0 12 6.5 A3 3 0 0 0 12 12.5 Z" fill-rule="evenodd"/></svg>
                        <small>{{ $invCopy['sticker_place'] ?? 'El lugar' }}</small>
                        @break
                    @default
                        <svg viewBox="0 0 24 24" focusable="false"><path d="M12 2 L14.9 8.6 L22 9.3 L16.6 14 L18.2 21 L12 17.3 L5.8 21 L7.4 14 L2 9.3 L9.1 8.6 Z"/></svg>
                @endswitch
            </span>
        @endforeach
    </div>

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
