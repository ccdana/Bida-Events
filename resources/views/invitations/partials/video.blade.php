<section class="invitation-section reveal">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Save the date</span>
            <h2 class="section-title">{{ $video['titulo'] ?? 'Nuestro video' }}</h2>
            <div class="section-ornament"></div>
        </header>
        <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-[9/16] max-w-xs mx-auto bg-black ring-4 ring-white/80">
            <video class="w-full h-full object-cover" controls playsinline poster="{{ $video['poster'] ?? '' }}">
                <source src="{{ $video['video_url'] }}" type="video/mp4">
            </video>
        </div>
    </div>
</section>
