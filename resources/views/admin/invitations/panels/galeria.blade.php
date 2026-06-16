<div x-show="activeTab === 'galeria'" x-cloak class="space-y-2">
    <!-- Resumen -->
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Galería</p>
                <p class="text-xs text-stone-600 truncate">Galería principal de fotos</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">
                    <span x-text="`${(modules.galeria.fotos || []).length} fotos`"></span>
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold"
                    :class="modules.config.modulos.galeria
                        ? 'text-green-700 bg-green-50 border-green-200'
                        : 'text-stone-500 bg-stone-50 border-stone-200'">
                    <span class="inline-block w-1.5 h-1.5 rounded-full"
                        :class="modules.config.modulos.galeria ? 'bg-green-500' : 'bg-stone-400'"></span>
                    <span x-text="modules.config.modulos.galeria ? 'Activo' : 'Inactivo'"></span>
                </span>
            </div>
        </div>

        <p class="text-xs text-stone-500 leading-relaxed">
            Sube las fotos que se mostrarán en el carrusel principal de la invitación. Activa o desactiva el módulo desde el menú lateral.
        </p>
    </section>

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
