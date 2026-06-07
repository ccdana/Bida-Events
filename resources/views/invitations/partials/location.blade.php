@php
    $lat = $ubicacion['lat'] ?? -16.5;
    $lng = $ubicacion['lng'] ?? -68.15;
    $placeName = urlencode($ubicacion['nombre_lugar'] ?? 'Evento');
    $mapEmbed = "https://maps.google.com/maps?q={$lat},{$lng}&z=15&output=embed";
    $mapsNavUrl = $ubicacion['maps_url'] ?? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
    $uberUrl = "https://m.uber.com/ul/?action=setPickup&dropoff%5Blatitude%5D={$lat}&dropoff%5Blongitude%5D={$lng}&dropoff%5Bnickname%5D={$placeName}";
    $yangoUrl = "https://yango.com/route?end-lat={$lat}&end-lon={$lng}";
    $indriveDeep = "indriver://open/client/root?latitude={$lat}&longitude={$lng}";
@endphp
<section class="invitation-section reveal">
    <div class="section-inner-wide">
        <header class="section-header">
            @include('invitations.partials.icon', ['name' => 'map-pin', 'class' => 'w-8 h-8 text-primary mx-auto mb-3'])
            <span class="section-eyebrow">¿Dónde nos vemos?</span>
            <h2 class="section-title">Ubicación</h2>
            <div class="section-ornament"></div>
            @if(!empty($ubicacion['nombre_lugar']))
                <p class="font-title text-lg mt-3">{{ $ubicacion['nombre_lugar'] }}</p>
            @endif
            @if(!empty($ubicacion['direccion']))
                <p class="text-sm opacity-60 mt-2 leading-relaxed max-w-xs mx-auto">{{ $ubicacion['direccion'] }}</p>
            @endif
        </header>

        @if(!empty($ubicacion['imagen_lugar']))
            <div class="rounded-2xl overflow-hidden mb-5 aspect-[16/9] shadow-lg border border-primary/15">
                <img src="{{ $ubicacion['imagen_lugar'] }}" alt="{{ $ubicacion['nombre_lugar'] ?? 'Lugar del evento' }}" class="w-full h-full object-cover" loading="lazy">
            </div>
        @endif

        <div class="rounded-2xl overflow-hidden border border-primary/15 shadow-lg mb-5 aspect-[4/3] inv-card-soft">
            <iframe src="{{ $mapEmbed }}" width="100%" height="100%" style="border:0" allowfullscreen loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Mapa del evento" class="w-full h-full min-h-[220px]"></iframe>
        </div>

        <a href="{{ $mapsNavUrl }}" target="_blank" rel="noopener"
                class="flex items-center justify-center gap-2 py-3.5 rounded-xl bg-primary text-white text-sm font-medium tracking-wide shadow-md active:scale-[0.98] transition-transform mb-6">
                @include('invitations.partials.icon', ['name' => 'map-pin', 'class' => 'w-4 h-4', 'animated' => false])
                Abrir en Google Maps
            </a>

        @if($transporte ?? false)
            <div class="inv-card p-5">
                <p class="text-[10px] uppercase tracking-[0.3em] text-center text-primary/60 mb-4">Llegar en transporte</p>
                <div class="transport-grid">
                    <a href="{{ $uberUrl }}" target="_blank" rel="noopener"
                        class="transport-btn bg-[#000] text-white shadow-md">
                        <svg viewBox="0 0 48 16" class="h-4" fill="currentColor" aria-label="Uber">
                            <text x="0" y="13" font-family="system-ui,sans-serif" font-size="14" font-weight="600">Uber</text>
                        </svg>
                        <span>Pedir viaje</span>
                    </a>
                    <a href="{{ $yangoUrl }}" target="_blank" rel="noopener"
                        class="transport-btn bg-[#FFCC00] text-[#1a1a1a] shadow-md">
                        <svg viewBox="0 0 56 16" class="h-4" fill="currentColor" aria-label="Yango">
                            <text x="0" y="13" font-family="system-ui,sans-serif" font-size="14" font-weight="700">Yango</text>
                        </svg>
                        <span>Solicitar</span>
                    </a>
                    <a href="{{ $indriveDeep }}" target="_blank" rel="noopener"
                        class="transport-btn bg-[#26A65B] text-white shadow-md">
                        <svg viewBox="0 0 64 16" class="h-4" fill="currentColor" aria-label="inDrive">
                            <text x="0" y="13" font-family="system-ui,sans-serif" font-size="13" font-weight="600">inDrive</text>
                        </svg>
                        <span>Negociar</span>
                    </a>
                </div>
                <p class="text-[10px] text-center opacity-40 mt-3">Se abrirá la app si la tienes instalada</p>
            </div>
        @endif

        @if(!empty($ubicacion['nota']))
            <p class="text-xs text-center opacity-45 mt-6 leading-relaxed">{{ $ubicacion['nota'] }}</p>
        @endif
    </div>
</section>
