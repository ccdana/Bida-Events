{{--
    Apertura de «Nuestra historia»: un estanque quieto con la luna reflejada. Un toque en el agua
    abre ondas desde el dedo, la luna tiembla y la historia empieza (y la música, si tiene autoplay).
    pondCover (resources/js/cards/historia/cover.js) extiende invitationCover de shell/cover-component.
--}}
<div class="story-cover"
    x-data="pondCover({ part: 900, reveal: 1300, close: 2100 })"
    x-show="!closed"
    :class="{ 'is-touched': stage >= 1, 'is-gone': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $cardTo !== '' ? 'Una historia para '.$cardTo : 'Una historia para ti' }}">
    <div class="story-cover__inner">
        <p class="story-cover__to">
            @if($cardTo !== '')
                Una historia para {{ $cardTo }}
            @else
                Una historia para ti
            @endif
        </p>
        <p class="story-cover__names">{{ $coupleLabel }}</p>

        <button type="button" class="story-cover__pond" data-cover-trigger @click="touch($event)"
            aria-label="{{ $invCopy['cover_hint'] ?? 'Toca el agua para empezar' }}">
            <span class="story-cover__moon" aria-hidden="true"></span>
            <span class="story-cover__ring" aria-hidden="true"></span>
            <span class="story-cover__ring" aria-hidden="true"></span>
            <span class="story-cover__ring" aria-hidden="true"></span>
        </button>

        <p class="story-cover__hint">{{ $invCopy['cover_hint'] ?? 'Toca el agua para empezar' }}</p>
        <button type="button" class="story-cover__skip" @click.stop="open()">{{ $invCopy['cover_skip'] ?? 'Entrar sin esperar' }}</button>
    </div>
</div>
