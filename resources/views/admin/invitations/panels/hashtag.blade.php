<div x-show="activeTab === 'hashtag'" x-cloak class="space-y-2">
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Hashtag</p>
                <p class="text-xs text-stone-600 truncate">Etiqueta oficial para redes sociales</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.hashtag
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.hashtag ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.hashtag ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Hashtag del evento</label>
                <input type="text" x-model="modules.hashtag.hashtag" @input="schedulePreview()" class="admin-input" placeholder="#MiEvento2026">
            </div>
            <div class="space-y-3">
                <div x-data="{ open: false }" class="admin-accordion">
                    <button type="button" @click="open = !open" class="admin-accordion-trigger">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-stone-900 text-left">Plataforma</p>
                            <p class="text-xs text-stone-500 text-left truncate" x-text="modules.hashtag.plataforma === 'tiktok' ? 'TikTok' : 'Instagram'"></p>
                        </div>
                        <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="admin-accordion-panel space-y-1">
                        <button type="button"
                            @click="modules.hashtag.plataforma = 'instagram'; schedulePreview(); open = false"
                            class="admin-accordion-option"
                            :class="modules.hashtag.plataforma === 'instagram' ? 'is-selected' : ''">
                            Instagram
                        </button>
                        <button type="button"
                            @click="modules.hashtag.plataforma = 'tiktok'; schedulePreview(); open = false"
                            class="admin-accordion-option"
                            :class="modules.hashtag.plataforma === 'tiktok' ? 'is-selected' : ''">
                            TikTok
                        </button>
                    </div>
                </div>
                <div>
                    <label class="admin-label">Texto del botón</label>
                    <input type="text" x-model="modules.hashtag.texto_boton" @input="schedulePreview()" class="admin-input" placeholder="Ej. Ver en Instagram">
                </div>
            </div>
        </div>
    </section>
</div>
