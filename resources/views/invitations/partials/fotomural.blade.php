<section class="invitation-section reveal" x-data="fotomural('{{ $slug }}', '{{ $guestToken }}', @js($photos ?? []), @js($isPreview ?? false))" x-init="init()">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Recuerdos en vivo</span>
            <h2 class="section-title">Fotomural</h2>
            <div class="section-ornament"></div>
            <p class="text-sm opacity-60 mt-3 max-w-xs mx-auto">Comparte tus fotos del evento y míralas aquí al instante</p>
        </header>

        <div class="flex justify-center mb-8">
            <button type="button" @click="$refs.fileInput.click()" :disabled="uploading"
                class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl bg-primary text-white shadow-lg active:scale-[0.98] transition-all disabled:opacity-60">
                @include('invitations.partials.icon', ['name' => 'camera', 'class' => 'w-5 h-5', 'animated' => false])
                <span class="text-sm tracking-wide font-medium" x-text="uploading ? 'Subiendo...' : 'Compartir foto'"></span>
            </button>
            <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="upload">
        </div>

        <p x-show="message" x-text="message" x-transition
            class="text-sm text-center text-primary mb-6 px-4 py-2 rounded-xl bg-primary/5 border border-primary/10" x-cloak></p>

        <template x-if="photos.length > 0">
            <div>
                <p class="text-[10px] uppercase tracking-widest text-center opacity-40 mb-4">
                    <span x-text="photos.length"></span> <span x-text="photos.length === 1 ? 'foto compartida' : 'fotos compartidas'"></span>
                </p>
                <div class="photo-masonry">
                    <template x-for="(photo, i) in photos" :key="photo.id">
                        <div class="photo-masonry-item" :style="`animation-delay: ${Math.min(i * 0.05, 0.4)}s`">
                            <img :src="photo.url" :alt="photo.guest ? 'Foto de ' + photo.guest : 'Foto del evento'" loading="lazy">
                            <div x-show="photo.guest" class="absolute bottom-0 inset-x-0 px-3 py-2 bg-gradient-to-t from-black/60 to-transparent" x-cloak>
                                <p class="text-[10px] text-white/90 truncate" x-text="photo.guest"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <template x-if="photos.length === 0 && !loading">
            <div class="text-center py-10 rounded-2xl border-2 border-dashed border-primary/15">
                @include('invitations.partials.icon', ['name' => 'camera', 'class' => 'w-10 h-10 text-primary/30 mx-auto mb-3', 'animated' => false])
                <p class="text-sm opacity-40">Sé el primero en compartir una foto</p>
            </div>
        </template>
    </div>
</section>
<script>
function fotomural(slug, guestToken, initialPhotos, isPreview) {
    return {
        photos: initialPhotos,
        message: '',
        uploading: false,
        loading: false,
        init() {
            if (!isPreview) {
                this.refresh();
            }
        },
        async refresh() {
            if (isPreview) return;
            this.loading = true;
            try {
                const res = await fetch(`/p/${slug}/fotomural`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.photos) this.photos = data.photos;
                if (!res.ok && data.message) this.message = data.message;
            } catch (e) {
                this.message = 'No se pudo cargar el fotomural';
            } finally {
                this.loading = false;
            }
        },
        async upload(e) {
            if (isPreview) {
                this.message = 'El fotomural no está disponible en vista previa';
                return;
            }
            const file = e.target.files[0];
            if (!file) return;
            this.uploading = true;
            this.message = '';
            const fd = new FormData();
            fd.append('photo', file);
            if (guestToken) fd.append('guest_token', guestToken);
            try {
                const res = await fetch(`/p/${slug}/fotomural`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: fd
                });
                const data = await res.json();
                this.message = data.message || '¡Foto compartida!';
                if (data.success) await this.refresh();
            } catch (err) {
                this.message = 'No se pudo subir la foto';
            } finally {
                this.uploading = false;
                e.target.value = '';
            }
        }
    };
}
</script>
