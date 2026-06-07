<div x-show="activeTab === 'ubicacion'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Ubicación</h2>
    <div>
        <label class="admin-label">Nombre del lugar</label>
        <input type="text" x-model="modules.ubicacion.nombre_lugar" class="admin-input" placeholder="Salón Imperial La Paz">
    </div>
    <div>
        <label class="admin-label">Dirección</label>
        <div class="flex gap-2">
            <input type="text" x-model="modules.ubicacion.direccion" class="admin-input flex-1" placeholder="Av. Costanera 1234, La Paz">
            <button type="button" @click="geocodeAddress()" :disabled="geocodeLoading"
                class="shrink-0 px-4 py-2 rounded-xl bg-stone-900 text-white text-xs uppercase tracking-wider disabled:opacity-50">
                <span x-text="geocodeLoading ? '...' : 'Buscar'"></span>
            </button>
        </div>
        <p class="text-[10px] text-stone-400 mt-1">Obtiene coordenadas y enlace de Google Maps automáticamente</p>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="admin-label">Latitud</label>
            <input type="number" step="any" x-model.number="modules.ubicacion.lat" class="admin-input bg-stone-50">
        </div>
        <div>
            <label class="admin-label">Longitud</label>
            <input type="number" step="any" x-model.number="modules.ubicacion.lng" class="admin-input bg-stone-50">
        </div>
    </div>

    <div x-show="modules.ubicacion.maps_url" x-cloak class="rounded-xl bg-stone-50 border border-stone-200 px-3 py-2">
        <p class="text-[10px] uppercase tracking-wider text-stone-400 mb-1">Maps generado</p>
        <a :href="modules.ubicacion.maps_url" target="_blank" class="text-xs text-amber-800 break-all underline" x-text="modules.ubicacion.maps_url"></a>
    </div>

    @include('admin.partials.cloudinary-upload', [
        'label' => 'Foto del lugar (opcional)',
        'type' => 'image',
        'context' => 'ubicacion',
        'accept' => 'image/jpeg,image/png,image/webp',
        'previewExpr' => 'modules.ubicacion.imagen_lugar',
    ])

    <div>
        <label class="admin-label">Nota adicional</label>
        <textarea x-model="modules.ubicacion.nota" rows="2" class="admin-input"></textarea>
    </div>
</div>
