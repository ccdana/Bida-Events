<div x-show="activeTab === 'countdown'" x-cloak class="space-y-4">
    <section class="admin-card space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Cuenta regresiva</p>
                <h2 class="font-serif text-xl text-stone-950">Tiempo al evento</h2>
                <p class="mt-1 text-sm text-stone-500">El invitado ve los días, horas, minutos y segundos que faltan. Al llegar la fecha cambia a «¡Hoy es el gran día!».</p>
                <p class="mt-1 text-xs text-stone-400">La fecha y hora se toman de «General». Si «Calendario» está activo, se agrega el botón para agendar.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.cuenta_regresiva" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </section>
</div>
