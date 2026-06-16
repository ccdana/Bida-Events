@php
    $lat = $ubicacion['lat'] ?? -16.5;
    $lng = $ubicacion['lng'] ?? -68.15;
    $placeName = urlencode($ubicacion['nombre_lugar'] ?? 'Evento');
    $mapEmbed = "https://maps.google.com/maps?q={$lat},{$lng}&z=15&output=embed";
    $mapsNavUrl = $ubicacion['maps_url'] ?? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}";
    
    // URLs mejoradas para apps de transporte
    $uberUrl = "https://m.uber.com/?action=setPickup&pickup%5Blatitude%5D=&pickup%5Blongitude%5D=&dropoff%5Blatitude%5D={$lat}&dropoff%5Blongitude%5D={$lng}&dropoff%5Bnickname%5D={$placeName}";
    $yangoUrl = "https://yango.com/route?start-lat=&start-lon=&end-lat={$lat}&end-lon={$lng}&end-address=" . urlencode($ubicacion['nombre_lugar'] ?? '');
    $indriveUrl = "https://indriver.com/search?latitude={$lat}&longitude={$lng}&address=" . urlencode($ubicacion['nombre_lugar'] ?? '');
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
                class="flex items-center justify-center gap-2 py-3.5 rounded-xl bg-primary text-white text-sm font-medium tracking-wide shadow-md active:scale-[0.98] transition-all duration-200 hover:shadow-lg mb-6">
                @include('invitations.partials.icon', ['name' => 'map-pin', 'class' => 'w-4 h-4', 'animated' => false])
                Abrir en Google Maps
            </a>

        @if($transporte ?? false)
            <div class="inv-card p-6">
                <p class="text-[10px] uppercase tracking-[0.3em] text-center text-primary/60 mb-6">¿Cómo llegar? Solicita tu viaje</p>
                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    <!-- Uber -->
                    <a href="{{ $uberUrl }}" target="_blank" rel="noopener"
                        class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-gradient-to-br from-black to-gray-800 text-white shadow-md hover:shadow-lg active:scale-[0.95] transition-all duration-200 group">
                        <svg class="w-8 h-8 group-hover:scale-110 transition-transform" viewBox="0 0 48 48" fill="currentColor">
                            <path d="M24 2C12.95 2 4 10.95 4 22c0 9.39 6.94 17.15 16 18.66V43h8v-2.34c9.06-1.51 16-9.27 16-18.66 0-11.05-8.95-20-20-20zm0 36c-8.84 0-16-7.16-16-16s7.16-16 16-16 16 7.16 16 16-7.16 16-16 16z"/>
                        </svg>
                        <span class="text-xs font-semibold text-center">Uber</span>
                    </a>
                    
                    <!-- Yango -->
                    <a href="{{ $yangoUrl }}" target="_blank" rel="noopener"
                        class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-500 text-black shadow-md hover:shadow-lg active:scale-[0.95] transition-all duration-200 group">
                        <svg class="w-8 h-8 group-hover:scale-110 transition-transform" viewBox="0 0 48 48" fill="currentColor">
                            <text x="6" y="32" font-family="Arial, sans-serif" font-size="24" font-weight="bold">Y</text>
                        </svg>
                        <span class="text-xs font-semibold text-center">Yango</span>
                    </a>
                    
                    <!-- inDriver -->
                    <a href="{{ $indriveUrl }}" target="_blank" rel="noopener"
                        class="flex flex-col items-center justify-center gap-3 p-4 rounded-xl bg-gradient-to-br from-green-500 to-green-600 text-white shadow-md hover:shadow-lg active:scale-[0.95] transition-all duration-200 group">
                        <svg class="w-8 h-8 group-hover:scale-110 transition-transform" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="24" cy="20" r="6"/>
                            <path d="M12 35c0-6.627 5.373-12 12-12s12 5.373 12 12"/>
                        </svg>
                        <span class="text-xs font-semibold text-center">inDriver</span>
                    </a>
                </div>
                <p class="text-[10px] text-center opacity-40 mt-4">Se abrirá la app si la tienes instalada, o el navegador</p>
            </div>
        @endif

        @if(!empty($ubicacion['nota']))
            <p class="text-xs text-center opacity-45 mt-6 leading-relaxed">{{ $ubicacion['nota'] }}</p>
        @endif
    </div>
</section>
