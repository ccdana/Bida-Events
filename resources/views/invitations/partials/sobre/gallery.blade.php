{{--
    Galería de la tarjeta «Sobre lacrado»: polaroids pegadas sobre el fondo, unidas por un camino de
    puntos con corazones. Al tocar una polaroid se levanta y se ve en grande; tocarla otra vez la baja.
    Sin JavaScript es una cuadrícula de fotos. Recibe $data (titulo, fotos).
--}}
@php
    $polaroids = collect($data['fotos'] ?? [])
        ->map(fn ($foto) => is_array($foto)
            ? ['url' => $foto['url'] ?? null, 'alt' => trim((string) ($foto['alt'] ?? ''))]
            : ['url' => $foto, 'alt' => ''])
        ->filter(fn ($foto) => is_string($foto['url']) && $foto['url'] !== '')
        ->values();
@endphp

<section class="inv-section reveal inv-gallery inv-polaroids" id="galeria">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'eyebrow' => $invCopy['gallery_eyebrow'] ?? 'Nuestros momentos',
            'title' => ($data['titulo'] ?? null) ?: ($invCopy['gallery_title'] ?? 'Galería'),
        ])

        @if($polaroids->isNotEmpty())
            <ul class="inv-polaroids__grid" x-data="{ lifted: null }" data-story-ignore>
                @foreach($polaroids as $index => $photo)
                    <li class="inv-polaroids__item" style="--i: {{ $index }}; --lean: {{ [-4, 3, -2, 5, -3, 2][$index % 6] }}deg"
                        :class="{ 'is-lifted': lifted === {{ $index }} }">
                        <button type="button" class="inv-polaroids__card"
                            :aria-pressed="(lifted === {{ $index }}).toString()"
                            @click="lifted = lifted === {{ $index }} ? null : {{ $index }}">
                            <img src="{{ \App\Support\CloudinaryImage::url($photo['url'], 600) }}"
                                alt="{{ $photo['alt'] !== '' ? $photo['alt'] : 'Recuerdo '.($index + 1) }}"
                                loading="{{ $index < 4 ? 'eager' : 'lazy' }}" decoding="async" draggable="false">
                            @if($photo['alt'] !== '')
                                <span class="inv-polaroids__caption">{{ $photo['alt'] }}</span>
                            @endif
                        </button>
                    </li>
                @endforeach
            </ul>

            @if($polaroids->count() > 1)
                <p class="inv-polaroids__hint">{{ $invCopy['gallery_hint'] ?? 'Toca una polaroid para verla en grande' }}</p>
            @endif
        @endif
    </div>
</section>
