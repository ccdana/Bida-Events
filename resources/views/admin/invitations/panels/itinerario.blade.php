<div x-show="activeTab === 'itinerario'" x-cloak class="admin-card space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="admin-eyebrow">Itinerario</p>
            <h2 class="font-serif text-xl text-stone-950">Cronograma del evento</h2>
            <p class="mt-1 text-sm text-stone-500">Define los momentos clave de la velada con hora, título e ícono.</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <label class="admin-toggle-row">
                <input type="checkbox" x-model="modules.config.modulos.itinerario" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </div>

    <div>
        <label class="admin-label">Título de la sección</label>
        <input type="text" x-model="modules.itinerario.titulo" @input="schedulePreview()"
            class="admin-input" placeholder="Ej. Itinerario, La noche en orden…">
    </div>

    {{-- Lista de eventos --}}
    <template x-if="modules.itinerario.eventos.length === 0">
        <div class="flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-stone-200 bg-stone-50 py-10 text-center">
            <svg class="w-8 h-8 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/>
            </svg>
            <p class="text-sm text-stone-400">Sin eventos aún</p>
        </div>
    </template>

    <template x-for="(evento, i) in modules.itinerario.eventos" :key="i">
        <div class="rounded-xl border border-stone-200 bg-stone-50/60 p-4 space-y-3">

            {{-- Cabecera del evento --}}
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-stone-200 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-stone-500">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><polyline points="12 6 12 12 16 14"/></svg>
                        Evento <span x-text="i + 1"></span>
                    </span>
                    <span class="text-xs text-stone-400 font-mono" x-text="evento.hora || '--:--'"></span>
                </div>
                <button type="button" @click="removeEvento(i)"
                    class="flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
            </div>

            {{-- Hora y título --}}
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="admin-label">Hora</label>
                    <input type="text" x-model="evento.hora" @input="schedulePreview()"
                        class="admin-input font-mono" placeholder="19:00">
                </div>
                <div>
                    <label class="admin-label">Título del momento</label>
                    <input type="text" x-model="evento.titulo" @input="schedulePreview()"
                        class="admin-input" placeholder="Ej. Ceremonia de Vals">
                </div>
            </div>

            {{-- Ícono --}}
            <div>
                <label class="admin-label">Ícono</label>
                @include('admin.partials.icon-picker', [
                    'model'  => 'evento.icono',
                    'icons'  => ['star','glass','candle','dance','dinner','music','gift','crown','sparkle','camera','users','map-pin','calendar','shirt','poll'],
                    'change' => 'schedulePreview()',
                ])
            </div>

            {{-- Descripción --}}
            <div>
                <label class="admin-label">Descripción <span class="normal-case text-stone-400">(opcional)</span></label>
                <input type="text" x-model="evento.descripcion" @input="schedulePreview()"
                    class="admin-input" placeholder="Breve descripción de este momento">
            </div>
        </div>
    </template>

    {{-- Botón agregar --}}
    <button type="button" @click="addEvento()"
        class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-stone-200 bg-stone-50 py-3 text-xs font-semibold text-stone-500 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Agregar momento
    </button>
</div>
