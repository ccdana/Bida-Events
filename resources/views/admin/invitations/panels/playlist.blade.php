<div x-show="activeTab === 'playlist'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Playlist</p>
                <h2 class="font-serif text-xl text-stone-950">Colaboración musical</h2>
                <p class="mt-1 text-sm text-stone-500">Configura el bloque colaborativo de canciones en su propia sección.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.playlist" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
        <input type="text" x-model="modules.playlist.titulo" class="admin-input" placeholder="Título">
        <textarea x-model="modules.playlist.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>
        <input type="text" x-model="modules.playlist.placeholder" class="admin-input" placeholder="Placeholder del input">
    </section>
</div>
