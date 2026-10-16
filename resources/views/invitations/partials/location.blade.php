@php
    $mapEmbed = \App\Support\MapsLinkParser::embedUrl($ubicacion);
    $mapsNavUrl = \App\Support\MapsLinkParser::directionsUrl($ubicacion);
    $imageUrl = $ubicacion['imagen_lugar'] ?? null;
    $placeName = $ubicacion['nombre_lugar'] ?? null;
    $address = $ubicacion['direccion'] ?? null;
    $nota = $ubicacion['nota'] ?? null;
@endphp

<section class="inv-section reveal inv-location" id="ubicacion">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'location',
            'eyebrow' => $invCopy['location_eyebrow'] ?? __('¿Dónde nos vemos?'),
            'title' => $placeName ?: ($invCopy['location_title'] ?? __('Ubicación')),
            'intro' => $address,
        ])

        @if($imageUrl || $mapEmbed)
            <div class="inv-location__media {{ $imageUrl && $mapEmbed ? 'has-both' : '' }}">
                @if($imageUrl)
                    @php($locationSrcset = \App\Support\CloudinaryImage::srcset($imageUrl))
                    <figure class="inv-location__photo">
                        <img
                            src="{{ \App\Support\CloudinaryImage::url($imageUrl, 1200) }}"
                            @if($locationSrcset) srcset="{{ $locationSrcset }}" sizes="(min-width: 768px) 40vw, 100vw" @endif
                            alt="{{ $placeName ?? __('Lugar del evento') }}"
                            loading="lazy"
                            decoding="async"
                        >
                    </figure>
                @endif

                @if($mapEmbed)
                    <div class="inv-location__map">
                        <iframe
                            src="{{ $mapEmbed }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ __('Mapa de :value', ['value' => $placeName ?? __('la ubicación del evento')]) }}"
                            allowfullscreen
                        ></iframe>
                    </div>
                @endif
            </div>
        @endif

        @if($mapsNavUrl)
            <div class="inv-actions">
                <a href="{{ $mapsNavUrl }}" target="_blank" rel="noopener" class="inv-btn inv-btn--block">{{ $invCopy['location_button'] ?? __('Cómo llegar') }}</a>
            </div>
        @endif

        @if($nota)
            <p class="inv-help inv-location__note">{{ $nota }}</p>
        @endif
    </div>
</section>
