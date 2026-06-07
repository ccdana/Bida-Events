<div x-show="activeTab === 'itinerario'" x-cloak class="admin-card space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="font-serif text-lg text-stone-900">Itinerario</h2>
        <button type="button" @click="addEvento()" class="text-xs px-3 py-1.5 bg-stone-900 text-white rounded-lg">+ Evento</button>
    </div>
    <div>
        <label class="admin-label">Título de sección</label>
        <input type="text" x-model="modules.itinerario.titulo" class="admin-input">
    </div>
    <template x-for="(evento, i) in modules.itinerario.eventos" :key="i">
        <div class="rounded-xl border border-stone-200 p-4 space-y-3 bg-stone-50/50">
            <div class="flex justify-between items-center">
                <span class="text-xs font-medium text-stone-500" x-text="'Evento ' + (i+1)"></span>
                <button type="button" @click="removeEvento(i)" class="text-xs text-red-600">Eliminar</button>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="admin-label">Hora</label>
                    <input type="text" x-model="evento.hora" class="admin-input" placeholder="19:00">
                </div>
                <div>
                    <label class="admin-label">Icono</label>
                    <select x-model="evento.icono" class="admin-input">
                        <template x-for="icon in itineraryIcons" :key="icon">
                            <option :value="icon" x-text="icon"></option>
                        </template>
                    </select>
                </div>
            </div>
            <div>
                <label class="admin-label">Título</label>
                <input type="text" x-model="evento.titulo" class="admin-input">
            </div>
            <div>
                <label class="admin-label">Descripción</label>
                <input type="text" x-model="evento.descripcion" class="admin-input">
            </div>
        </div>
    </template>
</div>
