<section class="invitation-section py-16 px-6 z-10 relative">
    <div class="max-w-sm mx-auto">
        <h2 class="font-title text-2xl text-center text-primary mb-6">{{ $video['titulo'] ?? 'Save the Date' }}</h2>
        <div class="relative rounded-3xl overflow-hidden shadow-2xl aspect-[9/16] bg-black">
            <video class="w-full h-full object-cover" controls playsinline poster="{{ $video['poster'] ?? '' }}">
                <source src="{{ $video['video_url'] }}" type="video/mp4">
            </video>
        </div>
    </div>
</section>
