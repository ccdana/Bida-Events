<div x-show="activeTab === 'ubicacion'" x-cloak class="space-y-3">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Ubicación',
        'title' => 'Dónde es el evento',
        'description' => 'el nombre del lugar como título, la dirección, la foto y el mapa, con botones «Cómo llegar» (Google Maps) y «Abrir en Waze».',
        'tip' => 'Cuando aparezca el mapa aquí abajo, los botones de navegación ya funcionan en la invitación.',
        'moduleKey' => 'ubicacion',
    ])

    <!-- 1. Selección de mapa de Google -->
    <section class="admin-card p-3 space-y-3">
        <p class="admin-eyebrow mb-1">Mapa de Google</p>

        <div>
            <label class="admin-label">Coordenadas de Google Maps </label>
            <div class="flex gap-2">
                <input type="text" x-model="modules.ubicacion.maps_url"
                    @paste="onMapsLinkPaste($event)"
                    @keydown.enter.prevent="applyMapsLink()"
                    class="admin-input flex-1"
                    placeholder="Ej. -17.39558,-66.16388">
                <button type="button" @click="applyMapsLink()" :disabled="mapsLinkLoading || !modules.ubicacion.maps_url"
                    class="admin-link-button text-xs shrink-0 self-stretch px-4">
                    <span x-text="mapsLinkLoading ? '…' : 'Aplicar'"></span>
                </button>
            </div>
            <p class="mt-1.5 text-xs leading-relaxed text-stone-500">
                En Google Maps mantén presionado el lugar, copia las coordenadas que aparecen y pégalas aquí. También puedes pegar el enlace de «Compartir».
            </p>
        </div>

        <!-- Vista previa de Google Maps Embed -->
        <div x-show="googleMapsEmbedUrl()" x-cloak class="space-y-2">
            <div class="rounded-xl overflow-hidden border border-stone-200 shadow-sm aspect-[16/9] bg-stone-100 min-h-[180px]">
                <iframe :src="googleMapsEmbedUrl()" width="100%" height="100%" style="border:0" allowfullscreen loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade" title="Vista previa de Google Maps" class="w-full h-full min-h-[180px]"></iframe>
            </div>
            <div class="flex items-center justify-between gap-2 text-xs">
                <span class="text-stone-500 truncate font-mono" x-text="hasLocationCoordinates() ? ('Coordenadas: ' + formatCoordinates()) : 'Google Maps'"></span>
                <a x-show="hasLocationCoordinates()" x-cloak :href="googleMapsPreviewUrl()" target="_blank" rel="noopener" class="admin-link-button text-xs shrink-0">
                    Abrir en Google Maps
                </a>
            </div>
        </div>
    </section>

    <!-- 2. Nombre del lugar -->
    <section class="admin-card p-3 space-y-2">
        <label class="admin-label mb-1">Nombre del lugar</label>
        <input type="text" x-model="modules.ubicacion.nombre_lugar" @input="schedulePreview()"
            class="admin-input" placeholder="Ej. Salón Imperial, Hacienda Los Olivos">
    </section>

    <!-- 3. Dirección visible -->
    <section class="admin-card p-3 space-y-2">
        <label class="admin-label mb-1">Dirección visible</label>
        <textarea x-model="modules.ubicacion.direccion" @input="schedulePreview()" rows="2"
            class="admin-input" placeholder="Dirección que verán los invitados"></textarea>
    </section>

    <!-- 4. Nota adicional -->
    <section class="admin-card p-3 space-y-2">
        <label class="admin-label mb-1">Nota adicional</label>
        <textarea x-model="modules.ubicacion.nota" @input="schedulePreview()" rows="2"
            class="admin-input" placeholder="Ej. Ingreso por puerta lateral, estacionamiento disponible"></textarea>
    </section>

    <!-- 5. Foto del lugar -->
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

</div>
