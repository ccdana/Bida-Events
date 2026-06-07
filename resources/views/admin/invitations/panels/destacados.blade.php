<div x-show="activeTab === 'destacados'" x-cloak class="space-y-4">
    <div class="admin-card space-y-3">
        <div class="flex justify-between items-center">
            <h2 class="font-serif text-lg text-stone-900">Chambelanes</h2>
            <button type="button" @click="addChambelan()" class="text-xs px-3 py-1.5 bg-stone-900 text-white rounded-lg">+ Agregar</button>
        </div>
        <template x-for="(p, i) in modules.destacados.chambelanes" :key="'ch'+i">
            <div class="grid grid-cols-3 gap-2">
                <input type="text" :value="typeof p === 'string' ? p : p.nombre" @input="normalizePerson(modules.destacados.chambelanes, i, 'nombre', $event.target.value)" class="admin-input col-span-2" placeholder="Nombre">
                <input type="text" :value="typeof p === 'object' ? p.iniciales : ''" @input="normalizePerson(modules.destacados.chambelanes, i, 'iniciales', $event.target.value)" class="admin-input" placeholder="Iniciales">
            </div>
        </template>
    </div>
    <div class="admin-card space-y-3">
        <div class="flex justify-between items-center">
            <h2 class="font-serif text-lg text-stone-900">Damitas</h2>
            <button type="button" @click="addDamita()" class="text-xs px-3 py-1.5 bg-stone-900 text-white rounded-lg">+ Agregar</button>
        </div>
        <template x-for="(p, i) in modules.destacados.damitas" :key="'dm'+i">
            <div class="grid grid-cols-3 gap-2">
                <input type="text" :value="typeof p === 'string' ? p : p.nombre" @input="normalizePerson(modules.destacados.damitas, i, 'nombre', $event.target.value)" class="admin-input col-span-2" placeholder="Nombre">
                <input type="text" :value="typeof p === 'object' ? p.iniciales : ''" @input="normalizePerson(modules.destacados.damitas, i, 'iniciales', $event.target.value)" class="admin-input" placeholder="Iniciales">
            </div>
        </template>
    </div>
    <div class="admin-card space-y-3">
        <div class="flex justify-between items-center">
            <h2 class="font-serif text-lg text-stone-900">Padrinos</h2>
            <button type="button" @click="addPadrino()" class="text-xs px-3 py-1.5 bg-stone-900 text-white rounded-lg">+ Agregar</button>
        </div>
        <template x-for="(pad, i) in modules.destacados.padrinos" :key="'pd'+i">
            <div class="rounded-xl border border-stone-200 p-3 space-y-2 bg-stone-50/50">
                <input type="text" x-model="pad.rol" class="admin-input" placeholder="Rol (Padrinos de Honor)">
                <input type="text" x-model="pad.nombres" class="admin-input" placeholder="Nombres">
                <textarea x-model="pad.mensaje" rows="2" class="admin-input" placeholder="Mensaje opcional"></textarea>
            </div>
        </template>
    </div>
</div>
