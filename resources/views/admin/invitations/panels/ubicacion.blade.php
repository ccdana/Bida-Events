<div x-show="activeTab === 'ubicacion'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Ubicación</h2>
    <div>
        <label class="admin-label">Nombre del lugar</label>
        <input type="text" x-model="modules.ubicacion.nombre_lugar" class="admin-input">
    </div>
    <div>
        <label class="admin-label">Dirección completa</label>
        <input type="text" x-model="modules.ubicacion.direccion" class="admin-input">
    </div>
    <div>
        <label class="admin-label">URL Google Maps</label>
        <input type="url" x-model="modules.ubicacion.maps_url" class="admin-input">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="admin-label">Latitud</label>
            <input type="number" step="any" x-model.number="modules.ubicacion.lat" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Longitud</label>
            <input type="number" step="any" x-model.number="modules.ubicacion.lng" class="admin-input">
        </div>
    </div>
    <div>
        <label class="admin-label">Nota adicional</label>
        <textarea x-model="modules.ubicacion.nota" rows="2" class="admin-input"></textarea>
    </div>
</div>
