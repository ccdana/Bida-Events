{{-- Tapa de cuero gastado: título pintado a mano, la foto de los dos sujeta con cinta y un girasol. --}}
@php
    $coverTitle = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Nuestro libro de aventuras');
    $coverAlt = ($page->welcome['imagen_hero_alt'] ?? null) ?: ($cardTo !== '' ? 'Foto para '.$cardTo : '');
@endphp
<article class="nb-page nb-cover" data-nb-page data-density="hard" id="inicio">
    <div class="nb-cover__inner">
        <span class="nb-cover__stitch" aria-hidden="true"></span>

        <h1 class="nb-cover__title">{{ $coverTitle }}</h1>

        @if($page->heroImage)
            <figure class="nb-cover__photo">
                <span class="nb-tape nb-tape--a" aria-hidden="true"></span>
                <span class="nb-tape nb-tape--b" aria-hidden="true"></span>
                <img src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 700) }}" alt="{{ $coverAlt }}"
                    loading="eager" fetchpriority="high" decoding="async" draggable="false">
            </figure>
        @endif

        <p class="nb-cover__plate">
            <span class="nb-cover__to">{{ $cardTo !== '' ? 'Para '.$cardTo : $page->displayName }}</span>
            @if($cardFrom !== '')
                <span class="nb-cover__from">de {{ $cardFrom }}</span>
            @endif
        </p>

        @include('invitations.partials.aventura.flower', ['kind' => 'girasol', 'class' => 'nb-cover__flower'])
        @include('invitations.partials.aventura.flower', ['kind' => 'margarita', 'class' => 'nb-cover__flower nb-cover__flower--small'])

        <button type="button" class="nb-cover__open" data-nb-open data-needs-js>
            {{ $invCopy['intro_hint'] ?? 'Toca la tapa para abrir el libro' }}
        </button>
    </div>
</article>
