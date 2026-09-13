@php
    $fotos = array_values(array_filter($postEvento['fotos'] ?? [], fn ($foto) => is_string($foto) && $foto !== ''));
    $externalLink = $postEvento['enlace_externo'] ?? '';
@endphp

<section class="inv-section reveal" id="post-evento">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'heart',
            'eyebrow' => 'Recuerdos oficiales',
            'title' => $postEvento['titulo'] ?? 'Galería del fotógrafo',
            'intro' => $postEvento['descripcion'] ?? null,
        ])

        @if(count($fotos))
            <div class="inv-mural">
                @foreach($fotos as $index => $foto)
                    <a href="{{ $foto }}" target="_blank" rel="noopener" class="inv-mural__item">
                        <img src="{{ \App\Support\CloudinaryImage::url($foto, 600) }}" alt="Foto oficial {{ $index + 1 }}" loading="lazy" decoding="async">
                    </a>
                @endforeach
            </div>
        @endif

        @if(!empty($externalLink))
            <div class="inv-actions">
                <a href="{{ $externalLink }}" target="_blank" rel="noopener" class="inv-btn inv-btn--block">Ver galería completa</a>
            </div>
        @elseif(!count($fotos))
            <p class="inv-empty">Las fotos oficiales se publicarán muy pronto.</p>
        @endif
    </div>
</section>
