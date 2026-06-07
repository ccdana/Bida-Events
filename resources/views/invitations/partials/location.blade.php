@php
    $address = urlencode($ubicacion['direccion'] ?? '');
    $lat = $ubicacion['lat'] ?? -16.5;
    $lng = $ubicacion['lng'] ?? -68.15;
@endphp
<section class="invitation-section py-16 px-6 z-10 relative">
    <div class="max-w-md mx-auto text-center">
        <h2 class="font-title text-2xl text-primary mb-6">Ubicación</h2>
        <p class="font-title text-lg">{{ $ubicacion['nombre_lugar'] ?? '' }}</p>
        <p class="text-sm opacity-70 mt-2 mb-6">{{ $ubicacion['direccion'] ?? '' }}</p>

        @if(!empty($ubicacion['maps_url']))
            <a href="{{ $ubicacion['maps_url'] }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary text-white text-sm tracking-wide shadow-lg">
                📍 Abrir en Google Maps
            </a>
        @endif

        @if($agendar ?? false)
            <a href="{{ $calendarUrl }}" target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 px-6 py-3 mt-4 rounded-full border border-primary text-primary text-sm tracking-wide">
                📅 Agendar en Calendar
            </a>
        @endif

        @if($transporte ?? false)
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="https://m.uber.com/ul/?action=setPickup&dropoff[latitude]={{ $lat }}&dropoff[longitude]={{ $lng }}&dropoff[nickname]=Evento"
                    class="px-4 py-2 rounded-xl bg-black text-white text-xs">Uber</a>
                <a href="https://3.redirect.appmetrica.yandex.com/route?end-lat={{ $lat }}&end-lon={{ $lng }}"
                    class="px-4 py-2 rounded-xl bg-yellow-500 text-black text-xs">Yango</a>
                <a href="indrive://order?destination={{ $address }}"
                    class="px-4 py-2 rounded-xl bg-green-600 text-white text-xs">InDrive</a>
            </div>
        @endif

        @if(!empty($ubicacion['nota']))
            <p class="text-xs opacity-50 mt-6">{{ $ubicacion['nota'] }}</p>
        @endif
    </div>
</section>
