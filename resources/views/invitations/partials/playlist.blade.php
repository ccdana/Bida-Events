<section class="inv-section reveal inv-playlist" id="playlist" x-data="playlistApp(@js($slug), @js($guestToken), @js($songs ?? []), @js($isPreview ?? false))" x-init="init()">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'compact' => true,
            'lottie' => 'music',
            'eyebrow' => $invCopy['playlist_eyebrow'] ?? 'Colabora con la fiesta',
            'title' => $playlist['titulo'] ?? 'Playlist colaborativa',
            'intro' => $playlist['descripcion'] ?? 'Sugiere la canción que no puede faltar en la pista.',
        ])

        <form class="inv-playlist__form" data-needs-js @submit.prevent="submit">
            <label class="inv-label" for="playlist-song">{{ $invCopy['playlist_label'] ?? 'Tu canción' }}</label>
            <div class="inv-playlist__row">
                <input id="playlist-song" type="text" class="inv-input" x-model="song" maxlength="200" autocomplete="off"
                    placeholder="{{ $playlist['placeholder'] ?? 'Ej. Vivir mi vida – Marc Anthony' }}">
                <button type="submit" class="inv-btn" :disabled="submitting || !song.trim()">
                    <span x-text="submitting ? 'Enviando…' : @js($invCopy['playlist_button'] ?? 'Sugerir')">{{ $invCopy['playlist_button'] ?? 'Sugerir' }}</span>
                </button>
            </div>
            <p class="inv-help inv-playlist__help">{{ $invCopy['playlist_help'] ?? 'Escribe el nombre y el artista, o pega un enlace de YouTube.' }}</p>
        </form>

        <noscript>
            <p class="inv-noscript">Para sugerir una canción necesitas activar JavaScript en tu navegador.</p>
        </noscript>

        <p class="inv-status" :class="{ 'is-error': error }" x-text="message" aria-live="polite"></p>

        <div class="inv-playlist__head">
            <span class="inv-label" x-text="songs.length === 1 ? '1 canción sugerida' : songs.length + ' canciones sugeridas'">{{ $invCopy['playlist_list_title'] ?? 'Canciones sugeridas' }}</span>
            <button type="button" class="inv-link" @click="refresh()">Actualizar</button>
        </div>

        <ol class="inv-list" x-show="songs.length" x-cloak>
            <template x-for="(item, i) in pagedSongs" :key="item.id">
                <li>
                    <div class="inv-playlist__song">
                        <span class="inv-playlist__num" x-text="String(page * perPage + i + 1).padStart(2, '0')"></span>
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

        <nav class="inv-pager" x-show="pageCount > 1" x-cloak aria-label="Páginas de canciones">
            <button type="button" class="inv-gallery__arrow" @click="goTo(page - 1)" :disabled="page === 0" aria-label="Canciones anteriores">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <p class="inv-pager__count" aria-live="polite">
                <span x-text="page + 1">1</span> de <span x-text="pageCount">1</span>
            </p>
            <button type="button" class="inv-gallery__arrow" @click="goTo(page + 1)" :disabled="page >= pageCount - 1" aria-label="Más canciones">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </nav>

        <p class="inv-empty" x-show="!songs.length" x-cloak>{{ $invCopy['playlist_empty'] ?? 'Sé la primera persona en sugerir una canción.' }}</p>
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
        // Canciones sugeridas en la muestra de la home: solo existen en este navegador
        localSongs: [],
        // Las canciones se muestran de 5 en 5; la más reciente va primero
        page: 0,
        perPage: 5,
        get pageCount() {
            return Math.max(1, Math.ceil(this.songs.length / this.perPage));
        },
        get pagedSongs() {
            const start = this.page * this.perPage;
            return this.songs.slice(start, start + this.perPage);
        },
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
                if (data.songs) {
                    this.songs = [...this.localSongs, ...data.songs];
                    this.page = Math.min(this.page, this.pageCount - 1);
                }
                if (!res.ok && data.message) this.notify(data.message, true);
            } catch (e) {
                this.notify('No se pudo cargar la playlist.', true);
            }
        },
        goTo(page) {
            this.page = Math.min(Math.max(page, 0), this.pageCount - 1);
            this.playingId = null;
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
                // Muestra de la home: la canción aparece en la lista solo en este navegador
                if (window.invDemo) {
                    await new Promise((resolve) => setTimeout(resolve, 400));
                    const text = this.song.trim();
                    const youtube = text.match(/(?:youtu\.be\/|[?&]v=|shorts\/|embed\/)([\w-]{11})/);
                    const item = { id: `muestra-${Date.now()}`, text, guest: 'Tú', at: 'ahora', is_youtube: !!youtube, youtube_id: youtube ? youtube[1] : null };
                    this.localSongs = [item, ...this.localSongs];
                    this.songs = [item, ...this.songs];
                    this.song = '';
                    this.page = 0;
                    this.notify('¡Listo! Tu canción ya está en la lista. Es una muestra: no se guarda.');
                    return;
                }

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
                    this.page = 0;
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
