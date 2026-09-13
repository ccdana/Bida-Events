<div x-show="activeTab === 'itinerario'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Itinerario',
        'title' => 'Los momentos de la noche',
        'description' => 'una línea de tiempo con hora, título e ícono de cada momento. Una luz recorre la línea a medida que baja por la página.',
        'tip' => 'Escribe la hora en formato 24 h (ej. 19:30) y usa títulos cortos. Los momentos se muestran en el orden de esta lista.',
        'moduleKey' => 'itinerario',
        'countExpr' => '`${modules.itinerario.eventos.length} momentos`',
    ])

    <!-- Título de sección -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Título de la sección</p>
        <input type="text" x-model="modules.itinerario.titulo" @input="schedulePreview()"
            class="admin-input" placeholder="Ej. Itinerario, La noche en orden…">
    </section>

    <!-- Lista de eventos -->
    <template x-if="modules.itinerario.eventos.length === 0">
        <section class="admin-card p-3">
            <div class="flex flex-col items-center gap-2 rounded-lg border-2 border-dashed border-stone-200 bg-stone-50 py-8 text-center">
                <svg class="w-7 h-7 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/>
                </svg>
                <p class="text-xs text-stone-500">Sin eventos aún</p>
            </div>
        </section>
    </template>

    <template x-for="(evento, i) in modules.itinerario.eventos" :key="i">
        <section class="admin-card p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center gap-1 rounded-md bg-white border border-stone-200 px-2 py-1 text-xs font-semibold text-stone-600 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span x-text="`Momento ${i + 1}`"></span>
                    </span>
                    <span class="text-xs text-stone-400 font-mono truncate" x-text="evento.hora || '--:--'"></span>
                </div>
                <button type="button" @click="removeEvento(i)"
                    class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="admin-label">Hora</label>
                    <input type="text" x-model="evento.hora" @input="schedulePreview()"
                        class="admin-input font-mono" placeholder="19:00">
                </div>
                <div>
                    <label class="admin-label">Título</label>
                    <input type="text" x-model="evento.titulo" @input="schedulePreview()"
                        class="admin-input" placeholder="Ej. Ceremonia de Vals">
                </div>
            </div>

            <div>
                <label class="admin-label">Ícono</label>
                @include('admin.partials.icon-picker', [
                    'model'  => 'evento.icono',
                    'icons'  => ['star','glass','candle','dance','dinner','music','gift','crown','sparkle','camera','users','map-pin','calendar','shirt','poll'],
                    'change' => 'schedulePreview()',
                ])
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Descripción</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="evento.descripcion || 'Opcional · breve detalle del momento'"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel">
                    <input type="text" x-model="evento.descripcion" @input="schedulePreview()"
                        class="admin-input" placeholder="Breve descripción de este momento">
                </div>
            </div>
        </section>
    </template>

    <!-- Agregar momento -->
    <section class="admin-card p-3">
        <button type="button" @click="addEvento()"
            class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-stone-200 bg-stone-50 py-2.5 text-xs font-semibold text-stone-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar momento
        </button>
    </section>
</div>
