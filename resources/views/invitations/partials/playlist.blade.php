<section class="inv-section reveal inv-playlist" id="playlist" x-data="playlistApp(@js($slug), @js($guestToken), @js($songs ?? []), @js($isPreview ?? false))" x-init="init()">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'music',
            'eyebrow' => 'Colabora con la fiesta',
            'title' => $playlist['titulo'] ?? 'Playlist colaborativa',
            'intro' => $playlist['descripcion'] ?? 'Sugiere la canción que no puede faltar en la pista.',
        ])

        <form class="inv-playlist__form" @submit.prevent="submit">
            <label class="inv-label" for="playlist-song">Tu canción</label>
            <div class="inv-playlist__row">
                <input id="playlist-song" type="text" class="inv-input" x-model="song" maxlength="200" autocomplete="off"
                    placeholder="{{ $playlist['placeholder'] ?? 'Ej. Vivir mi vida – Marc Anthony' }}">
                <button type="submit" class="inv-btn" :disabled="submitting || !song.trim()">
                    <span x-text="submitting ? 'Enviando…' : 'Sugerir'">Sugerir</span>
                </button>
            </div>
            <p class="inv-help inv-playlist__help">Escribe el nombre y el artista, o pega un enlace de YouTube.</p>
        </form>

        <p class="inv-status" :class="{ 'is-error': error }" x-text="message" aria-live="polite"></p>

        <div class="inv-playlist__head">
            <span class="inv-label" x-text="songs.length === 1 ? '1 canción sugerida' : songs.length + ' canciones sugeridas'">Canciones sugeridas</span>
            <button type="button" class="inv-link" @click="refresh()">Actualizar</button>
        </div>

        <ol class="inv-list" x-show="songs.length" x-cloak>
            <template x-for="(item, i) in songs" :key="item.id">
                <li>
                    <div class="inv-playlist__song">
                        <span class="inv-playlist__num" x-text="String(i + 1).padStart(2, '0')"></span>
                        <div class="inv-playlist__meta">
                            <p class="inv-playlist__title" x-text="item.text"></p>
                            <p class="inv-playlist__by" x-text="[item.guest, item.at].filter(Boolean).join(' · ')"></p>
                        </div>
                        <template x-if="item.is_youtube">
                            <button type="button" class="inv-playlist__play" @click="togglePlay(item)"
                                :aria-label="playingId === item.id ? 'Cerrar reproductor' : 'Escuchar en YouTube'">
                                <span x-show="playingId !== item.id">@include('invitations.partials.icon', ['name' => 'play', 'animated' => false])</span>
                                <span x-show="playingId === item.id">@include('invitations.partials.icon', ['name' => 'close', 'animated' => false])</span>
                            </button>
                        </template>
                    </div>
                    <template x-if="playingId === item.id && item.is_youtube">
                        <div class="inv-playlist__embed">
                            <iframe :src="'https://www.youtube.com/embed/' + item.youtube_id + '?autoplay=1&rel=0'"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen title="Reproductor de YouTube"></iframe>
                        </div>
                    </template>
                </li>
            </template>
        </ol>

        <p class="inv-empty" x-show="!songs.length" x-cloak>Sé la primera persona en sugerir una canción.</p>
    </div>
</section>
<script>
function playlistApp(slug, guestToken, initialSongs, isPreview) {
    return {
        songs: initialSongs,
        song: '',
        message: '',
        error: false,
        submitting: false,
        playingId: null,
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
            try {
                const res = await fetch(`/p/${slug}/playlist`, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.songs) this.songs = data.songs;
                if (!res.ok && data.message) this.notify(data.message, true);
            } catch (e) {
                this.notify('No se pudo cargar la playlist.', true);
            }
        },
        togglePlay(item) {
            this.playingId = this.playingId === item.id ? null : item.id;
        },
        async submit() {
            if (isPreview) {
                this.notify('La playlist no está disponible en la vista previa.', true);
                return;
            }
            if (!this.song.trim() || this.submitting) return;
            this.submitting = true;
            this.notify('');
            try {
                const res = await fetch(`/p/${slug}/playlist`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ content_text: this.song, guest_token: guestToken || null })
                });
                const data = await res.json().catch(() => ({}));
                if (data.success) {
                    this.song = '';
                    this.notify(data.message || '¡Gracias! Tu canción ya está en la lista.');
                    await this.refresh();
                } else {
                    this.notify(data.message || 'No se pudo enviar la canción.', true);
                }
            } catch (e) {
                this.notify('Revisa tu conexión e intenta de nuevo.', true);
            } finally {
                this.submitting = false;
            }
        }
    };
}
</script>
