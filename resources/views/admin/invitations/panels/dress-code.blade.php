<div x-show="activeTab === 'dress'" x-cloak class="space-y-2">
    <!-- Resumen -->
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Dress code</p>
                <p class="text-xs text-stone-600 truncate">Código de vestimenta</p>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">
                    <span x-text="`${modules.dress_code.sugerencias.length} sugerencias`"></span>
                </span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold"
                    :class="modules.config.modulos.dress_code
                        ? 'text-green-700 bg-green-50 border-green-200'
                        : 'text-stone-500 bg-stone-50 border-stone-200'">
                    <span class="inline-block w-1.5 h-1.5 rounded-full"
                        :class="modules.config.modulos.dress_code ? 'bg-green-500' : 'bg-stone-400'"></span>
                    <span x-text="modules.config.modulos.dress_code ? 'Activo' : 'Inactivo'"></span>
                </span>
            </div>
        </div>

        <p class="text-xs text-stone-500 leading-relaxed">
            Explica qué vestir de forma clara y elegante. Activa o desactiva el módulo desde el menú lateral.
        </p>
    </section>

    <!-- Información general -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Información general</p>

        <div class="grid gap-2 grid-cols-2">
            <div>
                <label class="admin-label">Título</label>
                <input type="text" x-model="modules.dress_code.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Código de vestimenta">
            </div>
            <div>
                <label class="admin-label">Estilo</label>
                <input type="text" x-model="modules.dress_code.estilo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Formal elegante">
            </div>
        </div>

        <div x-data="{ open: true }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Descripción</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="modules.dress_code.descripcion || 'Mensaje general sobre la vestimenta'"></p>
                </div>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            <div x-show="open" class="admin-accordion-panel">
                <textarea x-model="modules.dress_code.descripcion" @input="schedulePreview()" rows="3" class="admin-input" placeholder="Describe el tono general del dress code"></textarea>
            </div>
        </div>
    </section>

    <!-- Sugerencias -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="admin-eyebrow mb-0">Sugerencias</p>
            <button type="button" @click="addSugerencia()" class="admin-link-button text-xs">+ Agregar</button>
        </div>

        <template x-if="modules.dress_code.sugerencias.length === 0">
            <div class="rounded-lg border-2 border-dashed border-stone-200 bg-stone-50 py-6 text-center">
                <p class="text-xs text-stone-500">Sin sugerencias aún. Agrega una para damas, caballeros u otros grupos.</p>
            </div>
        </template>
    </section>

    <template x-for="(sug, i) in modules.dress_code.sugerencias" :key="i">
        <section class="admin-card p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">
                    Sugerencia <span x-text="i + 1"></span>
                </span>
                <button type="button" @click="removeSugerencia(i)" class="text-xs font-medium text-red-600 hover:text-red-700">Eliminar</button>
            </div>

            <div class="grid gap-2 grid-cols-2">
                <div>
                    <label class="admin-label">Para</label>
                    <input type="text" x-model="sug.para" @input="schedulePreview()" class="admin-input" placeholder="Damas, Caballeros…">
                </div>
                <div>
                    <label class="admin-label">Título</label>
                    <input type="text" x-model="sug.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Vestido largo">
                </div>
            </div>

            <div>
                <label class="admin-label">Descripción</label>
                <textarea x-model="sug.descripcion" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Detalle de la sugerencia"></textarea>
            </div>

            <div>
                <label class="admin-label">Ejemplos</label>
                <input type="text"
                    :value="(sug.ejemplos || []).join(', ')"
                    @input="sug.ejemplos = $event.target.value.split(',').map(s => s.trim()).filter(Boolean); schedulePreview()"
                    class="admin-input" placeholder="Separados por coma">
            </div>

            <div class="flex flex-col gap-2 rounded-lg border border-stone-200 bg-stone-50 p-2.5 sm:flex-row sm:items-center">
                <img x-show="sug.imagen" :src="sug.imagen" class="w-14 h-14 rounded-lg object-cover border border-white shadow-sm shrink-0" x-cloak alt="">
                <label class="flex-1 flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg border border-dashed border-stone-200 text-xs text-stone-600 cursor-pointer hover:border-amber-400 hover:bg-white transition">
                    <span x-text="sug.imagen ? 'Cambiar foto' : 'Agregar referencia visual'"></span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                        @change="pickLocalFileReplace($event, 'image', 'dress-code', () => sug.imagen, url => sug.imagen = url)">
                </label>
                <button type="button" x-show="sug.imagen" x-cloak
                    @click="clearMediaUrl(sug.imagen); sug.imagen = null; schedulePreview()"
                    class="text-xs font-medium text-red-600 hover:text-red-700 shrink-0">Quitar</button>
            </div>
        </section>
    </template>

    <!-- Colores permitidos -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0">Colores permitidos</p>
                <p class="text-xs text-stone-500 mt-1">Paleta coherente con el tono de la invitación.</p>
            </div>
            <button type="button" @click="addColorPermitido()" class="admin-link-button text-xs shrink-0">+ Color</button>
        </div>

        <template x-if="modules.dress_code.colores_permitidos.length === 0">
            <p class="text-xs text-stone-500 rounded-lg border border-dashed border-stone-200 bg-stone-50 py-4 text-center">Sin colores definidos</p>
        </template>

        <template x-for="(c, i) in modules.dress_code.colores_permitidos" :key="'p'+i">
            <div class="flex items-center gap-2 rounded-lg border border-stone-200 bg-stone-50 p-2.5">
                <input type="color" x-model="c.hex" @input="schedulePreview()" class="w-10 h-10 rounded-lg border border-stone-200 bg-white cursor-pointer shrink-0">
                <input type="text" x-model="c.nombre" @input="schedulePreview()" class="admin-input flex-1" placeholder="Nombre del color">
                <button type="button" @click="modules.dress_code.colores_permitidos.splice(i, 1); schedulePreview()"
                    class="text-xs text-red-600 hover:text-red-700 shrink-0 px-1">×</button>
            </div>
        </template>
    </section>

    <!-- Colores a evitar -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0">Colores a evitar</p>
                <p class="text-xs text-stone-500 mt-1">Solo lo que realmente choque con el evento.</p>
            </div>
            <button type="button" @click="addColorProhibido()" class="admin-link-button text-xs shrink-0">+ Color</button>
        </div>

        <template x-if="modules.dress_code.colores_prohibidos.length === 0">
            <p class="text-xs text-stone-500 rounded-lg border border-dashed border-stone-200 bg-stone-50 py-4 text-center">Sin restricciones de color</p>
        </template>

        <template x-for="(c, i) in modules.dress_code.colores_prohibidos" :key="'x'+i">
            <div class="grid gap-2 rounded-lg border border-stone-200 bg-stone-50 p-2.5 sm:grid-cols-[auto,1fr,1fr,auto] sm:items-center">
                <input type="color" x-model="c.hex" @input="schedulePreview()" class="w-10 h-10 rounded-lg border border-stone-200 bg-white cursor-pointer">
                <input type="text" x-model="c.nombre" @input="schedulePreview()" class="admin-input" placeholder="Nombre">
                <input type="text" x-model="c.motivo" @input="schedulePreview()" class="admin-input" placeholder="Motivo">
                <button type="button" @click="modules.dress_code.colores_prohibidos.splice(i, 1); schedulePreview()"
                    class="text-xs text-red-600 hover:text-red-700 sm:justify-self-end">×</button>
            </div>
        </template>
    </section>
</div>
