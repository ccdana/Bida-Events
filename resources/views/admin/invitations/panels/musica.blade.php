<div x-show="activeTab === 'musica'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Música</p>
                <h2 class="font-serif text-xl text-stone-950">Audio de fondo</h2>
                <p class="mt-1 text-sm text-stone-500">Controla la pista musical sin mezclarla con otros módulos.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.musica" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
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
    </section>
</div>
