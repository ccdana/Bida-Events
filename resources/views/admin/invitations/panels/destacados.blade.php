<div x-show="activeTab === 'destacados'" x-cloak class="space-y-4">
    <section class="admin-card space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Cortejo</p>
                <h2 class="font-serif text-xl text-stone-950">Chambelanes y damitas</h2>
                <p class="mt-1 text-sm text-stone-500">Agrega una breve descripción para que cada invitado destaque como parte de la historia.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.destacados" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
    </section>

    <section class="admin-card space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-serif text-lg text-stone-900">Chambelanes</h3>
            <button type="button" @click="addChambelan()" class="admin-link-button">+ Agregar</button>
        </div>
        <template x-for="(p, i) in modules.destacados.chambelanes" :key="'ch'+i">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
                <div class="grid gap-2 md:grid-cols-[1.5fr_0.6fr]">
                    <input type="text" :value="typeof p === 'string' ? p : p.nombre" @input="normalizePerson(modules.destacados.chambelanes, i, 'nombre', $event.target.value)" class="admin-input" placeholder="Nombre">
                    <input type="text" :value="typeof p === 'object' ? p.iniciales : ''" @input="normalizePerson(modules.destacados.chambelanes, i, 'iniciales', $event.target.value)" class="admin-input" placeholder="Iniciales">
                </div>
                <input type="text" :value="typeof p === 'object' ? (p.detalle || '') : ''" @input="normalizePerson(modules.destacados.chambelanes, i, 'detalle', $event.target.value)" class="admin-input" placeholder="Descripción breve">
            </div>
        </template>
    </section>

    <section class="admin-card space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-serif text-lg text-stone-900">Damitas</h3>
            <button type="button" @click="addDamita()" class="admin-link-button">+ Agregar</button>
        </div>
        <template x-for="(p, i) in modules.destacados.damitas" :key="'dm'+i">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
                <div class="grid gap-2 md:grid-cols-[1.5fr_0.6fr]">
                    <input type="text" :value="typeof p === 'string' ? p : p.nombre" @input="normalizePerson(modules.destacados.damitas, i, 'nombre', $event.target.value)" class="admin-input" placeholder="Nombre">
                    <input type="text" :value="typeof p === 'object' ? p.iniciales : ''" @input="normalizePerson(modules.destacados.damitas, i, 'iniciales', $event.target.value)" class="admin-input" placeholder="Iniciales">
                </div>
                <input type="text" :value="typeof p === 'object' ? (p.detalle || '') : ''" @input="normalizePerson(modules.destacados.damitas, i, 'detalle', $event.target.value)" class="admin-input" placeholder="Descripción breve">
            </div>
        </template>
    </section>

    <section class="admin-card space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h3 class="font-serif text-lg text-stone-900">Padrinos</h3>
            <button type="button" @click="addPadrino()" class="admin-link-button">+ Agregar</button>
        </div>
        <template x-for="(pad, i) in modules.destacados.padrinos" :key="'pd'+i">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
                <input type="text" x-model="pad.rol" class="admin-input" placeholder="Rol (Padrinos de Honor)">
                <input type="text" x-model="pad.nombres" class="admin-input" placeholder="Nombres">
                <textarea x-model="pad.mensaje" rows="2" class="admin-input" placeholder="Mensaje opcional"></textarea>
            </div>
        </template>
    </section>
</div>
