{{--
    Apertura de «Tendedero»: el patio en una mañana de sol. El cordel espera tendido entre dos palos,
    con pinzas sueltas, y abajo, en el pasto, el canasto con la ropita recién lavada asomando y
    burbujas de jabón que suben. Al tocar el canasto, la ropa sale volando una por una (el gorrito,
    el enterito con su nombre y las medias), se engancha en el cordel —que se estira con el peso— y se
    mece; una ráfaga de viento abre la invitación. Lógica en shell/cover-component; estilos en
    tendencias/tendedero.css.
--}}
<div class="inv-themed-intro td-intro"
    x-data="invitationCover({ part: 1850, reveal: 2150, close: 2900 })"
    x-show="!closed"
    :class="{ 'is-hung': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al baby shower de {{ $page->displayName }}">
    <span class="td-intro__sun" aria-hidden="true"></span>
    <span class="td-intro__ground" aria-hidden="true"></span>

    <p class="td-intro__eyebrow">
        @if($guest)
            Para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Viene alguien muy especial' }}
        @endif
    </p>

    <div class="td-yard" aria-hidden="true">
        <span class="td-yard__post td-yard__post--left"></span>
        <span class="td-yard__post td-yard__post--right"></span>
        <span class="td-line"></span>
        {{-- Pinzas sueltas en el cordel, esperando la ropa --}}
        <span class="td-yard__spare" style="--x: 14%; --y: 0.55rem"></span>
        <span class="td-yard__spare" style="--x: 49%; --y: 1.2rem"></span>
        <span class="td-yard__spare" style="--x: 83%; --y: 0.65rem"></span>
        <span class="td-yard__clothes">
            @include('invitations.partials.tendencias.tendedero.garment', ['kind' => 'hat', 'class' => 'td-yard__item td-yard__item--1'])
            @include('invitations.partials.tendencias.tendedero.garment', ['kind' => 'onesie', 'class' => 'td-yard__item td-yard__item--2', 'text' => $page->displayName, 'fit' => true])
            @include('invitations.partials.tendencias.tendedero.garment', ['kind' => 'socks', 'class' => 'td-yard__item td-yard__item--3'])
        </span>
    </div>

    <button type="button" class="td-basket" data-cover-trigger @click="open()" aria-label="Colgar la ropita y abrir la invitación">
        {{-- La ropa que asoma del canasto --}}
        <span class="td-basket__peek td-basket__peek--hat" aria-hidden="true"></span>
        <span class="td-basket__peek td-basket__peek--onesie" aria-hidden="true"></span>
        <span class="td-basket__peek td-basket__peek--socks" aria-hidden="true"></span>
        <span class="td-basket__body" aria-hidden="true"></span>
        <span class="td-basket__handle td-basket__handle--left" aria-hidden="true"></span>
        <span class="td-basket__handle td-basket__handle--right" aria-hidden="true"></span>
        @for($i = 0; $i < 5; $i++)
            <span class="td-basket__bubble" style="--i: {{ $i }}; --x: {{ [18, 42, 64, 30, 78][$i] }}%" aria-hidden="true"></span>
        @endfor
    </button>

    <p class="td-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el canasto para colgar la ropita' }}</p>
</div>
