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
        // Perfil de cada tipo de evento (app/EventProfiles): módulos, vocabulario y datos obligatorios
        profiles: config.profiles ?? {},
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
        palettes: config.palettes ?? [],
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

        allTabGroups: [
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
                id: 'tarjeta',
                label: 'Tarjeta',
                description: 'Lo que dice la carta',
                tabs: [
                    { id: 'relato', label: 'Relato en cuatro actos', moduleCode: 'relato', hint: 'Momentos, anécdota y reflexión' },
                    { id: 'dedicatoria', label: 'Dedicatoria', moduleCode: 'dedicatoria', hint: 'De, para, mensaje y firma' },
                    { id: 'juntos_desde', label: 'Juntos desde', moduleCode: 'juntos_desde', hint: 'Fecha y contador de tiempo' },
                    { id: 'respuesta', label: 'Respuesta', moduleCode: 'respuesta', hint: 'Quien la recibe te responde' },
                ],
            },
            {
                id: 'libro',
                label: 'Libro',
                description: 'Las hojas del libro de aventuras',
                tabs: [
                    { id: 'historia', label: 'Nuestra historia', moduleCode: 'historia', hint: 'Capítulos con fecha, texto y foto' },
                    { id: 'recuerdos', label: 'Recuerdos', moduleCode: 'recuerdos', hint: 'Fotos con una nota' },
                    { id: 'collage', label: 'Collage', moduleCode: 'collage', hint: 'Fotos en formas con flores' },
                    { id: 'marcos', label: 'Marcos', moduleCode: 'marcos', hint: 'Fotos con marco y pie' },
                    { id: 'memoria', label: 'Juego', moduleCode: 'memoria', hint: 'Memoria con sus fotos' },
                    { id: 'aventuras', label: 'Aventuras por vivir', moduleCode: 'aventuras', hint: 'Lo que les falta vivir juntos' },
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
                return this.modules.bienvenida?.nombre_quinceanera || this.modules.dedicatoria?.para || this.profile.sample?.name || 'Sofía Valentina';
            }
            return roleKey === 'titulos' ? 'Itinerario' : 'Te esperamos a las 18:00';
        },
        fontOptions: {
            titulos: [
                'Playfair Display', 'Cormorant Garamond', 'Cinzel', 'Libre Baskerville',
                'Bodoni Moda', 'Prata', 'Lora', 'Merriweather', 'Fredoka', 'Inter',
            ],
            cuerpo: [
                'Montserrat', 'Inter', 'Lato', 'Nunito Sans', 'Source Sans 3',
                'Poppins', 'Raleway', 'Open Sans',
            ],
            script: [
                'Great Vibes', 'Parisienne', 'Alex Brush', 'Dancing Script',
                'Sacramento', 'Allura', 'Tangerine', 'Petit Formal Script', 'Creepster', 'Cinzel', 'Inter',
            ],
        },

        // ── Perfil del evento ───────────────────────────────────────────────
        // La plantilla elegida apunta a un perfil; el editor habla su idioma y
        // solo ofrece sus módulos. Un evento o temporada nueva no toca este archivo.

        get profile() {
            const option = this.templateOptions.find(item => item.value === String(this.meta.template ?? ''));
            return this.profiles[option?.event] ?? Object.values(this.profiles)[0] ?? {
                code: 'xv', kind: 'invitation', modules: [], heroFields: {}, featuredGroups: {}, required: {}, sample: {},
            };
        },

        get isCard() {
            return this.profile.kind === 'card';
        },

        get tabGroups() {
            const modules = this.profile.modules ?? [];

            return this.allTabGroups
                .map(group => ({ ...group, tabs: group.tabs.filter(tab => !tab.moduleCode || modules.includes(tab.moduleCode)) }))
                .filter(group => group.tabs.length > 0);
        },

        /** Nombre de un grupo de personas destacadas en este evento: plural (0) o singular (1). */
        featuredLabel(group, form = 0) {
            return this.profile.featuredGroups?.[group]?.[form] ?? '';
        },

        /** Campos de nombre de la portada que pide el perfil (nombre, pareja, edad…). */
        get heroFields() {
            return Object.entries(this.profile.heroFields ?? {}).map(([key, field]) => ({ key, ...field }));
        },

        /** Mantiene el texto completo que leen las plantillas cuando se editan los nombres por separado. */
        syncHeroName() {
            const hero = this.modules.bienvenida;
            const primary = String(hero.nombre ?? '').trim();
            const secondary = String(hero.nombre_pareja ?? '').trim();
            hero.nombre_quinceanera = secondary ? [primary, secondary].filter(Boolean).join(' & ') : primary;
            this.schedulePreview();
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

        // ── Qué falta para publicar ─────────────────────────────────────────
        // Un módulo encendido pero vacío no se ve en la invitación. En vez de que el
        // cliente lo descubra después, el editor lo marca apartado por apartado.

        moduleIssues(tabId) {
            const m = this.modules;
            const list = (value) => Array.isArray(value) ? value : [];
            const filled = (value) => Array.isArray(value) ? value.length > 0 : (typeof value === 'string' ? value.trim() !== '' : Boolean(value));
            const some = (values) => values.filter(Boolean);

            // Si el perfil del evento dice qué es obligatorio en este módulo, eso es lo que se revisa
            const moduleCode = this.allTabGroups.flatMap(group => group.tabs).find(tab => tab.id === tabId)?.moduleCode;
            const required = moduleCode ? this.profile.required?.[moduleCode] : null;

            if (required) {
                return Object.entries(required)
                    .filter(([path]) => !filled(path.split('.').reduce((value, key) => value?.[key], m[moduleCode])))
                    .map(([, message]) => message);
            }

            switch (tabId) {
                case 'general':
                    return some([
                        !filled(this.meta.title) && 'Falta el título',
                        !filled(this.meta.event_date) && 'Falta la fecha del evento',
                        !filled(this.meta.slug) && 'Falta la dirección de la invitación',
                        !this.meta.user_id && 'Falta asignar el cliente',
                    ]);

                case 'hero':
                    return some([
                        !filled(m.bienvenida?.nombre_quinceanera) && 'Falta el nombre del festejado',
                        !filled(m.bienvenida?.imagen_hero) && 'Falta la foto de portada',
                    ]);

                case 'ubicacion':
                    return some([
                        !filled(m.ubicacion?.nombre_lugar) && !filled(m.ubicacion?.direccion) && 'Falta el lugar o la dirección',
                        !this.hasLocationCoordinates() && !filled(m.ubicacion?.maps_url) && 'Falta el punto en el mapa',
                    ]);

                case 'itinerario':
                    return list(m.itinerario?.eventos).some(evento => filled(evento?.titulo))
                        ? [] : ['No hay ningún momento cargado'];

                case 'dress':
                    return list(m.dress_code?.sugerencias).length
                        || list(m.dress_code?.colores_permitidos).length
                        || list(m.dress_code?.evitar).length
                            ? [] : ['No hay sugerencias, colores ni cosas a evitar'];

                case 'destacados':
                    return list(m.destacados?.chambelanes).length
                        + list(m.destacados?.damitas).length
                        + list(m.destacados?.padrinos).length > 0
                            ? [] : ['No hay personas cargadas'];

                case 'galeria':
                    return list(m.galeria?.fotos).length ? [] : ['No hay fotos'];

                case 'historia':
                    return list(m.historia?.capitulos).length ? [] : ['No hay capítulos'];

                case 'recuerdos':
                    return list(m.recuerdos?.recuerdos).length ? [] : ['No hay recuerdos'];

                case 'collage':
                case 'marcos':
                    return list(m[tabId]?.fotos).length ? [] : ['No hay fotos'];

                case 'memoria':
                    return list(m.memoria?.fotos).length >= 3 ? [] : ['El juego necesita al menos 3 fotos'];

                case 'aventuras':
                    return list(m.aventuras?.lista).some(item => filled(item?.titulo)) ? [] : ['No hay aventuras'];

                case 'video':
                    return filled(m.video?.video_url) ? [] : ['Falta el video'];

                case 'musica':
                    return filled(m.musica?.audio_url) ? [] : ['Falta la canción de fondo'];

                case 'hashtag':
                    return filled(m.hashtag?.hashtag) ? [] : ['Falta la etiqueta'];

                case 'encuestas':
                    return list(m.encuestas?.preguntas).some(poll =>
                        filled(poll?.pregunta) && list(poll?.opciones).filter(filled).length >= 2
                    ) ? [] : ['No hay ninguna pregunta con dos opciones'];

                case 'regalos':
                    return filled(m.regalos?.banco?.cuenta) || filled(m.regalos?.banco?.qr_url)
                        || filled(m.regalos?.sobres?.titulo) || filled(m.regalos?.tienda_url)
                        || list(m.regalos?.opciones).some(gift => filled(gift?.titulo))
                            ? [] : ['No hay ninguna forma de regalo cargada'];

                case 'post_evento':
                    return list(m.post_evento?.fotos).length || filled(m.post_evento?.enlace_externo)
                        ? [] : ['No hay fotos oficiales ni enlace a la galería'];

                case 'countdown':
                case 'agendar':
                    return filled(this.meta.event_date) ? [] : ['Necesita la fecha del evento'];

                // Playlist, RSVP, fotomural y estética funcionan sin cargar nada
                default:
                    return [];
            }
        },

        // Un módulo apagado no está incompleto: simplemente no sale
        tabIssues(tab) {
            if (tab.moduleCode && !this.isTabEnabled(tab)) return [];

            return this.moduleIssues(tab.id);
        },

        groupIssueCount(groupId) {
            const group = this.tabGroups.find(item => item.id === groupId);

            return (group?.tabs ?? []).reduce((total, tab) => total + this.tabIssues(tab).length, 0);
        },

        /** Todo lo que falta, con el apartado donde se arregla. */
        get pendingIssues() {
            return this.tabGroups.flatMap(group =>
                group.tabs.flatMap(tab => this.tabIssues(tab).map(issue => ({
                    tabId: tab.id,
                    groupId: group.id,
                    label: tab.label,
                    issue,
                })))
            );
        },

        get isPublished() {
            return this.meta.status === 'active';
        },

        // Publicar es un cambio de estado explícito: se guarda en el mismo envío del formulario
        publish() {
            this.meta.status = 'active';
            this.$refs.saveButton?.click();
        },

        unpublish() {
            this.meta.status = 'inactive';
            this.$refs.saveButton?.click();
        },

        init() {
            this.ensureStructure();
            this.normalizeMetaSelects();
            this.syncEventTypeWithTemplate();
            this.initEventDateFields();
            this.syncActiveGroup();
            this.$watch('meta.template', v => {
                if (this.modules.config) this.modules.config.template = v;
                this.applyProfile();
            });
            this.$watch('modules', () => this.schedulePreview(), { deep: true });
            this.$watch('meta', () => this.schedulePreview(), { deep: true });
            // La vista previa sigue a la pestaña: al cambiar de módulo (o al recargarse) salta a su sección
            this.$watch('activeTab', () => this.focusPreviewSection(true));
            this.$nextTick(() => this.$refs.previewFrame?.addEventListener('load', () => this.focusPreviewSection(false)));
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
                nombre: '',
                nombre_pareja: '',
                edad: '',
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
            if (!m.bienvenida.nombre && !m.bienvenida.nombre_pareja) {
                m.bienvenida.nombre = m.bienvenida.nombre_quinceanera ?? '';
            }

            const objectModules = ['musica', 'video', 'playlist', 'hashtag', 'post_evento', 'rsvp', 'dedicatoria', 'juntos_desde', 'respuesta', 'relato'];
            for (const code of objectModules) {
                m[code] = {
                    ...this.plainModuleValue(m[code]),
                };
            }
            m.relato.momentos = Array.isArray(m.relato.momentos) ? m.relato.momentos : [];

            m.config ??= { colores: {}, tipografias: {}, modulos: {}, template: this.meta.template };
            m.config.colores ??= {};
            m.config.tipografias ??= {};
            // PHP manda [] cuando no hay textos propios: aquí tiene que ser un objeto clave → texto
            m.config.textos = m.config.textos && typeof m.config.textos === 'object' && !Array.isArray(m.config.textos) ? { ...m.config.textos } : {};
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
            // Libro de aventuras: listas de capítulos, recuerdos y fotos
            m.historia = { ...this.plainModuleValue(m.historia) };
            m.historia.capitulos = Array.isArray(m.historia.capitulos) ? m.historia.capitulos : [];
            m.recuerdos = { ...this.plainModuleValue(m.recuerdos) };
            m.recuerdos.recuerdos = Array.isArray(m.recuerdos.recuerdos) ? m.recuerdos.recuerdos : [];
            m.aventuras = { ...this.plainModuleValue(m.aventuras) };
            m.aventuras.lista = Array.isArray(m.aventuras.lista) ? m.aventuras.lista : [];
            for (const code of ['collage', 'marcos', 'memoria']) {
                m[code] = { ...this.plainModuleValue(m[code]) };
                m[code].fotos = Array.isArray(m[code].fotos) ? m[code].fotos : [];
            }
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

        /** Textos editables del apartado abierto: los del módulo o, en «General», los de toda la invitación. */
        activeTextFields() {
            const byTemplate = this.config.editableTexts?.[String(this.meta.template ?? '')] ?? {};
            if (this.activeTab === 'general') return byTemplate.general ?? [];
            const tab = this.tabGroups.flatMap(g => g.tabs).find(item => item.id === this.activeTab);
            return tab?.moduleCode ? (byTemplate[tab.moduleCode] ?? []) : [];
        },

        customTextsCount() {
            return this.activeTextFields().filter(field => (this.modules.config.textos[field.key] ?? '').trim() !== '').length;
        },

        resetTexts() {
            for (const field of this.activeTextFields()) delete this.modules.config.textos[field.key];
            this.modules.config.textos = { ...this.modules.config.textos };
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

            // Libro de aventuras: fotos sueltas (URL o {url, alt}) y fotos de capítulos y recuerdos
            if (['collage', 'marcos', 'memoria'].includes(code) && Array.isArray(data.fotos)) {
                data.fotos = await Promise.all(data.fotos.map(async (photo) => (
                    typeof photo === 'string'
                        ? this.blobUrlToDataUrl(photo)
                        : { ...photo, url: await this.blobUrlToDataUrl(photo?.url) }
                )));
            }

            const entryLists = { historia: 'capitulos', recuerdos: 'recuerdos' };
            if (entryLists[code] && Array.isArray(data[entryLists[code]])) {
                data[entryLists[code]] = await Promise.all(data[entryLists[code]].map(async (entry) => (
                    entry?.foto ? { ...entry, foto: await this.blobUrlToDataUrl(entry.foto) } : entry
                )));
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
            // Una invitación nueva nace con los módulos que el perfil enciende por defecto
            if (this.config.isCreate && Array.isArray(this.profile.enabledByDefault)) {
                return Object.fromEntries((this.profile.modules ?? []).map(code => [code, this.profile.enabledByDefault.includes(code)]));
            }

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

        /**
         * Sección de la vista previa que corresponde a la pestaña abierta: la del módulo (sus ids son
         * el código con guiones: dress_code → #dress-code) o la portada para General, Estética y el banner.
         */
        previewSectionId() {
            const tab = this.tabGroups.flatMap(group => group.tabs).find(item => item.id === this.activeTab);
            const code = tab?.moduleCode;

            return !code || code === 'bienvenida' ? 'inicio' : code.replaceAll('_', '-');
        },

        /** Lleva la vista previa a la sección que se está editando y, al cambiar de pestaña, la marca un momento. */
        focusPreviewSection(smooth = true) {
            const doc = this.$refs.previewFrame?.contentDocument;
            if (!doc || !doc.body) return;

            const section = doc.getElementById(this.previewSectionId());
            if (!section) return;

            // Solo se desplaza la invitación dentro del marco: scrollIntoView movería también el editor
            // y el campo donde se está escribiendo se iría de la pantalla
            const view = doc.defaultView;
            view.scrollTo({ top: section.getBoundingClientRect().top + view.scrollY, behavior: smooth ? 'smooth' : 'auto' });

            // En cada recarga por tipear no se marca: distraería
            if (!smooth) return;

            section.style.transition = 'outline-color 0.6s ease';
            section.style.outline = '3px dashed rgba(201, 169, 110, 0.9)';
            section.style.outlineOffset = '-6px';
            clearTimeout(this.previewFocusTimer);
            this.previewFocusTimer = setTimeout(() => { section.style.outlineColor = 'transparent'; }, 1400);
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

        /**
         * Al elegir otra plantilla, el tipo de evento se ajusta a su perfil y, si la pestaña abierta
         * no existe en ese evento, se vuelve a General. En una invitación nueva también se encienden
         * los módulos del perfil.
         */
        applyProfile() {
            const type = this.eventTypes.find(item => item.code === this.profile.code);
            if (type) this.meta.event_type_id = String(type.id);

            if (this.config.isCreate) {
                this.modules.config.modulos = { ...this.modules.config.modulos, ...this.defaultModuleVisibility() };

                // Una invitación nueva nace con los colores y las letras de su plantilla (Lienzo: blanco y negro)
                const option = this.templateOptions.find(item => item.value === String(this.meta.template ?? ''));
                if (option?.palette) Object.assign(this.modules.config.colores, option.palette);
                if (option?.fonts) Object.assign(this.modules.config.tipografias, option.fonts);
            }

            if (!this.tabGroups.some(group => group.tabs.some(tab => tab.id === this.activeTab))) {
                this.selectTab('general');
            }
        },

        /** Plantillas del producto elegido (invitación o tarjeta). */
        templatesOfKind(kind) {
            return this.templateOptions.filter(option => (this.profiles[option.event]?.kind ?? 'invitation') === kind);
        },

        chooseKind(kind) {
            if ((this.profile.kind ?? 'invitation') === kind) return;
            const first = this.templatesOfKind(kind)[0];
            if (first) this.meta.template = first.value;
        },

        get selectedEventType() {
            const id = String(this.meta.event_type_id ?? '');
            return this.eventTypes.find(type => String(type.id) === id) ?? null;
        },

        /** Tipos de evento del producto elegido que tienen al menos una plantilla disponible. */
        eventTypesOfKind(kind) {
            return this.eventTypes.filter(type => (type.kind ?? 'invitation') === kind
                && (!type.code || this.templateOptions.some(option => option.event === type.code)));
        },

        /** Plantillas del tipo de evento elegido (un tipo sin código ve todas las de su producto). */
        templatesForEventType() {
            const code = this.selectedEventType?.code;
            return code
                ? this.templateOptions.filter(option => option.event === code)
                : this.templatesOfKind(this.profile.kind ?? 'invitation');
        },

        /** Al cambiar el tipo de evento, la plantilla pasa a la primera de ese evento si la actual no le corresponde. */
        chooseEventType(type) {
            this.meta.event_type_id = String(type.id);
            const current = this.templateOptions.find(option => option.value === String(this.meta.template ?? ''));

            if (type.code && current?.event !== type.code) {
                const first = this.templateOptions.find(option => option.event === type.code);
                if (first) this.meta.template = first.value;
            }
        },

        /** Invitaciones guardadas con un tipo que no es el de su plantilla: se corrige al abrir el editor. */
        syncEventTypeWithTemplate() {
            if (this.selectedEventType?.code === this.profile.code) return;
            const type = this.eventTypes.find(item => item.code === this.profile.code);
            if (type) this.meta.event_type_id = String(type.id);
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
                case 'recuerdos':
                case 'marcos':
                    return 4 / 5;
                case 'memoria':
                case 'collage':
                    return 1;
                case 'historia':
                    return 16 / 10;
                case 'ubicacion':
                    return 3 / 2;
                default:
                    return 1;
            }
        },

        // ── Fotos con descripción ───────────────────────────────────────────
        // Una foto es su URL a secas o {url, alt}: solo se convierte en objeto
        // cuando el cliente escribe una descripción, para no ensuciar los datos.

        photoUrl(photo) {
            return typeof photo === 'string' ? photo : (photo?.url ?? '');
        },

        photoAlt(photo) {
            return typeof photo === 'string' ? '' : (photo?.alt ?? '');
        },

        setPhotoAlt(list, index, value) {
            const photo = list[index];
            const alt = (value ?? '').trim();
            const url = this.photoUrl(photo);

            list[index] = alt === ''
                ? url
                : { ...(typeof photo === 'object' && photo ? photo : {}), url, alt };

            this.schedulePreview();
        },

        openImageCropperFromGallery(index) {
            this.openImageCropper(this.photoUrl(this.modules.galeria.fotos[index]), 'gallery');
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
            this.clearMediaUrl(this.photoUrl(this.modules.galeria.fotos[index]));
            this.modules.galeria.fotos.splice(index, 1);
        },

        // ── Libro de aventuras ──────────────────────────────────────────────
        // Fotos sueltas (collage, marcos, memoria) y listas de entradas con foto
        // (capítulos de la historia y recuerdos). Se suben al guardar, como la galería.

        uploadBookPhotos(code, event, max) {
            const files = [...(event.target.files || [])];
            const list = this.modules[code].fotos;
            for (const file of files) {
                if (max && list.length >= max) break;
                const blobUrl = URL.createObjectURL(file);
                list.push(blobUrl);
                this.pendingUploads.push({
                    file,
                    type: 'image',
                    context: code,
                    blobUrl,
                    apply: this.replaceInListApplier(() => this.modules[code].fotos, blobUrl),
                });
            }
            event.target.value = '';
            this.schedulePreview();
        },

        removeBookPhoto(code, index) {
            this.clearMediaUrl(this.photoUrl(this.modules[code].fotos[index]));
            this.modules[code].fotos.splice(index, 1);
            this.schedulePreview();
        },

        moveBookItem(list, index, step) {
            const target = index + step;
            if (target < 0 || target >= list.length) return;
            [list[index], list[target]] = [list[target], list[index]];
            this.schedulePreview();
        },

        addBookEntry(code, key, max) {
            const list = this.modules[code][key];
            if (max && list.length >= max) return;
            list.push({ titulo: '', fecha: '', texto: '', foto: '', alt: '' });
            this.schedulePreview();
        },

        removeBookEntry(code, key, index) {
            const entry = this.modules[code][key][index];
            if (entry?.foto) this.clearMediaUrl(entry.foto);
            this.modules[code][key].splice(index, 1);
            this.schedulePreview();
        },

        uploadBookEntryPhoto(code, key, index, event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file) return;
            const entry = this.modules[code][key][index];
            if (entry.foto) this.clearMediaUrl(entry.foto);
            const blobUrl = URL.createObjectURL(file);
            entry.foto = blobUrl;
            this.pendingUploads.push({
                file,
                type: 'image',
                context: code,
                blobUrl,
                // Se busca la entrada por su foto: si se reordenan antes de guardar, el índice cambia
                apply: (url) => {
                    const target = this.modules[code][key].find(item => item.foto === blobUrl);
                    if (target) target.foto = url;
                },
            });
            this.schedulePreview();
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
                const index = list.findIndex(photo => this.photoUrl(photo) === current);
                if (index >= 0) {
                    const photo = list[index];
                    // Si ya tenía descripción, se conserva al cambiar la URL temporal por la definitiva
                    list[index] = typeof photo === 'string' ? url : { ...photo, url };
                }
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

        // ── Paletas listas (config/palettes.php, App\Support\ColorPalettes) ──
        // Primero la original de la plantilla elegida, después las de su tipo de evento y al
        // final las que sirven para cualquiera.
        getColorPresets() {
            const template = String(this.meta.template ?? '');
            const event = this.profile.code;
            const rank = (preset) => (preset.template === template ? 0 : (preset.events.includes(event) ? 1 : 2));

            return this.palettes
                // Las originales de otras plantillas no se ofrecen: confunden más de lo que ayudan
                .filter((preset) => !preset.template || preset.template === template)
                .map((preset, index) => ({ preset, index }))
                .sort((a, b) => rank(a.preset) - rank(b.preset) || a.index - b.index)
                .map((item) => item.preset);
        },

        /** Las pensadas para este evento (incluida la original de la plantilla). */
        getEventColorPresets() {
            const template = String(this.meta.template ?? '');
            const event = this.profile.code;

            return this.getColorPresets().filter((preset) => preset.template === template || preset.events.includes(event));
        },

        /** Las que sirven para cualquier evento. */
        getGeneralColorPresets() {
            return this.getColorPresets().filter((preset) => !preset.template && preset.events.length === 0);
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
            return this.contrastBetween(this.modules.config.colores.text, this.modules.config.colores.background).toFixed(2);
        },

        // ── Contraste de la paleta ──────────────────────────────────────────
        // La invitación no pinta los colores tal cual: mezcla el texto con el fondo
        // para los tonos apagados (resources/css/invitation/base.css). Aquí se repiten
        // esas mezclas para medir lo que de verdad va a leer el invitado.

        contrastBetween(colorA, colorB) {
            const a = this.getRelativeLuminance(colorA);
            const b = this.getRelativeLuminance(colorB);
            return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
        },

        mixColors(hexA, hexB, weight) {
            const parse = (hex) => {
                const clean = String(hex ?? '').replace('#', '');
                return clean.length === 6 ? [0, 2, 4].map(i => parseInt(clean.slice(i, i + 2), 16)) : [0, 0, 0];
            };
            const [ar, ag, ab] = parse(hexA);
            const [br, bg, bb] = parse(hexB);
            const channel = (x, y) => Math.round(x * weight + y * (1 - weight));
            return '#' + [channel(ar, br), channel(ag, bg), channel(ab, bb)]
                .map(v => v.toString(16).padStart(2, '0')).join('');
        },

        /**
         * Cada fila dice qué pieza se mide, con cuánto contraste y cuánto necesita:
         * 4.5 para texto normal y 3 para texto grande o bordes (WCAG AA).
         */
        contrastChecks() {
            const colors = this.modules.config?.colores ?? {};
            const text = colors.text ?? '#000000';
            const bg = colors.background ?? '#ffffff';
            const primary = colors.primary ?? '#000000';

            return [
                { label: 'Textos', ratio: this.contrastBetween(text, bg), min: 4.5 },
                { label: 'Textos apagados', ratio: this.contrastBetween(this.mixColors(text, bg, 0.78), bg), min: 4.5 },
                { label: 'Etiquetas y ayudas', ratio: this.contrastBetween(this.mixColors(text, bg, 0.70), bg), min: 4.5 },
                { label: 'Detalles en color', ratio: this.contrastBetween(this.mixColors(primary, text, 0.55), bg), min: 4.5 },
                { label: 'Números grandes', ratio: this.contrastBetween(this.mixColors(primary, text, 0.80), bg), min: 3 },
                { label: 'Texto de los botones', ratio: this.contrastBetween(bg, text), min: 4.5 },
            ];
        },

        contrastIssues() {
            return this.contrastChecks().filter(check => check.ratio < check.min);
        },

        // Validación de colores hexadecimales
        validateHexColor(key) {
            const hex = this.modules.config.colores[key];
            if (!hex.match(/^#[0-9A-Fa-f]{6}$/)) {
                // Restaurar color anterior si es inválido
                const original = this.getColorPresets()[0];

                if (original) {
                    this.modules.config.colores[key] = original.colors[key];
                }
            }
            this.schedulePreview();
        },
    };
}

window.invitationForm = invitationForm;
</script>
