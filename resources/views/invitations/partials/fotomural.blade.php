@php
    $readOnly = $readOnly ?? false;
@endphp
<section class="inv-section reveal inv-mural-section" id="fotomural" x-data="fotomural(@js($slug), @js($guestToken), @js($photos ?? []), @js($isPreview ?? false), @js($readOnly))" x-init="init()">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'lottie' => 'camera',
            'eyebrow' => 'Recuerdos en vivo',
            'title' => 'Fotomural',
            'intro' => $readOnly
                ? 'Las fotos que compartieron los invitados durante la fiesta.'
                : 'Toma o sube una foto durante la fiesta y aparecerá aquí para todos.',
        ])

        @unless($readOnly)
            <div class="inv-actions inv-mural__actions">
                <button type="button" class="inv-btn inv-btn--block" @click="$refs.fileInput.click()" :disabled="uploading">
                    @include('invitations.partials.icon', ['name' => 'camera', 'class' => 'inv-btn__icon', 'animated' => false])
                    <span x-text="uploading ? 'Subiendo foto…' : 'Compartir una foto'">Compartir una foto</span>
                </button>
                <input type="file" x-ref="fileInput" accept="image/*" class="sr-only" tabindex="-1" @change="upload">
                <p class="inv-help">Puedes usar la cámara o elegir una foto de tu galería.</p>
            </div>
        @endunless

        <p class="inv-status" :class="{ 'is-error': error }" x-text="message" aria-live="polite"></p>

        <div x-show="photos.length" x-cloak>
            <p class="inv-label inv-mural__count" x-text="photos.length === 1 ? '1 foto compartida' : photos.length + ' fotos compartidas'"></p>
            <div class="inv-mural">
                <template x-for="photo in photos" :key="photo.id">
                    <figure class="inv-mural__item">
                        <img :src="photo.url" :alt="photo.guest ? 'Foto de ' + photo.guest : 'Foto del evento'" loading="lazy" decoding="async">
                        <figcaption x-show="photo.guest" x-text="photo.guest"></figcaption>
                    </figure>
                </template>
            </div>
        </div>

        <p class="inv-empty" x-show="!photos.length && !loading" x-cloak>
            {{ $readOnly ? 'No se compartieron fotos en el fotomural.' : 'Aún no hay fotos. ¡Comparte la primera!' }}
        </p>
    </div>
</section>
<script>
function fotomural(slug, guestToken, initialPhotos, isPreview, readOnly) {
    return {
        photos: initialPhotos,
        message: '',
        error: false,
        uploading: false,
        loading: false,
        readOnly: !!readOnly,
        init() {
            if (!isPreview) {
                this.refresh();
            }
        },
        notify(message, isError = false) {
            this.message = message;
            this.error = isError;
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
                if (!res.ok && data.message) this.notify(data.message, true);
            } catch (e) {
                this.notify('No se pudo cargar el fotomural.', true);
            } finally {
                this.loading = false;
            }
        },
        async upload(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (isPreview || this.readOnly) {
                this.notify(isPreview ? 'El fotomural no está disponible en la vista previa.' : 'El fotomural ya no recibe fotos.', true);
                e.target.value = '';
                return;
            }

            this.uploading = true;
            this.notify('');
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
                const data = await res.json().catch(() => ({}));
                if (data.success) {
                    this.notify(data.message || '¡Gracias! Tu foto ya está en el mural.');
                    await this.refresh();
                } else {
                    this.notify(data.message || 'No se pudo subir la foto. Prueba con una imagen más liviana.', true);
                }
            } catch (err) {
                this.notify('Revisa tu conexión e intenta de nuevo.', true);
            } finally {
                this.uploading = false;
                e.target.value = '';
            }
        }
    };
}
</script>
