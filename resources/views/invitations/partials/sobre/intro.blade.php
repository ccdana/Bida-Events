{{--
    Apertura de la tarjeta «Sobre lacrado»: un sobre rojo cerrado con un sello de lacre. Al tocar el
    lacre el sello se parte en dos, la solapa se abre hacia atrás, la carta sale del sobre y una
    lluvia de flores cruza la pantalla; después el sobre se retira y queda la portada.
    Lógica en resources/js/cards/sobre/cover.js (waxCover, sobre invitationCover de shell/cover-component);
    estilos en resources/css/cards/sobre/cover.css.
--}}
@php
    $introTo = $cardTo !== '' ? $cardTo : null;
    $introFrom = $cardFrom !== '' ? $cardFrom : null;
    $waxInitial = $introFrom ? mb_strtoupper(mb_substr($introFrom, 0, 1)) : null;
@endphp

<div class="inv-sobre-intro"
    x-data="waxCover({ part: 950, reveal: 1750, close: 2700 })"
    x-show="!closed"
    :class="{
        'is-cracked': cracked,
        'is-leaving': stage >= 1,
        'is-gone': stage >= 2,
    }"
    @click="crack()"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $introTo ? 'Carta para '.$introTo : 'Una carta para ti' }}">

    <p class="inv-mail__eyebrow">{{ $introFrom ? 'De '.$introFrom : '21 de septiembre' }}</p>
    <p class="inv-mail__title">{{ $invCopy['intro_title'] ?? '¡Te llegó una carta!' }}</p>

    <div class="inv-mail">
        <button type="button" class="inv-mail__envelope" data-cover-trigger
            @click.stop="crack()"
            :aria-label="cracked ? 'El sobre se está abriendo' : 'Abrir el sobre'">

            {{-- La carta que sale del sobre al romperse el lacre --}}
            <span class="inv-mail__letter" aria-hidden="true">
                @for($line = 0; $line < 4; $line++)
                    <i class="inv-mail__line" style="--i: {{ $line }}"></i>
                @endfor
                <svg class="inv-mail__heart" viewBox="0 0 24 22" aria-hidden="true">
                    <path d="M12 21S3 15.2 1 10C-.6 5.8 2 1 6.5 1c2.4 0 4 1.4 5.5 3.3C13.5 2.4 15.1 1 17.5 1 22 1 24.6 5.8 23 10c-2 5.2-11 11-11 11z" />
                </svg>
            </span>

            <span class="inv-mail__front" aria-hidden="true"></span>
            <span class="inv-mail__flap" aria-hidden="true"></span>

            <span class="inv-wax" aria-hidden="true">
                @foreach(['left', 'right'] as $half)
                    <span class="inv-wax__half inv-wax__half--{{ $half }}">
                        @if($waxInitial)
                            <span class="inv-wax__mark">{{ $waxInitial }}</span>
                        @else
                            <svg class="inv-wax__mark inv-wax__mark--heart" viewBox="0 0 24 22"><path d="M12 21S3 15.2 1 10C-.6 5.8 2 1 6.5 1c2.4 0 4 1.4 5.5 3.3C13.5 2.4 15.1 1 17.5 1 22 1 24.6 5.8 23 10c-2 5.2-11 11-11 11z" /></svg>
                        @endif
                    </span>
                @endforeach
            </span>
        </button>
    </div>

    <div class="inv-mail__caption" aria-live="polite">
        <p class="inv-mail__hint" x-show="!cracked">
            <span class="inv-mail__tap" aria-hidden="true"></span>
            {{ $invCopy['intro_hint'] ?? 'Toca el lacre para abrir' }}
        </p>
        <p class="inv-mail__to" x-show="cracked" x-cloak>{{ $introTo ? 'Para '.$introTo : 'Para ti' }}</p>
    </div>
</div>
