<div x-show="activeTab === 'ubicacion'" x-cloak class="admin-card space-y-4"
    x-effect="if (activeTab === 'ubicacion') initLocationMap()">
    <style>.leaflet-container { z-index: 0; font-family: inherit; }</style>
    <h2 class="font-serif text-lg text-stone-900">Ubicación</h2>

    <div>
        <label class="admin-label">Nombre del lugar</label>
        <input type="text" x-model="modules.ubicacion.nombre_lugar" class="admin-input" placeholder="Salón Imperial La Paz">
    </div>

    <div>
        <label class="admin-label">Enlace de Google Maps</label>
        <div class="flex gap-2">
            <input type="url" x-model="modules.ubicacion.maps_url" class="admin-input flex-1"
                placeholder="https://maps.google.com/... o https://goo.gl/maps/...">
            <button type="button" @click="applyMapsLink()" :disabled="!modules.ubicacion.maps_url"
                class="shrink-0 px-4 py-2 rounded-xl bg-stone-900 text-white text-xs uppercase tracking-wider disabled:opacity-40">
                Aplicar
            </button>
        </div>
        <p class="text-[10px] text-stone-400 mt-1">Pega el enlace compartido de Google Maps para ubicar el evento al instante</p>
    </div>

    <div>
        <label class="admin-label">Dirección visible</label>
        <div class="flex gap-2">
            <input type="text" x-model="modules.ubicacion.direccion" class="admin-input flex-1" placeholder="Av. Costanera 1234, La Paz">
            <button type="button" @click="geocodeAddress()" :disabled="geocodeLoading"
                class="shrink-0 px-4 py-2 rounded-xl border border-stone-300 text-stone-700 text-xs uppercase tracking-wider disabled:opacity-50">
                <span x-text="geocodeLoading ? '...' : 'Buscar'"></span>
            </button>
        </div>
    </div>

    <div>
        <label class="admin-label">Ubicación en el mapa</label>
        <p class="text-[10px] text-stone-400 mb-2">Haz clic en el mapa o arrastra el marcador para ajustar la ubicación</p>
        <div x-ref="locationMap" class="w-full h-56 rounded-xl overflow-hidden border border-stone-200 bg-stone-100 z-0"></div>
        <p class="text-[10px] text-stone-400 mt-1.5">
            Coordenadas:
            <span x-text="(modules.ubicacion.lat ?? 0).toFixed(5) + ', ' + (modules.ubicacion.lng ?? 0).toFixed(5)"></span>
        </p>
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
