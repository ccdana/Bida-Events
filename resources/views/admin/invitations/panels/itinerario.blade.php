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
                <x-phosphor-calendar-blank-light class="w-7 h-7 text-stone-300" aria-hidden="true" />
                <p class="text-xs text-stone-500">Aún no hay momentos. Agrega el primero con el botón de abajo.</p>
            </div>
        </section>
    </template>

    <template x-for="(evento, i) in modules.itinerario.eventos" :key="i">
        <section class="admin-card p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center gap-1 rounded-md bg-white border border-stone-200 px-2 py-1 text-xs font-semibold text-stone-600 shrink-0">
                        <x-phosphor-clock class="w-3.5 h-3.5" aria-hidden="true" />
                        <span x-text="`Momento ${i + 1}`"></span>
                    </span>
                    <span class="text-xs text-stone-400 font-mono truncate" x-text="evento.hora || '--:--'"></span>
                </div>
                <button type="button" @click="removeEvento(i)"
                    class="flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-50 transition shrink-0">
                    <x-phosphor-trash class="w-3.5 h-3.5" aria-hidden="true" />
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
                <label class="admin-label">Ícono del momento</label>
                @include('admin.partials.itinerary-icon-picker', [
                    'model'  => 'evento.icono',
                    'change' => 'schedulePreview()',
                ])
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Descripción</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="evento.descripcion || 'Opcional · breve detalle del momento'"></p>
                    </div>
                    <x-phosphor-caret-down class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
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
            <x-phosphor-plus class="w-4 h-4" aria-hidden="true" />
            Agregar momento
        </button>
    </section>
</div>
