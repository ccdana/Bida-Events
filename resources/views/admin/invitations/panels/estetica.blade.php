<div x-show="activeTab === 'estetica'" x-cloak class="admin-card space-y-5">
    <h2 class="font-serif text-lg text-stone-900">Estética y módulos</h2>
    <div>
        <p class="admin-label mb-3">Paleta de colores</p>
        <div class="grid grid-cols-2 gap-3">
            <template x-for="(color, key) in modules.config.colores" :key="key">
                <div class="flex items-center gap-2">
                    <input type="color" x-model="modules.config.colores[key]" class="w-10 h-10 rounded-lg cursor-pointer border-0">
                    <span class="text-xs capitalize text-stone-600" x-text="key"></span>
                </div>
            </template>
        </div>
    </div>
    <div class="grid gap-3">
        <div>
            <label class="admin-label">Fuente títulos</label>
            <input type="text" x-model="modules.config.tipografias.titulos" class="admin-input" placeholder="Playfair Display">
        </div>
        <div>
            <label class="admin-label">Fuente cuerpo</label>
            <input type="text" x-model="modules.config.tipografias.cuerpo" class="admin-input" placeholder="Montserrat">
        </div>
        <div>
            <label class="admin-label">Fuente script</label>
            <input type="text" x-model="modules.config.tipografias.script" class="admin-input" placeholder="Great Vibes">
        </div>
    </div>
    <div>
        <p class="admin-label mb-3">Módulos visibles</p>
        <div class="grid grid-cols-2 gap-2">
            <template x-for="(enabled, code) in modules.config.modulos" :key="code">
                <label class="flex items-center gap-2 text-sm py-1.5 px-2 rounded-lg hover:bg-stone-50 cursor-pointer">
                    <input type="checkbox" x-model="modules.config.modulos[code]" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <span class="capitalize text-stone-700" x-text="code.replace(/_/g, ' ')"></span>
                </label>
            </template>
        </div>
    </div>
</div>
