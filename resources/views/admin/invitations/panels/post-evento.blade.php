<div x-show="activeTab === 'post_evento'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Post-evento</p>
                <h2 class="font-serif text-xl text-stone-950">Galería oficial</h2>
                <p class="mt-1 text-sm text-stone-500">Sección independiente para fotos y enlace externo del fotógrafo.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.post_evento" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
        <div>
            <label class="admin-label">Título</label>
            <input type="text" x-model="modules.post_evento.titulo" class="admin-input" placeholder="Título galería fotógrafo">
        </div>
        <div>
            <label class="admin-label">Descripción</label>
            <textarea x-model="modules.post_evento.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>
        </div>
        <div>
            <label class="admin-label">Fotos oficiales del fotógrafo</label>
            <label class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 cursor-pointer text-sm text-stone-600"
                :class="mediaUploading ? 'opacity-60 pointer-events-none' : ''">
                <span x-text="mediaUploading ? 'Subiendo...' : 'Subir fotos oficiales'"></span>
                <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="uploadPostEventPhotos($event)">
            </label>
            <div class="grid grid-cols-3 gap-2 mt-3" x-show="(modules.post_evento.fotos || []).length" x-cloak>
                <template x-for="(foto, i) in modules.post_evento.fotos" :key="'pe'+i">
                    <div class="relative aspect-square rounded-lg overflow-hidden border">
                        <img :src="foto" class="w-full h-full object-cover">
                        <button type="button" @click="clearMediaUrl(foto); modules.post_evento.fotos.splice(i,1)" class="absolute top-0.5 right-0.5 w-5 h-5 bg-red-600 text-white text-xs rounded-full">×</button>
                    </div>
                </template>
            </div>
        </div>
        <div>
            <label class="admin-label">Enlace externo galería <span class="normal-case text-stone-400">(opcional)</span></label>
            <input type="url" x-model="modules.post_evento.enlace_externo" class="admin-input" placeholder="Google Drive, Dropbox, etc.">
        </div>
    </section>
</div>
