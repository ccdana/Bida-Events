@php
    $address = urlencode($ubicacion['direccion'] ?? '');
    $lat = $ubicacion['lat'] ?? -16.5;
    $lng = $ubicacion['lng'] ?? -68.15;
    $mapEmbed = "https://maps.google.com/maps?q={$lat},{$lng}&z=15&output=embed";
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
            <div class="rounded-2xl overflow-hidden mb-5 aspect-[16/9] shadow-lg ring-2 ring-white/80 border border-primary/10">
                <img src="{{ $ubicacion['imagen_lugar'] }}" alt="{{ $ubicacion['nombre_lugar'] ?? 'Lugar del evento' }}" class="w-full h-full object-cover" loading="lazy">
            </div>
        @endif

        {{-- Mapa embebido --}}
        <div class="rounded-2xl overflow-hidden border border-primary/15 shadow-lg mb-6 aspect-[4/3] bg-stone-100 ring-2 ring-white/80">
            <iframe src="{{ $mapEmbed }}" width="100%" height="100%" style="border:0" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Mapa del evento" class="w-full h-full min-h-[220px]"></iframe>
        </div>

        {{-- Acciones principales --}}
        <div class="flex flex-col gap-3 mb-6">
            @if(!empty($ubicacion['maps_url']))
                <a href="{{ $ubicacion['maps_url'] }}" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 py-3.5 rounded-xl bg-primary text-white text-sm font-medium tracking-wide shadow-md active:scale-[0.98] transition-transform">
                    @include('invitations.partials.icon', ['name' => 'map-pin', 'class' => 'w-4 h-4', 'animated' => false])
                    Abrir en Google Maps
                </a>
            @endif
            @if($agendar ?? false)
                <a href="{{ $calendarUrl }}" target="_blank" rel="noopener"
                    class="flex items-center justify-center gap-2 py-3.5 rounded-xl border border-primary/30 text-sm active:scale-[0.98] transition-transform">
                    @include('invitations.partials.icon', ['name' => 'calendar', 'class' => 'w-4 h-4 text-primary', 'animated' => false])
                    Agendar en Calendar
                </a>
            @endif
        </div>

        {{-- Transporte con logos --}}
        @if($transporte ?? false)
            <div class="mt-8">
                <p class="text-[10px] uppercase tracking-[0.3em] text-center opacity-50 mb-4">Llegar en transporte</p>
                <div class="grid grid-cols-3 gap-3">
                    <a href="https://m.uber.com/ul/?action=setPickup&dropoff[latitude]={{ $lat }}&dropoff[longitude]={{ $lng }}&dropoff[nickname]=Evento"
                        class="flex flex-col items-center gap-2 py-4 px-2 rounded-2xl bg-stone-900 text-white active:scale-95 transition-transform">
                        <svg class="h-5" viewBox="0 0 64 20" fill="currentColor" aria-label="Uber">
                            <text x="0" y="16" font-family="system-ui,sans-serif" font-size="16" font-weight="600">Uber</text>
                        </svg>
                        <span class="text-[10px] opacity-70">Pedir viaje</span>
                    </a>
                    <a href="https://3.redirect.appmetrica.yandex.com/route?end-lat={{ $lat }}&end-lon={{ $lng }}"
                        class="flex flex-col items-center gap-2 py-4 px-2 rounded-2xl bg-[#FFCC00] text-stone-900 active:scale-95 transition-transform">
                        <svg class="h-5" viewBox="0 0 72 20" fill="currentColor" aria-label="Yango">
                            <text x="0" y="16" font-family="system-ui,sans-serif" font-size="16" font-weight="700">Yango</text>
                        </svg>
                        <span class="text-[10px] opacity-70">Solicitar</span>
                    </a>
                    <a href="indrive://order?destination={{ $address }}"
                        class="flex flex-col items-center gap-2 py-4 px-2 rounded-2xl bg-[#26A65B] text-white active:scale-95 transition-transform">
                        <svg class="h-5" viewBox="0 0 80 20" fill="currentColor" aria-label="inDrive">
                            <text x="0" y="16" font-family="system-ui,sans-serif" font-size="14" font-weight="600">inDrive</text>
                        </svg>
                        <span class="text-[10px] opacity-70">Negociar</span>
                    </a>
                </div>
            </div>
        @endif

        @if(!empty($ubicacion['nota']))
            <p class="text-xs text-center opacity-45 mt-8 leading-relaxed">{{ $ubicacion['nota'] }}</p>
        @endif
    </div>
</section>
