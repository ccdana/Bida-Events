@php
    // La foto llega como URL suelta o como {url, alt}
    $fotos = collect($postEvento['fotos'] ?? [])
        ->map(fn ($foto) => is_array($foto)
            ? ['url' => $foto['url'] ?? null, 'alt' => trim((string) ($foto['alt'] ?? ''))]
            : ['url' => $foto, 'alt' => ''])
        ->filter(fn ($foto) => is_string($foto['url']) && $foto['url'] !== '')
        ->values()
        ->all();
    $externalLink = $postEvento['enlace_externo'] ?? '';
@endphp

<section class="inv-section reveal" id="post-evento">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'heart',
            'eyebrow' => $invCopy['post_eyebrow'] ?? 'Recuerdos oficiales',
            'title' => $postEvento['titulo'] ?? 'Galería del fotógrafo',
            'intro' => $postEvento['descripcion'] ?? null,
        ])

        @if(count($fotos))
            <div class="inv-mural">
                @foreach($fotos as $index => $foto)
                    <a href="{{ $foto['url'] }}" target="_blank" rel="noopener" class="inv-mural__item">
                        @php($postSrcset = \App\Support\CloudinaryImage::srcset($foto['url'], [320, 640, 960]))
                        <img src="{{ \App\Support\CloudinaryImage::url($foto['url'], 600) }}"
                            @if($postSrcset) srcset="{{ $postSrcset }}" sizes="(min-width: 768px) 18rem, 45vw" @endif
                            alt="{{ $foto['alt'] !== '' ? $foto['alt'] : 'Foto oficial '.($index + 1) }}" loading="lazy" decoding="async">
                    </a>
                @endforeach
            </div>
        @endif

        @if(!empty($externalLink))
            <div class="inv-actions">
                <a href="{{ $externalLink }}" target="_blank" rel="noopener" class="inv-btn inv-btn--block">{{ $invCopy['post_button'] ?? 'Ver galería completa' }}</a>
            </div>
        @elseif(!count($fotos))
            <p class="inv-empty">{{ $invCopy['post_empty'] ?? 'Las fotos oficiales se publicarán muy pronto.' }}</p>
        @endif
    </div>
</section>
