<div x-show="activeTab === 'hashtag'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Hashtag',
        'title' => 'Etiqueta para redes',
        'description' => 'el hashtag en grande con un botón para copiarlo y otro para ver las publicaciones en la red elegida.',
        'moduleKey' => 'hashtag',
    ])

    <section class="admin-card p-3 space-y-3">
        <div class="grid gap-2">
            <div>
                <label class="admin-label">Hashtag del evento</label>
                <input type="text" x-model="modules.hashtag.hashtag" @input="schedulePreview()" class="admin-input" placeholder="#SofiaXV2026">
                <p class="mt-1 text-[11px] text-stone-400">Incluye el símbolo # y no uses espacios ni tildes para que la búsqueda funcione.</p>
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
                    <label class="admin-label">Título de la sección</label>
                    <input type="text" x-model="modules.hashtag.texto_boton" @input="schedulePreview()" class="admin-input" placeholder="Ej. Usa nuestro hashtag">
                </div>
            </div>
        </div>
    </section>
</div>
