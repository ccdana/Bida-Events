<div x-show="activeTab === 'transporte'" x-cloak class="space-y-4">
    <section class="admin-card space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Transporte</p>
                <h2 class="font-serif text-xl text-stone-950">Opciones para llegar</h2>
                <p class="mt-1 text-sm text-stone-500">Muestra transportes sugeridos dentro del bloque de ubicación.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.transporte" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </section>
</div>
