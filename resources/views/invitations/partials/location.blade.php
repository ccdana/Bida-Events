@php
    $lat      = $ubicacion['lat']  ?? null;
    $lng      = $ubicacion['lng']  ?? null;
    $hasCoords = $lat !== null && $lng !== null;
    $mapEmbed  = $hasCoords
        ? "https://maps.google.com/maps?q={$lat},{$lng}&z=15&output=embed"
        : null;
    $mapsNavUrl = $ubicacion['maps_url'] ?? ($hasCoords
        ? "https://www.google.com/maps?q={$lat},{$lng}"
        : '#');
    $imageUrl  = $ubicacion['imagen_lugar'] ?? null;
    $placeName = $ubicacion['nombre_lugar'] ?? null;
    $address   = $ubicacion['direccion']    ?? null;
    $nota      = $ubicacion['nota']         ?? null;
@endphp
<section class="invitation-section reveal invitation-location" id="ubicacion">
    <div class="section-inner-wide">
        <div class="invitation-location__shell">
            @include('invitations.partials.lottie-framed-icon', ['name' => 'invitation'])
            <p class="invitation-location__eyebrow">¿Dónde nos vemos?</p>
            <h2 class="invitation-location__title">Ubicación</h2>
            <div class="invitation-location__rule" aria-hidden="true"></div>

            @if($placeName)
                <p class="invitation-location__place">{{ $placeName }}</p>
            @endif
            @if($address)
                <p class="invitation-location__address">{{ $address }}</p>
            @endif
        </div>

        {{-- Layout dividido: foto + mapa lado a lado en desktop, apilados en mobile --}}
        <div class="invitation-location__stage">

            {{-- Columna izquierda: imagen del lugar (si hay) --}}
            @if($imageUrl)
                <div class="invitation-location__photo">
                    <div class="invitation-location__photo-frame">
                        <img
                            src="{{ $imageUrl }}"
                            alt="{{ $placeName ?? 'Lugar del evento' }}"
                            class="invitation-location__photo-img"
                            loading="lazy"
                        >
                        <div class="invitation-location__photo-caption" aria-hidden="true">
                            <span class="invitation-location__photo-label">El lugar</span>
                            @if($placeName)
                                <strong class="invitation-location__photo-name">{{ $placeName }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Columna derecha: mapa de Google --}}
            @if($mapEmbed)
                <div class="invitation-location__map {{ $imageUrl ? '' : 'invitation-location__map--full' }}">
                    <div class="invitation-location__map-frame">
                        <a
                            href="{{ $mapsNavUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="invitation-location__map-link"
                            aria-label="Abrir en Google Maps"
                        >
                            Abrir en Maps&nbsp;↗
                        </a>
                        <iframe
                            src="{{ $mapEmbed }}"
                            width="100%"
                            height="100%"
                            style="border:0"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Mapa del evento"
                            class="invitation-location__iframe"
                        ></iframe>
                    </div>
                </div>
            @endif
        </div>

        {{-- Nota adicional --}}
        @if($nota)
            <p class="invitation-location__nota">{{ $nota }}</p>
        @endif
    </div>
</section>
