<section class="invitation-section relative z-10 text-center" x-data="fotomural('{{ $slug }}', '{{ $guestToken }}')">
    <div class="section-inner">
        <button type="button" @click="$refs.fileInput.click()"
            class="inline-flex items-center gap-3 px-8 py-4 rounded-2xl bg-primary text-white shadow-lg active:scale-[0.98] transition-transform">
            @include('invitations.partials.icon', ['name' => 'camera', 'class' => 'w-5 h-5', 'animated' => false])
            <span class="text-sm tracking-wide font-medium">Compartir foto en vivo</span>
        </button>
        <input type="file" x-ref="fileInput" accept="image/*" capture="environment" class="hidden" @change="upload">
        <p x-show="message" x-text="message" class="text-sm text-primary mt-5" x-cloak></p>
    </div>
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
            this.message = data.message || 'Foto enviada al fotomural';
        }
    };
}
</script>
