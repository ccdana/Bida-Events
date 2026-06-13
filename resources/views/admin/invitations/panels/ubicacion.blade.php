<div x-show="activeTab === 'ubicacion'" x-cloak class="space-y-4"
    x-effect="if (activeTab === 'ubicacion') initLocationMap()">
    <section class="admin-card space-y-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Ubicación</p>
                <h2 class="font-serif text-xl text-stone-950">Lugar del evento</h2>
                <p class="mt-1 text-sm text-stone-500">Pega un enlace de Google Maps para fijar las coordenadas automáticamente.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.ubicacion" @change="schedulePreview()" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>

        {{-- Estado de coordenadas --}}
        <div class="rounded-2xl border p-4"
            :class="hasLocationCoordinates() ? 'border-emerald-200 bg-emerald-50/60' : 'border-stone-200 bg-stone-50'">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.28em]"
                        :class="hasLocationCoordinates() ? 'text-emerald-700' : 'text-stone-400'"
                        x-text="hasLocationCoordinates() ? 'Ubicación confirmada' : 'Sin coordenadas'"></p>
                    <p class="mt-1 text-sm text-stone-700" x-text="locationStatusMessage || 'Pega un enlace de Google Maps para fijar la ubicación.'"></p>
                </div>
                <a x-show="hasLocationCoordinates()" x-cloak
                    :href="googleMapsPreviewUrl()" target="_blank" rel="noopener"
                    class="admin-link-button shrink-0">Ver en Maps</a>
            </div>
        </div>

        {{-- Pegar enlace de Google Maps --}}
        <div class="space-y-3">
            <div>
                <label class="admin-label">Enlace de Google Maps</label>
                <div class="flex gap-2">
                    <input type="url" x-model="modules.ubicacion.maps_url"
                        @paste="onMapsLinkPaste($event)"
                        @keydown.enter.prevent="applyMapsLink()"
                        class="admin-input flex-1"
                        placeholder="https://maps.app.goo.gl/... o https://www.google.com/maps/place/...">
                    <button type="button" @click="applyMapsLink()" :disabled="mapsLinkLoading || !modules.ubicacion.maps_url"
                        class="admin-primary-button shrink-0 !min-h-[2.75rem] !px-4 !text-xs !uppercase !tracking-wider">
                        <span x-text="mapsLinkLoading ? '…' : 'Aplicar'"></span>
                    </button>
                </div>
                <p class="mt-2 text-[11px] leading-relaxed text-stone-400">
                    Compatible con enlaces compartidos, cortos <code class="text-stone-500">maps.app.goo.gl</code> y URLs con coordenadas.
                    También puedes pegar y presionar Enter.
                </p>
            </div>
        </div>

        {{-- Nombre del lugar --}}
        <div>
            <label class="admin-label">Nombre del lugar</label>
            <input type="text" x-model="modules.ubicacion.nombre_lugar" @input="schedulePreview()"
                class="admin-input" placeholder="Ej. Salón Imperial, Hacienda Los Olivos">
            <p class="mt-1 text-[11px] text-stone-400">Se intenta extraer del enlace automáticamente. Puedes editar el nombre aquí.</p>
        </div>

        {{-- Dirección visible --}}
        <div>
            <label class="admin-label">Dirección visible en la invitación</label>
            <textarea x-model="modules.ubicacion.direccion" @input="schedulePreview()" rows="2"
                class="admin-input" placeholder="Dirección que verán los invitados"></textarea>
        </div>

        {{-- Mapa interactivo --}}
        <div>
            <div class="flex items-center justify-between gap-3 mb-2">
                <label class="admin-label mb-0">Mapa interactivo</label>
                <button type="button" @click="syncLocationMarker()" class="text-[11px] text-stone-500 hover:text-stone-800">
                    Centrar marcador
                </button>
            </div>
            <p class="text-[11px] text-stone-400 mb-2">Haz clic en el mapa o arrastra el pin para afinar la ubicación.</p>
            <div x-ref="locationMap" class="admin-location-map"></div>
            <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px] text-stone-500">
                <span class="admin-editor-badge">Lat/Lng</span>
                <span x-text="formatCoordinates()"></span>
            </div>
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
            <textarea x-model="modules.ubicacion.nota" @input="schedulePreview()" rows="2"
                class="admin-input" placeholder="Ej. Ingreso por puerta lateral, estacionamiento disponible"></textarea>
        </div>
    </section>
</div>
