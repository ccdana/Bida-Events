{{--
    Vista simple de los módulos de fotos del «Libro de aventuras» (collage, marcos, memoria) para
    plantillas que no son cuaderno: título y las fotos en una grilla. El cuaderno usa sus propias hojas
    (partials/aventura/pages). Parámetros: $data (titulo, fotos), sectionId, defaultTitle.
--}}
@php
    $cardPhotos = array_values(array_filter((array) ($data['fotos'] ?? []), fn ($photo) => is_string($photo) ? trim($photo) !== '' : ! empty($photo['url'] ?? null)));
@endphp

@if($cardPhotos !== [])
    <section class="inv-section reveal" id="{{ $sectionId }}">
        <div class="inv-wrap">
            @include('invitations.partials.section-header', ['compact' => true, 'title' => ($data['titulo'] ?? null) ?: $defaultTitle])
            <div class="inv-card-photos">
                @foreach($cardPhotos as $photo)
                    @php($photoUrl = is_array($photo) ? $photo['url'] : $photo)
                    <img src="{{ \App\Support\CloudinaryImage::url($photoUrl, 600) }}" alt="{{ is_array($photo) ? ($photo['alt'] ?? '') : '' }}" loading="lazy" decoding="async">
                @endforeach
            </div>
        </div>
    </section>
@endif
