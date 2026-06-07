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

        <div class="inv-card overflow-hidden">
            <form @submit.prevent="submit" class="flex gap-2 p-4 border-b border-primary/10">
                <input type="text" x-model="song" placeholder="{{ $playlist['placeholder'] ?? 'Canción o link de YouTube' }}"
                    class="flex-1 rounded-xl border border-primary/20 px-4 py-3 text-sm inv-card-soft focus:border-primary focus:outline-none">
                <button type="submit" :disabled="submitting"
                    class="shrink-0 px-4 py-3 rounded-xl bg-primary text-white text-sm font-medium disabled:opacity-50 active:scale-95 transition-transform">
                    <span x-text="submitting ? '...' : 'Agregar'"></span>
                </button>
            </form>

            <div class="max-h-80 overflow-y-auto">
                <template x-if="songs.length === 0">
                    <p class="text-sm text-center opacity-40 py-10 px-4">Sé el primero en sugerir una canción</p>
                </template>
                <ul class="divide-y divide-primary/8">
                    <template x-for="(item, i) in songs" :key="item.id">
                        <li class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="togglePlay(item)"
                                    class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center transition-all"
                                    :class="playingId === item.id ? 'bg-primary text-white' : 'inv-card-soft text-primary'">
                                    <span x-show="playingId !== item.id">@include('invitations.partials.icon', ['name' => 'play', 'class' => 'w-4 h-4', 'animated' => false])</span>
                                    <span x-show="playingId === item.id" x-cloak>@include('invitations.partials.icon', ['name' => 'pause', 'class' => 'w-4 h-4', 'animated' => false])</span>
                                </button>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium leading-snug" x-text="item.text"></p>
                                    <p class="text-[10px] opacity-45 mt-0.5">
                                        <span x-show="item.guest" x-text="item.guest + ' · '" x-cloak></span>
                                        <span x-text="item.at"></span>
                                    </p>
                                </div>
                                <span class="shrink-0 w-6 h-6 rounded-md inv-card-soft text-primary flex items-center justify-center text-[10px] font-medium tabular-nums" x-text="i+1"></span>
                            </div>
                            <div x-show="playingId === item.id && item.is_youtube" x-cloak x-transition class="mt-3 rounded-xl overflow-hidden aspect-video inv-card-soft">
                                <iframe :src="'https://www.youtube.com/embed/' + item.youtube_id + '?autoplay=1&rel=0'"
                                    class="w-full h-full" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen loading="lazy" title="Reproductor YouTube"></iframe>
                            </div>
                            <div x-show="playingId === item.id && !item.is_youtube" x-cloak class="mt-2 text-xs text-primary/70 px-1">
                                <span x-text="item.text"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="px-4 py-2.5 border-t border-primary/10 flex items-center justify-between inv-card-soft">
                <p x-show="message" x-text="message" class="text-xs text-primary" x-cloak></p>
                <button type="button" @click="refresh()" class="text-[10px] uppercase tracking-wider opacity-40 hover:opacity-70 ml-auto">Actualizar</button>
            </div>
        </div>
    </div>
</section>
<script>
function playlistApp(slug, guestToken, initialSongs) {
    return {
        songs: initialSongs,
        song: '',
        message: '',
        submitting: false,
        playingId: null,
        init() { this.refresh(); },
        async refresh() {
            const res = await fetch(`/p/${slug}/playlist`);
            const data = await res.json();
            if (data.songs) this.songs = data.songs;
        },
        togglePlay(item) {
            if (this.playingId === item.id) {
                this.playingId = null;
                return;
            }
            this.playingId = item.id;
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
