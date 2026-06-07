<div x-show="activeTab === 'media'" x-cloak class="space-y-4">
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Video (Save the Date)</h2>
        <div>
            <label class="admin-label">Título</label>
            <input type="text" x-model="modules.video.titulo" class="admin-input">
        </div>
        <div>
            <label class="admin-label">URL del video</label>
            <input type="url" x-model="modules.video.video_url" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Poster / thumbnail</label>
            <input type="url" x-model="modules.video.poster" class="admin-input">
        </div>
    </div>
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Música de fondo</h2>
        <p class="text-xs text-stone-500">Activa el módulo "musica" en Estética. Los invitados pueden play/pause.</p>
        <div>
            <label class="admin-label">Título</label>
            <input type="text" x-model="modules.musica.titulo" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Artista / descripción</label>
            <input type="text" x-model="modules.musica.artista" class="admin-input">
        </div>
        <div>
            <label class="admin-label">URL del audio (MP3)</label>
            <input type="url" x-model="modules.musica.audio_url" class="admin-input">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" x-model="modules.musica.autoplay" class="rounded border-stone-300 text-amber-600">
            Autoplay al abrir
        </label>
    </div>
</div>
