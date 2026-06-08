<div x-show="activeTab === 'estetica'" x-cloak class="space-y-4">
    <section class="admin-card space-y-5">
        <div>
            <p class="admin-eyebrow">Diseño visual</p>
            <h2 class="font-serif text-xl text-stone-950">Estética de la invitación</h2>
            <p class="mt-1 text-sm leading-relaxed text-stone-500">Ajusta color y tipografía con controles que sí muestran el resultado final.</p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest text-stone-500">Selección actual</p>
                    <p class="mt-1 text-sm text-stone-500">Paleta y fuentes activas para esta invitación.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="admin-context-badge is-primary">
                        <span class="admin-editor-swatch" :style="`background:${modules.config.colores.primary}`"></span>
                        Primario
                    </span>
                    <span class="admin-context-badge is-secondary">
                        <span class="admin-editor-swatch" :style="`background:${modules.config.colores.secondary}`"></span>
                        Secundario
                    </span>
                </div>
            </div>
            <div class="mt-4 rounded-[1.25rem] border border-white p-4 shadow-sm overflow-hidden"
                :style="`background:${modules.config.colores.background};color:${modules.config.colores.text}`">
                <p class="text-[10px] uppercase tracking-[0.3em]" :style="`color:${modules.config.colores.secondary}`">Preview</p>
                <p class="mt-2 font-serif text-lg" :style="`font-family:'${modules.config.tipografias.titulos}', serif;color:${modules.config.colores.secondary}`">Sofía Valentina</p>
                <p class="mt-1 text-sm" :style="`font-family:'${modules.config.tipografias.cuerpo}', sans-serif`">Una noche para celebrar, con una presencia visual limpia y elegante.</p>
                <div class="mt-4 h-1.5 rounded-full" :style="`background:linear-gradient(90deg, ${modules.config.colores.primary}, ${modules.config.colores.secondary})`"></div>
            </div>
        </div>
    </section>

    <section class="admin-card space-y-5">
        <div>
            <p class="admin-label mb-3">Paleta de colores</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <template x-for="(color, key) in modules.config.colores" :key="key">
                    <label class="admin-color-row">
                        <input type="color" x-model="modules.config.colores[key]" class="admin-color-input">
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-medium" :class="key === 'primary' || key === 'secondary' ? 'text-stone-950' : 'text-stone-800'" x-text="colorLabels[key] || key"></span>
                            <span class="block font-mono text-[11px] uppercase text-stone-400" x-text="modules.config.colores[key]"></span>
                        </span>
                        <input type="text" x-model="modules.config.colores[key]" class="admin-hex-input" maxlength="7">
                    </label>
                </template>
            </div>
        </div>
    </section>

    <section class="admin-card space-y-5">
        <div>
            <p class="admin-eyebrow">Tipografías</p>
            <h3 class="font-serif text-lg text-stone-950">Elegir por apariencia</h3>
            <p class="mt-1 text-sm text-stone-500">Las tarjetas muestran el nombre de cada fuente con la propia fuente aplicada.</p>
        </div>

        <div class="space-y-4">
            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="admin-label mb-0">Títulos</p>
                    <span class="text-xs uppercase tracking-widest text-stone-400" x-text="modules.config.tipografias.titulos"></span>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    <template x-for="font in fontOptions.titulos" :key="font">
                        <button type="button" @click="modules.config.tipografias.titulos = font"
                            class="rounded-2xl border p-3 text-left transition-all duration-300"
                            :class="modules.config.tipografias.titulos === font ? 'border-stone-900 bg-white shadow-sm ring-1 ring-stone-900/10' : 'border-stone-200 bg-stone-50 hover:bg-white'">
                            <span class="block text-[10px] uppercase tracking-widest text-stone-400" x-text="font"></span>
                            <span class="mt-2 block text-xl leading-tight" :style="`font-family:'${font}', serif`">Sofía Valentina</span>
                            <span class="mt-2 block text-xs text-stone-500">Una noche para celebrar</span>
                        </button>
                    </template>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="admin-label mb-0">Cuerpo</p>
                    <span class="text-xs uppercase tracking-widest text-stone-400" x-text="modules.config.tipografias.cuerpo"></span>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    <template x-for="font in fontOptions.cuerpo" :key="font">
                        <button type="button" @click="modules.config.tipografias.cuerpo = font"
                            class="rounded-2xl border p-3 text-left transition-all duration-300"
                            :class="modules.config.tipografias.cuerpo === font ? 'border-stone-900 bg-white shadow-sm ring-1 ring-stone-900/10' : 'border-stone-200 bg-stone-50 hover:bg-white'">
                            <span class="block text-[10px] uppercase tracking-widest text-stone-400" x-text="font"></span>
                            <span class="mt-2 block text-sm leading-relaxed" :style="`font-family:'${font}', sans-serif`">Texto legible para detalles, horarios y descripciones.</span>
                        </button>
                    </template>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="admin-label mb-0">Script</p>
                    <span class="text-xs uppercase tracking-widest text-stone-400" x-text="modules.config.tipografias.script"></span>
                </div>
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    <template x-for="font in fontOptions.script" :key="font">
                        <button type="button" @click="modules.config.tipografias.script = font"
                            class="rounded-2xl border p-3 text-left transition-all duration-300"
                            :class="modules.config.tipografias.script === font ? 'border-stone-900 bg-white shadow-sm ring-1 ring-stone-900/10' : 'border-stone-200 bg-stone-50 hover:bg-white'">
                            <span class="block text-[10px] uppercase tracking-widest text-stone-400" x-text="font"></span>
                            <span class="mt-2 block text-3xl leading-none" :style="`font-family:'${font}', cursive`">Sofía</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-card">
        <div class="flex items-end justify-between gap-3 mb-3">
            <div>
                <p class="admin-label mb-1">Módulos visibles</p>
                <p class="text-xs text-stone-500">Activa sólo lo necesario para mantener la UI limpia.</p>
            </div>
        </div>
        <div class="grid gap-2 sm:grid-cols-2">
            <template x-for="(enabled, code) in modules.config.modulos" :key="code">
                <label class="admin-toggle-row">
                    <input type="checkbox" x-model="modules.config.modulos[code]" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <span class="capitalize text-stone-700" x-text="code.replace(/_/g, ' ')"></span>
                </label>
            </template>
        </div>
    </section>
</div>
