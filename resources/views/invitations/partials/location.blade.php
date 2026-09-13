@php
    $lat = $ubicacion['lat'] ?? null;
    $lng = $ubicacion['lng'] ?? null;
    $hasCoords = is_numeric($lat) && is_numeric($lng);
    $mapEmbed = $hasCoords ? "https://maps.google.com/maps?q={$lat},{$lng}&z=15&output=embed" : null;
    $mapsNavUrl = $ubicacion['maps_url'] ?? ($hasCoords ? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}" : null);
    $wazeUrl = $hasCoords ? "https://waze.com/ul?ll={$lat},{$lng}&navigate=yes" : null;
    $imageUrl = $ubicacion['imagen_lugar'] ?? null;
    $placeName = $ubicacion['nombre_lugar'] ?? null;
    $address = $ubicacion['direccion'] ?? null;
    $nota = $ubicacion['nota'] ?? null;
@endphp

<section class="inv-section reveal inv-location" id="ubicacion">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'location',
            'eyebrow' => '¿Dónde nos vemos?',
            'title' => $placeName ?: 'Ubicación',
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
                            alt="{{ $placeName ?? 'Lugar del evento' }}"
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
                            title="Mapa de {{ $placeName ?? 'la ubicación del evento' }}"
                            allowfullscreen
                        ></iframe>
                    </div>
                @endif
            </div>
        @endif

        @if($mapsNavUrl || $wazeUrl)
            <div class="inv-actions inv-actions--split">
                @if($mapsNavUrl)
                    <a href="{{ $mapsNavUrl }}" target="_blank" rel="noopener" class="inv-btn">Cómo llegar</a>
                @endif
                @if($wazeUrl)
                    <a href="{{ $wazeUrl }}" target="_blank" rel="noopener" class="inv-btn inv-btn--ghost">Abrir en Waze</a>
                @endif
            </div>
        @endif

        @if($agendar ?? false)
            <div class="inv-location__calendar">
                <button type="button" class="inv-link" data-url="{{ $calendarUrl }}" onclick="openCalendar(this.dataset.url)">
                    Agregar la fecha a mi calendario
                </button>
            </div>
        @endif

        @if($nota)
            <p class="inv-help inv-location__note">{{ $nota }}</p>
        @endif
    </div>
</section>
