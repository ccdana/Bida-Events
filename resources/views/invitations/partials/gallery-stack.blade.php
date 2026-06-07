<section class="invitation-section py-16 px-6 z-10 relative" x-data="galleryStack(@js($galeria['fotos']))">
    <div class="max-w-sm mx-auto">
        <h2 class="font-title text-2xl text-center text-primary mb-8">{{ $galeria['titulo'] ?? 'Galería' }}</h2>
        <div class="relative h-80 touch-pan-y" @touchstart="touchStart($event)" @touchend="touchEnd($event)">
            <template x-for="(photo, index) in photos" :key="index">
                <div class="card-stack-item absolute inset-0 rounded-2xl overflow-hidden shadow-xl border-2 border-white"
                    :style="stackStyle(index)">
                    <img :src="photo" :alt="'Foto ' + (index+1)" class="w-full h-full object-cover" loading="lazy">
                </div>
            </template>
            <div class="absolute -bottom-8 left-0 right-0 flex justify-center gap-1.5">
                <template x-for="(_, i) in photos" :key="'dot-'+i">
                    <span class="w-1.5 h-1.5 rounded-full transition" :class="i === current ? 'bg-primary w-4' : 'bg-primary/30'"></span>
                </template>
            </div>
        </div>
        <p class="text-center text-xs opacity-50 mt-12">Desliza para ver más fotos ← →</p>
    </div>
</section>
<script>
function galleryStack(photos) {
    return {
        photos, current: 0, startX: 0,
        stackStyle(index) {
            const diff = index - this.current;
            if (diff < 0) return 'opacity:0;transform:translateX(-120%) rotate(-8deg);pointer-events:none';
            return `z-index:${10-diff};transform:translateX(${diff*8}px) translateY(${diff*4}px) rotate(${diff*2}deg) scale(${1-diff*0.03});opacity:${diff>2?0:1}`;
        },
        touchStart(e) { this.startX = e.changedTouches[0].screenX; },
        touchEnd(e) {
            const diff = e.changedTouches[0].screenX - this.startX;
            if (diff < -50 && this.current < this.photos.length - 1) this.current++;
            if (diff > 50 && this.current > 0) this.current--;
        }
    };
}
</script>
