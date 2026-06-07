<section class="invitation-section py-16 px-6 z-10 relative" x-data="{ tab: 'permitidos' }">
    <div class="max-w-md mx-auto text-center">
        <h2 class="font-title text-2xl text-primary mb-2">{{ $dressCode['titulo'] ?? 'Dress Code' }}</h2>
        <p class="font-title text-lg mb-2">{{ $dressCode['estilo'] ?? '' }}</p>
        <p class="text-sm opacity-70 mb-8">{{ $dressCode['descripcion'] ?? '' }}</p>

        <div class="flex justify-center gap-4 mb-6 text-sm">
            <button @click="tab='permitidos'" :class="tab==='permitidos' ? 'text-primary border-b-2 border-primary' : 'opacity-50'">Permitidos</button>
            <button @click="tab='prohibidos'" :class="tab==='prohibidos' ? 'text-primary border-b-2 border-primary' : 'opacity-50'">Evitar</button>
        </div>

        <div class="flex justify-center gap-4 flex-wrap" x-show="tab==='permitidos'">
            @foreach($dressCode['colores_permitidos'] ?? [] as $color)
                <div class="text-center">
                    <div class="w-14 h-14 rounded-full border-2 border-white shadow-lg mx-auto" style="background: {{ $color['hex'] }}"></div>
                    <p class="text-xs mt-2">{{ $color['nombre'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="flex justify-center gap-4 flex-wrap" x-show="tab==='prohibidos'" x-cloak>
            @foreach($dressCode['colores_prohibidos'] ?? [] as $color)
                <div class="text-center">
                    <div class="w-14 h-14 rounded-full border-2 border-red-300 shadow-lg mx-auto relative" style="background: {{ $color['hex'] }}">
                        <span class="absolute inset-0 flex items-center justify-center text-red-600 text-xl font-bold">✕</span>
                    </div>
                    <p class="text-xs mt-2">{{ $color['nombre'] }}</p>
                    @if(!empty($color['motivo']))<p class="text-[10px] opacity-50">{{ $color['motivo'] }}</p>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>
