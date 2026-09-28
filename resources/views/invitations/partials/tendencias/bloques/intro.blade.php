{{--
    Apertura de «Bloques de juguete»: el cuarto de juegos con su alfombra redonda y el baúl de
    juguetes cerrado, que se sacude un poco (hay algo adentro). Al tocarlo se abre la tapa y los
    bloques saltan de a uno, dan una vuelta en el aire y caen en fila, con un pequeño rebote, hasta
    formar su nombre; después la fila entera da un saltito de alegría y queda la portada.
    El nombre: su primera palabra (hasta diez letras; si no, «BEBÉ»). Lógica en shell/cover-component;
    estilos en css/invitation/tendencias/bloques.css.
--}}
@php
    $introWord = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first() ?: '';
    $introWord = mb_strlen($introWord) >= 2 && mb_strlen($introWord) <= 10 ? $introWord : 'Bebé';
    $introLetters = mb_str_split(mb_strtoupper($introWord));
    $introCount = count($introLetters);
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F'));
@endphp

<div class="inv-themed-intro bl-intro"
    x-data="invitationCover({ part: 2350, reveal: 2900, close: 3700 })"
    x-show="!closed"
    :class="{ 'is-out': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al baby shower de {{ $page->displayName }}">
    <span class="bl-intro__rug" aria-hidden="true"></span>

    <p class="bl-intro__eyebrow">
        @if($guest)
            {{ $guest->name }}: {{ \Illuminate\Support\Str::lcfirst($invCopy['intro_eyebrow'] ?? 'Viene alguien muy especial') }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Viene alguien muy especial' }}
        @endif
    </p>

    <div class="bl-intro__stage" style="--n: {{ $introCount }}">
        {{-- La fila donde caen los bloques --}}
        <span class="bl-intro__word" aria-hidden="true">
            @foreach($introLetters as $index => $letter)
                @include('invitations.partials.tendencias.bloques.block', [
                    'letter' => $letter,
                    'tone' => $index % 3,
                    'class' => 'bl-intro__block',
                    'style' => '--i: '.$index.'; --k: '.(($introCount - 1) / 2 - $index).'; --spin: '.(($index % 2) ? -1 : 1),
                ])
            @endforeach
        </span>

        <p class="bl-intro__date">{{ $invCopy['hero_eyebrow'] ?? 'Baby shower' }} · {{ $introDate }}</p>

        {{-- El baúl de juguetes: la tapa con bisagra atrás y el frente de tablitas --}}
        <button type="button" class="bl-chest" data-cover-trigger aria-label="Abrir el baúl y armar su nombre">
            <span class="bl-chest__mouth" aria-hidden="true"></span>
            <span class="bl-chest__lid" aria-hidden="true"></span>
            <span class="bl-chest__body" aria-hidden="true">
                <i class="bl-chest__latch"></i>
                <b class="bl-chest__star"></b>
            </span>
        </button>
    </div>

    <p class="bl-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el baúl para sacar los bloques' }}</p>
</div>
