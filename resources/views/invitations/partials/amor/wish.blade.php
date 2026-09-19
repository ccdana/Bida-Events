{{--
    Cierre de la tarjeta de amor: un diente de león para pedir un deseo. Deslizar hacia arriba lo
    sopla entero, cada toque suelta unas semillas y «Soplar de verdad» usa el micrófono (con permiso).
    Cuando se va casi todo, aparece el deseo. Es una puerta del modo historia: «siguiente» lo sopla.
    dandelionWish en resources/js/cards/amor/wish.js; estilos en resources/css/cards/amor/wish.css.
    Sin JavaScript se ve la flor y el deseo escrito.
--}}
@php
    $wishTo = $cardTo !== '' ? $cardTo : null;
    $seedAngles = [];
    // Dos coronas de semillas: la de afuera más larga, la de adentro entre medio
    for ($seed = 0; $seed < 18; $seed++) {
        $seedAngles[] = ['angle' => $seed * 20, 'length' => 1];
    }
    for ($seed = 0; $seed < 12; $seed++) {
        $seedAngles[] = ['angle' => $seed * 30 + 15, 'length' => 0.72];
    }
@endphp

<section class="inv-section inv-wish" id="deseo"
    x-data="dandelionWish()"
    data-story-gate
    :data-gate-done="blown"
    :class="{ 'is-listening': listening }">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'eyebrow' => $invCopy['wish_eyebrow'] ?? 'Antes de irte',
            'title' => $invCopy['wish_title'] ?? 'Pide un deseo',
        ])

        <div class="inv-wish__flower" x-ref="flower" data-story-ignore
            @pointerdown="press($event)" @pointerup="release($event)" @pointercancel="cancelPress()">
            <svg class="inv-wish__art" viewBox="0 0 240 320" aria-hidden="true">
                <path class="inv-wish__stem" d="M120 320 C 112 270, 128 200, 120 128" />
                <path class="inv-wish__leaf" d="M118 262 C 96 246, 80 252, 70 236 C 92 226, 108 236, 118 262 Z" />
                @foreach($seedAngles as $seed)
                    <g class="inv-wish__seed" data-seed style="--i: {{ $loop->index }}">
                        <g transform="rotate({{ $seed['angle'] }} 120 120)">
                            @php($tip = 120 - 46 * $seed['length'])
                            <line class="inv-wish__thread" x1="120" y1="118" x2="120" y2="{{ $tip }}" />
                            <path class="inv-wish__fluff" d="M120 {{ $tip }} l-9 -10 M120 {{ $tip }} l-5 -12 M120 {{ $tip }} l0 -13 M120 {{ $tip }} l5 -12 M120 {{ $tip }} l9 -10" />
                        </g>
                    </g>
                @endforeach
                <circle class="inv-wish__core" cx="120" cy="120" r="7" />
            </svg>
        </div>

        <div class="inv-wish__controls" data-needs-js x-show="!blown">
            <p class="inv-wish__hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $invCopy['wish_hint'] ?? 'Desliza hacia arriba para soplar' }}
            </p>
            <button type="button" class="inv-wish__mic" @click="listening ? stopListening() : listen()" x-show="micAvailable" :style="`--level: ${level.toFixed(2)}`" :aria-pressed="listening.toString()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3" stroke-linecap="round"/></svg>
                <span x-text="listening ? 'Sopla hacia el teléfono…' : 'Soplar de verdad'">Soplar de verdad</span>
            </button>
            <button type="button" class="inv-wish__blow" @click="blowAll()">Soplar</button>
            <p class="inv-wish__note" x-show="micError" x-cloak x-text="micError" role="status"></p>
        </div>

        <div class="inv-wish__message">
            <p class="inv-wish__text">{{ $invCopy['wish_message'] ?? 'Que esta primavera nos encuentre juntos, y todas las que vengan.' }}</p>
            <p class="inv-wish__to">{{ $wishTo ? 'Feliz primavera, '.$wishTo : 'Feliz primavera' }}</p>
            <a href="#inicio" class="inv-wish__again" data-needs-js>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.4-5.7M4 4v4h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Volver a empezar
            </a>
        </div>
    </div>

    @include('invitations.partials.amor.butterfly', ['butterflyId' => 'deseo', 'butterflyStyle' => '--bx: 80%; --by: 30%; --delay: 1s'])
</section>
