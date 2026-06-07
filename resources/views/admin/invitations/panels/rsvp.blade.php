<div x-show="activeTab === 'rsvp'" x-cloak class="space-y-4">
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Confirmación (RSVP)</h2>
        <input type="text" x-model="modules.rsvp.titulo_confirmacion" class="admin-input" placeholder="Título">
        <textarea x-model="modules.rsvp.mensaje_personalizado" rows="2" class="admin-input" placeholder="Mensaje personalizado"></textarea>
        <input type="text" x-model="modules.rsvp.texto_confirmado" class="admin-input" placeholder="Texto al confirmar">
        <input type="text" x-model="modules.rsvp.texto_declinado" class="admin-input" placeholder="Texto al declinar">
    </div>
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Post-evento</h2>
        <input type="text" x-model="modules.post_evento.titulo" class="admin-input" placeholder="Título galería fotógrafo">
        <textarea x-model="modules.post_evento.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>

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
                        <button type="button" @click="modules.post_evento.fotos.splice(i,1)" class="absolute top-0.5 right-0.5 w-5 h-5 bg-red-600 text-white text-xs rounded-full">×</button>
                    </div>
                </template>
            </div>
        </div>

        <div>
            <label class="admin-label">Enlace externo galería <span class="normal-case text-stone-400">(opcional)</span></label>
            <input type="url" x-model="modules.post_evento.enlace_externo" class="admin-input" placeholder="Google Drive, Dropbox, etc.">
        </div>
    </div>
</div>
