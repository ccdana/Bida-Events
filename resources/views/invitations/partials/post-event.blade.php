<section class="invitation-section py-16 px-6 z-10 relative">
    <div class="max-w-lg mx-auto text-center">
        <h2 class="font-title text-2xl text-primary mb-4">{{ $postEvento['titulo'] ?? 'Galería del Fotógrafo' }}</h2>
        <p class="text-sm opacity-70 mb-8">{{ $postEvento['descripcion'] ?? '' }}</p>

        @if(!empty($postEvento['fotos']))
            <div class="grid grid-cols-2 gap-3">
                @foreach($postEvento['fotos'] as $foto)
                    <a href="{{ $foto }}" target="_blank" class="block rounded-xl overflow-hidden aspect-square">
                        <img src="{{ $foto }}" alt="Foto oficial" class="w-full h-full object-cover" loading="lazy">
                    </a>
                @endforeach
            </div>
        @elseif(!empty($postEvento['enlace_externo']))
            <a href="{{ $postEvento['enlace_externo'] }}" target="_blank" class="inline-block px-6 py-3 rounded-full border border-primary text-primary text-sm">
                Ver galería completa
            </a>
        @else
            <div class="py-16 rounded-2xl border-2 border-dashed border-primary/20">
                <p class="text-sm opacity-50">Las fotos oficiales se publicarán pronto ✨</p>
            </div>
        @endif
    </div>
</section>
