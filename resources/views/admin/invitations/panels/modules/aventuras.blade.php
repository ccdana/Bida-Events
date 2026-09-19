{{-- Panel de App\Modules\Card\AdventuresModule: la lista de «Aventuras por vivir» --}}
<div x-show="activeTab === 'aventuras'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Aventuras por vivir',
        'title' => 'Lo que les falta vivir juntos',
        'description' => 'la última hoja del libro: una lista de sueños y planes, cada uno en su renglón con una casilla. Si la lista queda vacía, la hoja no aparece.',
        'tip' => 'Frases cortas se leen mejor, por ejemplo «Ver el amanecer en el lago Titicaca».',
        'moduleKey' => 'aventuras',
        'countExpr' => '`${(modules.aventuras.lista || []).length} aventuras`',
    ])

    <section class="admin-card p-3 space-y-2">
        <label class="admin-eyebrow mb-1 block" for="aventuras-titulo">Título de la hoja</label>
        <input id="aventuras-titulo" type="text" x-model="modules.aventuras.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Aventuras por vivir">
    </section>

    <section class="admin-card p-3 space-y-2">
        <template x-for="(item, i) in modules.aventuras.lista" :key="i">
            <div class="flex items-center gap-2">
                <span class="w-6 text-right text-xs text-stone-500" x-text="(i + 1) + '.'"></span>
                <input type="text" x-model="item.titulo" @input="schedulePreview()" maxlength="{{ \App\Modules\Card\AdventuresModule::ITEM_LIMIT }}"
                    class="admin-input flex-1" placeholder="Ej. Aprender a bailar salsa" :aria-label="'Aventura ' + (i + 1)">
                <button type="button" @click="moveBookItem(modules.aventuras.lista, i, -1)" :disabled="i === 0"
                    class="px-2 py-1 rounded-md border border-stone-200 text-xs disabled:opacity-40" :aria-label="'Subir aventura ' + (i + 1)">↑</button>
                <button type="button" @click="moveBookItem(modules.aventuras.lista, i, 1)" :disabled="i === modules.aventuras.lista.length - 1"
                    class="px-2 py-1 rounded-md border border-stone-200 text-xs disabled:opacity-40" :aria-label="'Bajar aventura ' + (i + 1)">↓</button>
                <button type="button" @click="removeBookEntry('aventuras', 'lista', i)"
                    class="px-2 py-1 rounded-md border border-red-200 text-xs text-red-700" :aria-label="'Quitar aventura ' + (i + 1)">Quitar</button>
            </div>
        </template>

        <button type="button" @click="addBookEntry('aventuras', 'lista', {{ \App\Modules\Card\AdventuresModule::MAX_ITEMS }})"
            :disabled="(modules.aventuras.lista || []).length >= {{ \App\Modules\Card\AdventuresModule::MAX_ITEMS }}"
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 text-sm text-stone-600 disabled:opacity-50">
            <x-phosphor-plus class="w-4 h-4" aria-hidden="true" />
            <span x-text="(modules.aventuras.lista || []).length >= {{ \App\Modules\Card\AdventuresModule::MAX_ITEMS }} ? 'Llegaste al máximo de {{ \App\Modules\Card\AdventuresModule::MAX_ITEMS }}' : 'Agregar aventura'"></span>
        </button>
    </section>
</div>
