@php
    $inv = $invitation;
    $defaultEventDate = $inv?->event_date?->format('Y-m-d\TH:i') ?? now()->addMonths(3)->format('Y-m-d\TH:i');
    $defaultExpires = $inv?->expires_at?->format('Y-m-d') ?? now()->addMonths(9)->format('Y-m-d');
@endphp

<div class="flex flex-col h-full">
    @unless($cloudinaryConfigured)
        <div class="shrink-0 bg-amber-50 border-b border-amber-200 text-amber-900 px-4 py-2 text-xs text-center">
            Cloudinary no configurado — los archivos se guardarán en storage local al guardar la invitación. Configura <code class="font-mono">CLOUDINARY_URL</code> en tu .env
        </div>
    @endunless

<div class="flex flex-1 min-h-0" x-data="invitationForm(@js([
    'modules' => $modulos,
    'meta' => [
        'title' => $inv?->title ?? '',
        'slug' => $inv?->slug ?? '',
        'template' => $inv?->template ?? array_key_first($templates),
        'event_type_id' => $inv?->event_type_id ?? ($eventTypes->first()?->id),
        'plan_id' => $inv?->plan_id ?? ($plans->first()?->id),
        'user_id' => $inv?->user_id ?? '',
        'event_date' => $defaultEventDate,
        'expires_at' => $defaultExpires,
        'status' => $inv?->status ?? 'draft',
    ],
    'isCreate' => $isCreate ?? false,
    'slugManual' => !($isCreate ?? false),
    'previewUrl' => route('admin.preview.frame'),
    'previewStoreUrl' => route('admin.preview.store'),
    'mediaUploadUrl' => route('admin.media.upload'),
    'itineraryIcons' => $itineraryIcons,
    'cloudinaryConfigured' => $cloudinaryConfigured,
]))" x-init="init()">

    {{-- Panel izquierdo: navegación + formulario --}}
    <div class="w-full lg:w-[480px] xl:w-[540px] shrink-0 flex flex-col border-r border-stone-200 bg-white h-full">
        {{-- Tabs --}}
        <nav class="shrink-0 flex gap-1 overflow-x-auto p-3 border-b border-stone-100 scrollbar-hide">
            <template x-for="tab in tabs" :key="tab.id">
                <button type="button" @click="activeTab = tab.id"
                    class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium uppercase tracking-wider transition whitespace-nowrap"
                    :class="activeTab === tab.id ? 'admin-tab-active' : 'text-stone-500 hover:bg-stone-100'"
                    x-text="tab.label"></button>
            </template>
        </nav>

        <form id="invitation-form" method="POST" action="{{ $formAction }}" class="flex-1 overflow-y-auto" @submit.prevent="handleFormSubmit($event)">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif

            <div class="p-4 space-y-4 pb-24">
                @include('admin.invitations.panels.general')
                @include('admin.invitations.panels.estetica')
                @include('admin.invitations.panels.bienvenida')
                @include('admin.invitations.panels.ubicacion')
                @include('admin.invitations.panels.itinerario')
                @include('admin.invitations.panels.dress-code')
                @include('admin.invitations.panels.destacados')
                @include('admin.invitations.panels.galeria')
                @include('admin.invitations.panels.multimedia')
                @include('admin.invitations.panels.interactivo')
                @include('admin.invitations.panels.regalos')
                @include('admin.invitations.panels.rsvp')
            </div>

            {{-- Hidden JSON sync --}}
            @foreach(['config','bienvenida','ubicacion','itinerario','dress_code','destacados','galeria','musica','video','playlist','hashtag','encuestas','regalos','post_evento','rsvp'] as $code)
                <input type="hidden" name="modulos[{{ $code }}]" :value="JSON.stringify(modules['{{ $code }}'] || {})">
            @endforeach
        </form>
    </div>

    {{-- Panel derecho: vista previa --}}
    <div class="hidden lg:flex flex-1 flex-col bg-stone-200/60 min-w-0">
        <div class="shrink-0 flex items-center justify-between px-4 py-2.5 border-b border-stone-200/80 bg-white/70 backdrop-blur-sm">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="previewLoading ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'"></span>
                <span class="text-xs uppercase tracking-widest text-stone-500">Vista previa en vivo</span>
            </div>
            <button type="button" @click="refreshPreview()" class="text-xs text-stone-500 hover:text-stone-800 uppercase tracking-wider">Actualizar</button>
        </div>
        <div class="flex-1 p-4 min-h-0">
            <div class="mx-auto h-full max-w-[390px] rounded-[2rem] border-[6px] border-stone-800 bg-stone-800 shadow-2xl overflow-hidden">
                <div class="h-6 bg-stone-800 flex items-center justify-center">
                    <div class="w-16 h-1 rounded-full bg-stone-600"></div>
                </div>
                <iframe x-ref="previewFrame" :src="previewUrl + '?t=' + previewTick"
                    class="w-full bg-white" style="height: calc(100% - 1.5rem)" title="Vista previa"></iframe>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function invitationForm(config) {
    return {
        modules: config.modules,
        meta: config.meta,
        slugManual: config.slugManual ?? false,
        activeTab: 'general',
        previewUrl: config.previewUrl,
        previewStoreUrl: config.previewStoreUrl,
        mediaUploadUrl: config.mediaUploadUrl,
        previewTick: Date.now(),
        previewLoading: false,
        previewTimer: null,
        mediaUploading: false,
        geocodeLoading: false,
        pendingUploads: [],
        locationMap: null,
        locationMarker: null,
        locationMapReady: false,
        itineraryIcons: config.itineraryIcons,
        moduleCodes: [
            'config', 'bienvenida', 'ubicacion', 'itinerario', 'dress_code',
            'destacados', 'galeria', 'musica', 'video', 'playlist', 'hashtag',
            'encuestas', 'regalos', 'post_evento', 'rsvp',
        ],
        tabs: [
            { id: 'general', label: 'General' },
            { id: 'estetica', label: 'Estética' },
            { id: 'bienvenida', label: 'Bienvenida' },
            { id: 'ubicacion', label: 'Ubicación' },
            { id: 'itinerario', label: 'Itinerario' },
            { id: 'dress', label: 'Dress code' },
            { id: 'destacados', label: 'Cortejo' },
            { id: 'galeria', label: 'Galería' },
            { id: 'media', label: 'Multimedia' },
            { id: 'interactivo', label: 'Interactivo' },
            { id: 'regalos', label: 'Regalos' },
            { id: 'rsvp', label: 'RSVP' },
        ],

        init() {
            this.ensureStructure();
            this.$watch('meta.template', v => { if (this.modules.config) this.modules.config.template = v; });
            this.$watch('modules', () => this.schedulePreview(), { deep: true });
            this.$watch('meta', () => this.schedulePreview(), { deep: true });
            this.$watch('modules.ubicacion.lat', () => this.syncLocationMarker());
            this.$watch('modules.ubicacion.lng', () => this.syncLocationMarker());
            this.refreshPreview();
        },

        ensureStructure() {
            const m = this.modules;
            m.config ??= { colores: {}, tipografias: {}, modulos: {}, template: this.meta.template };
            m.config.colores ??= {};
            m.config.tipografias ??= {};
            m.config.modulos ??= {};
            m.bienvenida ??= {};
            m.ubicacion ??= { lat: -16.5, lng: -68.15 };
            m.itinerario ??= { titulo: 'Itinerario', eventos: [] };
            m.dress_code ??= { sugerencias: [], colores_permitidos: [], colores_prohibidos: [] };
            m.destacados ??= { chambelanes: [], damitas: [], padrinos: [] };
            m.galeria ??= { fotos: [] };
            m.galeria.fotos ??= [];
            m.musica ??= {};
            m.video ??= {};
            m.playlist ??= {};
            m.hashtag ??= {};
            m.encuestas ??= { preguntas: [] };
            m.regalos ??= { sobres: {}, banco: {}, titulo: '' };
            m.regalos.sobres ??= {};
            m.regalos.banco ??= {};
            m.post_evento ??= {};
            m.rsvp ??= {};
        },

        schedulePreview() {
            clearTimeout(this.previewTimer);
            this.previewLoading = true;
            this.previewTimer = setTimeout(() => this.refreshPreview(), 500);
        },

        buildPreviewPayload() {
            const fd = new FormData();
            fd.append('title', this.meta.title ?? '');
            fd.append('slug', this.meta.slug ?? '');
            fd.append('template', this.meta.template ?? '');
            fd.append('event_type_id', this.meta.event_type_id ?? '');
            fd.append('plan_id', this.meta.plan_id ?? '');
            fd.append('user_id', this.meta.user_id ?? '');
            fd.append('event_date', this.meta.event_date ?? '');
            fd.append('expires_at', this.meta.expires_at ?? '');
            fd.append('status', this.meta.status ?? 'draft');
            for (const code of this.moduleCodes) {
                fd.append(`modulos[${code}]`, JSON.stringify(this.modules[code] ?? {}));
            }
            return fd;
        },

        async refreshPreview() {
            this.previewLoading = true;
            try {
                await fetch(this.previewStoreUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: this.buildPreviewPayload(),
                });
                this.previewTick = Date.now();
            } catch (e) {
                console.error(e);
            } finally {
                this.previewLoading = false;
            }
        },

        slugify(text) {
            return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        },

        onTitleInput() {
            if (config.isCreate && !this.slugManual) {
                this.meta.slug = this.slugify(this.meta.title);
            }
        },

        pickLocalFileReplace(event, type, context, getUrl, setUrl) {
            this.clearMediaUrl(getUrl());
            this.pickLocalFile(event, type, context, setUrl);
        },

        pickLocalFile(event, type, context, setUrl) {
            const file = event.target.files?.[0];
            if (!file) return;
            const blobUrl = URL.createObjectURL(file);
            this.pendingUploads.push({
                file,
                type,
                context,
                blobUrl,
                apply: (url) => setUrl(url),
            });
            setUrl(blobUrl);
            event.target.value = '';
        },

        clearMediaUrl(url) {
            if (!url || !String(url).startsWith('blob:')) return;
            const idx = this.pendingUploads.findIndex(p => p.blobUrl === url);
            if (idx >= 0) {
                URL.revokeObjectURL(this.pendingUploads[idx].blobUrl);
                this.pendingUploads.splice(idx, 1);
            }
        },

        async _uploadFile(file, type, context) {
            const fd = new FormData();
            fd.append('file', file);
            fd.append('type', type);
            fd.append('context', context);
            fd.append('slug', this.meta.slug || 'draft');
            const res = await fetch(this.mediaUploadUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: fd,
            });
            const data = await res.json();
            if (!res.ok || !data.success) throw new Error(data.message || 'Error de subida');
            return data.url;
        },

        async uploadPendingMedia() {
            for (const pending of this.pendingUploads) {
                const url = await this._uploadFile(pending.file, pending.type, pending.context);
                pending.apply(url);
                URL.revokeObjectURL(pending.blobUrl);
            }
            this.pendingUploads = [];
        },

        syncHiddenInputs() {
            const form = document.getElementById('invitation-form');
            if (!form) return;
            for (const code of this.moduleCodes) {
                const input = form.querySelector(`input[name="modulos[${code}]"]`);
                if (input) input.value = JSON.stringify(this.modules[code] ?? {});
            }
        },

        async handleFormSubmit(event) {
            const form = event.target;
            if (this.mediaUploading) return;
            this.mediaUploading = true;
            try {
                if (this.pendingUploads.length) {
                    await this.uploadPendingMedia();
                }
                this.syncHiddenInputs();
                await this.$nextTick();
                form.submit();
            } catch (e) {
                alert(e.message || 'Error al subir archivos. Intenta de nuevo.');
                this.mediaUploading = false;
            }
        },

        uploadGalleryFiles(event) {
            const files = [...(event.target.files || [])];
            for (const file of files) {
                const blobUrl = URL.createObjectURL(file);
                const index = this.modules.galeria.fotos.length;
                this.modules.galeria.fotos.push(blobUrl);
                this.pendingUploads.push({
                    file,
                    type: 'image',
                    context: 'gallery',
                    blobUrl,
                    apply: (url) => { this.modules.galeria.fotos[index] = url; },
                });
            }
            event.target.value = '';
        },

        removeGalleryPhoto(index) {
            this.clearMediaUrl(this.modules.galeria.fotos[index]);
            this.modules.galeria.fotos.splice(index, 1);
        },

        uploadPostEventPhotos(event) {
            const files = [...(event.target.files || [])];
            this.modules.post_evento.fotos ??= [];
            for (const file of files) {
                const blobUrl = URL.createObjectURL(file);
                const index = this.modules.post_evento.fotos.length;
                this.modules.post_evento.fotos.push(blobUrl);
                this.pendingUploads.push({
                    file,
                    type: 'image',
                    context: 'post-evento',
                    blobUrl,
                    apply: (url) => { this.modules.post_evento.fotos[index] = url; },
                });
            }
            event.target.value = '';
        },

        parseMapsLink(link) {
            if (!link?.trim()) return null;
            const s = link.trim();
            let m = s.match(/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            m = s.match(/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            m = s.match(/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            m = s.match(/place\/[^/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/);
            if (m) return { lat: parseFloat(m[1]), lng: parseFloat(m[2]) };
            return null;
        },

        applyMapsLink() {
            const coords = this.parseMapsLink(this.modules.ubicacion.maps_url);
            if (!coords) {
                alert('No se pudieron extraer coordenadas del enlace. Pega el enlace completo de Google Maps.');
                return;
            }
            this.modules.ubicacion.lat = coords.lat;
            this.modules.ubicacion.lng = coords.lng;
            this.syncLocationMarker();
        },

        loadStylesheet(href) {
            if (document.querySelector(`link[href="${href}"]`)) return Promise.resolve();
            return new Promise((resolve, reject) => {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = href;
                link.onload = resolve;
                link.onerror = reject;
                document.head.appendChild(link);
            });
        },

        loadScript(src) {
            if (document.querySelector(`script[src="${src}"]`)) {
                return window.L ? Promise.resolve() : new Promise(r => {
                    const check = setInterval(() => { if (window.L) { clearInterval(check); r(); } }, 50);
                });
            }
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = src;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },

        async ensureLeaflet() {
            await Promise.all([
                this.loadStylesheet('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
                this.loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
            ]);
        },

        async initLocationMap() {
            if (this.locationMapReady) {
                this.syncLocationMarker();
                return;
            }
            await this.$nextTick();
            const el = this.$refs.locationMap;
            if (!el || el.offsetParent === null) return;
            try {
                await this.ensureLeaflet();
                const lat = this.modules.ubicacion.lat ?? -16.5;
                const lng = this.modules.ubicacion.lng ?? -68.15;
                this.locationMap = L.map(el).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19,
                }).addTo(this.locationMap);
                this.locationMarker = L.marker([lat, lng], { draggable: true }).addTo(this.locationMap);
                this.locationMarker.on('dragend', () => {
                    const p = this.locationMarker.getLatLng();
                    this.modules.ubicacion.lat = p.lat;
                    this.modules.ubicacion.lng = p.lng;
                    this.modules.ubicacion.maps_url = `https://www.google.com/maps?q=${p.lat},${p.lng}`;
                });
                this.locationMap.on('click', (e) => {
                    this.modules.ubicacion.lat = e.latlng.lat;
                    this.modules.ubicacion.lng = e.latlng.lng;
                    this.modules.ubicacion.maps_url = `https://www.google.com/maps?q=${e.latlng.lat},${e.latlng.lng}`;
                    this.syncLocationMarker(false);
                });
                this.locationMapReady = true;
                setTimeout(() => this.locationMap?.invalidateSize(), 150);
            } catch (e) {
                console.error('No se pudo cargar el mapa', e);
            }
        },

        syncLocationMarker(pan = true) {
            if (!this.locationMap || !this.locationMarker) return;
            const lat = this.modules.ubicacion.lat ?? -16.5;
            const lng = this.modules.ubicacion.lng ?? -68.15;
            this.locationMarker.setLatLng([lat, lng]);
            if (pan) this.locationMap.setView([lat, lng], this.locationMap.getZoom());
        },

        async geocodeAddress() {
            const q = [this.modules.ubicacion.nombre_lugar, this.modules.ubicacion.direccion].filter(Boolean).join(', ');
            if (!q.trim()) { alert('Ingresa el nombre del lugar o la dirección'); return; }
            this.geocodeLoading = true;
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`, {
                    headers: { 'Accept-Language': 'es', 'User-Agent': 'BidaEvents/1.0' },
                });
                const data = await res.json();
                if (data?.[0]) {
                    this.modules.ubicacion.lat = parseFloat(data[0].lat);
                    this.modules.ubicacion.lng = parseFloat(data[0].lon);
                    this.modules.ubicacion.maps_url = `https://www.google.com/maps?q=${data[0].lat},${data[0].lon}`;
                    if (!this.modules.ubicacion.direccion) {
                        this.modules.ubicacion.direccion = data[0].display_name;
                    }
                    this.syncLocationMarker();
                } else {
                    alert('No se encontró la ubicación. Verifica la dirección.');
                }
            } catch (e) {
                alert('Error al buscar la ubicación');
            } finally {
                this.geocodeLoading = false;
            }
        },

        // Itinerario
        addEvento() { this.modules.itinerario.eventos.push({ hora: '20:00', titulo: '', icono: 'star', descripcion: '' }); },
        removeEvento(i) { this.modules.itinerario.eventos.splice(i, 1); },

        // Dress code
        addSugerencia() { this.modules.dress_code.sugerencias.push({ para: '', titulo: '', descripcion: '', ejemplos: [] }); },
        removeSugerencia(i) { this.modules.dress_code.sugerencias.splice(i, 1); },
        addColorPermitido() { this.modules.dress_code.colores_permitidos.push({ nombre: '', hex: '#C9A96E' }); },
        addColorProhibido() { this.modules.dress_code.colores_prohibidos.push({ nombre: '', hex: '#FFFFFF', motivo: '' }); },

        // Destacados
        addChambelan() { this.modules.destacados.chambelanes.push({ nombre: '', iniciales: '', detalle: '' }); },
        addDamita() { this.modules.destacados.damitas.push({ nombre: '', iniciales: '' }); },
        addPadrino() { this.modules.destacados.padrinos.push({ rol: '', nombres: '', mensaje: '' }); },

        // Encuestas
        addEncuesta() { this.modules.encuestas.preguntas.push({ id: 'poll-' + Date.now(), pregunta: '', opciones: ['', ''] }); },
        removeEncuesta(i) { this.modules.encuestas.preguntas.splice(i, 1); },
        addOpcion(poll) { poll.opciones.push(''); },
        removeOpcion(poll, i) { poll.opciones.splice(i, 1); },

        normalizePerson(list, i, field, val) {
            if (typeof list[i] === 'string') list[i] = { nombre: list[i], iniciales: list[i].substring(0,2).toUpperCase() };
            list[i][field] = val;
        },
    };
}
</script>
