<div x-show="activeTab === 'estetica'" x-cloak class="space-y-2">
    <!-- Resumen rápido -->
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Diseño Visual</p>
                <p class="text-xs text-stone-600 truncate">Paleta y tipografía</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold shrink-0" :class="{ 'text-green-700 bg-green-50 border-green-200': getContrastRatio() >= 4.5, 'text-amber-700 bg-amber-50 border-amber-200': getContrastRatio() >= 3 && getContrastRatio() < 4.5, 'text-red-700 bg-red-50 border-red-200': getContrastRatio() < 3 }">
                <span class="inline-block w-1.5 h-1.5 rounded-full" :class="{ 'bg-green-500': getContrastRatio() >= 4.5, 'bg-amber-500': getContrastRatio() >= 3 && getContrastRatio() < 4.5, 'bg-red-500': getContrastRatio() < 3 }"></span>
                <span x-text="getContrastRatio() >= 4.5 ? 'AAA' : getContrastRatio() >= 3 ? 'AA' : 'Bajo'"></span>
            </span>
        </div>
        
        <!-- Swatches compactos -->
        <div class="grid grid-cols-5 gap-1.5">
            <template x-for="(color, key) in modules.config.colores" :key="key">
                <div class="flex flex-col items-center gap-1">
                    <div class="w-full aspect-square rounded-lg border border-stone-200 shadow-sm transition-all hover:shadow-md" :style="`background:${color}`" :title="colorLabels[key]"></div>
                    <span class="text-xs text-center leading-tight font-medium text-stone-600 truncate w-full" x-text="colorLabels[key]"></span>
                </div>
            </template>
        </div>
    </section>

    <!-- Presets -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="admin-eyebrow mb-0">Presets</p>
            <button type="button" @click="applyColorPreset('Elegancia Clásica')" class="text-xs px-2 py-1 rounded border border-stone-200 bg-white hover:bg-stone-50 transition-colors font-medium">↻</button>
        </div>
        <div class="grid grid-cols-2 gap-1.5">
            <template x-for="preset in getColorPresets()" :key="preset.name">
                <button type="button" @click="applyColorPreset(preset.name)"
                    class="relative p-2 rounded-lg border-2 transition-all" :class="isCurrentPreset(preset.name) ? 'border-stone-900 bg-white ring-1 ring-stone-900/20' : 'border-stone-200 bg-stone-50 hover:border-stone-300'">
                    <div class="flex gap-0.5 mb-1.5 h-3">
                        <div class="flex-1 rounded-sm" :style="`background:${preset.colors.primary}`"></div>
                        <div class="flex-1 rounded-sm" :style="`background:${preset.colors.secondary}`"></div>
                        <div class="flex-1 rounded-sm" :style="`background:${preset.colors.accent}`"></div>
                    </div>
                    <p class="text-xs font-semibold text-stone-700 truncate" x-text="preset.name"></p>
                </button>
            </template>
        </div>
    </section>

    <!-- Editor de colores individuales -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Colores</p>
        <div class="grid gap-2">
            <template x-for="(color, key) in modules.config.colores" :key="key">
                <label class="flex items-center gap-2 p-2 rounded-lg border border-stone-200 bg-stone-50 hover:bg-white transition-colors cursor-pointer">
                    <input type="color" x-model="modules.config.colores[key]" class="w-9 h-9 rounded cursor-pointer flex-shrink-0 border border-stone-300">
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-stone-900 truncate" x-text="colorLabels[key] || key"></span>
                    </span>
                    <input type="text" x-model="modules.config.colores[key]" class="w-16 h-8 text-xs font-mono uppercase rounded border border-stone-200 bg-white px-1.5 py-1 text-center flex-shrink-0" maxlength="7" @change="validateHexColor(key)">
                </label>
            </template>
        </div>
    </section>

    <!-- Tipografías en acordeón -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Tipografías</p>
        
        <template x-for="(fontType, typeKey) in { titulos: 'Títulos', cuerpo: 'Cuerpo', script: 'Script' }" :key="typeKey">
            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left" x-text="fontType"></p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="`Actual: ${modules.config.tipografias[typeKey]}`"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                
                <div x-show="open" class="admin-accordion-panel space-y-1">
                    <template x-for="font in fontOptions[typeKey]" :key="font">
                        <button type="button"
                            @click="modules.config.tipografias[typeKey] = font; open = false"
                            class="admin-accordion-option"
                            :class="modules.config.tipografias[typeKey] === font ? 'is-selected' : ''">
                            <span class="block font-semibold" :style="`font-family:'${font}', ${typeKey === 'script' ? 'cursive' : typeKey === 'titulos' ? 'serif' : 'sans-serif'}`" x-text="font"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </section>
</div>
