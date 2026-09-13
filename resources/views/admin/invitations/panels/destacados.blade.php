<div x-show="activeTab === 'destacados'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Invitados de honor',
        'title' => 'Cortejo y padrinos',
        'description' => 'dos pestañas: «Cortejo», con chambelanes y damitas en lista, y «Padrinos», con su rol, nombres y mensaje. Los grupos vacíos no se muestran.',
        'tip' => 'La descripción es opcional y aparece en letra pequeña debajo del nombre (ej. «Primo y confidente»).',
        'moduleKey' => 'destacados',
        'countExpr' => '`${(modules.destacados.chambelanes?.length || 0) + (modules.destacados.damitas?.length || 0) + (modules.destacados.padrinos?.length || 0)} personas`',
    ])

    <!-- Chambelanes -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="admin-eyebrow mb-0">Chambelanes</p>
            <button type="button" @click="addChambelan()" class="admin-link-button text-xs">+ Agregar</button>
        </div>

        <template x-if="modules.destacados.chambelanes.length === 0">
            <p class="text-xs text-stone-500 rounded-lg border border-dashed border-stone-200 bg-stone-50 py-4 text-center">Sin chambelanes registrados</p>
        </template>
    </section>

    <template x-for="(p, i) in modules.destacados.chambelanes" :key="'ch'+i">
        <section class="admin-card p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">
                    Chambelán <span x-text="i + 1"></span>
                </span>
                <button type="button" @click="modules.destacados.chambelanes.splice(i, 1); schedulePreview()"
                    class="text-xs font-medium text-red-600 hover:text-red-700">Eliminar</button>
            </div>

            <div>
                <label class="admin-label">Nombre</label>
                <input type="text" :value="typeof p === 'string' ? p : p.nombre"
                    @input="normalizePerson(modules.destacados.chambelanes, i, 'nombre', $event.target.value); schedulePreview()"
                    class="admin-input" placeholder="Ej. Mateo Rodríguez">
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Descripción</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="(typeof p === 'object' ? p.detalle : '') || 'Opcional · rol o detalle breve'"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel">
                    <input type="text" :value="typeof p === 'object' ? (p.detalle || '') : ''"
                        @input="normalizePerson(modules.destacados.chambelanes, i, 'detalle', $event.target.value); schedulePreview()"
                        class="admin-input" placeholder="Ej. Primo, mejor amigo">
                </div>
            </div>
        </section>
    </template>

    <!-- Damitas -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="admin-eyebrow mb-0">Damitas</p>
            <button type="button" @click="addDamita()" class="admin-link-button text-xs">+ Agregar</button>
        </div>

        <template x-if="modules.destacados.damitas.length === 0">
            <p class="text-xs text-stone-500 rounded-lg border border-dashed border-stone-200 bg-stone-50 py-4 text-center">Sin damitas registradas</p>
        </template>
    </section>

    <template x-for="(p, i) in modules.destacados.damitas" :key="'dm'+i">
        <section class="admin-card p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">
                    Damita <span x-text="i + 1"></span>
                </span>
                <button type="button" @click="modules.destacados.damitas.splice(i, 1); schedulePreview()"
                    class="text-xs font-medium text-red-600 hover:text-red-700">Eliminar</button>
            </div>

            <div>
                <label class="admin-label">Nombre</label>
                <input type="text" :value="typeof p === 'string' ? p : p.nombre"
                    @input="normalizePerson(modules.destacados.damitas, i, 'nombre', $event.target.value); schedulePreview()"
                    class="admin-input" placeholder="Ej. Valentina Castro">
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Descripción</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="(typeof p === 'object' ? p.detalle : '') || 'Opcional · rol o detalle breve'"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel">
                    <input type="text" :value="typeof p === 'object' ? (p.detalle || '') : ''"
                        @input="normalizePerson(modules.destacados.damitas, i, 'detalle', $event.target.value); schedulePreview()"
                        class="admin-input" placeholder="Ej. Prima, compañera de escuela">
                </div>
            </div>
        </section>
    </template>

    <!-- Padrinos -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="admin-eyebrow mb-0">Padrinos</p>
            <button type="button" @click="addPadrino()" class="admin-link-button text-xs">+ Agregar</button>
        </div>

        <template x-if="modules.destacados.padrinos.length === 0">
            <p class="text-xs text-stone-500 rounded-lg border border-dashed border-stone-200 bg-stone-50 py-4 text-center">Sin padrinos registrados</p>
        </template>
    </section>

    <template x-for="(pad, i) in modules.destacados.padrinos" :key="'pd'+i">
        <section class="admin-card p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">
                    Padrino <span x-text="i + 1"></span>
                </span>
                <button type="button" @click="modules.destacados.padrinos.splice(i, 1); schedulePreview()"
                    class="text-xs font-medium text-red-600 hover:text-red-700">Eliminar</button>
            </div>

            <div class="grid gap-2">
                <div>
                    <label class="admin-label">Rol</label>
                    <input type="text" x-model="pad.rol" @input="schedulePreview()" class="admin-input" placeholder="Ej. Padrinos de honor">
                </div>
                <div>
                    <label class="admin-label">Nombres</label>
                    <input type="text" x-model="pad.nombres" @input="schedulePreview()" class="admin-input" placeholder="Nombres de los padrinos">
                </div>
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Mensaje</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="pad.mensaje || 'Opcional · dedicatoria o agradecimiento'"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel">
                    <textarea x-model="pad.mensaje" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Mensaje opcional para los padrinos"></textarea>
                </div>
            </div>
        </section>
    </template>
</div>
