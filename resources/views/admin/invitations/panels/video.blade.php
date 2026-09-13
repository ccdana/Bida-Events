<div x-show="activeTab === 'video'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Video',
        'title' => 'Save the date',
        'description' => 'un reproductor con la miniatura como portada y un botón de play al centro; el video empieza al tocarlo.',
        'tip' => 'Sube siempre una miniatura: es lo que se ve mientras el video no se reproduce y hace que la sección cargue más rápido.',
        'moduleKey' => 'video',
    ])

    <!-- Información general -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Información general</p>
        <div>
            <label class="admin-label">Título</label>
            <input type="text" x-model="modules.video.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Nuestro Save the Date">
        </div>
    </section>

    <!-- Video principal -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Video principal</p>
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Archivo de video (MP4)',
            'type' => 'video',
            'context' => 'video',
            'accept' => 'video/mp4,video/webm,video/quicktime',
            'previewExpr' => 'modules.video.video_url',
        ])
    </section>

    <!-- Poster / miniatura -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Miniatura del video</p>
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Imagen del poster',
            'type' => 'image',
            'context' => 'video-poster',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.video.poster',
        ])
        <p class="text-xs text-stone-500">Se recomienda usar una imagen en formato vertical pensada para la tarjeta de video. Puedes recortarla para ajustar el encuadre.</p>
    </section>
</div>
