<section class="invitation-section reveal" x-data="playlistApp('{{ $slug }}', '{{ $guestToken }}', @js($songs ?? []))" x-init="init()">
    <div class="section-inner-wide">
        <header class="section-header">
            @include('invitations.partials.icon', ['name' => 'music', 'class' => 'w-8 h-8 text-primary mx-auto mb-3'])
            <span class="section-eyebrow">Colabora con la fiesta</span>
            <h2 class="section-title">{{ $playlist['titulo'] ?? 'Playlist Colaborativa' }}</h2>
            <div class="section-ornament"></div>
            @if(!empty($playlist['descripcion']))
                <p class="text-sm opacity-60 mt-2 max-w-xs mx-auto">{{ $playlist['descripcion'] }}</p>
            @endif
        </header>

        <div class="rounded-2xl border border-primary/15 bg-white/60 backdrop-blur-sm overflow-hidden">
            {{-- Formulario --}}
            <form @submit.prevent="submit" class="flex gap-2 p-4 border-b border-primary/10">
                <input type="text" x-model="song" placeholder="{{ $playlist['placeholder'] ?? 'Canción o link de YouTube' }}"
                    class="flex-1 rounded-xl border border-primary/20 px-4 py-3 text-sm bg-white/80 focus:border-primary focus:outline-none">
                <button type="submit" :disabled="submitting"
                    class="shrink-0 px-4 py-3 rounded-xl bg-primary text-white text-sm font-medium disabled:opacity-50 active:scale-95 transition-transform">
                    <span x-text="submitting ? '...' : 'Agregar'"></span>
                </button>
            </form>

            {{-- Lista de canciones --}}
            <div class="max-h-64 overflow-y-auto">
                <template x-if="songs.length === 0">
                    <p class="text-sm text-center opacity-40 py-10 px-4">Sé el primero en sugerir una canción</p>
                </template>
                <ul class="divide-y divide-primary/5">
                    <template x-for="(item, i) in songs" :key="item.id">
                        <li class="flex items-center gap-3 px-4 py-3.5 hover:bg-primary/5 transition-colors">
                            <span class="shrink-0 w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center text-xs font-medium tabular-nums" x-text="i+1"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate" x-text="item.text"></p>
                                <p class="text-[10px] opacity-45 mt-0.5">
                                    <span x-show="item.guest" x-text="item.guest + ' · '" x-cloak></span>
                                    <span x-text="item.at"></span>
                                </p>
                            </div>
                            @include('invitations.partials.icon', ['name' => 'music', 'class' => 'w-4 h-4 text-primary/40 shrink-0', 'animated' => false])
                        </li>
                    </template>
                </ul>
            </div>

            <div class="px-4 py-2 border-t border-primary/10 flex items-center justify-between">
                <p x-show="message" x-text="message" class="text-xs text-primary" x-cloak></p>
                <button type="button" @click="refresh()" class="text-[10px] uppercase tracking-wider opacity-40 hover:opacity-70 ml-auto">Actualizar</button>
            </div>
        </div>
    </div>
</section>
<script>
function playlistApp(slug, guestToken, initialSongs) {
    return {
        songs: initialSongs, song: '', message: '', submitting: false,
        init() { this.refresh(); },
        async refresh() {
            const res = await fetch(`/p/${slug}/playlist`);
            const data = await res.json();
            if (data.songs) this.songs = data.songs;
        },
        async submit() {
            if (!this.song.trim() || this.submitting) return;
            this.submitting = true;
            const res = await fetch(`/p/${slug}/playlist`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ content_text: this.song, guest_token: guestToken || null })
            });
            const data = await res.json();
            this.submitting = false;
            if (data.success) {
                this.message = data.message;
                this.song = '';
                await this.refresh();
            }
        }
    };
}
</script>
