{{--
    Apertura de «Caleidoscopio»: el visor redondo del caleidoscopio. Adentro, doce espejos: cada uno
    muestra el mismo trozo de cristales y los de lugar impar lo reflejan, así el dibujo es simétrico de
    verdad y se mueve solo, despacio. Al tocarlo gira el anillo, los cristales se reacomodan y en el
    centro aparece el «XV»; después el visor crece hasta llenar la pantalla y se entra a la portada.
    Solo se anima transform y opacity. Lógica en shell/cover-component; estilos en
    css/invitation/tendencias/caleidoscopio.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
@endphp

<div class="inv-themed-intro ka-intro"
    x-data="invitationCover({ part: 1300, reveal: 2450, close: 3350 })"
    x-show="!closed"
    :class="{ 'is-turning': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los quince de {{ $page->displayName }}">
    <p class="ka-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Mis quince' }}</p>

    <button type="button" class="ka-scope" data-cover-trigger @click="open()" aria-label="Girar el caleidoscopio">
        <span class="ka-scope__ring" aria-hidden="true"></span>
        <span class="ka-scope__view" aria-hidden="true">
            @for($wedge = 0; $wedge < 12; $wedge++)
                <span class="ka-wedge" style="--w: {{ $wedge }}"><i></i></span>
            @endfor
        </span>
        {{-- Fuera del visor, así no gira con él --}}
        <span class="ka-scope__center" aria-hidden="true"><b>XV</b></span>
    </button>

    <p class="ka-intro__name">{{ $page->displayName }}</p>
    <div class="ka-intro__meta">
        <p class="ka-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="ka-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Invitación para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="ka-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca para girar el caleidoscopio' }}</p>
</div>
