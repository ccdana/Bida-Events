<div x-show="activeTab === 'video'" x-cloak class="space-y-2">
    <!-- Resumen -->
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Video</p>
                <p class="text-xs text-stone-600 truncate">Save the date principal</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.video
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.video ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.video ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <p class="text-xs text-stone-500 leading-relaxed">
            Panel dedicado al video principal del evento y su miniatura. Activa o desactiva el módulo desde el menú lateral.
        </p>
    </section>

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
            'label' => 'Video vertical',
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
