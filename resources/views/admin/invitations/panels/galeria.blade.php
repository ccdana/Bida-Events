<div x-show="activeTab === 'galeria'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Galería de fotos</h2>
    <div>
        <label class="admin-label">Título de sección</label>
        <input type="text" x-model="modules.galeria.titulo" class="admin-input">
    </div>

    <div>
        <label class="admin-label">Subir fotos</label>
        <label class="flex items-center justify-center gap-2 px-4 py-4 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 hover:bg-amber-50/30 cursor-pointer transition text-sm text-stone-600"
            :class="mediaUploading ? 'opacity-60 pointer-events-none' : ''">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-linecap="round"/></svg>
            <span x-text="mediaUploading ? 'Subiendo...' : 'Agregar fotos (múltiple)'"></span>
            <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="uploadGalleryFiles($event)">
        </label>
    </div>

    <div x-show="(modules.galeria.fotos || []).length" x-cloak>
        <p class="admin-label"><span x-text="(modules.galeria.fotos || []).length"></span> fotos en Cloudinary</p>
        <div class="grid grid-cols-3 gap-2">
            <template x-for="(foto, i) in modules.galeria.fotos" :key="i">
                <div class="relative group aspect-square rounded-xl overflow-hidden border border-stone-200">
                    <img :src="foto" class="w-full h-full object-cover">
                    <button type="button" @click="removeGalleryPhoto(i)"
                        class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600 text-white text-xs opacity-0 group-hover:opacity-100 transition">×</button>
                </div>
            </template>
        </div>
    </div>
</div>
