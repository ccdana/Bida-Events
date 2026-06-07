<div x-show="activeTab === 'dress'" x-cloak class="admin-card space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-serif text-lg text-stone-900">Dress code</h2>
        <button type="button" @click="addSugerencia()" class="text-xs px-3 py-1.5 bg-stone-900 text-white rounded-lg">+ Sugerencia</button>
    </div>
    <div class="grid grid-cols-2 gap-3">
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
        <textarea x-model="modules.dress_code.descripcion" rows="2" class="admin-input"></textarea>
    </div>

    <template x-for="(sug, i) in modules.dress_code.sugerencias" :key="i">
        <div class="rounded-xl border border-stone-200 p-4 space-y-2 bg-stone-50/50">
            <div class="flex justify-between">
                <span class="text-xs text-stone-500" x-text="'Sugerencia ' + (i+1)"></span>
                <button type="button" @click="removeSugerencia(i)" class="text-xs text-red-600">Eliminar</button>
            </div>
            <input type="text" x-model="sug.para" class="admin-input" placeholder="Para (Damas, Caballeros...)">
            <input type="text" x-model="sug.titulo" class="admin-input" placeholder="Título">
            <textarea x-model="sug.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>
            <input type="text"
                :value="(sug.ejemplos || []).join(', ')"
                @input="sug.ejemplos = $event.target.value.split(',').map(s => s.trim()).filter(Boolean)"
                class="admin-input" placeholder="Ejemplos separados por coma">
            <div class="flex gap-2 items-center pt-1">
                <img x-show="sug.imagen" :src="sug.imagen" class="w-14 h-14 rounded-lg object-cover border" x-cloak>
                <label class="flex-1 flex items-center justify-center px-3 py-2 rounded-lg border border-dashed border-stone-200 text-xs text-stone-500 cursor-pointer hover:border-amber-400">
                    <span x-text="sug.imagen ? 'Cambiar foto' : 'Foto de referencia'"></span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                        @change="pickLocalFileReplace($event, 'image', 'dress-code', () => sug.imagen, url => sug.imagen = url)">
                </label>
                <button type="button" x-show="sug.imagen" x-cloak
                    @click="clearMediaUrl(sug.imagen); sug.imagen = null" class="text-xs text-red-600">×</button>
            </div>
        </div>
    </template>

    <div class="pt-2 border-t border-stone-100">
        <div class="flex justify-between items-center mb-2">
            <p class="admin-label mb-0">Colores permitidos</p>
            <button type="button" @click="addColorPermitido()" class="text-xs text-amber-800">+ Color</button>
        </div>
        <template x-for="(c, i) in modules.dress_code.colores_permitidos" :key="'p'+i">
            <div class="flex gap-2 mb-2">
                <input type="color" x-model="c.hex" class="w-10 h-10 rounded-lg">
                <input type="text" x-model="c.nombre" class="admin-input flex-1" placeholder="Nombre">
            </div>
        </template>
    </div>
    <div>
        <div class="flex justify-between items-center mb-2">
            <p class="admin-label mb-0">Colores a evitar</p>
            <button type="button" @click="addColorProhibido()" class="text-xs text-amber-800">+ Color</button>
        </div>
        <template x-for="(c, i) in modules.dress_code.colores_prohibidos" :key="'x'+i">
            <div class="flex gap-2 mb-2">
                <input type="color" x-model="c.hex" class="w-10 h-10 rounded-lg">
                <input type="text" x-model="c.nombre" class="admin-input flex-1" placeholder="Nombre">
                <input type="text" x-model="c.motivo" class="admin-input flex-1" placeholder="Motivo">
            </div>
        </template>
    </div>
</div>
