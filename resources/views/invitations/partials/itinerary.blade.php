<section class="invitation-section py-16 px-6 z-10 relative">
    <div class="max-w-md mx-auto">
        <h2 class="font-title text-2xl text-center text-primary mb-10">{{ $itinerario['titulo'] ?? 'Itinerario' }}</h2>
        <div class="relative pl-8 border-l-2 border-primary/30 space-y-8">
            @foreach($itinerario['eventos'] ?? [] as $evento)
                <div class="relative">
                    <span class="timeline-dot absolute -left-[1.65rem] top-1 w-4 h-4 rounded-full bg-primary"></span>
                    <span class="text-2xl absolute -left-10 top-0">{{ $evento['icono'] ?? '✨' }}</span>
                    <p class="text-xs text-primary tracking-wider">{{ $evento['hora'] }}</p>
                    <p class="font-title text-lg">{{ $evento['titulo'] }}</p>
                    @if(!empty($evento['descripcion']))
                        <p class="text-sm opacity-60 mt-1">{{ $evento['descripcion'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
