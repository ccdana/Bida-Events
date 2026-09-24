<div x-show="activeTab === 'galeria'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Galería',
        'title' => 'Fotos en pila',
        'description' => 'las fotos aparecen apiladas como cartas; el invitado las desliza con el dedo o usa las flechas para ver la siguiente.',
        'tip' => 'Entre 5 y 15 fotos verticales (formato 4:5) se ven mejor. El orden de esta cuadrícula es el orden de la pila.',
        'moduleKey' => 'galeria',
        'countExpr' => '`${(modules.galeria.fotos || []).length} fotos`',
    ])

    <!-- Título -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Título de sección</p>
        <input type="text" x-model="modules.galeria.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Momentos especiales">
    </section>

    <!-- Subir fotos -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Subir fotos</p>
        <label class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 hover:bg-amber-50/30 cursor-pointer transition text-sm text-stone-600"
            :class="mediaUploading ? 'opacity-60 pointer-events-none' : ''">
            <x-phosphor-plus class="w-4 h-4" aria-hidden="true" />
            <span x-text="mediaUploading ? 'Subiendo...' : 'Agregar fotos (múltiple)'"></span>
            <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="uploadGalleryFiles($event)">
        </label>
        <p class="text-xs text-stone-500">Las fotos se ajustarán automáticamente al formato del carrusel. Puedes recortar cada imagen para afinar el encuadre.</p>
    </section>

    <!-- Grid de fotos -->
    <section class="admin-card p-3 space-y-2" x-show="(modules.galeria.fotos || []).length" x-cloak>
        <p class="admin-eyebrow mb-1">
            <span x-text="(modules.galeria.fotos || []).length"></span> fotos seleccionadas
        </p>
        <p class="text-xs text-stone-500">La descripción la leen en voz alta los lectores de pantalla y aparece si la foto no carga. Déjala vacía si la foto es solo decorativa.</p>
        <div class="grid grid-cols-2 gap-3">
            <template x-for="(foto, i) in modules.galeria.fotos" :key="i">
                <div class="space-y-1">
                <div class="relative group aspect-[4/5] rounded-xl overflow-hidden border border-stone-200 bg-stone-100">
                    <img :src="photoUrl(foto)" :alt="photoAlt(foto)" class="w-full h-full object-cover select-none" draggable="false">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition pointer-events-none"></div>
                    <div class="absolute inset-1 flex flex-col justify-between opacity-0 group-hover:opacity-100 transition pointer-events-none">
                        <div class="flex justify-end">
                            <button type="button" @click.stop="removeGalleryPhoto(i)"
                                class="pointer-events-auto w-6 h-6 rounded-full bg-red-600 text-white text-xs flex items-center justify-center shadow">
                                ×
                            </button>
                        </div>
                        <div class="flex justify-between gap-1">
                            <button type="button"
                                @click.stop="openImageCropperFromGallery(i)"
                                class="pointer-events-auto flex-1 inline-flex items-center justify-center gap-1 px-1.5 py-1 rounded-md bg-white/95 text-[11px] font-medium text-stone-700 shadow">
                                <x-phosphor-crop class="w-3.5 h-3.5" aria-hidden="true" />
                                Recortar
                            </button>
                        </div>
                    </div>
                </div>
                <input type="text" maxlength="255"
                    class="admin-input admin-input--sm"
                    :value="photoAlt(foto)"
                    @change="setPhotoAlt(modules.galeria.fotos, i, $event.target.value)"
                    :aria-label="'Descripción de la foto ' + (i + 1)"
                    placeholder="Describe la foto (opcional)">
                </div>
            </template>
        </div>
    </section>
</div>
