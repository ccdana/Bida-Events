<div x-show="activeTab === 'fotomural'" x-cloak class="space-y-4">
    <section class="admin-card space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Fotomural</p>
                <h2 class="font-serif text-xl text-stone-950">Fotos en vivo</h2>
                <p class="mt-1 text-sm text-stone-500">Durante la fiesta, los invitados toman o suben fotos desde su teléfono y todos las ven en un mural.</p>
                <p class="mt-1 text-xs text-stone-400">Cuando termina el evento, el mural queda en modo solo lectura. En la vista previa no se pueden subir fotos.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.fotomural" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </section>
</div>
