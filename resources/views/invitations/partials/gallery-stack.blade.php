<section class="invitation-section reveal" x-data="galleryStack(@js($galeria['fotos']))">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Momentos especiales</span>
            <h2 class="section-title">{{ $galeria['titulo'] ?? 'Galería' }}</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="relative h-72 sm:h-80 touch-pan-y mx-auto max-w-sm" @touchstart="touchStart($event)" @touchend="touchEnd($event)">
            <template x-for="(photo, index) in photos" :key="index">
                <div class="card-stack-item absolute inset-0 rounded-2xl overflow-hidden shadow-xl border-[3px] border-white"
                    :style="stackStyle(index)">
                    <img :src="photo" :alt="'Foto ' + (index+1)" class="w-full h-full object-cover" loading="lazy">
                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/30 to-transparent pointer-events-none"></div>
                </div>
            </template>
            <div class="absolute -bottom-6 left-0 right-0 flex justify-center gap-1.5">
                <template x-for="(_, i) in photos" :key="'dot-'+i">
                    <button type="button" @click="current = i"
                        class="h-1.5 rounded-full transition-all duration-300"
                        :class="i === current ? 'bg-primary w-5' : 'bg-primary/25 w-1.5'"></button>
                </template>
            </div>
        </div>
        <p class="text-center text-[10px] uppercase tracking-widest opacity-40 mt-10">Desliza para ver más ← →</p>
    </div>
</section>
<script>
function galleryStack(photos) {
    return {
        photos, current: 0, startX: 0,
        stackStyle(index) {
            const diff = index - this.current;
            if (diff < 0) return 'opacity:0;transform:translateX(-120%) rotate(-8deg);pointer-events:none';
            return `z-index:${10-diff};transform:translateX(${diff*10}px) translateY(${diff*5}px) rotate(${diff*2.5}deg) scale(${1-diff*0.04});opacity:${diff>2?0:1}`;
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
