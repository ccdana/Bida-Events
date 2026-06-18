<div x-show="activeTab === 'playlist'" x-cloak class="space-y-2">
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Playlist</p>
                <p class="text-xs text-stone-600 truncate">Sugerencias de canciones de los invitados</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.playlist
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.playlist ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.playlist ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Título de la sección</label>
                <input type="text" x-model="modules.playlist.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. ¡Ayúdanos con la música!">
            </div>
            <div>
                <label class="admin-label">Descripción</label>
                <textarea x-model="modules.playlist.descripcion" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Ej. ¿Qué canción no puede faltar en la fiesta?"></textarea>
            </div>
            <div>
                <label class="admin-label">Texto del campo de entrada</label>
                <input type="text" x-model="modules.playlist.placeholder" @input="schedulePreview()" class="admin-input" placeholder="Ej. Recomienda una canción">
            </div>
        </div>
    </section>
</div>
