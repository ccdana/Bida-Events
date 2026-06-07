<section class="invitation-section reveal" x-data="galleryCarousel(@js($galeria['fotos']))" x-init="startAutoplay()">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Momentos especiales</span>
            <h2 class="section-title">{{ $galeria['titulo'] ?? 'Galería' }}</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="relative mx-auto max-w-sm">
            <div class="gallery-carousel h-72 sm:h-80 touch-pan-y"
                @touchstart="onTouchStart($event)"
                @touchend="onTouchEnd($event)"
                @mousedown="onTouchStart($event)"
                @mouseup="onTouchEnd($event)">
                <template x-for="(photo, index) in photos" :key="index">
                    <div class="gallery-slide" :class="slideClass(index)">
                        <img :src="photo" :alt="'Foto ' + (index + 1)" class="w-full h-full object-cover" loading="lazy" draggable="false">
                        <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/25 to-transparent pointer-events-none"></div>
                    </div>
                </template>
            </div>

            <div class="flex items-center justify-center gap-4 mt-6">
                <button type="button" @click="prev()" aria-label="Anterior"
                    class="w-9 h-9 rounded-full inv-card-soft flex items-center justify-center text-primary active:scale-95 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 18l-6-6 6-6" stroke-linecap="round"/></svg>
                </button>
                <div class="flex gap-1.5">
                    <template x-for="(_, i) in photos" :key="'dot-'+i">
                        <button type="button" @click="goTo(i)"
                            class="h-1.5 rounded-full transition-all duration-500 ease-out"
                            :class="i === current ? 'bg-primary w-6' : 'bg-primary/25 w-1.5'"></button>
                    </template>
                </div>
                <button type="button" @click="next()" aria-label="Siguiente"
                    class="w-9 h-9 rounded-full inv-card-soft flex items-center justify-center text-primary active:scale-95 transition-transform">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" stroke-linecap="round"/></svg>
                </button>
            </div>
            <p class="text-center text-[10px] uppercase tracking-widest opacity-40 mt-4">
                <span x-text="(current + 1) + ' / ' + photos.length"></span>
            </p>
        </div>
    </div>
</section>
<script>
function galleryCarousel(photos) {
    return {
        photos,
        current: 0,
        direction: 1,
        startX: 0,
        autoplayTimer: null,
        slideClass(index) {
            if (index === this.current) return 'is-active';
            const len = this.photos.length;
            const prev = (this.current - 1 + len) % len;
            const next = (this.current + 1) % len;
            if (index === prev && this.direction === -1) return 'is-prev';
            if (index === next && this.direction === 1) return 'is-next';
            return 'is-hidden';
        },
        goTo(i) {
            this.direction = i > this.current ? 1 : -1;
            this.current = i;
            this.resetAutoplay();
        },
        next() {
            this.direction = 1;
            this.current = (this.current + 1) % this.photos.length;
            this.resetAutoplay();
        },
        prev() {
            this.direction = -1;
            this.current = (this.current - 1 + this.photos.length) % this.photos.length;
            this.resetAutoplay();
        },
        onTouchStart(e) {
            this.startX = (e.changedTouches ? e.changedTouches[0] : e).clientX;
        },
        onTouchEnd(e) {
            const endX = (e.changedTouches ? e.changedTouches[0] : e).clientX;
            const diff = endX - this.startX;
            if (Math.abs(diff) < 40) return;
            diff < 0 ? this.next() : this.prev();
        },
        startAutoplay() {
            if (this.photos.length <= 1) return;
            this.autoplayTimer = setInterval(() => this.next(), 5000);
        },
        resetAutoplay() {
            clearInterval(this.autoplayTimer);
            this.startAutoplay();
        },
    };
}
</script>
