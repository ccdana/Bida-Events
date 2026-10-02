{{--
    Apertura de «Casa de muñecas de medianoche»: la casa cerrada, de noche, bajo la luna. La fachada es
    de dos hojas, como las casas de muñecas antiguas: cada una lleva su ventana de arriba, su ventana de
    abajo y una hoja de la puerta doble; la aldaba va en la puerta. Al tocarla, la aldaba golpea dos
    veces y se encienden las ventanas de abajo hacia arriba, hasta la buhardilla, donde se asoma el
    fantasma. Después la fachada se abre en dos hacia los costados y deja ver los cuartos encendidos
    con su nombre. Todo con la paleta del editor. Solo transform y opacity. Lógica en
    shell/cover-component; estilos en css/invitation/tendencias/casona.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F · H:i'));
@endphp

<div class="inv-themed-intro cs-intro"
    x-data="invitationCover({ part: 1300, reveal: 2900, close: 3700 })"
    x-show="!closed"
    :class="{ 'is-knocked': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a {{ $page->displayName }}">
    <p class="cs-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Esta noche hay fiesta en la casa' }}</p>

    <div class="cs-doll">
        <div class="cs-doll__roof">
            <span class="cs-intro__moon" aria-hidden="true"></span>
            @include('invitations.partials.tendencias.casona.roof', ['id' => 'cs-intro-roof'])
        </div>

        <div class="cs-doll__box">
            {{-- Adentro: los cuartos encendidos y su nombre, que se ven al abrir la fachada --}}
            <div class="cs-doll__inside" aria-hidden="true">
                <span class="cs-doll__room cs-doll__room--up"><i class="cs-doll__lamp"></i></span>
                <span class="cs-doll__room cs-doll__room--left"><i class="cs-doll__lamp"></i></span>
                <span class="cs-doll__room cs-doll__room--right"><i class="cs-doll__lamp"></i></span>
                <p class="cs-doll__welcome">
                    <small>{{ $invCopy['house_welcome'] ?? 'Bienvenidos a la casa de' }}</small>
                    <span>{{ $page->displayName }}</span>
                </p>
            </div>

            {{-- La fachada, en dos hojas con bisagras a los costados --}}
            @foreach(['left', 'right'] as $side)
                <div class="cs-doll__half cs-doll__half--{{ $side }}" aria-hidden="true">
                    <span class="cs-doll__face">
                        <span class="cs-window cs-window--up" style="--n: 2"><i class="cs-window__glow"></i></span>
                        <span class="cs-window cs-window--down" style="--n: 1"><i class="cs-window__glow"></i></span>
                        <span class="cs-door cs-door--{{ $side }}" style="--n: 0"><i class="cs-door__glow"></i></span>
                    </span>
                    <span class="cs-doll__back"></span>
                </div>
            @endforeach

            {{-- La aldaba, en la puerta: se toca para entrar --}}
            <button type="button" class="cs-knocker" data-cover-trigger @click.stop="open()" aria-label="Tocar la aldaba y entrar a la casa">
                <span class="cs-knocker__ring" aria-hidden="true"></span>
            </button>
        </div>

        <span class="cs-doll__base" aria-hidden="true"></span>
    </div>

    <div class="cs-intro__meta">
        <p class="cs-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="cs-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Esta llave es para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="cs-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la aldaba para entrar' }}</p>
</div>
