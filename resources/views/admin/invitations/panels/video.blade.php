<div x-show="activeTab === 'video'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Video</p>
                <h2 class="font-serif text-xl text-stone-950">Save the Date</h2>
                <p class="mt-1 text-sm text-stone-500">Un panel único para el video principal del evento.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.video" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
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
    </section>
</div>
