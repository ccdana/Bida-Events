<div x-show="activeTab === 'agendar'" x-cloak class="space-y-4">
    <section class="admin-card space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Agendar</p>
                <h2 class="font-serif text-xl text-stone-950">Google Calendar</h2>
                <p class="mt-1 text-sm text-stone-500">Agrega un botón para guardar la fecha en el calendario del teléfono (abre la app de Google Calendar en Android).</p>
                <p class="mt-1 text-xs text-stone-400">Aparece en la cuenta regresiva y en Ubicación. Si la cuenta regresiva está oculta, se muestra en su propia sección.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.agendar" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </section>
</div>
