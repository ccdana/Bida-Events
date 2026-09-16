<script>
/**
 * Campo de fecha en formato DD-MM-AAAA. Se enlaza con x-model a un valor ISO (AAAA-MM-DD):
 * se puede escribir con máscara o elegir en el calendario nativo.
 */
function dateField() {
    return {
        value: '',
        text: '',
        error: '',

        init() {
            this.$nextTick(() => { this.text = this.toDisplay(this.value); });
            this.$watch('value', (iso) => {
                if (this.toIso(this.text) !== iso) {
                    this.text = this.toDisplay(iso);
                    this.error = '';
                }
            });
        },

        get readable() {
            const iso = this.toIso(this.text);
            if (!iso) {
                return 'Formato DD-MM-AAAA';
            }
            return new Date(`${iso}T12:00:00`).toLocaleDateString('es-BO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        },

        toDisplay(iso) {
            const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
            return match ? `${match[3]}-${match[2]}-${match[1]}` : '';
        },

        toIso(text) {
            const match = /^(\d{2})-(\d{2})-(\d{4})$/.exec(text || '');
            if (!match) {
                return null;
            }
            const [, day, month, year] = match;
            const date = new Date(Number(year), Number(month) - 1, Number(day));
            const valid = date.getFullYear() === Number(year) && date.getMonth() === Number(month) - 1 && date.getDate() === Number(day);
            return valid ? `${year}-${month}-${day}` : null;
        },

        onInput(event) {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 8);
            let masked = digits.slice(0, 2);
            if (digits.length > 2) masked += `-${digits.slice(2, 4)}`;
            if (digits.length > 4) masked += `-${digits.slice(4)}`;
            this.text = masked;
            event.target.value = masked;

            if (digits.length < 8) {
                this.error = '';
                return;
            }
            const iso = this.toIso(masked);
            this.error = iso ? '' : 'Esa fecha no existe';
            if (iso) {
                this.value = iso;
            }
        },

        onBlur() {
            if (this.text && !this.toIso(this.text)) {
                this.error = 'Escribe la fecha como DD-MM-AAAA';
            }
        },

        openPicker() {
            const picker = this.$refs.picker;
            picker.value = this.value || '';
            try {
                picker.showPicker();
            } catch (error) {
                picker.click();
            }
        },

        onPick(event) {
            if (event.target.value) {
                this.value = event.target.value;
                this.text = this.toDisplay(event.target.value);
                this.error = '';
            }
        },
    };
}

window.dateField = dateField;

/** Hora en formato de 24 horas (HH:MM), con la misma máscara que la fecha. */
function timeField() {
    return {
        value: '',
        text: '',
        error: '',

        init() {
            this.$nextTick(() => { this.text = this.value || ''; });
            this.$watch('value', (time) => {
                if (this.text !== time) {
                    this.text = time || '';
                    this.error = '';
                }
            });
        },

        onInput(event) {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 4);
            const masked = digits.length > 2 ? `${digits.slice(0, 2)}:${digits.slice(2)}` : digits;
            this.text = masked;
            event.target.value = masked;

            if (digits.length < 4) {
                this.error = '';
                return;
            }
            const hours = Number(digits.slice(0, 2));
            const minutes = Number(digits.slice(2));
            this.error = hours > 23 || minutes > 59 ? 'Esa hora no existe' : '';
            if (!this.error) {
                this.value = masked;
            }
        },

        onBlur() {
            if (this.text && !/^([01]\d|2[0-3]):[0-5]\d$/.test(this.text)) {
                this.error = 'Escribe la hora como HH:MM';
            }
        },
    };
}

window.timeField = timeField;

function invitationForm(config) {
    return {
        modules: config.modules,
        config,
        clients: config.clients ?? [],
        eventTypes: config.eventTypes ?? [],
        templateOptions: config.templateOptions ?? [],
        statusLabels: {
            active: 'Activa',
            inactive: 'Inactiva',
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
        clientPasswordUrl: config.clientPasswordUrl,
        clientPasswordLoading: false,
        mediaUploadUrl: config.mediaUploadUrl,
        previewTick: Date.now(),
        previewLoading: false,
        previewTimer: null,
        previewSeq: 0,
        previewAbort: null,
        previewDebounceMs: 280,
        mediaUploading: false,
        clientCreating: false,
        newClient: { name: '' },
        clientError: '',
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
                    { id: 'hero', label: 'Banner principal', moduleCode: 'bienvenida', hint: 'Foto, nombre y fecha de portada' },
                    { id: 'ubicacion', label: 'Ubicación', moduleCode: 'ubicacion', hint: 'Lugar, mapa y cómo llegar' },
                    { id: 'itinerario', label: 'Itinerario', moduleCode: 'itinerario', hint: 'Horarios de la noche' },
                    { id: 'dress', label: 'Dress code', moduleCode: 'dress_code', hint: 'Qué usar, colores y qué evitar' },
                ],
            },
            {
                id: 'contenido',
                label: 'Contenido',
                description: 'Historias y medios del evento',
                tabs: [
                    { id: 'destacados', label: 'Invitados de honor', moduleCode: 'destacados', hint: 'Chambelanes, damitas y padrinos' },
                    { id: 'galeria', label: 'Galería', moduleCode: 'galeria', hint: 'Fotos que se deslizan' },
                    { id: 'video', label: 'Video', moduleCode: 'video', hint: 'Save the date con portada' },
                ],
            },
            {
                id: 'interaccion',
                label: 'Interacción',
                description: 'Participación de los invitados',
                tabs: [
                    { id: 'musica', label: 'Música', moduleCode: 'musica', hint: 'Canción de fondo' },
                    { id: 'playlist', label: 'Playlist', moduleCode: 'playlist', hint: 'Los invitados sugieren canciones' },
                    { id: 'hashtag', label: 'Hashtag', moduleCode: 'hashtag', hint: 'Etiqueta para compartir fotos' },
                    { id: 'encuestas', label: 'Encuestas', moduleCode: 'encuestas', hint: 'Preguntas con votación' },
                    { id: 'regalos', label: 'Regalos', moduleCode: 'regalos', hint: 'Transferencia, tienda y sobres' },
                    { id: 'rsvp', label: 'RSVP', moduleCode: 'rsvp', hint: 'Confirmación y pase con QR' },
                ],
            },
            {
                id: 'logistica',
                label: 'Logística',
                description: 'Utilidades antes y después del evento',
                tabs: [
                    { id: 'countdown', label: 'Cuenta regresiva', moduleCode: 'cuenta_regresiva', hint: 'Tiempo que falta para el evento' },
                    { id: 'agendar', label: 'Calendario', moduleCode: 'agendar', hint: 'Botón para guardar la fecha' },
                    { id: 'fotomural', label: 'Fotomural', moduleCode: 'fotomural', hint: 'Fotos de invitados en vivo' },
                    { id: 'post_evento', label: 'Post-evento', moduleCode: 'post_evento', hint: 'Fotos oficiales después de la fiesta' },
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
        // Dónde usa cada color la plantilla (resources/css/invitation); en el orden en que conviene elegirlos
        colorRoles: [
            { key: 'background', label: 'Fondo', usage: 'Color de fondo de toda la invitación.' },
            { key: 'text', label: 'Texto', usage: 'Títulos, párrafos y horarios. Tiene que leerse bien sobre el fondo.' },
            { key: 'primary', label: 'Principal', usage: 'Botones, líneas decorativas, íconos animados y la luz del itinerario.' },
            { key: 'accent', label: 'Acento', usage: 'Tono suave de las zonas destacadas: portada, galería y recuadros.' },
            { key: 'secondary', label: 'Secundario', usage: 'Esta plantilla todavía no lo usa. Se guarda para otras plantillas.', unused: true },
        ],
        fontRoles: [
            { key: 'script', label: 'Nombre del festejado', usage: 'Letra decorativa del nombre en la portada y en el menú.', fallback: 'cursive', size: 'text-2xl' },
            { key: 'titulos', label: 'Títulos de secciones', usage: 'Títulos como Itinerario o Ubicación y los números de la cuenta regresiva.', fallback: 'serif', size: 'text-lg' },
            { key: 'cuerpo', label: 'Textos', usage: 'Párrafos, horarios, botones y formularios.', fallback: 'sans-serif', size: 'text-base' },
        ],

        fontSample(roleKey) {
            if (roleKey === 'script') {
                return this.modules.bienvenida?.nombre_quinceanera || (this.isWeddingTemplate() ? 'Ana & Luis' : (this.isBaptismTemplate() ? 'Mateo Andrés' : (this.isBirthdayTemplate() ? 'Valeria' : 'Sofía Valentina')));
            }
            return roleKey === 'titulos' ? 'Itinerario' : 'Te esperamos a las 18:00';
        },
        fontOptions: {
            titulos: [
                'Playfair Display', 'Cormorant Garamond', 'Cinzel', 'Libre Baskerville',
                'Bodoni Moda', 'Prata', 'Lora', 'Merriweather', 'Fredoka',
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
            m.dress_code ??= { sugerencias: [], colores_permitidos: [], evitar: [] };
            m.dress_code.evitar ??= [];
            // Migrar datos legacy: colores_prohibidos → evitar
            if (m.dress_code.colores_prohibidos && !m.dress_code.evitar.length) {
                m.dress_code.evitar = m.dress_code.colores_prohibidos.map(c => typeof c === 'string' ? c : (c.motivo || c.nombre || ''));
                delete m.dress_code.colores_prohibidos;
            }
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
            m.regalos = {
                sobres: { titulo: '', direccion: '' },
                banco: { banco: '', titular: '', ci: '', cuenta: '', qr_url: '' },
                titulo: '',
                tienda_url: '',
                tienda_texto: '',
                opciones: [],
                ...this.plainModuleValue(m.regalos),
            };
            m.regalos.sobres = {
                titulo: '',
                direccion: '',
                ...this.plainModuleValue(m.regalos.sobres),
            };
            m.regalos.banco = {
                banco: '',
                titular: '',
                ci: '',
                cuenta: '',
                qr_url: '',
                ...this.plainModuleValue(m.regalos.banco),
            };
            m.regalos.opciones = Array.isArray(m.regalos.opciones) ? m.regalos.opciones : [];
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
            this.clientError = '';
            if (!this.newClient.name?.trim()) {
                this.clientError = 'Escribe el nombre del cliente.';
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
                this.newClient = { name: '' };
            } catch (error) {
                this.clientError = error.message || 'No se pudo crear el cliente.';
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
        },

        /**
         * La contraseña no se guarda descifrable: esto genera una nueva y la muestra una sola vez,
         * para dictarla o enviarla al cliente en el momento.
         */
        async regenerateClientPassword(client) {
            if (!client || this.clientPasswordLoading) {
                return;
            }

            this.clientPasswordLoading = true;
            this.clientError = '';

            try {
                const res = await fetch(this.clientPasswordUrl.replace('__ID__', client.id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                });
                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'No se pudo generar la contraseña.');
                }

                client.password = data.client.password;
            } catch (error) {
                this.clientError = error.message;
            } finally {
                this.clientPasswordLoading = false;
            }
        },

        /** Mensaje listo para enviar al cliente por WhatsApp con sus datos de acceso. */
        clientAccessMessage(client) {
            const lines = [
                `Hola ${client.name}, estos son tus datos para ingresar a ${window.location.origin}/login`,
                `Usuario: ${client.username}`,
            ];
            if (client.password) {
                lines.push(`Contraseña: ${client.password}`);
            }
            return lines.join('\n');
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

        // Plantilla de boda: el editor habla de novios, damas y caballeros de honor
        isWeddingTemplate() {
            const value = String(this.meta.template ?? '');
            return this.templateOptions.find(option => option.value === value)?.event === 'boda';
        },

        // Plantilla de bautizo: el editor habla del bebé, abuelos y tíos
        isBaptismTemplate() {
            const value = String(this.meta.template ?? '');
            return this.templateOptions.find(option => option.value === value)?.event === 'bautizo';
        },

        // Plantilla de cumpleaños: el editor habla de amigos, familia y anfitriones
        isBirthdayTemplate() {
            const value = String(this.meta.template ?? '');
            return this.templateOptions.find(option => option.value === value)?.event === 'cumple';
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

                this.syncHiddenInputs();
                await this.$nextTick();
                form.submit();
            } catch (err) {
                console.error('Error al guardar la invitación:', err);
                alert(err.message || 'Error al subir archivos. Intenta de nuevo.');
                this.mediaUploading = false;
            }
        },

        uploadGalleryFiles(event) {
            const files = [...(event.target.files || [])];
            for (const file of files) {
                const blobUrl = URL.createObjectURL(file);
                this.modules.galeria.fotos.push(blobUrl);
                this.pendingUploads.push({
                    file,
                    type: 'image',
                    context: 'gallery',
                    blobUrl,
                    apply: this.replaceInListApplier(() => this.modules.galeria.fotos, blobUrl),
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
                this.modules.post_evento.fotos.push(blobUrl);
                this.pendingUploads.push({
                    file,
                    type: 'image',
                    context: 'post-evento',
                    blobUrl,
                    apply: this.replaceInListApplier(() => this.modules.post_evento.fotos, blobUrl),
                });
            }
            event.target.value = '';
        },

        // Reemplaza la foto por su valor y no por su posición: si se quitan fotos antes de guardar, los índices cambian
        replaceInListApplier(getList, initialUrl) {
            let current = initialUrl;
            return (url) => {
                const list = getList() ?? [];
                const index = list.indexOf(current);
                if (index >= 0) list[index] = url;
                current = url;
            };
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

        googleMapsEmbedUrl() {
            if (this.hasLocationCoordinates()) {
                const lat = this.modules.ubicacion.lat;
                const lng = this.modules.ubicacion.lng;
                return `https://maps.google.com/maps?q=${lat},${lng}&z=15&output=embed`;
            }
            const mapsUrl = this.modules.ubicacion?.maps_url?.trim();
            if (mapsUrl) {
                const coords = this.parseMapsLink(mapsUrl);
                if (coords) {
                    return `https://maps.google.com/maps?q=${coords.lat},${coords.lng}&z=15&output=embed`;
                }
            }
            const q = this.modules.ubicacion?.direccion?.trim() || this.modules.ubicacion?.nombre_lugar?.trim() || '';
            if (q) {
                return `https://maps.google.com/maps?q=${encodeURIComponent(q)}&z=15&output=embed`;
            }
            return '';
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

            // 1. Coordenadas exactas del marcador del lugar (!3d<lat>!4d<lng>)
            const exactPinMatch = s.match(/!3d(-?\d+\.?\d*)!4d(-?\d+\.?\d*)/);
            if (exactPinMatch) {
                const lat = parseFloat(exactPinMatch[1]);
                const lng = parseFloat(exactPinMatch[2]);
                if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && !(lat === 0 && lng === 0)) {
                    return { lat, lng };
                }
            }

            // 2. Parámetros de consulta directos: q=lat,lng, query=lat,lng, ll=lat,lng
            const queryPatterns = [
                /[?&]q=(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)/,
                /[?&]query=(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)/,
                /[?&]ll=(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)/,
            ];
            for (const pattern of queryPatterns) {
                const m = s.match(pattern);
                if (m) {
                    const lat = parseFloat(m[1]);
                    const lng = parseFloat(m[2]);
                    if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && !(lat === 0 && lng === 0)) {
                        return { lat, lng };
                    }
                }
            }

            // 3. Coordenadas directas en texto plano: "-17.3955837, -66.1638894"
            const rawMatch = s.match(/^(-?\d+\.?\d*)[\s,]+(-?\d+\.?\d*)$/);
            if (rawMatch) {
                const lat = parseFloat(rawMatch[1]);
                const lng = parseFloat(rawMatch[2]);
                if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && !(lat === 0 && lng === 0)) {
                    return { lat, lng };
                }
            }

            // 4. Último recurso: centro de la cámara del mapa @lat,lng
            const viewportPatterns = [
                /@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /place\/[^/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /\/maps\/(?:search|place)\/[^/]+\/@(-?\d+\.?\d*),(-?\d+\.?\d*)/,
                /\/(-?\d{1,2}\.\d+),(-?\d{1,3}\.\d+)\/?(?:\?|$)/,
            ];
            for (const pattern of viewportPatterns) {
                const m = s.match(pattern);
                if (m) {
                    const lat = parseFloat(m[1]);
                    const lng = parseFloat(m[2]);
                    if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && !(lat === 0 && lng === 0)) {
                        return { lat, lng };
                    }
                }
            }

            return null;
        },

        looksLikeMapsLink(value) {
            const text = String(value || '').trim();
            return /(-?\d+\.\d+[\s,]+-?\d+\.\d+|google\.[a-z.]+\/maps|maps\.app\.goo\.gl|goo\.gl\/maps)/i.test(text);
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

        addEvento() { this.modules.itinerario.eventos.push({ hora: '20:00', titulo: '', icono: 'especial', descripcion: '' }); },
        removeEvento(i) { this.modules.itinerario.eventos.splice(i, 1); },
        addSugerencia() { this.modules.dress_code.sugerencias.push({ para: '', titulo: '', descripcion: '', ejemplos: [] }); },
        removeSugerencia(i) {
            // Evita subir a Cloudinary la foto pendiente de una sugerencia eliminada
            this.clearMediaUrl(this.modules.dress_code.sugerencias[i]?.imagen);
            this.modules.dress_code.sugerencias.splice(i, 1);
        },
        addColorPermitido() { this.modules.dress_code.colores_permitidos.push({ nombre: '', hex: '#C9A96E' }); },
        addEvitar() { this.modules.dress_code.evitar.push(''); },
        removeEvitar(i) { this.modules.dress_code.evitar.splice(i, 1); },
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

        // handleFormSubmit consolidado arriba

        // Funciones para presets de paletas de colores
        getColorPresets() {
            return [
                // Modo claro
                {
                    mode: 'light',
                    name: 'Champagne Marfil',
                    description: 'Dorado clásico sobre marfil',
                    colors: {
                        primary: '#B8956B',
                        secondary: '#3D3228',
                        accent: '#F3E8D8',
                        text: '#2A241E',
                        background: '#FBF7F0',
                    },
                },
                {
                    mode: 'light',
                    name: 'Rosa Jardín',
                    description: 'Rosa polvoriento y crema',
                    colors: {
                        primary: '#C97B84',
                        secondary: '#5C3D42',
                        accent: '#F8E4E6',
                        text: '#3A2828',
                        background: '#FFF8F8',
                    },
                },
                {
                    mode: 'light',
                    name: 'Cielo Bautizo',
                    description: 'Celeste suave y blanco nube',
                    colors: {
                        primary: '#6B9AC4',
                        secondary: '#C9A96E',
                        accent: '#DCEBF5',
                        text: '#2E3A46',
                        background: '#F7FBFE',
                    },
                },
                {
                    mode: 'light',
                    name: 'Rosa Bautizo',
                    description: 'Rosa delicado y marfil',
                    colors: {
                        primary: '#D48BA4',
                        secondary: '#C9A96E',
                        accent: '#F8E3EA',
                        text: '#46323A',
                        background: '#FFF9FB',
                    },
                },
                {
                    mode: 'light',
                    name: 'Confeti Coral',
                    description: 'Coral, amarillo sol y menta',
                    colors: {
                        primary: '#F25C54',
                        secondary: '#F7B32B',
                        accent: '#9ADBC5',
                        text: '#2B2D42',
                        background: '#FFF8F0',
                    },
                },
                {
                    mode: 'light',
                    name: 'Fiesta Lila',
                    description: 'Lila vibrante y amarillo',
                    colors: {
                        primary: '#8E6CEF',
                        secondary: '#FFC93C',
                        accent: '#E6DDFB',
                        text: '#2A2440',
                        background: '#FBF8FF',
                    },
                },
                {
                    mode: 'light',
                    name: 'Salvia Serena',
                    description: 'Verde natural y elegante',
                    colors: {
                        primary: '#6B8F71',
                        secondary: '#2F4535',
                        accent: '#E2EDE4',
                        text: '#1E2E24',
                        background: '#F6FAF7',
                    },
                },
                {
                    mode: 'light',
                    name: 'Perla Costera',
                    description: 'Azul grisáceo sofisticado',
                    colors: {
                        primary: '#5B7C99',
                        secondary: '#2C3E50',
                        accent: '#DCE8F0',
                        text: '#1A2832',
                        background: '#F4F8FB',
                    },
                },
                {
                    mode: 'light',
                    name: 'Terracota Luxe',
                    description: 'Cálido mediterráneo',
                    colors: {
                        primary: '#C17A5C',
                        secondary: '#5C3A2E',
                        accent: '#F5E6DC',
                        text: '#342520',
                        background: '#FDF8F5',
                    },
                },
                // Modo noche
                {
                    mode: 'night',
                    name: 'Noir Dorado',
                    description: 'Negro y oro de gala',
                    colors: {
                        primary: '#D4AF37',
                        secondary: '#1A1814',
                        accent: '#3D3528',
                        text: '#F5F0E6',
                        background: '#0D0C0A',
                    },
                },
                {
                    mode: 'night',
                    name: 'Rosa Terciopelo',
                    description: 'Noche romántica profunda',
                    colors: {
                        primary: '#E8A0B4',
                        secondary: '#2A1520',
                        accent: '#4A2A38',
                        text: '#FCE8EE',
                        background: '#140A10',
                    },
                },
                {
                    mode: 'night',
                    name: 'Zafiro Medianoche',
                    description: 'Azul noche refinado',
                    colors: {
                        primary: '#7EB8DA',
                        secondary: '#0E1A2B',
                        accent: '#1E3A5F',
                        text: '#E3EEF8',
                        background: '#060D18',
                    },
                },
                {
                    mode: 'night',
                    name: 'Esmeralda Soirée',
                    description: 'Verde gala nocturno',
                    colors: {
                        primary: '#7EC9A0',
                        secondary: '#0F1F18',
                        accent: '#1A3D2E',
                        text: '#E0F2E9',
                        background: '#051510',
                    },
                },
                {
                    mode: 'night',
                    name: 'Gala Amatista',
                    description: 'Púrpura lujoso',
                    colors: {
                        primary: '#B8A0D8',
                        secondary: '#1A1428',
                        accent: '#352850',
                        text: '#EDE6F8',
                        background: '#0A0812',
                    },
                },
            ];
        },

        getLightColorPresets() {
            return this.getColorPresets().filter((preset) => preset.mode === 'light');
        },

        getNightColorPresets() {
            return this.getColorPresets().filter((preset) => preset.mode === 'night');
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

        isPresetDark(preset) {
            const bg = (preset?.colors?.background || '').replace('#','');
            if (!bg || bg.length !== 6) return false;
            const r = parseInt(bg.slice(0,2),16);
            const g = parseInt(bg.slice(2,4),16);
            const b = parseInt(bg.slice(4,6),16);
            // luminance approximation
            const lum = 0.2126 * r + 0.7152 * g + 0.0722 * b;
            return lum < 100; // dark when luminance is low
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
