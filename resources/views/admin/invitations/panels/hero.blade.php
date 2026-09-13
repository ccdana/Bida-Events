<div x-show="activeTab === 'hero'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Banner principal',
        'title' => 'Portada de la invitación',
        'description' => 'es lo primero que aparece al abrir el enlace: subtítulo, nombre en letra manuscrita, mensaje y fecha sobre la foto de fondo.',
        'tip' => 'Usa una foto vertical con el rostro en la parte superior: el texto se ubica al centro y la imagen se oscurece un poco para que se lea bien.',
        'moduleKey' => 'bienvenida',
    ])

    <!-- Identidad -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Identidad</p>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Nombre de la protagonista</label>
                <input type="text" x-model="modules.bienvenida.nombre_quinceanera" @input="schedulePreview()" class="admin-input" placeholder="Ej. Sofía Valentina">
            </div>
            <div>
                <label class="admin-label">Subtítulo superior</label>
                <input type="text" x-model="modules.bienvenida.subtitulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Celebrando mis XV Años">
            </div>
        </div>
    </section>

    <!-- Mensaje de bienvenida -->
    <section class="admin-card p-3 space-y-2">
        <div x-data="{ open: true }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Mensaje de bienvenida</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="modules.bienvenida.mensaje || 'Texto principal para tus invitados'"></p>
                </div>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            <div x-show="open" class="admin-accordion-panel">
                <textarea x-model="modules.bienvenida.mensaje" @input="schedulePreview()" rows="4" class="admin-input" placeholder="Escribe el mensaje principal que verán tus invitados."></textarea>
            </div>
        </div>
    </section>

    <!-- Fecha visible -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Fecha visible</p>
        <div>
            <label class="admin-label">Texto de fecha en el banner</label>
            <input type="text" x-model="modules.bienvenida.fecha_texto" @input="schedulePreview()" class="admin-input" placeholder="Ej. Sábado 15 de Noviembre, 2026">
            <p class="mt-1.5 text-xs text-stone-500">Este texto se muestra en la portada. Puede diferir del formato de la fecha del evento.</p>
        </div>
    </section>

    <!-- Mensaje post-evento -->
    <section class="admin-card p-3 space-y-2">
        <div x-data="{ open: false }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Mensaje post-evento</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="modules.bienvenida.mensaje_post_evento || 'Opcional · agradecimiento después del evento'"></p>
                </div>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            <div x-show="open" class="admin-accordion-panel">
                <textarea x-model="modules.bienvenida.mensaje_post_evento" @input="schedulePreview()" rows="3" class="admin-input" placeholder="Mensaje de agradecimiento para después del evento."></textarea>
            </div>
        </div>
    </section>

    <!-- Imagen del banner -->
    <section class="admin-card p-3 space-y-2">
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Imagen del banner',
            'type' => 'image',
            'context' => 'hero',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.bienvenida.imagen_hero',
        ])
        <p class="text-xs text-stone-500">Recomendado: imagen vertical o cuadrada en alta resolución (JPG, PNG o WebP).</p>
    </section>
</div>
