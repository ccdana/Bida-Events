@php
    $galleryPhotos = collect($galeria['fotos'] ?? [])
        ->map(fn ($foto) => is_array($foto) ? ($foto['url'] ?? null) : $foto)
        ->filter(fn ($url) => is_string($url) && $url !== '')
        ->values();
    $galleryUrls = $galleryPhotos->map(fn ($url) => \App\Support\CloudinaryImage::url($url, 1200))->all();
    $gallerySrcsets = $galleryPhotos->map(fn ($url) => \App\Support\CloudinaryImage::srcset($url))->all();
    $galleryCount = count($galleryUrls);
@endphp

<section
    class="inv-section reveal inv-gallery"
    id="galeria"
    x-data="galleryStack(@js($galleryUrls), @js($gallerySrcsets))"
    x-init="init()"
>
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'eye-image',
            'eyebrow' => 'Momentos especiales',
            'title' => $galeria['titulo'] ?? 'Galería',
        ])

        @if($galleryCount > 0)
            <div class="inv-gallery__stage">
                <div
                    class="inv-gallery__stack"
                    x-ref="stack"
                    tabindex="0"
                    role="group"
                    aria-roledescription="carrusel"
                    aria-label="Galería de fotos. Desliza o usa las flechas para cambiar de foto."
                    @keydown.arrow-left.prevent="swipePrev()"
                    @keydown.arrow-right.prevent="swipeNext()"
                >
                    @foreach($galleryUrls as $index => $url)
                        <figure
                            class="inv-gallery__card {{ $index === 0 ? 'is-top' : '' }}"
                            data-gallery-card="{{ $index }}"
                            style="z-index: {{ $galleryCount - $index }}"
                        >
                            <img
                                src="{{ $url }}"
                                @if(!empty($gallerySrcsets[$index])) srcset="{{ $gallerySrcsets[$index] }}" sizes="(min-width: 1024px) 25rem, (min-width: 768px) 22rem, 80vw" @endif
                                alt="Foto {{ $index + 1 }} de {{ $galleryCount }}"
                                class="inv-gallery__image"
                                loading="{{ $index < 3 ? 'eager' : 'lazy' }}"
                                decoding="async"
                                draggable="false"
                            >
                        </figure>
                    @endforeach
                </div>

                @if($galleryCount > 1)
                    <p class="inv-gallery__hint" :class="{ 'is-hidden': interacted }">Desliza la foto hacia un lado para ver la siguiente</p>

                    <div class="inv-gallery__nav">
                        <button type="button" class="inv-gallery__arrow" @click="swipePrev()" aria-label="Foto anterior">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <p class="inv-gallery__counter" aria-live="polite">
                            <span x-text="current + 1">1</span> / {{ $galleryCount }}
                        </p>
                        <button type="button" class="inv-gallery__arrow" @click="swipeNext()" aria-label="Foto siguiente">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                @endif
            </div>
        @else
            <p class="inv-empty">Pronto compartiremos aquí las fotos.</p>
        @endif
    </div>
</section>
