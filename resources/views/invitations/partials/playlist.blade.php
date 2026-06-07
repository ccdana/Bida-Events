<section class="invitation-section py-16 px-6 z-10 relative" x-data="playlistForm('{{ $slug }}', '{{ $guestToken }}')">
    <div class="max-w-md mx-auto rounded-2xl border border-primary/20 bg-white/60 backdrop-blur p-6">
        <h2 class="font-title text-xl text-primary mb-2">{{ $playlist['titulo'] ?? 'Playlist' }}</h2>
        <p class="text-sm opacity-70 mb-4">{{ $playlist['descripcion'] ?? '' }}</p>
        <form @submit.prevent="submit" class="flex gap-2">
            <input type="text" x-model="song" :placeholder="'{{ $playlist['placeholder'] ?? 'Canción o link YouTube' }}'"
                class="flex-1 rounded-xl border border-primary/30 px-4 py-3 text-sm bg-white/80">
            <button type="submit" class="px-4 py-3 rounded-xl bg-primary text-white text-sm">+</button>
        </form>
        <p x-show="message" x-text="message" class="text-sm text-primary mt-3"></p>
    </div>
</section>
<script>
function playlistForm(slug, guestToken) {
    return {
        song: '', message: '',
        async submit() {
            if (!this.song.trim()) return;
            const res = await fetch(`/p/${slug}/playlist`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ content_text: this.song, guest_token: guestToken || null })
            });
            const data = await res.json();
            this.message = data.message || '¡Enviado!';
            this.song = '';
        }
    };
}
</script>
