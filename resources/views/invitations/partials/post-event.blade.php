<section class="invitation-section reveal" id="post-evento">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Recuerdos oficiales</span>
            <h2 class="section-title">{{ $postEvento['titulo'] ?? 'Galería del Fotógrafo' }}</h2>
            <div class="section-ornament"></div>
            @if(!empty($postEvento['descripcion']))
                <p class="text-sm opacity-60 mt-2 max-w-xs mx-auto">{{ $postEvento['descripcion'] }}</p>
            @endif
        </header>

        @if(!empty($postEvento['fotos']))
            <div class="photo-grid max-w-md mx-auto">
                @foreach($postEvento['fotos'] as $i => $foto)
                    <a href="{{ $foto }}" target="_blank" rel="noopener"
                        class="photo-grid-item block {{ $i % 5 === 4 ? 'col-span-2' : '' }}">
                        <img src="{{ $foto }}" alt="Foto oficial {{ $i + 1 }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" loading="lazy">
                    </a>
                @endforeach
            </div>
        @endif

        @if(!empty($postEvento['enlace_externo']))
            <div class="text-center {{ !empty($postEvento['fotos']) ? 'mt-6' : '' }}">
                <a href="{{ $postEvento['enlace_externo'] }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-primary text-white text-sm font-medium shadow-lg active:scale-[0.98] transition-transform">
                    Ver galería completa
                </a>
            </div>
        @elseif(empty($postEvento['fotos']))
            <div class="py-12 rounded-2xl border-2 border-dashed border-primary/15 text-center">
                <p class="text-sm opacity-40">Las fotos oficiales se publicarán pronto</p>
            </div>
        @endif
    </div>
</section>
