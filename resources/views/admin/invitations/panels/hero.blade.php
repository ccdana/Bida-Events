<div x-show="activeTab === 'hero'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Hero principal</p>
                <h2 class="font-serif text-xl text-stone-950">Portada de la invitación</h2>
                <p class="mt-1 text-sm text-stone-500">Aquí se define la primera impresión visual del evento.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.bienvenida" @change="schedulePreview()" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>

        <div>
            <label class="admin-label">Nombre de la protagonista</label>
            <input type="text" x-model="modules.bienvenida.nombre_quinceanera" @input="schedulePreview()" class="admin-input" placeholder="Ej. Sofía Valentina">
        </div>
        <div>
            <label class="admin-label">Subtítulo</label>
            <input type="text" x-model="modules.bienvenida.subtitulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Celebrando mis XV Años">
        </div>
        <div>
            <label class="admin-label">Mensaje de bienvenida</label>
            <textarea x-model="modules.bienvenida.mensaje" @input="schedulePreview()" rows="4" class="admin-input" placeholder="Escribe el mensaje principal que verán tus invitados."></textarea>
        </div>
        <div>
            <label class="admin-label">Texto de fecha visible</label>
            <input type="text" x-model="modules.bienvenida.fecha_texto" @input="schedulePreview()" class="admin-input" placeholder="Ej. Sábado 15 de Noviembre, 2026">
        </div>
        <div>
            <label class="admin-label">Mensaje post-evento</label>
            <textarea x-model="modules.bienvenida.mensaje_post_evento" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Mensaje de agradecimiento para después del evento."></textarea>
        </div>
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Imagen hero',
            'type' => 'image',
            'context' => 'hero',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.bienvenida.imagen_hero',
        ])
    </section>
</div>
