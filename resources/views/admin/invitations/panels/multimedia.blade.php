<div x-show="activeTab === 'media'" x-cloak class="space-y-4">
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Video (Save the Date)</h2>
        <div>
            <label class="admin-label">Título</label>
            <input type="text" x-model="modules.video.titulo" class="admin-input">
        </div>
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Video vertical',
            'type' => 'video',
            'context' => 'video',
            'accept' => 'video/mp4,video/webm,video/quicktime',
            'previewExpr' => 'modules.video.video_url',
        ])
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Poster / miniatura',
            'type' => 'image',
            'context' => 'video-poster',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.video.poster',
        ])
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
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Archivo de audio (MP3)',
            'type' => 'audio',
            'context' => 'musica',
            'accept' => 'audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/aac,audio/mp4',
            'previewExpr' => 'modules.musica.audio_url',
        ])
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" x-model="modules.musica.autoplay" class="rounded border-stone-300 text-amber-600">
            Autoplay al abrir
        </label>
    </div>
</div>
