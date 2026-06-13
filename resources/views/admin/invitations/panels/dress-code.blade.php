<div x-show="activeTab === 'dress'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Vestimenta</p>
                <h2 class="font-serif text-xl text-stone-950">Dress code</h2>
                <p class="mt-1 text-sm text-stone-500">Una experiencia visual más elegante para explicar qué vestir sin parecer lista rígida.</p>
            </div>
            <div class="flex items-center gap-3">
                <label class="admin-toggle-row">
                    <input type="checkbox" x-model="modules.config.modulos.dress_code" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <span class="text-stone-700">Activo</span>
                </label>
                <button type="button" @click="addSugerencia()" class="admin-primary-button">+ Sugerencia</button>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label class="admin-label">Título</label>
                <input type="text" x-model="modules.dress_code.titulo" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Estilo</label>
                <input type="text" x-model="modules.dress_code.estilo" class="admin-input">
            </div>
        </div>
        <div>
            <label class="admin-label">Descripción</label>
            <textarea x-model="modules.dress_code.descripcion" rows="3" class="admin-input"></textarea>
        </div>
    </section>

    <template x-for="(sug, i) in modules.dress_code.sugerencias" :key="i">
        <section class="admin-card space-y-3">
            <div class="flex items-center justify-between gap-3">
                <span class="admin-context-badge is-secondary">Sugerencia <span x-text="i + 1"></span></span>
                <button type="button" @click="removeSugerencia(i)" class="text-xs text-red-600 hover:text-red-700">Eliminar</button>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                <input type="text" x-model="sug.para" class="admin-input" placeholder="Para (Damas, Caballeros...)">
                <input type="text" x-model="sug.titulo" class="admin-input" placeholder="Título">
            </div>
            <textarea x-model="sug.descripcion" rows="3" class="admin-input" placeholder="Descripción"></textarea>
            <input type="text"
                :value="(sug.ejemplos || []).join(', ')"
                @input="sug.ejemplos = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)"
                class="admin-input" placeholder="Ejemplos separados por coma">
            <div class="flex flex-col gap-3 rounded-2xl border border-stone-200 bg-stone-50 p-3 sm:flex-row sm:items-center">
                <img x-show="sug.imagen" :src="sug.imagen" class="w-16 h-16 rounded-xl object-cover border border-white shadow-sm" x-cloak>
                <label class="flex-1 flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border border-dashed border-stone-200 text-sm text-stone-500 cursor-pointer hover:border-amber-400 hover:bg-white transition">
                    <span x-text="sug.imagen ? 'Cambiar foto' : 'Agregar referencia visual'"></span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                        @change="pickLocalFileReplace($event, 'image', 'dress-code', () => sug.imagen, url => sug.imagen = url)">
                </label>
                <button type="button" x-show="sug.imagen" x-cloak
                    @click="clearMediaUrl(sug.imagen); sug.imagen = null" class="text-xs text-red-600">Quitar</button>
            </div>
        </section>
    </template>

    <section class="admin-card space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="admin-label mb-1">Colores permitidos</p>
                <p class="text-xs text-stone-500">Elige una paleta coherente con el tono de la invitación.</p>
            </div>
            <button type="button" @click="addColorPermitido()" class="admin-link-button">+ Color</button>
        </div>
        <template x-for="(c, i) in modules.dress_code.colores_permitidos" :key="'p'+i">
            <div class="flex items-center gap-2 rounded-2xl border border-stone-200 bg-stone-50 p-3">
                <input type="color" x-model="c.hex" class="w-11 h-11 rounded-xl border border-stone-200 bg-white">
                <input type="text" x-model="c.nombre" class="admin-input flex-1" placeholder="Nombre del color">
            </div>
        </template>
    </section>

    <section class="admin-card space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="admin-label mb-1">Colores a evitar</p>
                <p class="text-xs text-stone-500">No hace falta prohibir demasiado, solo lo que realmente choque con el evento.</p>
            </div>
            <button type="button" @click="addColorProhibido()" class="admin-link-button">+ Color</button>
        </div>
        <template x-for="(c, i) in modules.dress_code.colores_prohibidos" :key="'x'+i">
            <div class="grid gap-2 rounded-2xl border border-stone-200 bg-stone-50 p-3 md:grid-cols-[auto,1fr,1fr] md:items-center">
                <input type="color" x-model="c.hex" class="w-11 h-11 rounded-xl border border-stone-200 bg-white">
                <input type="text" x-model="c.nombre" class="admin-input" placeholder="Nombre">
                <input type="text" x-model="c.motivo" class="admin-input" placeholder="Motivo">
            </div>
        </template>
    </section>
</div>
