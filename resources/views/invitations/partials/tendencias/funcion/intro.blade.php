{{--
    Apertura de «Función de medianoche»: la sala a oscuras y la pantalla con la cinta de entrada de la
    película (el círculo con la cruz y el número). Al tocarla se enciende el proyector, la aguja da la
    vuelta y cuenta 3, 2, 1, y empieza la función. Sin destellos: la luz sube y baja despacio. Lógica
    en shell/cover-component; estilos en tendencias/funcion.css.
--}}
<div class="inv-themed-intro fn-intro"
    x-data="invitationCover({ part: 1250, reveal: 1600, close: 2300 })"
    x-show="!closed"
    :class="{ 'is-rolling': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a {{ $page->displayName }}">
    <span class="fn-intro__beam" aria-hidden="true"></span>

    <p class="fn-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Función de estreno' }}</p>

    <button type="button" class="fn-leader" data-cover-trigger @click="open()" aria-label="Encender el proyector y empezar la función">
        <span class="fn-leader__sweep" aria-hidden="true"></span>
        <span class="fn-leader__cross" aria-hidden="true"></span>
        <span class="fn-leader__rings" aria-hidden="true"></span>
        <span class="fn-leader__numbers" aria-hidden="true">
            <span>5</span><span>3</span><span>2</span><span>1</span>
        </span>
    </button>

    <p class="fn-intro__title">{{ $page->displayName }}</p>
    @if($guest)
        <p class="fn-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Boleto a nombre de' }} {{ $guest->name }}</p>
    @endif
    <p class="fn-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca para encender el proyector' }}</p>
</div>
