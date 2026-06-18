<script>
function invitationForm(config) {
    return {
        modules: config.modules,
        config,
        clients: config.clients ?? [],
        eventTypes: config.eventTypes ?? [],
        templateOptions: config.templateOptions ?? [],
        statusLabels: {
            draft: 'Borrador',
            active: 'Activa',
            suspended: 'Suspendida',
            expired: 'Expirada',
        },
        eventDatePart: '',
        eventTimePart: '',
        meta: config.meta,
        slugManual: config.slugManual ?? false,
        activeTab: 'general',
        activeGroup: 'config',
        previewUrl: config.previewUrl,
        previewStoreUrl: config.previewStoreUrl,
        previewKey: config.previewKey ?? 'draft',
        previewRevision: config.previewRevision ?? 0,
        clientStoreUrl: config.clientStoreUrl,
        mediaUploadUrl: config.mediaUploadUrl,
        previewTick: Date.now(),
        previewLoading: false,
        previewTimer: null,
        previewSeq: 0,
        previewAbort: null,
        previewDebounceMs: 280,
        mediaUploading: false,
        clientCreating: false,
        newClient: { name: '', email: '' },
        clientPasswords: config.clientPasswords ?? {},
        assignedClientPassword: config.assignedClientPassword ?? null,
        locationStatusMessage: '',
        mapsLinkLoading: false,
        mapsResolveUrl: config.mapsResolveUrl,
        pendingUploads: [],
        locationMap: null,
        locationMarker: null,
        locationMapReady: false,
        itineraryIcons: config.itineraryIcons,
        moduleCodes: [...(config.moduleCodes ?? [])],
        moduleTabMap: config.moduleTabMap ?? {},

        // Cropper de imágenes
        cropperOpen: false,
        cropperBlobUrl: null,
        cropperPendingIndex: null,
        cropperAspect: 1,
        cropperScale: 1,
        cropperOffsetX: 0,
        cropperOffsetY: 0,
        cropperMinScale: 1,
        cropperMaxScale: 3,
        cropperImageNaturalWidth: 0,
        cropperImageNaturalHeight: 0,
        cropperFrameWidth: 0,
        cropperFrameHeight: 0,
        cropperDragging: false,
        cropperLastX: 0,
        cropperLastY: 0,

        tabGroups: [
            {
                id: 'config',
                label: 'Configuración',
                description: 'Datos base y diseño visual',
                tabs: [
                    { id: 'general', label: 'General', hint: 'Título, cliente y fechas' },
                    { id: 'estetica', label: 'Estética', hint: 'Colores y tipografías' },
                ],
            },
            {
                id: 'presentacion',
                label: 'Presentación',
                description: 'Primera impresión del evento',
                tabs: [
                    { id: 'hero', label: 'Banner principal', moduleCode: 'bienvenida', hint: 'Portada, saludo y fecha' },
                    { id: 'ubicacion', label: 'Ubicación', moduleCode: 'ubicacion', hint: 'Lugar y mapa' },
                    { id: 'itinerario', label: 'Itinerario', moduleCode: 'itinerario', hint: 'Agenda del evento' },
                    { id: 'dress', label: 'Dress code', moduleCode: 'dress_code', hint: 'Código de vestimenta' },
                ],
            },
            {
                id: 'contenido',
                label: 'Contenido',
                description: 'Historias y medios del evento',
                tabs: [
                    { id: 'destacados', label: 'Invitados de honor', moduleCode: 'destacados', hint: 'Chambelanes, damitas y padrinos' },
                    { id: 'galeria', label: 'Galería', moduleCode: 'galeria', hint: 'Fotos del evento' },
                    { id: 'video', label: 'Video', moduleCode: 'video', hint: 'Video principal' },
                ],
            },
            {
                id: 'interaccion',
                label: 'Interacción',
                description: 'Participación de los invitados',
                tabs: [
                    { id: 'musica', label: 'Música', moduleCode: 'musica', hint: 'Reproductor de fondo' },
                    { id: 'playlist', label: 'Playlist', moduleCode: 'playlist', hint: 'Canciones de invitados' },
                    { id: 'hashtag', label: 'Hashtag', moduleCode: 'hashtag', hint: 'Etiqueta del evento' },
                    { id: 'encuestas', label: 'Encuestas', moduleCode: 'encuestas', hint: 'Preguntas para invitados' },
                    { id: 'regalos', label: 'Regalos', moduleCode: 'regalos', hint: 'Mesa de regalos' },
                    { id: 'rsvp', label: 'RSVP', moduleCode: 'rsvp', hint: 'Confirmación de asistencia' },
                ],
            },
            {
                id: 'logistica',
                label: 'Logística',
                description: 'Utilidades antes y después del evento',
                tabs: [
                    { id: 'countdown', label: 'Cuenta regresiva', moduleCode: 'cuenta_regresiva', hint: 'Countdown al evento' },
                    { id: 'agendar', label: 'Agendar', moduleCode: 'agendar', hint: 'Calendario' },
                    { id: 'transporte', label: 'Transporte', moduleCode: 'transporte', hint: 'Ubicación y traslado' },
                    { id: 'fotomural', label: 'Fotomural', moduleCode: 'fotomural', hint: 'Fotos en vivo' },
                    { id: 'post_evento', label: 'Post-evento', moduleCode: 'post_evento', hint: 'Contenido posterior' },
                ],
            },
        ],

        colorLabels: {
            primary: 'Principal',
            secondary: 'Secundario',
            accent: 'Acento',
            text: 'Texto',
            background: 'Fondo',
        },
        fontOptions: {
            titulos: [
                'Playfair Display', 'Cormorant Garamond', 'Cinzel', 'Libre Baskerville',
                'Bodoni Moda', 'Prata', 'Lora', 'Merriweather',
            ],
            cuerpo: [
                'Montserrat', 'Inter', 'Lato', 'Nunito Sans', 'Source Sans 3',
                'Poppins', 'Raleway', 'Open Sans',
            ],
            script: [
                'Great Vibes', 'Parisienne', 'Alex Brush', 'Dancing Script',
                'Sacramento', 'Allura', 'Tangerine', 'Petit Formal Script',
            ],
        },

        get activeGroupData() {
            return this.tabGroups.find(group => group.id === this.activeGroup) ?? this.tabGroups[0];
        },

        get activeGroupTabs() {
            return this.activeGroupData?.tabs ?? [];
        },

        get activeModulesCount() {
            const flags = this.modules.config?.modulos ?? {};
            return Object.values(flags).filter(Boolean).length;
        },

        init() {
            this.ensureStructure();
            this.normalizeMetaSelects();
            this.initEventDateFields();
            this.syncAssignedClientPassword();
            this.syncActiveGroup();
            this.$watch('meta.template', v => { if (this.modules.config) this.modules.config.template = v; });
            this.$watch('modules', () => this.schedulePreview(), { deep: true });
            this.$watch('meta', () => this.schedulePreview(), { deep: true });
            this.$watch('modules.ubicacion.lat', () => this.syncLocationMarker());
            this.$watch('modules.ubicacion.lng', () => this.syncLocationMarker());
            this.$watch('cropperScale', (value, oldValue) => {
                const numValue = Number(value) || 1;
                const newScale = Math.max(this.cropperMinScale, Math.min(this.cropperMaxScale, numValue));
                
                if (numValue !== newScale) {
                    this.cropperScale = newScale;
                    return;
                }

                const old = Number(oldValue);
                if (old > 0 && old !== newScale) {
                    const ratio = newScale / old;
                    this.cropperOffsetX *= ratio;
                    this.cropperOffsetY *= ratio;
                }
                
                this.clampCropperOffsets();
            });
            window.addEventListener('resize', () => {
                if (this.cropperOpen) {
                    this.syncCropperFrameSize();
                }
            });
            this.runPreviewUpdate();
        },

        heroDefaults() {
            return {
                nombre_quinceanera: '',
                subtitulo: '',
                mensaje: '',
                fecha_texto: '',
                mensaje_post_evento: '',
                imagen_hero: null,
            };
        },

        ensureStructure() {
            const m = this.modules;
            const savedHero = this.plainModuleValue(m.bienvenida);
            m.bienvenida = { ...this.heroDefaults(), ...savedHero };

            const objectModules = ['musica', 'video', 'playlist', 'hashtag', 'post_evento', 'rsvp'];
            for (const code of objectModules) {
                m[code] = {
                    ...this.plainModuleValue(m[code]),
                };
            }

            m.config ??= { colores: {}, tipografias: {}, modulos: {}, template: this.meta.template };
            m.config.colores ??= {};
            m.config.tipografias ??= {};
            m.config.modulos = {
                ...this.defaultModuleVisibility(),
                ...m.config.modulos,
            };
            m.config.colores = {
                primary: '#C9A96E',
                secondary: '#2C1810',
                accent: '#F5E6D3',
                text: '#1A1A1A',
                background: '#FFFAF5',
                ...m.config.colores,
            };
            m.config.tipografias = {
                titulos: 'Playfair Display',
                cuerpo: 'Montserrat',
                script: 'Great Vibes',
                ...m.config.tipografias,
            };
            m.ubicacion ??= { lat: -16.5, lng: -68.15 };
            m.ubicacion.lat ??= -16.5;
            m.ubicacion.lng ??= -68.15;
            m.ubicacion.maps_url ??= '';
            m.ubicacion.nombre_lugar ??= '';
            m.ubicacion.direccion ??= '';
            m.ubicacion.nota ??= '';
            this.updateLocationStatusMessage();
            m.itinerario ??= { titulo: 'Itinerario', eventos: [] };
            m.dress_code ??= { sugerencias: [], colores_permitidos: [], colores_prohibidos: [] };
            m.destacados ??= { chambelanes: [], damitas: [], padrinos: [] };
            m.destacados.chambelanes ??= [];
            m.destacados.damitas ??= [];
            m.destacados.padrinos ??= [];
            m.galeria ??= { fotos: [] };
            m.galeria.fotos ??= [];
            m.musica ??= {};
            m.video ??= {};
            m.playlist ??= {};
            m.hashtag ??= {};
            m.encuestas ??= { preguntas: [] };
            m.encuestas.preguntas ??= [];
            m.regalos ??= { sobres: {}, banco: {}, titulo: '', opciones: [] };
            m.regalos.sobres ??= {};
            m.regalos.banco ??= {};
            m.regalos.opciones ??= [];
            m.post_evento ??= {};
            m.rsvp ??= {};
            (m.encuestas.preguntas || []).forEach(poll => this.ensurePollDefaults(poll));
        },

        plainModuleValue(value) {
            if (value === null || value === undefined) return {};
            if (typeof value !== 'object') return {};

            const out = {};
            for (const key of Object.keys(value)) {
                if (Array.isArray(value) && /^\d+$/.test(key)) continue;
                const entry = value[key];
                if (entry !== undefined && typeof entry !== 'function') {
                    out[key] = entry;
                }
            }

            try {
                return JSON.parse(JSON.stringify(out));
            } catch {
                return out;
            }
        },

        plainModule(code) {
            return this.plainModuleValue(this.modules?.[code]);
        },

        moduleToJson(code) {
            return JSON.stringify(this.plainModule(code));
        },

        async blobUrlToDataUrl(url) {
            if (!url || !String(url).startsWith('blob:')) return url;
            try {
                const response = await fetch(url);
                const blob = await response.blob();
                return await new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = reject;
                    reader.readAsDataURL(blob);
                });
            } catch {
                return url;
            }
        },

        async prepareModuleForTransport(code) {
            const data = this.plainModule(code);
            const mediaFields = {
                bienvenida: ['imagen_hero'],
                ubicacion: ['imagen_lugar'],
                video: ['video_url', 'poster'],
                musica: ['audio_url'],
                regalos: [],
            };

            for (const field of mediaFields[code] ?? []) {
                if (data[field]) {
                    data[field] = await this.blobUrlToDataUrl(data[field]);
                }
            }

            if (code === 'galeria' && Array.isArray(data.fotos)) {
                data.fotos = await Promise.all(
                    data.fotos.map((url) => this.blobUrlToDataUrl(url))
                );
            }

            if (code === 'post_evento' && Array.isArray(data.fotos)) {
                data.fotos = await Promise.all(
                    data.fotos.map((url) => this.blobUrlToDataUrl(url))
                );
            }

            if (code === 'regalos' && data.banco?.qr_url) {
                data.banco.qr_url = await this.blobUrlToDataUrl(data.banco.qr_url);
            }

            return data;
        },

        syncActiveGroup() {
            const group = this.tabGroups.find(item => item.tabs.some(tab => tab.id === this.activeTab));
            if (group) this.activeGroup = group.id;
        },

        selectGroup(groupId) {
            const group = this.tabGroups.find(item => item.id === groupId);
            if (!group) return;
            this.activeGroup = groupId;
            if (!group.tabs.some(tab => tab.id === this.activeTab)) {
                this.activeTab = group.tabs[0]?.id ?? this.activeTab;
            }
        },

        selectTab(tabId) {
            this.activeTab = tabId;
            this.syncActiveGroup();
        },

        groupActiveCount(groupId) {
            const group = this.tabGroups.find(item => item.id === groupId);
            if (!group) return 0;
            return group.tabs.filter(tab => !tab.moduleCode || this.modules.config.modulos[tab.moduleCode]).length;
        },

        isTabEnabled(tab) {
            return !tab.moduleCode || !!this.modules.config.modulos[tab.moduleCode];
        },

        toggleModuleForTab(tab) {
            if (!tab.moduleCode) return;
            const enabled = !this.modules.config.modulos[tab.moduleCode];
            this.modules.config.modulos[tab.moduleCode] = enabled;
            this.onModuleToggle(tab.moduleCode, enabled);
        },

        defaultModuleVisibility() {
            return {
                bienvenida: true,
                video: false,
                musica: false,
                galeria: false,
                itinerario: false,
                dress_code: false,
                destacados: false,
                ubicacion: false,
                transporte: false,
                hashtag: false,
                encuestas: false,
                playlist: false,
                regalos: false,
                rsvp: false,
                fotomural: false,
                cuenta_regresiva: false,
                agendar: false,
                post_evento: false,
            };
        },

        schedulePreview() {
            clearTimeout(this.previewTimer);
            this.previewLoading = true;
            this.previewTimer = setTimeout(() => this.runPreviewUpdate(), this.previewDebounceMs);
        },

        previewFrameUrl() {
            return `${this.previewUrl}?key=${encodeURIComponent(this.previewKey)}&rev=${this.previewRevision}&_=${this.previewTick}`;
        },

        reloadPreviewFrame() {
            const frame = this.$refs.previewFrame;
            if (!frame) return;

            const url = this.previewFrameUrl();
            if (frame.getAttribute('data-preview-url') === url) {
                frame.src = 'about:blank';
            }

            frame.setAttribute('data-preview-url', url);
            frame.src = url;
        },

        async buildPreviewState() {
            const modulos = {};

            for (const code of this.moduleCodes) {
                modulos[code] = await this.prepareModuleForTransport(code);
            }

            return {
                title: this.meta.title ?? '',
                slug: this.meta.slug ?? '',
                template: this.meta.template ?? '',
                event_date: this.meta.event_date ?? '',
                expires_at: this.meta.expires_at ?? '',
                preview_key: this.previewKey,
                modulos,
            };
        },

        async runPreviewUpdate() {
            const seq = ++this.previewSeq;

            if (this.previewAbort) {
                this.previewAbort.abort();
            }

            this.previewAbort = new AbortController();
            const signal = this.previewAbort.signal;
            this.previewLoading = true;

            try {
                const payload = await this.buildPreviewState();
                if (seq !== this.previewSeq) return;

                const response = await fetch(this.previewStoreUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    signal,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(payload),
                });

                if (seq !== this.previewSeq) return;

                if (!response.ok) {
                    const message = await response.text();
                    throw new Error(message || `Error ${response.status} al actualizar la vista previa`);
                }

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo guardar la vista previa');
                }

                this.previewRevision = data.revision ?? Date.now();
                this.previewTick = Date.now();
                this.reloadPreviewFrame();
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    console.error('Preview error:', error);
                }
            } finally {
                if (seq === this.previewSeq) {
                    this.previewLoading = false;
                }
            }
        },

        refreshPreview() {
            this.runPreviewUpdate();
        },

        slugify(text) {
            return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        },

        onTitleInput() {
            if (config.isCreate && !this.slugManual) {
                this.meta.slug = this.slugify(this.meta.title);
            }
            this.schedulePreview();
        },

        async createClient() {
            if (!this.newClient.name?.trim() || !this.newClient.email?.trim()) {
                alert('Completa nombre y email del cliente.');
                return;
            }
            this.clientCreating = true;
            try {
                const res = await fetch(this.clientStoreUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(this.newClient),
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    const message = data.message || Object.values(data.errors || {})[0]?.[0] || 'No se pudo crear el cliente.';
                    throw new Error(message);
                }
                const client = {
                    ...data.client,
                    id: String(data.client.id),
                };
                this.clients.push(client);
                this.meta.user_id = client.id;
                this.clientPasswords[client.id] = data.client.tempPassword;
                this.assignedClientPassword = data.client.tempPassword;
                this.newClient = { name: '', email: '' };
            } catch (error) {
                alert(error.message || 'No se pudo crear el cliente.');
            } finally {
                this.clientCreating = false;
            }
        },

        normalizeMetaSelects() {
            if (this.meta.user_id !== '' && this.meta.user_id != null) {
                this.meta.user_id = String(this.meta.user_id);
            }
            if (this.meta.event_type_id != null && this.meta.event_type_id !== '') {
                this.meta.event_type_id = String(this.meta.event_type_id);
            }
            this.clients = (this.clients ?? []).map(client => ({
                ...client,
                id: String(client.id),
            }));
            this.clientPasswords = Object.fromEntries(
                Object.entries(this.clientPasswords ?? {}).map(([id, password]) => [String(id), password])
            );
        },

        syncAssignedClientPassword() {
            if (!this.meta.user_id) {
                this.assignedClientPassword = null;
                return;
            }
            const id = String(this.meta.user_id);
            this.assignedClientPassword = this.clientPasswords[id] ?? null;
        },

        onClientChange() {
            this.syncAssignedClientPassword();
        },

        initEventDateFields() {
            const [date = '', time = '12:00'] = (this.meta.event_date || '').split('T');
            this.eventDatePart = date;
            this.eventTimePart = time.slice(0, 5) || '12:00';

            this.$watch('eventDatePart', () => this.syncEventDateTime());
            this.$watch('eventTimePart', () => this.syncEventDateTime());
        },

        syncEventDateTime() {
            if (!this.eventDatePart) {
                return;
            }
            this.meta.event_date = `${this.eventDatePart}T${this.eventTimePart || '12:00'}`;
        },

        getEventTypeName() {
            const id = String(this.meta.event_type_id ?? '');
            return this.eventTypes.find(type => String(type.id) === id)?.name ?? 'Seleccionar tipo';
        },

        getTemplateLabel() {
            const value = String(this.meta.template ?? '');
            return this.templateOptions.find(option => option.value === value)?.label ?? 'Seleccionar plantilla';
        },

        getStatusLabel() {
            return this.statusLabels[this.meta.status] ?? this.meta.status ?? 'Seleccionar estado';
        },

        getAssignedClient() {
            if (!this.meta.user_id) {
                return null;
            }
            const id = String(this.meta.user_id);
            return this.clients.find(c => String(c.id) === id) ?? null;
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
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
            this.schedulePreview();
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

        getCropAspectForContext(context) {
            switch (context) {
                case 'galeria':
                case 'gallery':
                    return 4 / 3;
                case 'video-poster':
                    return 16 / 9;
                case 'bienvenida':
                    return 4 / 5;
                case 'ubicacion':
                    return 3 / 2;
                default:
                    return 1;
            }
        },

        openImageCropperFromGallery(index) {
            const url = this.modules.galeria.fotos[index];
            this.openImageCropper(url, 'gallery');
        },

        openImageCropper(url, contextOverride = null) {
            if (!url || !String(url).startsWith('blob:')) return;
            const idx = this.pendingUploads.findIndex(p => p.blobUrl === url);
            if (idx === -1) return;
            const entry = this.pendingUploads[idx];
            const context = contextOverride || entry.context;
            this.cropperPendingIndex = idx;
            this.cropperBlobUrl = url;
            this.cropperAspect = this.getCropAspectForContext(context);
            this.cropperScale = this.cropperMinScale;
            this.cropperOffsetX = 0;
            this.cropperOffsetY = 0;
            this.cropperImageNaturalWidth = 0;
            this.cropperImageNaturalHeight = 0;
            this.cropperDragging = false;
            this.cropperOpen = true;
            this.$nextTick(() => {
                this.syncCropperFrameSize();
            });
        },

        closeImageCropper() {
            this.cropperOpen = false;
            this.cropperDragging = false;
        },

        onCropperImageLoad(event) {
            const img = event?.target;
            if (!img) return;
            this.cropperImageNaturalWidth = img.naturalWidth || 0;
            this.cropperImageNaturalHeight = img.naturalHeight || 0;
            this.syncCropperFrameSize();
            this.cropperScale = this.cropperMinScale;
            this.cropperOffsetX = 0;
            this.cropperOffsetY = 0;
        },

        syncCropperFrameSize() {
            const frame = this.$refs.cropperFrame;
            if (!frame) return;
            this.cropperFrameWidth = frame.clientWidth || 0;
            this.cropperFrameHeight = frame.clientHeight || 0;
            this.clampCropperOffsets();
        },

        getCropperRenderMetrics() {
            const fw = this.cropperFrameWidth || 0;
            const fh = this.cropperFrameHeight || 0;
            const iw = this.cropperImageNaturalWidth || 0;
            const ih = this.cropperImageNaturalHeight || 0;
            if (!fw || !fh || !iw || !ih) {
                return { renderW: 0, renderH: 0, maxOffsetX: 0, maxOffsetY: 0 };
            }

            const imageAspect = iw / ih;
            const frameAspect = fw / fh;
            const baseW = imageAspect > frameAspect ? fh * imageAspect : fw;
            const baseH = imageAspect > frameAspect ? fh : fw / imageAspect;
            const zoom = Math.max(this.cropperMinScale, Math.min(this.cropperMaxScale, this.cropperScale || 1));
            const renderW = baseW * zoom;
            const renderH = baseH * zoom;
            return {
                renderW,
                renderH,
                maxOffsetX: Math.max(0, (renderW - fw) / 2),
                maxOffsetY: Math.max(0, (renderH - fh) / 2),
            };
        },

        clampCropperOffsets() {
            const m = this.getCropperRenderMetrics();
            this.cropperOffsetX = Math.max(-m.maxOffsetX, Math.min(m.maxOffsetX, this.cropperOffsetX));
            this.cropperOffsetY = Math.max(-m.maxOffsetY, Math.min(m.maxOffsetY, this.cropperOffsetY));
        },

        cropperImageStyle() {
            const m = this.getCropperRenderMetrics();
            if (!m.renderW || !m.renderH) {
                return '';
            }
            const left = (this.cropperFrameWidth / 2) - (m.renderW / 2) + this.cropperOffsetX;
            const top = (this.cropperFrameHeight / 2) - (m.renderH / 2) + this.cropperOffsetY;
            return `width:${m.renderW}px;height:${m.renderH}px;left:${left}px;top:${top}px;`;
        },

        startCropDrag(event) {
            const point = event.touches ? event.touches[0] : event;
            this.cropperDragging = true;
            this.cropperLastX = point.clientX;
            this.cropperLastY = point.clientY;
        },

        onCropDrag(event) {
            if (!this.cropperDragging) return;
            const point = event.touches ? (event.touches[0] || event.changedTouches?.[0]) : event;
            if (!point) return;
            const dx = point.clientX - this.cropperLastX;
            const dy = point.clientY - this.cropperLastY;
            this.cropperOffsetX += dx;
            this.cropperOffsetY += dy;
            this.clampCropperOffsets();
            this.cropperLastX = point.clientX;
            this.cropperLastY = point.clientY;
        },

        endCropDrag() {
            this.cropperDragging = false;
        },

        onCropWheel(event) {
            const delta = event.deltaY > 0 ? -0.1 : 0.1;
            this.cropperScale = Math.max(this.cropperMinScale, Math.min(this.cropperMaxScale, this.cropperScale + delta));
        },

        async applyImageCrop() {
            try {
                const idx = this.cropperPendingIndex;
                if (idx == null || idx < 0 || idx >= this.pendingUploads.length) {
                    this.closeImageCropper();
                    return;
                }
                const entry = this.pendingUploads[idx];
                if (!entry || !entry.file || !this.cropperBlobUrl) {
                    this.closeImageCropper();
                    return;
                }

                const image = new Image();
                image.crossOrigin = 'anonymous';
                const src = this.cropperBlobUrl;
                const loadPromise = new Promise((resolve, reject) => {
                    image.onload = () => resolve();
                    image.onerror = (e) => reject(e);
                });
                image.src = src;
                await loadPromise;

                const targetWidth = 800;
                const targetHeight = targetWidth / this.cropperAspect;
                const canvas = document.createElement('canvas');
                canvas.width = targetWidth;
                canvas.height = targetHeight;
                const ctx = canvas.getContext('2d');
                if (!ctx) {
                    this.closeImageCropper();
                    return;
                }

                this.clampCropperOffsets();
                const m = this.getCropperRenderMetrics();
                if (!m.renderW || !m.renderH || !this.cropperFrameWidth || !this.cropperFrameHeight) {
                    this.closeImageCropper();
                    return;
                }

                const left = (this.cropperFrameWidth / 2) - (m.renderW / 2) + this.cropperOffsetX;
                const top = (this.cropperFrameHeight / 2) - (m.renderH / 2) + this.cropperOffsetY;
                const srcX = Math.max(0, (0 - left) * (image.width / m.renderW));
                const srcY = Math.max(0, (0 - top) * (image.height / m.renderH));
                const srcW = Math.min(image.width - srcX, this.cropperFrameWidth * (image.width / m.renderW));
                const srcH = Math.min(image.height - srcY, this.cropperFrameHeight * (image.height / m.renderH));

                ctx.drawImage(image, srcX, srcY, srcW, srcH, 0, 0, targetWidth, targetHeight);

                const mimeType = entry.file.type && entry.file.type.startsWith('image/')
                    ? entry.file.type
                    : 'image/jpeg';

                const blob = await new Promise((resolve) => canvas.toBlob(resolve, mimeType, 0.9));
                if (!blob) {
                    this.closeImageCropper();
                    return;
                }

                const newFile = new File([blob], entry.file.name || 'crop.jpg', { type: blob.type });
                const newBlobUrl = URL.createObjectURL(newFile);

                // Actualizar entry y vista previa
                URL.revokeObjectURL(entry.blobUrl);
                entry.file = newFile;
                entry.blobUrl = newBlobUrl;
                if (typeof entry.apply === 'function') {
                    entry.apply(newBlobUrl);
                }

                this.closeImageCropper();
                this.schedulePreview();
            } catch (e) {
                console.error(e);
                alert('No se pudo aplicar el recorte. Intenta de nuevo.');
                this.closeImageCropper();
            }
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
                if (input) input.value = this.moduleToJson(code);
            }
        },

        onModuleToggle(code, enabled) {
            const tab = this.moduleTabMap[code];
            if (tab) {
                this.selectTab(tab);
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

        hasLocationCoordinates() {
            const lat = Number(this.modules.ubicacion?.lat);
            const lng = Number(this.modules.ubicacion?.lng);
            return Number.isFinite(lat) && Number.isFinite(lng) && !(lat === 0 && lng === 0);
        },

        formatCoordinates() {
            if (!this.hasLocationCoordinates()) return 'Sin definir';
            return `${Number(this.modules.ubicacion.lat).toFixed(5)}, ${Number(this.modules.ubicacion.lng).toFixed(5)}`;
        },

        googleMapsPreviewUrl() {
            if (!this.hasLocationCoordinates()) return '#';
            const lat = this.modules.ubicacion.lat;
            const lng = this.modules.ubicacion.lng;
            return this.modules.ubicacion.maps_url?.trim() || this.buildMapsUrl(lat, lng);
        },

        buildMapsUrl(lat, lng) {
            return `https://www.google.com/maps?q=${lat},${lng}`;
        },

        updateLocationStatusMessage() {
            if (!this.hasLocationCoordinates()) {
                this.locationStatusMessage = 'Pega un enlace de Google Maps para fijar la ubicación.';
                return;
            }
            const place = this.modules.ubicacion.nombre_lugar?.trim();
            const address = this.modules.ubicacion.direccion?.trim();
            this.locationStatusMessage = place || address || 'Coordenadas listas para el mapa de la invitación.';
        },

        setLocationCoordinates(lat, lng, options = {}) {
            this.modules.ubicacion.lat = lat;
            this.modules.ubicacion.lng = lng;
            if (options.mapsUrl) {
                this.modules.ubicacion.maps_url = options.mapsUrl;
            } else {
                this.modules.ubicacion.maps_url = this.buildMapsUrl(lat, lng);
            }
            this.updateLocationStatusMessage();
            this.syncLocationMarker(options.pan !== false);
            this.schedulePreview();
        },

        parseMapsLink(link) {
            if (!link?.trim()) return null;
            const s = link.trim();
            const patterns = [
                /@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /[?&]query=(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /[?&]ll=(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/,
                /place\/[^/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
            ];
            for (const pattern of patterns) {
                const m = s.match(pattern);
                if (m) {
                    const lat = parseFloat(m[1]);
                    const lng = parseFloat(m[2]);
                    if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                        return { lat, lng };
                    }
                }
            }
            return null;
        },

        looksLikeMapsLink(value) {
            const text = String(value || '').trim();
            return /(google\.[a-z.]+\/maps|maps\.app\.goo\.gl|goo\.gl\/maps)/i.test(text);
        },

        async onMapsLinkPaste(event) {
            const pasted = event.clipboardData?.getData('text')?.trim();
            if (!pasted || !this.looksLikeMapsLink(pasted)) return;
            event.preventDefault();
            this.modules.ubicacion.maps_url = pasted;
            await this.$nextTick();
            this.applyMapsLink();
        },

        async applyMapsLink() {
            const url = this.modules.ubicacion.maps_url?.trim();
            if (!url) return;

            this.mapsLinkLoading = true;
            try {
                let coords = this.parseMapsLink(url);

                if (!coords) {
                    const response = await fetch(this.mapsResolveUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ url }),
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'No se pudieron extraer coordenadas del enlace.');
                    }
                    coords = { lat: data.lat, lng: data.lng };
                    if (data.maps_url) {
                        this.modules.ubicacion.maps_url = data.maps_url;
                    }
                }

                // Intentar extraer el nombre del establecimiento de la URL
                const extractedName = this.extractPlaceNameFromMapsUrl(this.modules.ubicacion.maps_url || url);
                if (extractedName && !this.modules.ubicacion.nombre_lugar) {
                    this.modules.ubicacion.nombre_lugar = extractedName;
                }

                this.setLocationCoordinates(coords.lat, coords.lng, {
                    mapsUrl: this.modules.ubicacion.maps_url,
                    pan: true,
                });
            } catch (error) {
                alert(error.message || 'No se pudo aplicar el enlace de Google Maps.');
            } finally {
                this.mapsLinkLoading = false;
            }
        },

        extractPlaceNameFromMapsUrl(url) {
            if (!url) return null;
            try {
                // Formato: /maps/place/Nombre+Del+Lugar/ o /maps/place/Nombre%20Del%20Lugar/
                const placeMatch = url.match(/\/maps\/place\/([^/@?]+)/);
                if (placeMatch) {
                    const raw = decodeURIComponent(placeMatch[1].replace(/\+/g, ' ')).trim();
                    // Descartar si parece coordenadas o código interno
                    if (raw && !/^[-\d.,]+$/.test(raw) && raw.length > 2) {
                        return raw;
                    }
                }
            } catch { /* silent */ }
            return null;
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
                    this.setLocationCoordinates(p.lat, p.lng);
                });
                this.locationMap.on('click', (e) => {
                    this.setLocationCoordinates(e.latlng.lat, e.latlng.lng, { pan: false });
                });
                this.locationMapReady = true;
                setTimeout(() => this.locationMap?.invalidateSize(), 150);
                this.syncLocationMarker(false);
            } catch (e) {
                console.error('No se pudo cargar el mapa', e);
            }
        },

        syncLocationMarker(pan = true) {
            if (!this.locationMap || !this.locationMarker) return;
            const lat = this.modules.ubicacion.lat ?? -16.5;
            const lng = this.modules.ubicacion.lng ?? -68.15;
            this.locationMarker.setLatLng([lat, lng]);
            if (pan) this.locationMap.setView([lat, lng], Math.max(this.locationMap.getZoom(), 15));
            this.updateLocationStatusMessage();
        },

        addEvento() { this.modules.itinerario.eventos.push({ hora: '20:00', titulo: '', icono: 'star', descripcion: '' }); },
        removeEvento(i) { this.modules.itinerario.eventos.splice(i, 1); },
        addSugerencia() { this.modules.dress_code.sugerencias.push({ para: '', titulo: '', descripcion: '', ejemplos: [] }); },
        removeSugerencia(i) { this.modules.dress_code.sugerencias.splice(i, 1); },
        addColorPermitido() { this.modules.dress_code.colores_permitidos.push({ nombre: '', hex: '#C9A96E' }); },
        addColorProhibido() { this.modules.dress_code.colores_prohibidos.push({ nombre: '', hex: '#FFFFFF', motivo: '' }); },
        addChambelan() { this.modules.destacados.chambelanes.push({ nombre: '', iniciales: '', detalle: '' }); },
        addDamita() { this.modules.destacados.damitas.push({ nombre: '', iniciales: '', detalle: '' }); },
        addPadrino() { this.modules.destacados.padrinos.push({ rol: '', nombres: '', mensaje: '' }); },

        ensurePollDefaults(poll) {
            poll.tipo ??= 'single';
            poll.pregunta ??= '';
            poll.opciones ??= [];
            if (poll.tipo === 'rating') poll.opciones = poll.opciones.length ? poll.opciones : ['1', '2', '3', '4', '5'];
            if (poll.tipo === 'yesno') poll.opciones = poll.opciones.length ? poll.opciones : ['Si', 'No'];
            if (poll.tipo === 'emoji') poll.opciones = poll.opciones.length ? poll.opciones : ['😍', '✨', '🎉', '💖', '🔥'];
            if (poll.tipo === 'single' && poll.opciones.length < 2) poll.opciones = ['Opcion 1', 'Opcion 2'];
        },
        addEncuesta(tipo = 'single') {
            const poll = { id: 'poll-' + Date.now(), tipo, pregunta: '', opciones: [] };
            this.ensurePollDefaults(poll);
            this.modules.encuestas.preguntas.push(poll);
        },
        removeEncuesta(i) { this.modules.encuestas.preguntas.splice(i, 1); },
        addOpcion(poll) { poll.opciones.push(''); },
        removeOpcion(poll, i) { poll.opciones.splice(i, 1); },
        setPollType(poll, type) {
            poll.tipo = type;
            this.ensurePollDefaults(poll);
        },

        addGiftOption() {
            this.modules.regalos.opciones.push({ titulo: '', descripcion: '', enlace: '', icono: 'gift' });
        },
        removeGiftOption(i) {
            this.modules.regalos.opciones.splice(i, 1);
        },

        normalizePerson(list, i, field, val) {
            if (typeof list[i] === 'string') list[i] = { nombre: list[i], iniciales: list[i].substring(0,2).toUpperCase() };
            list[i][field] = val;
        },

        async handleFormSubmit(event) {
            if (this.mediaUploading) return; // already submitting
            this.mediaUploading = true;
            try {
                // Trigger any pending cloudinary uploads registered via custom event
                const pending = this.$el.querySelectorAll('[data-pending-upload]');
                if (pending.length > 0) {
                    const uploads = Array.from(pending).map(el => {
                        return new Promise(resolve => {
                            el.addEventListener('upload-done', resolve, { once: true });
                            el.dispatchEvent(new CustomEvent('trigger-upload'));
                        });
                    });
                    await Promise.all(uploads);
                }
                // Native form submit (bypass Alpine prevent)
                event.target.submit();
            } catch (err) {
                console.error('Error al guardar la invitación:', err);
                this.mediaUploading = false;
            }
        },

        // Funciones para presets de paletas de colores
        getColorPresets() {
            return [
                {
                    name: 'Elegancia Clásica',
                    description: 'Neutro elegante',
                    colors: { primary: '#C9A96E', secondary: '#2C1810', accent: '#F5E6D3', text: '#1A1A1A', background: '#FFFAF5' }
                },
                {
                    name: 'Rosa Romántico',
                    description: 'Tonos rosados',
                    colors: { primary: '#E75B8C', secondary: '#7B2D5C', accent: '#FFD4E5', text: '#2A1A2A', background: '#FFF5F9' }
                },
                {
                    name: 'Azul Profundo',
                    description: 'Tonos azules',
                    colors: { primary: '#2C5AA0', secondary: '#0F2847', accent: '#B8D4F1', text: '#0A1428', background: '#F0F4F9' }
                },
                {
                    name: 'Verde Naturaleza',
                    description: 'Tonos verdes',
                    colors: { primary: '#2D7A4A', secondary: '#1A4D2E', accent: '#C1E4D0', text: '#0D3B1C', background: '#F0F9F5' }
                },
                {
                    name: 'Coral Vibrante',
                    description: 'Tonos cálidos',
                    colors: { primary: '#FF6B5B', secondary: '#B82C1F', accent: '#FFD4CC', text: '#2A1A16', background: '#FFF5F3' }
                },
            ];
        },

        applyColorPreset(presetName) {
            const presets = this.getColorPresets();
            const preset = presets.find(p => p.name === presetName);
            if (preset) {
                Object.assign(this.modules.config.colores, preset.colors);
                this.schedulePreview();
            }
        },

        isCurrentPreset(presetName) {
            const presets = this.getColorPresets();
            const preset = presets.find(p => p.name === presetName);
            if (!preset) return false;
            
            const currentColors = this.modules.config.colores;
            return Object.keys(preset.colors).every(key => 
                (currentColors[key] || '').toUpperCase() === (preset.colors[key] || '').toUpperCase()
            );
        },

        // Función para calcular ratio de contraste WCAG
        hexToRgb(hex) {
            const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
            return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
            } : { r: 255, g: 255, b: 255 };
        },

        getRelativeLuminance(hex) {
            const rgb = this.hexToRgb(hex);
            const [r, g, b] = [rgb.r, rgb.g, rgb.b].map(x => {
                x = x / 255;
                return x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * r + 0.7152 * g + 0.0722 * b;
        },

        getContrastRatio() {
            const lum1 = this.getRelativeLuminance(this.modules.config.colores.text);
            const lum2 = this.getRelativeLuminance(this.modules.config.colores.background);
            const lighter = Math.max(lum1, lum2);
            const darker = Math.min(lum1, lum2);
            return ((lighter + 0.05) / (darker + 0.05)).toFixed(2);
        },

        // Validación de colores hexadecimales
        validateHexColor(key) {
            const hex = this.modules.config.colores[key];
            if (!hex.match(/^#[0-9A-Fa-f]{6}$/)) {
                // Restaurar color anterior si es inválido
                const presets = this.getColorPresets();
                const preset = presets[0]; // Default elegancia clásica
                this.modules.config.colores[key] = preset.colors[key];
            }
            this.schedulePreview();
        },
    };
}

window.invitationForm = invitationForm;
</script>
