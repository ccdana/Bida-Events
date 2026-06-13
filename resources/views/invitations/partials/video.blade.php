<section class="invitation-section reveal">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Save the date</span>
            <h2 class="section-title">{{ $video['titulo'] ?? 'Nuestro video' }}</h2>
            <div class="section-ornament"></div>
        </header>
        @if(!empty($video['video_url'] ?? null))
            <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-[9/16] max-w-xs mx-auto bg-black ring-4 ring-white/80">
                <video class="w-full h-full object-cover" controls playsinline poster="{{ $video['poster'] ?? '' }}">
                    <source src="{{ $video['video_url'] ?? '' }}" type="video/mp4">
                </video>
            </div>
        @else
            <div class="rounded-3xl border border-dashed border-primary/15 bg-white/60 px-6 py-10 text-center text-sm opacity-60 max-w-xs mx-auto">
                El video aún no tiene una URL configurada.
            </div>
        @endif
    </div>
</section>
