<section class="invitation-section py-12 px-6 z-10 relative text-center" x-data="fotomural('{{ $slug }}', '{{ $guestToken }}')">
    <button @click="$refs.fileInput.click()"
        class="inline-flex items-center gap-3 px-8 py-4 rounded-full bg-primary text-white shadow-lg">
        <span class="text-xl">📷</span>
        <span class="text-sm tracking-wide">Compartir foto en vivo</span>
    </button>
    <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="upload">
    <p x-show="message" x-text="message" class="text-sm text-primary mt-4"></p>
</section>
<script>
function fotomural(slug, guestToken) {
    return {
        message: '',
        async upload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append('photo', file);
            if (guestToken) fd.append('guest_token', guestToken);
            const res = await fetch(`/p/${slug}/fotomural`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: fd
            });
            const data = await res.json();
            this.message = data.message || '¡Foto enviada!';
        }
    };
}
</script>
