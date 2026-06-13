<div x-show="activeTab === 'agendar'" x-cloak class="space-y-4">
    <section class="admin-card space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Agendar</p>
                <h2 class="font-serif text-xl text-stone-950">Google Calendar</h2>
                <p class="mt-1 text-sm text-stone-500">Activa el acceso rápido a calendario cuando el evento lo requiera.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.agendar" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </section>
</div>
