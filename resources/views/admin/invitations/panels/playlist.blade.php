<div x-show="activeTab === 'playlist'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Playlist',
        'title' => 'Canciones sugeridas',
        'description' => 'un campo para sugerir una canción (nombre o enlace de YouTube) y la lista de sugerencias; las de YouTube se pueden escuchar ahí mismo.',
        'tip' => 'En la vista previa no se pueden enviar canciones: pruébalo desde el enlace público.',
        'moduleKey' => 'playlist',
    ])

    <section class="admin-card p-3 space-y-3">

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
                <label class="admin-label">Ejemplo dentro del campo</label>
                <input type="text" x-model="modules.playlist.placeholder" @input="schedulePreview()" class="admin-input" placeholder="Ej. Vivir mi vida – Marc Anthony">
                <p class="mt-1 text-[11px] text-stone-400">Texto gris que desaparece cuando el invitado empieza a escribir.</p>
            </div>
        </div>
    </section>
</div>
