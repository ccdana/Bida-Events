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
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
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
        <div class="grid grid-cols-3 gap-2">
            <template x-for="(foto, i) in modules.galeria.fotos" :key="i">
                <div class="relative group aspect-[4/3] rounded-xl overflow-hidden border border-stone-200 bg-stone-100">
                    <img :src="foto" class="w-full h-full object-cover select-none" draggable="false">
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
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M4 7h3M7 4v3m6-3h3m-3 3V4M4 17h3m0 3v-3m6 3h3m-3-3v3" stroke-linecap="round"/><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                                Recortar
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </section>
</div>
