<div x-show="activeTab === 'ubicacion'" x-cloak class="space-y-2"
    x-effect="if (activeTab === 'ubicacion') initLocationMap()">

    <!-- Resumen -->
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Ubicación</p>
                <p class="text-xs text-stone-600 truncate">Lugar y mapa del evento</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.ubicacion
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.ubicacion ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.ubicacion ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <p class="text-xs text-stone-500 leading-relaxed">
            Pega un enlace de Google Maps para fijar las coordenadas automáticamente. Activa o desactiva el módulo desde el menú lateral.
        </p>
    </section>

    <!-- Estado de coordenadas -->
    <section class="admin-card p-3 space-y-2">
        <div class="rounded-lg border p-2.5"
            :class="hasLocationCoordinates() ? 'border-emerald-200 bg-emerald-50' : 'border-stone-200 bg-stone-50'">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-xs font-semibold"
                        :class="hasLocationCoordinates() ? 'text-emerald-800' : 'text-stone-500'"
                        x-text="hasLocationCoordinates() ? 'Ubicación confirmada' : 'Sin coordenadas'"></p>
                    <p class="mt-1 text-xs leading-relaxed"
                        :class="hasLocationCoordinates() ? 'text-emerald-700' : 'text-stone-500'"
                        x-text="locationStatusMessage || 'Pega un enlace de Google Maps para fijar la ubicación.'"></p>
                </div>
                <a x-show="hasLocationCoordinates()" x-cloak
                    :href="googleMapsPreviewUrl()" target="_blank" rel="noopener"
                    class="admin-link-button text-xs shrink-0">Ver en Maps</a>
            </div>
        </div>
    </section>

    <!-- Enlace de Google Maps -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Google Maps</p>

        <div>
            <label class="admin-label">Enlace de Google Maps</label>
            <div class="flex gap-2">
                <input type="url" x-model="modules.ubicacion.maps_url"
                    @paste="onMapsLinkPaste($event)"
                    @keydown.enter.prevent="applyMapsLink()"
                    class="admin-input flex-1"
                    placeholder="https://maps.app.goo.gl/... o https://www.google.com/maps/place/...">
                <button type="button" @click="applyMapsLink()" :disabled="mapsLinkLoading || !modules.ubicacion.maps_url"
                    class="admin-link-button text-xs shrink-0 self-stretch px-4">
                    <span x-text="mapsLinkLoading ? '…' : 'Aplicar'"></span>
                </button>
            </div>
            <p class="mt-1.5 text-xs leading-relaxed text-stone-500">
                Compatible con enlaces compartidos, cortos <code class="text-stone-600">maps.app.goo.gl</code> y URLs con coordenadas. También puedes pegar y presionar Enter.
            </p>
        </div>
    </section>

    <!-- Detalles del lugar -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Detalles del lugar</p>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Nombre del lugar</label>
                <input type="text" x-model="modules.ubicacion.nombre_lugar" @input="schedulePreview()"
                    class="admin-input" placeholder="Ej. Salón Imperial, Hacienda Los Olivos">
                <p class="mt-1.5 text-xs text-stone-500">Se intenta extraer del enlace automáticamente. Puedes editarlo aquí.</p>
            </div>

            <div x-data="{ open: true }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Dirección visible</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="modules.ubicacion.direccion || 'Dirección que verán los invitados'"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel">
                    <textarea x-model="modules.ubicacion.direccion" @input="schedulePreview()" rows="2"
                        class="admin-input" placeholder="Dirección que verán los invitados"></textarea>
                </div>
            </div>
        </div>
    </section>

    <!-- Mapa interactivo -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2">
            <p class="admin-eyebrow mb-0">Mapa interactivo</p>
            <button type="button" @click="syncLocationMarker()" class="text-xs text-stone-500 hover:text-stone-800 font-medium">
                Centrar marcador
            </button>
        </div>

        <p class="text-xs text-stone-500">Haz clic en el mapa o arrastra el pin para afinar la ubicación.</p>
        <div x-ref="locationMap" class="admin-location-map"></div>
        <div class="flex flex-wrap items-center gap-2 text-xs text-stone-500">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold text-stone-600">Lat/Lng</span>
            <span class="font-mono" x-text="formatCoordinates()"></span>
        </div>
    </section>

    <!-- Imagen del lugar -->
    <section class="admin-card p-3 space-y-2">
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Foto del lugar (opcional)',
            'type' => 'image',
            'context' => 'ubicacion',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.ubicacion.imagen_lugar',
        ])
        <p class="text-xs text-stone-500">Imagen de referencia del venue que aparecerá en la invitación.</p>
    </section>

    <!-- Nota adicional -->
    <section class="admin-card p-3 space-y-2">
        <div x-data="{ open: false }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Nota adicional</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="modules.ubicacion.nota || 'Opcional · indicaciones de acceso o estacionamiento'"></p>
                </div>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            <div x-show="open" class="admin-accordion-panel">
                <textarea x-model="modules.ubicacion.nota" @input="schedulePreview()" rows="2"
                    class="admin-input" placeholder="Ej. Ingreso por puerta lateral, estacionamiento disponible"></textarea>
            </div>
        </div>
    </section>
</div>
