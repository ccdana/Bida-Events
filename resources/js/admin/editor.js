/**
 * Editor de invitaciones (admin, revendedor y «Hazlo tú»): los componentes de Alpine que usa
 * admin/invitations/editor/layout y sus paneles. app.js lo carga solo cuando la página tiene el
 * editor, antes de iniciar Alpine.
 */

/**
 * Qué archivos acepta cada tipo de medio y de qué peso (igual que App\Services\MediaUploadService::validateFile).
 * Se revisa al elegir el archivo, para avisar en el momento y no al guardar, cuando ya no se sabe cuál falló.
 */
const MEDIA_RULES = {
    image: { maxMb: 10, extensions: ['jpg', 'jpeg', 'png', 'gif', 'webp'], what: 'una foto (JPG, PNG, WebP o GIF)', tip: 'Si es una foto de iPhone en formato HEIC, guárdala como JPG y vuelve a elegirla.' },
    audio: { maxMb: 20, extensions: ['mp3', 'm4a', 'wav', 'ogg', 'aac', 'opus'], what: 'una canción (MP3, M4A, WAV, OGG, AAC u OPUS)', tip: '' },
    video: { maxMb: 100, extensions: ['mp4', 'mov', 'webm', 'avi'], what: 'un video (MP4, MOV, WEBM o AVI)', tip: 'Si pesa mucho, grábalo o expórtalo en menor calidad.' },
};

/** Textos editables ya armados por plantilla (se arman al pedirlos, una vez). */
const TEXT_CACHE = new Map();

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
        // Celular: el panel es una hoja que sube sobre la vista previa; la manija la estira
        sheetTall: false,
        // Panel («tocar y cambiar», ver panelView): 'field', 'section', 'full' o 'closed'. Una invitación
        // nueva que volvió con errores del guardado abre «Datos del evento» para corregirlos.
        panelMode: config.isCreate && !config.wizard ? 'full' : 'closed',
        // Las opciones de la parte abierta (panelMode 'section') y el campo a la vista ('field')
        sectionChoices: [],
        focusContainerEl: null,
        fieldLabel: '',
        fieldHelp: '',
        // Texto propio de la plantilla que se está cambiando (su clave en EditableTexts) y de dónde es
        copyFocusKey: '',
        fieldGroup: '',
        // Ayuda «?» abierta: 'panel' (la burbuja) o 'part:<pestaña>' (una fila de la barra lateral)
        helpOpen: null,
        // Burbuja junto a lo tocado: dónde va y a qué elemento de la vista previa señala
        bubble: { left: 0, top: 0, side: 'right', arrow: 40, ready: false },
        bubbleAnchor: null,
        // Celular: la barra lateral de partes se abre como cajón
        modulesOpen: false,
        // Computadora: la lista de partes junto al riel se pliega para darle todo el ancho a la invitación
        partsOpen: true,
        // El consejo «Toca un texto o una foto» sale hasta que se toca algo por primera vez
        tipOpen: false,
        // Parte recién encendida que se mostrará cuando la vista previa se recargue con ella
        revealPart: null,
        frameSeq: 0,
        partNotice: '',
        publishDialog: false,
        // «Hazlo tú»: el aviso de qué nivel tiene la invitación y «next» del formulario (publish = ir a publicar)
        tierDialog: false,
        diyNext: '',
        // Asistente de creación (editor/wizard)
        wizardOpen: !!config.wizard,
        wizardStep: 1,
        wizardError: '',
        wizardSaving: false,
        wizardCard: { de: '', para: '' },
        // Revendedor: el evento nuevo es suyo (queda a su nombre) o de un cliente
        ownEvent: false,
        // «Ver como invitado»: la vista previa sin botones «Cambiar», para probarla como la recibirán
        guestView: false,
        skipPreviewScroll: false,
        previewScrollY: null,
        // Tamaño del aparato en la vista previa: phone, tablet o desktop
        previewDevice: 'phone',
        // La invitación se dibuja con una sola petición: el editor manda lo que arma y el servidor la devuelve lista
        previewRenderUrl: config.previewRenderUrl,
        previewError: '',
        // Cuándo se cambió un texto directo en la invitación (setLiveText): la recarga completa espera a que se deje de escribir
        livePatchedAt: 0,
        previewTypingMs: 700,
        clientStoreUrl: config.clientStoreUrl,
        clientPasswordUrl: config.clientPasswordUrl,
        clientPasswordLoading: false,
        mediaUploadUrl: config.mediaUploadUrl,
        previewLoading: false,
        previewTimer: null,
        previewSeq: 0,
        previewDebounceMs: 250,
        mediaUploading: false,
        // Lo que se está haciendo al guardar («Subiendo archivo 2 de 3…») y si ya se está saliendo de la página
        uploadStatus: '',
        leaving: false,
        savedSnapshot: null,
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

        // Recortador de imágenes: la foto en el marco real del espacio donde va (App\Support\ImageFrames)
        cropperOpen: false,
        cropperBlobUrl: null,
        cropperEntry: null,
        cropperFrame: null,
        cropperLoading: false,
        cropperPinch: null,
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
                label: 'Lo básico',
                description: 'Datos base y diseño visual',
                tabs: [
                    { id: 'general', label: 'Datos del evento', hint: 'Nombre, fecha, hora y enlace' },
                    { id: 'estetica', label: 'Colores y letras', hint: 'Cómo se ve toda la invitación' },
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
                label: 'Portada y lugar',
                description: 'Primera impresión del evento',
                tabs: [
                    { id: 'hero', label: 'Portada', moduleCode: 'bienvenida', hint: 'Foto, nombre y fecha de arriba' },
                    { id: 'ubicacion', label: 'Ubicación', moduleCode: 'ubicacion', hint: 'Lugar, mapa y cómo llegar' },
                    { id: 'itinerario', label: 'Itinerario', moduleCode: 'itinerario', hint: 'Horarios de la noche' },
                    { id: 'dress', label: 'Cómo vestir', moduleCode: 'dress_code', hint: 'Qué usar, colores y qué evitar' },
                ],
            },
            {
                id: 'contenido',
                label: 'Fotos y personas',
                description: 'Historias y medios del evento',
                tabs: [
                    { id: 'destacados', label: 'Invitados de honor', moduleCode: 'destacados', hint: 'Chambelanes, damitas y padrinos' },
                    { id: 'galeria', label: 'Galería', moduleCode: 'galeria', hint: 'Fotos que se deslizan' },
                    { id: 'video', label: 'Video', moduleCode: 'video', hint: 'Save the date con portada' },
                ],
            },
            {
                id: 'interaccion',
                label: 'Para tus invitados',
                description: 'Participación de los invitados',
                tabs: [
                    { id: 'musica', label: 'Música', moduleCode: 'musica', hint: 'Canción de fondo' },
                    { id: 'playlist', label: 'Canciones de los invitados', moduleCode: 'playlist', hint: 'Te sugieren qué poner en la fiesta' },
                    { id: 'hashtag', label: 'Hashtag', moduleCode: 'hashtag', hint: 'Etiqueta para compartir fotos' },
                    { id: 'encuestas', label: 'Encuestas', moduleCode: 'encuestas', hint: 'Preguntas con votación' },
                    { id: 'regalos', label: 'Regalos', moduleCode: 'regalos', hint: 'Transferencia, tienda y sobres' },
                    // Dos formas de confirmar; una invitación usa una sola (encender una apaga la otra)
                    { id: 'rsvp_whatsapp', label: 'Confirmar por WhatsApp', moduleCode: 'rsvp_whatsapp', hint: 'Te llega la respuesta de cada invitado' },
                    { id: 'rsvp', label: 'Confirmar con pase QR', moduleCode: 'rsvp', hint: 'Respuesta guardada y pase de entrada' },
                ],
            },
            {
                id: 'logistica',
                label: 'Extras',
                description: 'Utilidades antes y después del evento',
                tabs: [
                    { id: 'countdown', label: 'Cuenta regresiva', moduleCode: 'cuenta_regresiva', hint: 'Tiempo que falta para el evento' },
                    { id: 'agendar', label: 'Guardar en el calendario', moduleCode: 'agendar', hint: 'Botón para agendar la fecha' },
                    { id: 'fotomural', label: 'Muro de fotos', moduleCode: 'fotomural', hint: 'Fotos de los invitados en vivo' },
                    { id: 'post_evento', label: 'Después de la fiesta', moduleCode: 'post_evento', hint: 'Gracias y fotos oficiales' },
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
        baseColorRoles: [
            { key: 'background', label: 'Fondo', usage: 'Color de fondo de toda la invitación.' },
            { key: 'text', label: 'Texto', usage: 'Títulos, párrafos y horarios. Tiene que leerse bien sobre el fondo.' },
            { key: 'primary', label: 'Principal', usage: 'Botones, líneas decorativas, íconos animados y la luz del itinerario.' },
            { key: 'accent', label: 'Acento', usage: 'Tono suave de las zonas destacadas: portada, galería y recuadros.' },
            { key: 'secondary', label: 'Secundario', usage: 'Esta plantilla todavía no lo usa. Se guarda para otras plantillas.', unused: true },
        ],

        // Las plantillas que cuentan para qué usan cada color («color_usage» en InvitationTemplates) lo dicen aquí
        get colorRoles() {
            const usage = this.templateOptions.find(item => item.value === String(this.meta.template ?? ''))?.colorUsage ?? {};

            return this.baseColorRoles.map(role => usage[role.key] ? { ...role, usage: usage[role.key], unused: false } : role);
        },
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
                'Libre Caslon Display', 'Cormorant Infant', 'Barlow Condensed', 'Bagel Fat One', 'Special Elite',
                'Instrument Serif', 'Fraunces', 'Bricolage Grotesque', 'Bungee', 'Italiana', 'Unbounded',
            ],
            cuerpo: [
                'Montserrat', 'Inter', 'Lato', 'Nunito Sans', 'Source Sans 3',
                'Poppins', 'Raleway', 'Open Sans', 'Instrument Sans', 'Figtree', 'Source Serif 4', 'Outfit', 'Public Sans', 'DM Sans',
            ],
            script: [
                'Great Vibes', 'Parisienne', 'Alex Brush', 'Dancing Script',
                'Sacramento', 'Allura', 'Tangerine', 'Petit Formal Script', 'Mrs Saint Delafield', 'Pinyon Script', 'Creepster', 'Cinzel', 'Inter',
                'Bodoni Moda', 'Libre Caslon Display', 'Cormorant Infant', 'Barlow Condensed', 'Bagel Fat One', 'Special Elite',
                'Instrument Serif', 'Fraunces', 'Bricolage Grotesque', 'Alfa Slab One', 'Fredoka', 'Caveat', 'Permanent Marker',
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

        /** Todas las secciones en el orden de la lista, para recorrerlas con «Anterior» y «Siguiente». */
        get flatTabs() {
            return this.tabGroups.flatMap(group => group.tabs);
        },

        get currentTab() {
            return this.flatTabs.find(tab => tab.id === this.activeTab) ?? null;
        },

        get prevTab() {
            const index = this.flatTabs.findIndex(tab => tab.id === this.activeTab);
            return index > 0 ? this.flatTabs[index - 1] : null;
        },

        get nextTab() {
            const index = this.flatTabs.findIndex(tab => tab.id === this.activeTab);
            return index >= 0 && index < this.flatTabs.length - 1 ? this.flatTabs[index + 1] : null;
        },

        /** Estado de una sección en la lista: oculta, le falta algo o lista. */
        tabStatus(tab) {
            if (this.packageLockLabel(tab)) return 'locked';
            if (!this.isTabEnabled(tab)) return 'off';
            return this.tabIssues(tab).length > 0 ? 'issue' : 'ok';
        },

        /** Cuántas de las secciones que se ven en la invitación ya tienen lo que necesitan. */
        get progress() {
            const visible = this.flatTabs.filter(tab => this.isTabEnabled(tab));
            return { done: visible.filter(tab => this.tabIssues(tab).length === 0).length, total: visible.length };
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
                case 'rsvp_whatsapp':
                    return String(m.rsvp_whatsapp?.whatsapp ?? '').replace(/\D+/g, '').length < 8
                        ? ['Falta el WhatsApp que recibe las confirmaciones'] : [];

                case 'general':
                    return some([
                        !filled(this.meta.title) && 'Falta el título',
                        !filled(this.meta.event_date) && 'Falta la fecha del evento',
                        !filled(this.meta.slug) && 'Falta la dirección de la invitación',
                        // «Hazlo tú»: la invitación ya es de quien la arma, no hay cliente que asignar
                        this.config.editorMode !== 'diy' && !this.meta.user_id && 'Falta asignar el cliente',
                        this.eventInPast && 'La fecha del evento ya pasó',
                        this.linkExpiresBeforeEvent && 'El enlace deja de funcionar antes del evento',
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
            this.publishDialog = false;
            this.meta.status = 'active';
            // «Hazlo tú»: se guarda y la página de publicar decide (gratis, con un nivel o subir de nivel)
            if (this.isDiy) this.diyNext = 'publish';
            this.$nextTick(() => document.getElementById('invitation-form')?.requestSubmit());
        },

        unpublish() {
            this.meta.status = 'inactive';
            this.$nextTick(() => document.getElementById('invitation-form')?.requestSubmit());
        },

        init() {
            this.ensureStructure();
            this.normalizeMetaSelects();
            this.syncEventTypeWithTemplate();
            this.initEventDateFields();
            this.syncActiveGroup();
            this.loadChromeState();
            this.$watch('meta.template', v => {
                if (this.modules.config) this.modules.config.template = v;
                this.applyProfile();
                this.normalizeRsvp();
            });
            // Al cambiar de paquete, la confirmación pasa a la que ese paquete incluye
            this.$watch('meta.package', () => this.normalizeRsvp());
            // Al cambiar el idioma, el contenido de ejemplo que nadie tocó pasa al idioma nuevo
            this.$watch('meta.locale', (next, previous) => this.retranslateSamples(previous, next));
            this.normalizeRsvp();
            this.$watch('modules', () => this.schedulePreview(), { deep: true });
            this.$watch('meta', () => this.schedulePreview(), { deep: true });
            // En vivo: lo escrito, los colores y las letras se ven al instante en la invitación, sin esperar al servidor
            document.getElementById('invitation-form')?.addEventListener('input', (event) => this.onFieldInput(event), true);
            this.$watch('modules.config.colores', () => this.setLiveColors(), { deep: true });
            this.$watch('modules.config.tipografias', () => this.setLiveFonts(), { deep: true });
            // La vista previa sigue a la pestaña: al cambiar de módulo (o al recargarse) salta a su sección
            this.$watch('activeTab', () => this.focusPreviewSection(true));
            this.$watch('panelMode', () => this.markPreviewActive());
            this.$watch('guestView', (value) => {
                this.$refs.previewFrame?.contentDocument?.body?.classList.toggle('ed-guest', value);
                if (value) this.finishEditing();
            });
            this.$nextTick(() => this.$refs.previewFrame?.addEventListener('load', () => {
                this.onPreviewLoaded();
                this.setupPreviewEditing();
                this.restorePreviewScroll();
                this.revealNewPart();
            }));
            // La burbuja sigue a lo tocado: al cambiar de tamaño el panel o la ventana
            this.$nextTick(() => {
                if (!window.ResizeObserver) return;
                // El panel cambia de alto y el celular se corre para dejarle lugar: la burbuja se reubica
                const observer = new ResizeObserver(() => this.placeBubble());
                [this.$refs.panel, this.$refs.previewFrame].forEach(el => el && observer.observe(el));
                this.$refs.previewFrame?.closest('.ed-preview__stage')?.addEventListener('transitionend', () => this.placeBubble());
            });
            window.addEventListener('resize', () => this.placeBubble());
            this.$watch('panelMode', () => this.$nextTick(() => this.placeBubble()));
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

            // Cambios sin guardar: al salir de la página (Cancelar, atrás, cerrar) el navegador pregunta antes
            window.addEventListener('beforeunload', (event) => {
                if (this.leaving || !this.hasUnsavedChanges()) return;
                event.preventDefault();
                event.returnValue = '';
            });
            // La foto del estado inicial se toma cuando el editor terminó de armarse
            setTimeout(() => { this.savedSnapshot = this.editSnapshot(); }, 2500);
        },

        /** Cambia al idioma nuevo los textos de ejemplo que siguen tal como estaban: lo que la persona escribió no se toca. */
        retranslateSamples(from, to) {
            const samples = this.config.samples ?? {};
            const old = samples[from]?.[this.profile.code];
            const fresh = samples[to]?.[this.profile.code];
            if (!old || !fresh) return;
            Object.keys(old).forEach(code => {
                const current = this.modules[code];
                if (!current || typeof current !== 'object' || Array.isArray(current)) return;
                const next = { ...current };
                let changed = false;
                Object.entries(old[code] ?? {}).forEach(([key, value]) => {
                    if (fresh[code]?.[key] !== undefined && JSON.stringify(current[key]) === JSON.stringify(value)) {
                        next[key] = JSON.parse(JSON.stringify(fresh[code][key]));
                        changed = true;
                    }
                });
                if (changed) this.modules[code] = next;
            });
        },

        /** Lo que se puede perder: los datos del evento y de las partes, más lo que espera para subirse. */
        editSnapshot() {
            try {
                return JSON.stringify([this.meta, this.modules]);
            } catch (error) {
                return null;
            }
        },

        hasUnsavedChanges() {
            if (this.pendingUploads.length > 0) return true;
            if (this.savedSnapshot === null) return false;
            return this.editSnapshot() !== this.savedSnapshot;
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

            const objectModules = ['musica', 'video', 'playlist', 'hashtag', 'post_evento', 'rsvp', 'rsvp_whatsapp', 'dedicatoria', 'juntos_desde', 'respuesta', 'relato'];
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
            m.rsvp_whatsapp ??= {};
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

        /**
         * Los textos editables de una plantilla por módulo, armados desde el catálogo compartido y lo propio de la
         * plantilla (igual que AppSupportEditableTexts::expand()).
         */
        editableTextsOf(template) {
            const key = String(template ?? '');
            if (TEXT_CACHE.has(key)) return TEXT_CACHE.get(key);
            const compact = this.config.editableTexts ?? {};
            const groups = {};
            Object.entries(compact.catalog ?? {}).forEach(([module, rows]) => {
                groups[module] = rows.map(([name, label, fallback, long]) => ({ key: name, label, default: compact.overrides?.[key]?.[name] ?? fallback, long }));
            });
            (compact.extras?.[key] ?? []).forEach(([name, label, fallback, long]) => {
                (groups.general ??= []).push({ key: name, label, default: fallback, long });
            });
            TEXT_CACHE.set(key, groups);
            return groups;
        },

        /** Textos editables del apartado abierto: los del módulo o, en «General», los de toda la invitación. */
        activeTextFields() {
            const byTemplate = this.editableTextsOf(this.meta.template);
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

        /**
         * El módulo tal como viaja a la vista previa. Las fotos, videos y canciones recién elegidos
         * (blob:) van tal cual: la vista previa es del mismo sitio y los lee del navegador. Antes se
         * convertían a base64 en cada cambio: una canción de 8 MB pesaba 10 MB por tecla, un video
         * de 50 MB reventaba la memoria del servidor y la vista previa tardaba segundos en recargar.
         */
        async prepareModuleForTransport(code) {
            return this.plainModule(code);
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

        /** Abrir una parte: sus opciones; desde «Todas las opciones» (Anterior / Siguiente), su formulario entero. */
        selectTab(tabId) {
            if (this.panelMode !== 'full') {
                this.openSection(tabId);
                return;
            }
            this.activeTab = tabId;
            this.syncActiveGroup();
            // Cada parte empieza arriba: al cambiar no se queda a mitad de la anterior
            this.$nextTick(() => document.getElementById('invitation-form')?.scrollTo({ top: 0 }));
        },

        groupActiveCount(groupId) {
            const group = this.tabGroups.find(item => item.id === groupId);
            if (!group) return 0;
            return group.tabs.filter(tab => !tab.moduleCode || this.modules.config.modulos[tab.moduleCode]).length;
        },

        isTabEnabled(tab) {
            return !tab.moduleCode || (!!this.modules.config.modulos[tab.moduleCode] && this.packageIncludes(tab.moduleCode));
        },

        // ── Paquete (App\Support\Packages): qué módulos incluye el paquete elegido ──
        // Sin paquete (tarjetas y anteriores) todo está incluido. Al revendedor, la confirmación
        // (WhatsApp o pase QR) se la da su plan: config.planRsvp.
        packageIncludes(moduleCode) {
            const rsvpMode = { rsvp: 'pass', rsvp_whatsapp: 'whatsapp' }[moduleCode];
            if (rsvpMode && Array.isArray(this.config.planRsvp)) {
                return this.config.planRsvp.includes(rsvpMode);
            }
            const order = this.config.packageOrder ?? [];
            const chosen = order.indexOf(this.meta.package ?? '');
            if (chosen < 0) return true;
            const required = order.indexOf(this.config.packageModules?.[moduleCode] ?? order[0]);
            return chosen >= required;
        },

        packageName(value) {
            return (this.config.packageOptions ?? []).find(option => option.value === value)?.label ?? '';
        },

        /** «Viene en Premium»: para las secciones que el paquete elegido no incluye. */
        packageLockLabel(tab) {
            if (!tab.moduleCode || this.packageIncludes(tab.moduleCode)) return '';
            if (Array.isArray(this.config.planRsvp)) return 'Viene en el plan ' + (this.config.planRsvpTiers?.[tab.moduleCode] ?? 'superior');
            return 'Viene en ' + (this.packageName(this.config.packageModules?.[tab.moduleCode]) || 'otro paquete');
        },

        // ── «Hazlo tú» (config.diyTiers, App\Support\Packages): todo desbloqueado; cada parte dice desde
        // qué nivel viene y la barra muestra el nivel que piden las partes encendidas ──
        get isDiy() {
            return !!this.config.diyTiers;
        },

        diyTierOf(moduleCode) {
            return this.config.diyTiers?.modules?.[moduleCode] ?? 'basico';
        },

        diyTierName(tier) {
            return this.config.diyTiers?.names?.[tier] ?? tier;
        },

        diyTierIndex(tier) {
            return (this.config.diyTiers?.order ?? []).indexOf(tier);
        },

        /** El nivel que piden las partes encendidas de esta plantilla. */
        get diyRequiredTier() {
            const order = this.config.diyTiers?.order ?? [];
            let index = 0;
            for (const tab of this.flatTabs) {
                if (tab.moduleCode && this.isTabEnabled(tab)) index = Math.max(index, order.indexOf(this.diyTierOf(tab.moduleCode)));
            }
            return order[index] ?? 'gratis';
        },

        /** Partes encendidas que no son gratuitas, con su nivel: el detalle del aviso «Nivel». */
        get diyPaidParts() {
            return this.flatTabs
                .filter(tab => tab.moduleCode && this.isTabEnabled(tab) && this.diyTierOf(tab.moduleCode) !== 'gratis')
                .map(tab => ({ id: tab.id, label: tab.label, tier: this.diyTierName(this.diyTierOf(tab.moduleCode)) }));
        },

        /** Publicada con un nivel menor que el que piden sus partes: lo de más no se ve hasta subir. */
        get diyNeedsUpgrade() {
            const paid = this.config.diyTiers?.paid;
            return this.isDiy && !!paid && this.diyTierIndex(this.diyRequiredTier) > this.diyTierIndex(paid);
        },

        /** La parte pide más que el nivel pagado: no se ve en la invitación publicada. */
        diyAbovePaid(tab) {
            const paid = this.config.diyTiers?.paid;
            return this.isDiy && !!paid && !!tab.moduleCode && this.isTabEnabled(tab)
                && this.diyTierIndex(this.diyTierOf(tab.moduleCode)) > this.diyTierIndex(paid);
        },

        /** Rótulo del nivel de una parte en la barra lateral («Estándar»); vacío si es gratuita. */
        diyPartTier(tab) {
            if (!this.isDiy || !tab.moduleCode) return '';
            const tier = this.diyTierOf(tab.moduleCode);
            return tier === 'gratis' ? '' : this.diyTierName(tier);
        },

        /** El nivel de una parte como 0–3 puntos (Gratis, Básico, Estándar, Premium); sin nivel fuera de «Hazlo tú». */
        diyPartLevel(tab) {
            return this.isDiy && tab.moduleCode ? Math.max(0, this.diyTierIndex(this.diyTierOf(tab.moduleCode))) : 0;
        },

        /** Cómo confirma el invitado: 'whatsapp', 'pass' o null, según el módulo encendido y lo que incluye el paquete. */
        get rsvpMode() {
            const flags = this.modules.config?.modulos ?? {};
            if (flags.rsvp_whatsapp && this.packageIncludes('rsvp_whatsapp')) return 'whatsapp';
            if (flags.rsvp && this.packageIncludes('rsvp')) return 'pass';
            return null;
        },

        /**
         * Una sola confirmación encendida y, si el paquete no incluye el pase QR pero sí WhatsApp,
         * la confirmación pasa a WhatsApp (p. ej. una invitación nueva en Estándar).
         */
        normalizeRsvp() {
            const flags = this.modules.config?.modulos;
            if (!flags) return;
            if (flags.rsvp && !this.packageIncludes('rsvp') && this.packageIncludes('rsvp_whatsapp')) {
                flags.rsvp = false;
                flags.rsvp_whatsapp = true;
            }
            if (flags.rsvp && flags.rsvp_whatsapp) flags.rsvp = false;
        },

        /** Mostrar u ocultar una parte. Desde la lista no se abre: solo cambia el interruptor. */
        toggleModuleForTab(tab, navigate = true) {
            if (!tab.moduleCode || !this.packageIncludes(tab.moduleCode)) return;
            const enabled = !this.modules.config.modulos[tab.moduleCode];
            this.modules.config.modulos[tab.moduleCode] = enabled;
            // Encender una confirmación apaga la otra
            const other = { rsvp: 'rsvp_whatsapp', rsvp_whatsapp: 'rsvp' }[tab.moduleCode];
            if (enabled && other) this.modules.config.modulos[other] = false;
            if (navigate) this.onModuleToggle(tab.moduleCode, enabled);
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
                rsvp_whatsapp: false,
                fotomural: false,
                cuenta_regresiva: false,
                agendar: false,
                post_evento: false,
            };
        },

        get previewWidth() {
            return { phone: 390, tablet: 768, desktop: 1180 }[this.previewDevice] ?? 390;
        },

        /**
         * El panel («tocar y cambiar», igual en la computadora y en el celular):
         * - 'field': una burbuja junto a lo que se tocó, con solo ese campo (el nombre, una foto, la hora de un momento)
         * - 'section': la burbuja de una parte con lo que se puede cambiar de ella, una fila por bloque
         * - 'full': el formulario entero de una parte en un panel lateral («Datos del evento», «Colores y
         *   letras», «Ver todas las opciones»)
         * - 'closed': nada abierto. Las partes se encienden y apagan en la barra lateral (editor/modules).
         * En el celular la burbuja y el panel son hojas que suben desde abajo.
         */
        get panelView() {
            return this.panelMode;
        },

        /** Hay una parte abierta (un campo, sus opciones o todo su formulario). */
        get isEditingPart() {
            return ['field', 'section', 'full'].includes(this.panelMode);
        },

        /** Burbuja junto a lo tocado (un campo o las opciones de una parte); 'full' es un panel lateral. */
        get isBubble() {
            return this.panelMode === 'field' || this.panelMode === 'section';
        },

        /** Celular y tableta: el panel es una hoja que sube desde abajo. */
        isCompactEditor() {
            return window.matchMedia('(max-width: 1023px)').matches;
        },

        /** «Listo»: se cierra y queda la invitación a la vista. */
        finishEditing() {
            this.clearFieldFocus();
            this.panelMode = 'closed';
            this.sheetTall = false;
            this.helpOpen = null;
            this.partNotice = '';
            this.bubbleAnchor = null;
        },

        /** La flecha de la burbuja: de un campo a las opciones de su parte; de ahí, se cierra. */
        goBack() {
            if (this.panelMode !== 'section' && this.currentTab?.moduleCode) {
                this.openSection(this.activeTab);
            } else {
                this.finishEditing();
            }
        },

        /** Las opciones de una parte; si tiene un solo bloque (o ninguno reconocible), su formulario entero. */
        openSection(tabId) {
            this.clearFieldFocus();
            this.helpOpen = null;
            this.partNotice = '';
            this.activeTab = tabId;
            this.syncActiveGroup();
            this.panelMode = 'section';
            this.sheetTall = false;
            this.$nextTick(() => {
                this.sectionChoices = this.buildSectionChoices();
                if (this.sectionChoices.length <= 1) {
                    this.openFull(tabId);
                    return;
                }
                this.bubbleAnchor = this.previewSectionElement(tabId);
                this.placeBubble();
            });
        },

        /** Lo que el editor recuerda de este aparato (lista de partes plegada, consejo ya visto); sin almacenamiento, todo sigue funcionando. */
        loadChromeState() {
            try {
                this.partsOpen = window.localStorage.getItem('ed.partsOpen') !== '0';
                this.tipOpen = window.localStorage.getItem('ed.tipSeen') !== '1';
            } catch {
                this.tipOpen = true;
            }
            // Tocar algo por primera vez ya enseñó cómo se edita: el consejo no vuelve
            this.$watch('panelMode', mode => {
                if (mode === 'closed' || !this.tipOpen) return;
                this.tipOpen = false;
                try { window.localStorage.setItem('ed.tipSeen', '1'); } catch { /* sin almacenamiento */ }
            });
        },

        /** Computadora: pliega o muestra la lista de partes. */
        togglePartsColumn() {
            this.partsOpen = !this.partsOpen;
            try { window.localStorage.setItem('ed.partsOpen', this.partsOpen ? '1' : '0'); } catch { /* sin almacenamiento */ }
            this.$nextTick(() => this.placeBubble());
        },

        /** Datos y Colores en el riel / la barra: abren su formulario y, tocados de nuevo, lo cierran. */
        toggleFull(tabId) {
            if (this.panelMode === 'full' && this.activeTab === tabId) {
                this.finishEditing();
                return;
            }
            this.openFull(tabId);
        },

        /** El formulario entero de una parte (sin parte: la abierta), en el panel lateral. */
        openFull(tabId = null) {
            this.clearFieldFocus();
            this.helpOpen = null;
            if (tabId) {
                this.activeTab = tabId;
                this.syncActiveGroup();
            }
            this.panelMode = 'full';
            this.bubbleAnchor = null;
            this.modulesOpen = false;
            this.$nextTick(() => document.getElementById('invitation-form')?.scrollTo({ top: 0 }));
        },

        // ── Las partes de la invitación (barra lateral: editor/modules) ──

        /** Las partes que se encienden y apagan: todas menos los datos y los colores (esos van arriba). */
        get partGroups() {
            return this.tabGroups
                .map(group => ({ ...group, tabs: group.tabs.filter(tab => tab.moduleCode) }))
                .filter(group => group.tabs.length);
        },

        /** Lo que dice cada parte en la barra lateral: qué le falta, si está apagada o qué es. */
        partStatus(tab) {
            const status = this.tabStatus(tab);
            if (status === 'locked') return this.packageLockLabel(tab);
            if (status === 'off') return 'Apagada';
            if (this.diyAbovePaid(tab)) return 'No se ve hasta subir a ' + this.diyTierName(this.diyTierOf(tab.moduleCode));
            const examples = this.examplesOf(tab);
            if (examples) return examples === 1 ? '1 cosa de ejemplo por cambiar' : examples + ' cosas de ejemplo por cambiar';
            if (status === 'issue') return this.tabIssues(tab)[0];
            return tab.hint;
        },

        /** Encender o apagar una parte. Encendida y vacía, nace con su ejemplo y la invitación baja hasta ella. */
        togglePart(tab) {
            if (!tab.moduleCode || this.packageLockLabel(tab)) return;
            const enabling = !this.isTabEnabled(tab);
            this.toggleModuleForTab(tab, false);
            if (!enabling) {
                if (this.activeTab === tab.id && this.isEditingPart) this.finishEditing();
                return;
            }
            // Si había otra parte abierta se cierra: su formulario y su aviso no son los de la que se enciende
            if (this.isEditingPart && this.activeTab !== tab.id) this.finishEditing();
            this.fillWithSample(tab.moduleCode);
            // Se ve cuando la vista previa se recargue con ella; si no aparece, le falta un dato propio
            this.revealPart = { tabId: tab.id, after: this.previewSeq };
            if (this.isCompactEditor()) this.modulesOpen = false;
        },

        /** Desde la barra lateral: la invitación baja hasta la parte y se abren sus opciones. */
        goToPart(tab) {
            if (!this.isTabEnabled(tab)) {
                this.togglePart(tab);
                return;
            }
            this.modulesOpen = false;
            const section = this.previewSectionElement(tab.id);
            if (section) {
                this.skipPreviewScroll = true;
                this.scrollPreviewTo(section);
            }
            this.openSection(tab.id);
        },

        previewSectionElement(tabId) {
            return this.$refs.previewFrame?.contentDocument?.querySelector('[data-ed-tab="' + tabId + '"]') ?? null;
        },

        scrollPreviewTo(el, smooth = true) {
            const view = el?.ownerDocument?.defaultView;
            if (!view) return;
            view.scrollTo({ top: Math.max(0, el.getBoundingClientRect().top + view.scrollY - 24), behavior: smooth ? 'smooth' : 'auto' });
        },

        /**
         * Tras recargar la vista previa con una parte recién encendida: baja hasta ella y la resalta un
         * momento. Si no aparece es porque le falta algo que el ejemplo no puede traer (la canción, el
         * número de WhatsApp): se abre su formulario para cargarlo.
         */
        revealNewPart() {
            const pending = this.revealPart;
            if (!pending || this.frameSeq <= pending.after) return;
            this.revealPart = null;

            const section = this.previewSectionElement(pending.tabId);
            if (!section) {
                this.openFull(pending.tabId);
                this.partNotice = 'Para que esta parte se vea en la invitación, completa lo que pide aquí abajo.';
                return;
            }
            // Algunas plantillas vuelven arriba al terminar de cargar: se baja un instante después
            setTimeout(() => {
                if (!section.isConnected) return;
                this.scrollPreviewTo(section);
                section.classList.add('ed-flash');
                setTimeout(() => section.classList.remove('ed-flash'), 1800);
            }, 450);
        },

        // ── Contenido de ejemplo (App\Support\SampleContent) ──

        /** Si una parte tiene algo propio: un texto o una lista distinta de cómo nace vacía. */
        moduleHasContent(code) {
            const walk = (value, empty) => {
                if (typeof value === 'string') return value.trim() !== '' && value !== empty;
                if (Array.isArray(value)) return value.some((item, index) => walk(item, Array.isArray(empty) ? empty[index] : undefined));
                if (value && typeof value === 'object') {
                    return Object.keys(value).some(key => walk(value[key], empty && typeof empty === 'object' ? empty[key] : undefined));
                }
                return false;
            };

            return walk(this.modules[code], this.config.moduleDefaults?.[code]);
        },

        /**
         * Completa una parte con el contenido de ejemplo de su evento: solo lo que está vacío, así no se
         * pisa nada escrito. Sin «force», una parte que ya tiene algo propio se deja como está.
         */
        fillWithSample(code, force = false) {
            const samples = this.config.samples?.[this.meta.locale] ?? this.config.samples?.es;
            const sample = samples?.[this.profile.code]?.[code];
            if (!sample || typeof sample !== 'object' || Array.isArray(sample)) return;
            if (!force && this.moduleHasContent(code)) return;

            const isEmpty = (value) => value === undefined || value === null || value === ''
                || (Array.isArray(value) && value.length === 0)
                || (typeof value === 'object' && !Array.isArray(value) && Object.values(value).every(isEmpty));
            const current = this.modules[code];
            const next = current && typeof current === 'object' && !Array.isArray(current) ? { ...current } : {};

            Object.entries(sample).forEach(([key, value]) => {
                if (isEmpty(next[key])) next[key] = JSON.parse(JSON.stringify(value));
            });
            this.modules[code] = next;
        },

        /** Lo de ejemplo que sigue sin cambiar en las partes encendidas («Tu foto aquí», «Escribe aquí…»). */
        get examplesLeft() {
            const placeholders = new Set(this.config.samplePlaceholders ?? []);
            if (!placeholders.size) return [];

            const count = (value) => {
                if (typeof value === 'string') return placeholders.has(value.trim()) ? 1 : 0;
                if (Array.isArray(value)) return value.reduce((sum, item) => sum + count(item), 0);
                if (value && typeof value === 'object') return Object.values(value).reduce((sum, item) => sum + count(item), 0);
                return 0;
            };

            return this.flatTabs
                .filter(tab => tab.moduleCode && this.isTabEnabled(tab))
                .map(tab => ({ tab, count: count(this.modules[tab.moduleCode]) }))
                .filter(item => item.count > 0);
        },

        examplesOf(tab) {
            return this.examplesLeft.find(item => item.tab.id === tab.id)?.count ?? 0;
        },

        get examplesTotal() {
            return this.examplesLeft.reduce((sum, item) => sum + item.count, 0);
        },

        /** «Publicar»: si queda algo de ejemplo o por completar, primero se avisa con atajos a cada parte. */
        requestPublish() {
            if (this.examplesLeft.length || this.pendingIssues.length) {
                this.publishDialog = true;
                return;
            }
            this.publish();
        },

        /** Desde el aviso de publicar: cerrarlo y abrir la parte para cambiar lo de ejemplo. */
        fixPart(tabId) {
            this.publishDialog = false;
            const section = this.previewSectionElement(tabId);
            if (section) this.scrollPreviewTo(section);
            this.skipPreviewScroll = true;
            this.openSection(tabId);
        },

        // ── Ayuda «?»: para qué se sugiere usar cada parte y cada campo ──

        partHelp(tab) {
            return (tab && this.partHelpTexts[tab.id]) || tab?.hint || '';
        },

        /** La ayuda de un campo: la propia, la nota que trae el panel o la de su parte. */
        helpForField(container, label) {
            const own = [...(this.fieldHelpTexts[this.activeTab] ?? []), ...this.fieldHelpTexts._]
                .find(([pattern]) => pattern.test(label));
            if (own) return own[1];

            const note = [...container.querySelectorAll('p')]
                .map(p => p.textContent.replace(/\s+/g, ' ').trim())
                .find(text => text.length > 24 && text !== label);

            return note || this.partHelp(this.currentTab);
        },

        get currentHelp() {
            return this.panelMode === 'field' ? (this.fieldHelp || this.partHelp(this.currentTab)) : this.partHelp(this.currentTab);
        },

        partHelpTexts: {
            general: 'El nombre del evento, la fecha y la hora. Con la fecha se arma la cuenta regresiva y el botón para agendar.',
            estetica: 'Los colores y las letras de toda la invitación. Úsalo para que combine con tu fiesta (por ejemplo, con los colores de la decoración).',
            hero: 'Es lo primero que ven tus invitados al abrir el enlace: la foto principal, el nombre y una frase. Una buena foto aquí hace toda la diferencia.',
            ubicacion: 'Dónde es el evento, con un botón que abre el mapa para llegar. Sugerido siempre que haya un lugar físico.',
            itinerario: 'Los momentos de la fiesta con su hora (recepción, vals, cena…). Ayuda a que nadie se pierda lo importante.',
            dress: 'Cómo pedirles que vengan vestidos y qué colores evitar. Úsalo si tu fiesta tiene un estilo o un color especial.',
            destacados: 'Las personas especiales del evento: padrinos, damas, chambelanes. Un lindo gesto para agradecerles.',
            galeria: 'Fotos que tus invitados deslizan. Sugerido para compartir tus mejores fotos antes de la fiesta.',
            video: 'Un video corto, como un «save the date». Úsalo si tienes un video que quieras compartir.',
            musica: 'Una canción que suena al abrir la invitación. Elige una que te represente.',
            playlist: 'Tus invitados sugieren canciones para la fiesta y tú las ves en tu panel. Ideal para que el DJ ponga lo que les gusta.',
            hashtag: 'Una etiqueta para que todos compartan sus fotos de la fiesta en redes y las encuentres juntas.',
            encuestas: 'Preguntas divertidas para tus invitados, con votación. Sirve para animar antes de la fiesta.',
            regalos: 'Cómo pueden hacerte un regalo: lista de regalos, transferencia o sobres. Úsalo solo si quieres mencionarlo.',
            rsvp_whatsapp: 'Tus invitados confirman si van y te llega la respuesta a tu WhatsApp. Lo más simple para saber cuántos van.',
            rsvp: 'Tus invitados confirman si van y reciben un pase con QR para la entrada. Sugerido para eventos con lista de invitados.',
            countdown: 'Muestra cuánto falta para el evento (días, horas, minutos). Genera emoción.',
            agendar: 'Un botón para guardar la fecha en el calendario del celular, así nadie se olvida.',
            fotomural: 'Tus invitados suben sus fotos durante la fiesta y se ven juntas. Ideal para tener todos los recuerdos.',
            post_evento: 'Después de la fiesta, un agradecimiento y las fotos oficiales para tus invitados.',
            relato: 'Su historia contada en momentos, con una anécdota y una promesa.',
            dedicatoria: 'La carta: de quién, para quién y lo que le quieres decir. Es el corazón de la tarjeta.',
            juntos_desde: 'Un contador del tiempo que llevan juntos desde una fecha especial.',
            respuesta: 'Quien recibe la tarjeta te puede responder con unas palabras.',
            historia: 'Capítulos de su historia, cada uno con fecha, texto y foto.',
            recuerdos: 'Fotos especiales, cada una con una nota.',
            collage: 'Un collage de fotos con formas y flores.',
            marcos: 'Fotos enmarcadas, cada una con un pie de foto.',
            memoria: 'Un juego de memoria con sus fotos: encontrar los pares.',
            aventuras: 'Lo que les falta vivir juntos: viajes, planes y sueños.',
        },

        // Por parte (y «_» para todas): la etiqueta del campo → para qué se sugiere usarlo
        fieldHelpTexts: {
            _: [
                [/imagen|foto|portada/i, 'Usa una foto nítida y vertical, con la cara en la parte de arriba. Después puedes encuadrarla.'],
                [/t[ií]tulo de la secci[oó]n|^t[ií]tulo$/i, 'El título que se ve arriba de esta parte. Corto y claro.'],
                [/descripci[oó]n/i, 'Un detalle breve. Si no hace falta, déjalo vacío y no se muestra.'],
                [/mensaje/i, 'Unas palabras para tus invitados. Mejor corto y cálido: dos o tres líneas.'],
            ],
            hero: [
                [/nombre/i, 'El nombre que se ve en grande en la portada. Escríbelo como quieres que se lea.'],
                [/subt[ií]tulo|frase/i, 'Una frase corta arriba del nombre, por ejemplo «Mis XV años» o «Nos casamos».'],
                [/fecha/i, 'La fecha tal como quieres que se lea en la portada, por ejemplo «Sábado 14 de marzo».'],
            ],
            ubicacion: [
                [/nombre del lugar/i, 'El nombre del salón, la iglesia o la casa. Es lo que tus invitados buscarán.'],
                [/direcci[oó]n/i, 'Calle, número y zona. Ayuda a llegar aunque no abran el mapa.'],
                [/mapa|coordenadas/i, 'Pega el enlace o las coordenadas de Google Maps: así funciona el botón «Cómo llegar».'],
                [/nota/i, 'Un dato útil para llegar: dónde estacionar, por qué puerta entrar.'],
            ],
            itinerario: [
                [/hora/i, 'La hora de este momento, por ejemplo 19:30.'],
                [/t[ií]tulo/i, 'Qué pasa en este momento, en pocas palabras: «Vals», «Cena», «Fiesta».'],
            ],
            rsvp_whatsapp: [
                [/whatsapp|n[uú]mero/i, 'El número donde quieres recibir las confirmaciones, con el código del país (por ejemplo 591).'],
            ],
            general: [
                [/t[ií]tulo/i, 'El nombre del evento. Se usa en el enlace y al compartir la invitación.'],
                [/direcci[oó]n de la invitaci[oó]n/i, 'Es el final del enlace que vas a compartir. Usa minúsculas y guiones.'],
            ],
        },

        // ── La burbuja junto a lo tocado (solo en la computadora; en el celular es una hoja abajo) ──

        /**
         * Ubica la burbuja al costado del celular de la vista previa, a la altura de lo tocado, con una
         * flecha que lo señala. Si no cabe al costado (vista de computadora), va debajo de lo tocado.
         */
        placeBubble() {
            if (!this.isBubble || this.isCompactEditor()) {
                this.bubble = { ...this.bubble, ready: false };
                return;
            }
            const canvas = this.$refs.canvas?.getBoundingClientRect();
            const frame = this.$refs.previewFrame?.getBoundingClientRect();
            const panel = this.$refs.panel;
            if (!canvas || !frame || !panel) return;

            const anchor = this.bubbleAnchor?.isConnected ? this.bubbleAnchor : null;
            const box = anchor ? anchor.getBoundingClientRect() : { top: 40, height: 0, left: 0, width: frame.width };
            const width = 360;
            const gap = 18;
            const margin = 12;
            const minTop = 72;
            const height = panel.offsetHeight || 280;
            const anchorTop = frame.top - canvas.top + Math.max(0, Math.min(box.top, frame.height - 24));
            const anchorMid = anchorTop + Math.min(Math.max(box.height, 24), 140) / 2;

            let left;
            let side;
            if (canvas.right - frame.right - gap >= width + margin) {
                left = frame.right - canvas.left + gap;
                side = 'right';
            } else if (frame.left - canvas.left - gap >= width + margin) {
                left = frame.left - canvas.left - gap - width;
                side = 'left';
            } else {
                left = Math.min(Math.max(margin, frame.left - canvas.left + box.left), canvas.width - width - margin);
                side = 'below';
            }

            const wanted = side === 'below' ? anchorTop + Math.min(box.height, 160) + gap : anchorMid - 44;
            const top = Math.max(minTop, Math.min(wanted, canvas.height - height - margin));

            this.bubble = {
                left: Math.round(left),
                top: Math.round(top),
                side,
                arrow: Math.round(Math.min(Math.max(22, anchorMid - top), height - 22)),
                ready: true,
            };
        },

        get bubbleStyle() {
            if (!this.isBubble || !this.bubble.ready) return '';
            return 'left:' + this.bubble.left + 'px;top:' + this.bubble.top + 'px;--ed-arrow:' + this.bubble.arrow + 'px';
        },

        // ── Asistente de creación: evento, diseño y datos antes de ver la vista previa ──

        /** Los tipos de evento agrupados: invitaciones (todo el año y temporadas) y tarjetas. */
        get wizardEventGroups() {
            const groups = [];
            ['invitation', 'card'].forEach(kind => {
                this.eventTypeGroups(kind).forEach(group => groups.push({
                    name: kind === 'card' ? 'Tarjetas · ' + group.name : group.name,
                    types: group.types,
                }));
            });
            return groups;
        },

        wizardChooseEvent(type) {
            if (this.eventTypeDisabledReason(type)) return;
            const kind = type.kind ?? 'invitation';
            if (kind !== (this.profile.kind ?? 'invitation')) this.chooseKind(kind);
            this.chooseEventType(type);
            this.wizardError = '';
            this.wizardStep = 2;
        },

        wizardChooseTemplate(option) {
            if (option.disabledReason) return;
            this.meta.template = option.value;
            this.wizardError = '';
            this.wizardStep = 3;
        },

        get wizardName() {
            return String(this.modules.bienvenida?.nombre_quinceanera || this.modules.bienvenida?.nombre || '').trim();
        },

        /** Crea la invitación con lo elegido: las partes del evento nacen con su ejemplo y se abre la vista previa. */
        finishWizard() {
            this.wizardError = '';
            const card = this.isCard;

            if (card) {
                if (!this.wizardCard.para.trim()) {
                    this.wizardError = 'Escribe para quién es la tarjeta.';
                    return;
                }
            } else {
                this.syncHeroName();
                if (!this.wizardName) {
                    this.wizardError = 'Falta un dato: ' + String(this.heroFields[0]?.label ?? 'el nombre').toLowerCase() + '.';
                    return;
                }
                if (!this.eventDatePart) {
                    this.wizardError = 'Elige la fecha del evento.';
                    return;
                }
            }

            // Las partes que trae el evento nacen con su ejemplo (los nombres escritos se conservan)
            (this.profile.enabledByDefault ?? []).forEach(code => this.fillWithSample(code, true));

            if (card) {
                this.modules.dedicatoria = {
                    ...(this.modules.dedicatoria ?? {}),
                    para: this.wizardCard.para.trim(),
                    de: this.wizardCard.de.trim() || (this.modules.dedicatoria?.de ?? ''),
                };
            }

            const name = card ? this.wizardCard.para.trim() : this.wizardName;
            this.meta.title = card
                ? 'Para ' + name
                : (['lienzo', 'halloween'].includes(this.profile.code) ? name : this.profile.label + ' de ' + name);
            this.meta.slug = this.slugify(this.meta.title);
            this.syncEventDateTime();

            this.wizardSaving = true;
            this.$nextTick(() => document.getElementById('invitation-form')?.requestSubmit());
        },

        // ── Los campos del panel, leídos de sus propias vistas (admin/invitations/panels) ──

        /** El contenedor del panel de una parte: <div x-show="activeTab === 'hero'">. */
        tabPanelRoot(tabId) {
            return [...document.querySelectorAll('#invitation-form [x-show]')]
                .find(el => el.getAttribute('x-show').trim() === "activeTab === '" + tabId + "'") ?? null;
        },

        /** De qué parte es un campo: la del panel que lo contiene (los textos editables, la abierta). */
        tabOfControl(control) {
            for (let el = control; el && el.id !== 'invitation-form'; el = el.parentElement) {
                const match = /^activeTab === '([^']+)'$/.exec(el.getAttribute?.('x-show')?.trim() ?? '');
                if (match) return match[1];
            }
            return this.activeTab;
        },

        /** Oculto dentro del panel por una condición (x-show deja display:none en el elemento). */
        hiddenInPanel(el, root) {
            for (let node = el; node && node !== root; node = node.parentElement) {
                if (node.style?.display === 'none') return true;
            }
            return false;
        },

        /** El nombre de un bloque del panel: su etiqueta, su título o su acordeón (no el botón «Elegir archivo»). */
        panelLabelElement(el) {
            return [...el.querySelectorAll('.admin-accordion-trigger p, .admin-eyebrow, .admin-label, label, h3')]
                .find(label => !label.querySelector('input, select, textarea')) ?? null;
        },

        panelLabelOf(el) {
            return (this.panelLabelElement(el)?.textContent ?? '').replace(/\s+/g, ' ').trim();
        },

        /** El bloque de un campo: lo más chico que lo contiene junto con su nombre (etiqueta, título o acordeón). */
        fieldContainer(control) {
            const stop = document.querySelector('#invitation-form > .flex-1');
            const isPanelRoot = (el) => el === stop || el.matches('[x-show^="activeTab ==="]');

            // Una foto: su tarjeta entera (la foto, su descripción y sus botones), sin las otras fotos
            if (control.tagName === 'IMG') {
                let card = control;
                while (card.parentElement && !isPanelRoot(card.parentElement) && card.parentElement.querySelectorAll('img').length === 1) {
                    card = card.parentElement;
                }
                return card;
            }

            let el = control;
            while (el.parentElement && el.parentElement !== stop && !el.parentElement.matches('[x-show^="activeTab ==="]')) {
                el = el.parentElement;
                if (this.panelLabelElement(el)) return el;
            }
            return el;
        },

        // ── Lo editable de cada plantilla: sus textos propios y el marco de sus fotos ──

        /** El marco de foto de una parte en la plantilla elegida (la portada cambia con cada plantilla). */
        imageFrameOfTab(tabId) {
            const contexts = {
                hero: 'hero', galeria: 'gallery', ubicacion: 'ubicacion', dress: 'dress-code', post_evento: 'post-evento',
                historia: 'historia', recuerdos: 'recuerdos', marcos: 'marcos', memoria: 'memoria', collage: 'collage',
                regalos: 'qr-banco', video: 'video-poster',
            };
            const context = contexts[tabId];
            if (!context) return null;
            const frames = this.config.imageFrames ?? {};
            return context === 'hero' ? (frames.hero?.[String(this.meta.template ?? '')] ?? null) : (frames.contexts?.[context] ?? null);
        },

        /** Cómo se ve la foto en esta plantilla, en palabras. */
        imageHelp(frame) {
            const where = {
                rect: 'en un marco rectangular',
                rounded: 'en un marco de esquinas redondeadas',
                arch: 'dentro de un arco',
                oval: 'dentro de un óvalo',
                circle: 'dentro de un círculo',
            }[frame.shape] ?? 'en su marco';
            const hint = ['circle', 'oval', 'arch'].includes(frame.shape)
                ? 'Elige una foto donde lo importante quede al centro: las orillas se recortan.'
                : 'Elige una foto nítida; lo que quede fuera del marco no se verá.';
            const template = this.config.templateOptions?.find(option => option.value === this.meta.template)?.label;
            return `${template ? 'En «' + template + '»' : 'Aquí'} la foto se ve ${where} (${this.frameShapeLabel(frame)}). ${hint} Después puedes encuadrarla.`;
        },

        /**
         * Los textos propios de la plantilla elegida (EditableTexts): lo que dice cada título, frase o botón.
         * Cada uno con su parte, para abrirlo desde la invitación aunque su campo no esté a la vista.
         */
        templateTexts() {
            const byModule = this.editableTextsOf(this.meta.template);
            const tabs = this.flatTabs;
            return Object.entries(byModule).flatMap(([module, fields]) => {
                const tab = module === 'general' ? tabs.find(item => item.id === 'general') : tabs.find(item => item.moduleCode === module);
                return tab ? fields.map(field => ({ ...field, tabId: tab.id, tabLabel: tab.label })) : [];
            });
        },

        /** Lo que dice hoy un texto de la plantilla: el que escribiste o el que trae la plantilla. */
        templateTextValue(field) {
            return String(this.modules.config?.textos?.[field.key] ?? '').trim() || String(field.default ?? '');
        },

        /** Abre un texto de la plantilla en la burbuja: su parte, su campo y una ayuda que dice dónde sale y qué dice por defecto. */
        openTemplateText(entry, tapped = null) {
            this.skipPreviewScroll = entry.tabId !== this.activeTab;
            this.clearFieldFocus();
            this.activeTab = entry.tabId;
            this.syncActiveGroup();
            this.$nextTick(() => {
                const section = document.querySelector('#invitation-form section[x-data*="textsOpen"]');
                if (section) {
                    try { Alpine.$data(section).textsOpen = true; } catch (error) { /* queda como está */ }
                }
                this.$nextTick(() => {
                    const input = document.getElementById('texto-' + entry.key);
                    if (!input) {
                        this.openFull(entry.tabId);
                        return;
                    }
                    const template = this.config.templateOptions?.find(option => option.value === this.meta.template)?.label;
                    const where = entry.tabId === 'general' ? 'Se usa en toda la invitación.' : 'Sale en «' + entry.tabLabel + '».';
                    const help = `Texto de la plantilla${template ? ' «' + template + '»' : ''}. ${where} Si lo dejas vacío dice: «${entry.default}».`;
                    this.focusContainer(input.parentElement, input, entry.label, tapped, help);
                    this.copyFocusKey = entry.key;
                    this.fieldGroup = entry.tabId === 'general' ? 'Texto de la plantilla' : entry.tabLabel;
                });
            });
        },

        /** Qué archivo se cambia al tocar el reproductor (música) o el video: su rótulo en el panel y su ayuda; null si no es eso. */
        mediaFieldAt(tabId, target) {
            if (tabId === 'musica') {
                return {
                    pattern: /archivo de audio/i,
                    help: 'La canción que suena al abrir la invitación. Sube un MP3, M4A o WAV de hasta 20 MB; si prefieres otra, elígela de nuevo. El título y el artista que se ven se cambian tocándolos.',
                };
            }
            if (tabId === 'video' && target?.closest?.('video, iframe, button, [class*="play"], [class*="player"], [class*="screen"], [class*="reel"]')) {
                return {
                    pattern: /archivo de video/i,
                    help: 'El video que se reproduce al tocarlo (MP4, MOV o WEBM, hasta 100 MB). Mejor corto y en vertical. La miniatura (lo que se ve antes de reproducirlo) se cambia tocándola.',
                };
            }
            return null;
        },

        /** Abre solo el archivo de música o de video de su parte, en la burbuja. */
        openMediaField(tabId, media, tapped = null) {
            this.skipPreviewScroll = tabId !== this.activeTab;
            this.clearFieldFocus();
            this.activeTab = tabId;
            this.syncActiveGroup();
            this.$nextTick(() => {
                const label = [...(this.tabPanelRoot(tabId)?.querySelectorAll('label.admin-label') ?? [])].find(item => media.pattern.test(item.textContent));
                const container = label?.closest('.space-y-2');
                if (!container) {
                    this.openSection(tabId);
                    return;
                }
                this.focusContainer(container, null, label.textContent.trim(), tapped, media.help);
            });
        },

        /** Tocar una fecha u hora de la invitación: la burbuja con el día o la hora del evento, de donde salen todas. */
        openEventField(kind, tapped = null) {
            this.skipPreviewScroll = this.activeTab !== 'general';
            this.clearFieldFocus();
            this.activeTab = 'general';
            this.syncActiveGroup();
            this.$nextTick(() => {
                const input = document.getElementById(kind === 'time' ? 'event-time' : 'event-date');
                const container = input?.closest('.admin-date-field');
                if (!container) {
                    this.openFull('general');
                    return;
                }
                const help = kind === 'time'
                    ? 'La hora de inicio. Se muestra en la invitación y en el botón para agendar. Cambiarla aquí la cambia en todos los lugares donde aparece.'
                    : 'El día del evento. De él salen todas las fechas de la invitación, la cuenta regresiva y el botón para agendar: se cambia una vez, aquí.';
                this.focusContainer(container, input, kind === 'time' ? 'Hora del evento' : 'Día del evento', tapped, help);
                this.copyFocusKey = 'event:' + kind;
                this.fieldGroup = 'Datos del evento';
            });
        },

        /** Una fila por bloque de la parte abierta que tenga algo para escribir o elegir. */
        buildSectionChoices() {
            const root = this.tabPanelRoot(this.activeTab);
            if (!root) return [];

            const controls = 'input:not([type=hidden]):not([type=checkbox]):not([type=radio]), textarea, select';
            const visible = (el) => el.tagName !== 'TEMPLATE' && !el.classList.contains('ed-intro') && !this.hiddenInPanel(el, root);
            let blocks = [...root.children].filter(visible);
            // Paneles que envuelven todo en un solo bloque: se baja un nivel
            for (let depth = 0; depth < 2 && blocks.length === 1 && blocks[0].children.length > 1; depth++) {
                blocks = [...blocks[0].children].filter(visible);
            }
            blocks = blocks.filter(block => block.querySelector(controls));

            const labels = blocks.map(block => this.panelLabelOf(block));
            const repeated = (label) => labels.filter(item => item === label).length > 1;

            const choices = blocks.map((block, index) => {
                const values = [...block.querySelectorAll('input[type=text], input:not([type]), textarea')]
                    .filter(input => !this.hiddenInPanel(input, block))
                    .map(input => String(input.value ?? '').replace(/\s+/g, ' ').trim())
                    .filter(Boolean);
                const image = [...block.querySelectorAll('img[src]')].map(img => img.getAttribute('src')).find(Boolean) ?? null;
                const own = labels[index];
                // Los elementos de una lista (cada momento, cada foto) se reconocen por lo que dicen
                const label = !own || repeated(own) ? (values.slice(0, 2).join(' · ') || own || 'Elemento ' + (index + 1)) : own;
                const summary = label === own ? (values[0] ?? (image ? 'Foto elegida' : '')) : values.slice(2).join(' · ');

                return { id: index, el: block, label, summary: summary.length > 70 ? summary.slice(0, 68) + '…' : summary, image };
            });

            // Los textos propios de esta plantilla en esta parte (la frase sobre el título, los botones…)
            this.templateTexts().filter(entry => entry.tabId === this.activeTab).forEach(entry => {
                const value = this.templateTextValue(entry);
                choices.push({
                    id: choices.length, el: null, copy: entry, label: entry.label, image: null,
                    summary: value.length > 70 ? value.slice(0, 68) + '…' : value,
                });
            });

            return choices;
        },

        openChoice(choice) {
            if (choice.copy) {
                this.openTemplateText(choice.copy);
                return;
            }
            const control = choice.el.querySelector('input[type=text], input:not([type]), textarea');
            this.focusContainer(choice.el, control, choice.label);
        },

        /**
         * Deja a la vista solo un bloque del panel: se marcan el bloque y sus antepasados, y el CSS esconde
         * todo lo demás («.ed-focus-path», admin.css). Abre sus acordeones y pone el cursor en el campo.
         */
        focusContainer(container, control = null, label = '', tapped = null, help = '') {
            this.clearFieldFocus();
            this.activeTab = this.tabOfControl(container);
            this.syncActiveGroup();
            this.panelMode = 'field';
            this.sheetTall = false;
            this.helpOpen = null;
            this.partNotice = '';
            this.focusContainerEl = container;
            this.fieldLabel = label || this.panelLabelOf(container) || (container.querySelector('img') ? 'Foto' : (this.currentTab?.label ?? ''));

            this.fieldHelp = help || this.helpForField(container, this.fieldLabel);

            // Una foto: se llama como el espacio de esta plantilla («Foto dentro del arco») y explica su forma
            const frame = !help && (tapped?.dataset?.edKind === 'image' || (!control && container.querySelector('img')))
                ? this.imageFrameOfTab(this.tabOfControl(container))
                : null;
            if (frame) {
                if (this.tabOfControl(container) === 'hero') this.fieldLabel = frame.label;
                this.fieldHelp = this.imageHelp(frame);
            }

            const stop = document.querySelector('#invitation-form > .flex-1');
            container.classList.add('ed-focus-field');
            // Su nombre ya está arriba («Cambiar · …»): no se repite encima del campo
            const title = this.panelLabelElement(container);
            if (title && title.textContent.replace(/\s+/g, ' ').trim() === this.fieldLabel) {
                (title.closest('.admin-accordion-trigger') ?? title).classList.add('ed-focus-title');
            }
            for (let el = container.parentElement; el; el = el.parentElement) {
                el.classList.add('ed-focus-path');
                if (el === stop) break;
            }
            [container, ...container.querySelectorAll('.admin-accordion')].forEach(el => {
                if (!el.matches('.admin-accordion')) return;
                try {
                    const scope = Alpine.$data(el);
                    if ('open' in scope) scope.open = true;
                } catch (error) {
                    // Acordeón sin estado propio: queda como está
                }
            });

            this.$nextTick(() => {
                document.getElementById('invitation-form')?.scrollTo({ top: 0 });
                if (control && control.offsetParent !== null) {
                    control.focus({ preventScroll: true });
                    // El cursor al final del texto, listo para seguir escribiendo
                    if (typeof control.setSelectionRange === 'function' && /^(text|search|tel|url|email|)$/.test(control.type ?? '')) {
                        const end = control.value.length;
                        control.setSelectionRange(end, end);
                    }
                }
                const marked = this.markPreviewActive();
                this.bubbleAnchor = tapped?.isConnected ? tapped : marked;
                this.placeBubble();
                if (this.bubbleAnchor) this.keepPreviewTargetVisible(this.bubbleAnchor);
            });
        },

        clearFieldFocus() {
            document.querySelectorAll('#invitation-form .ed-focus-field, #invitation-form .ed-focus-path, #invitation-form .ed-focus-title')
                .forEach(el => el.classList.remove('ed-focus-field', 'ed-focus-path', 'ed-focus-title'));
            this.focusContainerEl = null;
            this.copyFocusKey = '';
            this.fieldGroup = '';
            this.fieldLabel = '';
            this.fieldHelp = '';
        },

        // ── La invitación que se toca ──

        /**
         * Dónde se toca cada parte en la invitación. Primero lo que la plantilla marca con data-ed="código"
         * (la música, el botón de agendar, cada confirmación); si no, la sección con el id del módulo
         * (el código con guiones: dress_code → #dress-code; la portada es #inicio).
         * Devuelve [{ el, tab }] para las partes que están en la vista previa.
         */
        previewTargets(doc) {
            const tabs = {};
            this.flatTabs.forEach(tab => { if (tab.moduleCode) tabs[tab.moduleCode] = tab; });

            const targets = [];
            const taken = new Set();

            doc.querySelectorAll('[data-ed]').forEach(el => {
                const tab = tabs[el.dataset.ed];
                if (tab) {
                    targets.push({ el, tab });
                    taken.add(el);
                }
            });

            Object.entries(tabs).forEach(([code, tab]) => {
                const el = doc.getElementById(code === 'bienvenida' ? 'inicio' : code.replaceAll('_', '-'));
                if (el && !taken.has(el) && !el.dataset.ed) targets.push({ el, tab });
            });

            return targets;
        },

        /**
         * Los textos y las fotos de la invitación que salen de un campo del panel. Se reconocen por su
         * valor: un texto de la vista previa igual al de un campo (sin contar espacios, comillas ni
         * mayúsculas) o una foto con el mismo archivo. Así sirve en todas las plantillas sin marcarlas.
         * Lo que no se reconoce (fechas con otro formato, textos de la plantilla) se cambia desde las
         * opciones de su parte.
         */
        collectPreviewFields(doc, fields = []) {
            const form = document.getElementById('invitation-form');
            if (!form) return [];

            const norm = (value) => String(value ?? '')
                .replace(/[ \s]+/g, ' ')
                .replace(/^[\s"“”«»'‘’]+|[\s"“”«»'‘’.,;:!¡?¿]+$/g, '')
                .trim()
                .toLowerCase();
            const fileKey = (url) => {
                try {
                    const last = new URL(url, window.location.href).pathname.split('/').filter(Boolean).pop() ?? '';
                    return last.replace(/\.[a-z0-9]+$/i, '').toLowerCase();
                } catch (error) {
                    return '';
                }
            };

            const byValue = new Map();
            let longest = 0;
            form.querySelectorAll('input:not([type]), input[type=text], input[type=email], input[type=tel], textarea').forEach(control => {
                const value = norm(control.value);
                // Números sueltos (una edad, un día) aparecen en muchos lados: no alcanzan para reconocer
                if (value.length < 2 || (/^\d+$/.test(value) && value.length < 4)) return;
                if (!byValue.has(value)) byValue.set(value, []);
                byValue.get(value).push(control);
                longest = Math.max(longest, value.length);
            });

            const byImage = new Map();
            form.querySelectorAll('img[src]').forEach(img => {
                const key = fileKey(img.getAttribute('src'));
                if (key.length < 4 || img.getAttribute('src').startsWith('blob:')) return;
                if (!byImage.has(key)) byImage.set(key, []);
                byImage.get(key).push(img);
            });

            // Los textos propios de la plantilla (frase sobre el título, botones…): se reconocen por lo que dicen hoy
            const byCopy = new Map();
            this.templateTexts().forEach(entry => {
                const value = norm(this.templateTextValue(entry));
                if (value.length < 2) return;
                if (!byCopy.has(value)) byCopy.set(value, []);
                byCopy.get(value).push(entry);
                longest = Math.max(longest, value.length);
            });
            // «Colección XV · 2027»: un texto de la plantilla con algo más pegado (un año, un nombre)
            const copyWithin = (text) => {
                if (text.length > 60) return null;
                for (const [value, entries] of byCopy) {
                    if (value.length >= 6 && text.length <= value.length + 16
                        && (text.startsWith(value + ' ') || text.endsWith(' ' + value))) return entries;
                }
                return null;
            };

            // La fecha y la hora del evento, escritas como las arme la plantilla («Sábado 16 de enero», «16 · 01 · 2027», «2027»)
            const when = /^(\d{4})-(\d{2})-(\d{2})(?:T(\d{2}:\d{2}))?/.exec(String(this.meta.event_date ?? ''));
            const months = [['enero', 'january'], ['febrero', 'february'], ['marzo', 'march'], ['abril', 'april'], ['mayo', 'may'], ['junio', 'june'],
                ['julio', 'july'], ['agosto', 'august'], ['septiembre', 'september'], ['octubre', 'october'], ['noviembre', 'november'], ['diciembre', 'december']];
            longest = Math.max(longest, 44);
            const dateKind = (text) => {
                if (!when || text.length > 44) return null;
                const [, year, month, day, time] = when;
                if (time && text.includes(time)) return 'time';
                if (text === year) return 'date';
                const hasDay = new RegExp('(^|\\D)0?' + Number(day) + '(\\D|$)').test(text);
                const names = months[Number(month) - 1];
                const hasMonth = names.some(name => text.includes(name) || new RegExp('(^|[^a-z])' + name.slice(0, 3) + '([^a-z]|$)').test(text))
                    || new RegExp('(^|\\D)0?' + Number(month) + '(\\D|$)').test(text.replace(new RegExp('0?' + Number(day)), ''));
                return hasDay && hasMonth ? 'date' : null;
            };
            const registerEvent = (el, kind) => {
                if (el.dataset.edField) return;
                el.dataset.edField = String(fields.length);
                el.dataset.edKind = 'text';
                fields.push({ el, control: null, container: null, kind: 'text', copy: { key: 'event:' + kind, event: kind } });
            };

            const registerCopy = (el, entries) => {
                if (el.dataset.edField) return;
                const sectionTab = el.closest('[data-ed-tab]')?.dataset.edTab;
                const entry = entries.find(item => item.tabId === sectionTab) ?? entries[0];
                el.dataset.edField = String(fields.length);
                el.dataset.edKind = 'text';
                fields.push({ el, control: null, container: null, kind: 'text', copy: entry, shown: this.templateTextValue(entry) });
            };

            const register = (el, candidates, kind) => {
                if (el.dataset.edField) return;
                const sectionTab = el.closest('[data-ed-tab]')?.dataset.edTab;
                const control = candidates.find(item => this.tabOfControl(item) === sectionTab) ?? candidates[0];
                el.dataset.edField = String(fields.length);
                el.dataset.edKind = kind;
                fields.push({ el, control, container: this.fieldContainer(control), kind, shown: kind === 'text' ? String(control.value ?? '') : null });
            };

            const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT);
            while (walker.nextNode()) {
                const node = walker.currentNode;
                if (!node.nodeValue.trim()) continue;
                let el = node.parentElement;
                for (let depth = 0; el && el !== doc.body && depth < 6; depth++, el = el.parentElement) {
                    if (['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEMPLATE', 'TITLE'].includes(el.tagName)) break;
                    if (el.dataset.edField) break;
                    const text = norm(el.textContent);
                    if (text.length > longest + 4) break;
                    const candidates = byValue.get(text);
                    if (candidates) {
                        register(el, candidates, 'text');
                        break;
                    }
                    const copy = byCopy.get(text) ?? (depth === 0 ? copyWithin(text) : null);
                    if (copy) {
                        registerCopy(el, copy);
                        break;
                    }
                    const event = depth === 0 ? dateKind(text) : null;
                    if (event) {
                        registerEvent(el, event);
                        break;
                    }
                }
            }

            if (byImage.size > 0) {
                doc.querySelectorAll('img').forEach(img => {
                    const candidates = byImage.get(fileKey(img.currentSrc || img.getAttribute('src') || ''));
                    if (candidates) register(img, candidates, 'image');
                });
                doc.querySelectorAll('[style*="url("]').forEach(el => {
                    const url = /url\((['"]?)([^'")]+)\1\)/.exec(el.getAttribute('style'))?.[2];
                    const candidates = url ? byImage.get(fileKey(url)) : null;
                    if (candidates) register(el, candidates, 'image');
                });
            }

            return fields;
        },

        /**
         * El campo bajo el dedo. Muchas plantillas ponen capas decorativas encima o dejan los textos sin
         * recibir toques, así que se mira la caja de cada campo: primero los textos (lo más chico que
         * contenga el punto), después las fotos.
         */
        previewFieldAt(doc, event) {
            const fields = this.$refs.previewFrame?._edFields ?? [];
            const direct = event.target.closest?.('[data-ed-field]');
            if (direct?.dataset.edKind === 'text') return fields[Number(direct.dataset.edField)] ?? null;

            const under = (kind) => fields
                .filter(field => field.kind === kind && field.el.isConnected)
                .map(field => ({ field, box: field.el.getBoundingClientRect() }))
                .filter(({ box }) => box.width > 0 && box.height > 0
                    && event.clientX >= box.left - 2 && event.clientX <= box.right + 2
                    && event.clientY >= box.top - 2 && event.clientY <= box.bottom + 2)
                .sort((a, b) => a.box.width * a.box.height - b.box.width * b.box.height)[0]?.field ?? null;

            return under('text') ?? (direct ? fields[Number(direct.dataset.edField)] ?? null : null) ?? under('image');
        },

        /** El botón negro «Cambiar …» de una parte (dibujado con ::before arriba a la izquierda). */
        hitsSectionButton(section, event) {
            const box = section.getBoundingClientRect();
            const place = section.dataset.edPlace;
            if (place === 'above') return false;
            const top = place === 'inside' ? 6 : 10;
            return event.clientY >= box.top + top - 4 && event.clientY <= box.top + top + 44
                && event.clientX >= box.left && event.clientX <= box.left + Math.min(box.width, 280);
        },

        /**
         * «Tocar y cambiar»: cada parte lleva un botón negro «Cambiar …» y cada texto o foto que sale de un
         * campo se marca con un borde punteado. Tocar un texto o una foto abre solo ese campo; tocar el
         * botón o el resto de la parte, sus opciones. Se prepara en cada carga del marco.
         */
        setupPreviewEditing() {
            const doc = this.$refs.previewFrame?.contentDocument;
            if (!doc || !doc.body || doc.body.dataset.edReady) return;
            doc.body.dataset.edReady = '1';
            doc.body.classList.toggle('ed-guest', this.guestView);

            const touch = window.matchMedia('(hover: none)').matches || this.isCompactEditor();
            doc.body.classList.toggle('ed-touch', touch);

            const style = doc.createElement('style');
            style.textContent = [
                '[data-ed-tab]{cursor:pointer}',
                '[data-ed-tab]::before{content:"✎  " attr(data-ed-action);position:absolute;z-index:2147483000;top:10px;left:10px;right:auto;bottom:auto;display:flex;align-items:center;min-height:40px;max-width:calc(100% - 20px);padding:0 16px 0 14px;border-radius:22px;background:#1d1e20;color:#fff;font:600 14px/1 system-ui,-apple-system,sans-serif;letter-spacing:0;text-transform:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;box-shadow:0 6px 16px -6px rgba(0,0,0,.55);pointer-events:none;transform:none;opacity:1}',
                // Lo que flota (la música): un botón chico encima, a la derecha, para no tapar la invitación
                '[data-ed-place="above"]::before{top:auto;bottom:calc(100% + 6px);left:auto;right:0;min-height:32px;padding:0 12px;font-size:12px;content:"✎  " attr(data-ed-label)}',
                '[data-ed-place="inside"]::before{top:6px;left:6px}',
                '.ed-hover{outline:2px dashed rgba(138,106,28,.75)!important;outline-offset:-3px}',
                '.ed-active{outline:2px solid rgba(138,106,28,.55)!important;outline-offset:-3px}',
                '.ed-active::before{content:"Cambiando · " attr(data-ed-label);background:#8a6a1c}',
                // Cada texto o foto que se puede cambiar: punteado en el celular; con el cursor encima en la computadora
                '[data-ed-field]{cursor:text;border-radius:4px}',
                '[data-ed-kind="image"]{cursor:pointer}',
                '.ed-touch [data-ed-field]{outline:1.5px dashed rgba(138,106,28,.6);outline-offset:3px}',
                '[data-ed-field]:hover{outline:2px dashed #8a6a1c!important;outline-offset:3px;background-color:rgba(138,106,28,.08)}',
                '.ed-field-active{outline:3px solid #8a6a1c!important;outline-offset:3px;background-color:rgba(138,106,28,.12)}',
                // Las fotos suelen ir dentro de marcos que recortan: su borde va por dentro
                '.ed-touch [data-ed-kind="image"],[data-ed-kind="image"]:hover,[data-ed-kind="image"].ed-field-active{outline-offset:-4px!important}',
                '[data-ed-kind="image"].ed-field-active{outline-width:4px!important}',
                '.ed-guest [data-ed-tab],.ed-guest [data-ed-field]{cursor:auto;outline:none!important;background-color:transparent!important}',
                '.ed-guest [data-ed-tab]::before{display:none}',
                // Una parte recién encendida se resalta un momento
                '@keyframes ed-flash{0%,60%{box-shadow:inset 0 0 0 4px #8a6a1c,0 0 0 9999px rgba(138,106,28,.08)}100%{box-shadow:inset 0 0 0 4px transparent}}',
                '.ed-flash{animation:ed-flash 1.8s ease-out}',
            ].join('');
            doc.head.appendChild(style);

            const view = doc.defaultView;
            this.previewTargets(doc).forEach(({ el, tab }) => {
                el.dataset.edTab = tab.id;
                el.dataset.edLabel = tab.label;
                el.dataset.edAction = 'Cambiar ' + tab.label.charAt(0).toLowerCase() + tab.label.slice(1);
                const position = view.getComputedStyle(el).position;
                // Lo que flota (la música) lleva el botón encima; lo chico, adentro y más ceñido
                if (position === 'fixed' || position === 'sticky') {
                    el.dataset.edPlace = 'above';
                } else {
                    this.ensurePositioned(el);
                    if (el.offsetHeight < 90) el.dataset.edPlace = 'inside';
                }
            });

            // Los textos y las fotos se buscan cuando la plantilla ya armó todo (algunas lo hacen al cargar)
            this.$refs.previewFrame._edFields = this.collectPreviewFields(doc);
            setTimeout(() => {
                if (this.$refs.previewFrame?.contentDocument !== doc) return;
                this.collectPreviewFields(doc, this.$refs.previewFrame._edFields);
                this.markPreviewActive();
            }, 900);

            const find = (node) => {
                for (let el = node?.nodeType === 1 ? node : node?.parentElement; el && el !== doc.body; el = el.parentElement) {
                    if (el.dataset.edTab) return el;
                }
                return null;
            };

            let hovered = null;
            const unhover = () => { hovered?.classList.remove('ed-hover'); hovered = null; };

            doc.addEventListener('mouseover', (event) => {
                if (this.guestView) return;
                const el = find(event.target);
                if (el === hovered) return;
                unhover();
                if (el && !el.classList.contains('ed-active')) {
                    el.classList.add('ed-hover');
                    hovered = el;
                }
            });
            doc.addEventListener('mouseleave', unhover);

            // La burbuja acompaña a lo tocado mientras se desplaza la invitación
            let placing = false;
            view.addEventListener('scroll', () => {
                if (placing || !this.isBubble) return;
                placing = true;
                requestAnimationFrame(() => {
                    placing = false;
                    this.placeBubble();
                });
            }, { passive: true });

            // Al tocar, la invitación no reacciona (no se abren hojas, no se envían formularios, no se pasa
            // de escena): se abre lo que se tocó. Con «Ver como invitado» responde como siempre.
            doc.addEventListener('click', (event) => {
                if (this.guestView) return;
                const section = find(event.target);
                const field = this.previewFieldAt(doc, event);
                if (!section && !field) return;

                event.preventDefault();
                event.stopImmediatePropagation();
                unhover();

                // El botón de reproducir (música o video) abre su archivo, aunque haya una miniatura debajo
                const tab = section?.dataset.edTab;
                const player = (tab === 'musica' || tab === 'video') && event.target.closest?.('button, [class*="toggle"], [class*="play"]')
                    && !this.hitsSectionButton(section, event)
                    ? this.mediaFieldAt(tab, event.target)
                    : null;
                if (player) {
                    this.openMediaField(tab, player, event.target);
                    return;
                }

                if (field && !(section && this.hitsSectionButton(section, event))) {
                    if (field.copy?.event) {
                        this.openEventField(field.copy.event, field.el);
                        return;
                    }
                    if (field.copy) {
                        this.openTemplateText(field.copy, field.el);
                        return;
                    }
                    // Se tocó dentro de la invitación: no hace falta desplazarla
                    this.skipPreviewScroll = this.tabOfControl(field.control) !== this.activeTab;
                    this.focusContainer(field.container, field.kind === 'text' ? field.control : null, '', field.el);
                    return;
                }

                // El reproductor de música y el video abren directo su archivo, no la lista de opciones
                if (!this.hitsSectionButton(section, event)) {
                    const media = this.mediaFieldAt(section.dataset.edTab, event.target);
                    if (media) {
                        this.openMediaField(section.dataset.edTab, media, event.target);
                        return;
                    }
                }

                this.skipPreviewScroll = section.dataset.edTab !== this.activeTab;
                this.openSection(section.dataset.edTab);
            }, true);
        },

        /**
         * Marca en la invitación lo que se está cambiando: la parte abierta y, si es un campo, ese texto
         * o esa foto (todas sus apariciones). Devuelve lo marcado más específico.
         */
        markPreviewActive() {
            const doc = this.$refs.previewFrame?.contentDocument;
            if (!doc?.body) return null;

            doc.querySelectorAll('.ed-active, .ed-field-active').forEach(el => el.classList.remove('ed-active', 'ed-field-active'));
            if (!this.isEditingPart) return null;

            const section = doc.querySelector('[data-ed-tab="' + this.activeTab + '"]');
            section?.classList.remove('ed-hover');
            section?.classList.add('ed-active');

            if (this.panelMode !== 'field' || !this.focusContainerEl) return section;

            const container = this.focusContainerEl;
            const hits = (this.$refs.previewFrame._edFields ?? [])
                .filter(field => field.el.isConnected && (field.copy
                    ? field.copy.key === this.copyFocusKey
                    : (field.container === container || container.contains(field.control))))
                .map(field => field.el);
            hits.forEach(el => el.classList.add('ed-field-active'));

            return hits.find(el => el.getBoundingClientRect().height > 0) ?? section;
        },

        /** Que lo tocado no quede debajo de la hoja de edición (celular) ni fuera del marco. */
        keepPreviewTargetVisible(el) {
            setTimeout(() => {
                const frame = this.$refs.previewFrame;
                const view = frame?.contentWindow;
                if (!view || !el.isConnected) return;

                const frameBox = frame.getBoundingClientRect();
                const panelBox = document.querySelector('.ed-panel')?.getBoundingClientRect();
                const covered = this.isCompactEditor() && panelBox ? Math.max(0, frameBox.bottom - panelBox.top) : 0;
                const visible = frameBox.height - covered;
                const box = el.getBoundingClientRect();

                if (box.top >= 8 && box.bottom <= visible - 8) return;
                const target = box.top - Math.max(16, (visible - Math.min(box.height, visible)) / 2);
                view.scrollBy({ top: target, behavior: 'smooth' });
            }, 320);
        },

        /** Tras cada recarga la invitación vuelve a donde estaba (la primera vez, a la parte abierta). */
        restorePreviewScroll() {
            const view = this.$refs.previewFrame?.contentWindow;
            if (!view) return;
            if (this.previewScrollY === null) {
                this.focusPreviewSection(false);
                return;
            }
            view.scrollTo({ top: this.previewScrollY, behavior: 'auto' });
            const marked = this.markPreviewActive();
            if (this.isBubble) {
                this.bubbleAnchor = this.panelMode === 'section' ? this.previewSectionElement(this.activeTab) : marked;
                this.placeBubble();
            }
        },

        ensurePositioned(el) {
            const view = el.ownerDocument.defaultView;
            if (view.getComputedStyle(el).position === 'static') el.style.position = 'relative';
        },

        /**
         * Lleva la vista previa a la parte que se abrió y la deja marcada mientras se cambia. Las partes
         * sin lugar propio (datos del evento, colores) llevan al principio.
         */
        focusPreviewSection(smooth = true) {
            const doc = this.$refs.previewFrame?.contentDocument;
            if (!doc || !doc.body) return;

            const marked = this.markPreviewActive();
            if (!this.isEditingPart) return;

            // Solo se desplaza la invitación dentro del marco: scrollIntoView movería también el editor
            // y el campo donde se está escribiendo se iría de la pantalla
            const view = doc.defaultView;
            if (this.skipPreviewScroll) {
                this.skipPreviewScroll = false;
                return;
            }
            const target = marked ?? (this.currentTab?.moduleCode ? null : doc.getElementById('inicio'));
            if (!target) return;
            view.scrollTo({ top: Math.max(0, target.getBoundingClientRect().top + view.scrollY - 12), behavior: smooth ? 'smooth' : 'auto' });
        },

        /**
         * Pide actualizar la vista previa cuando se deja de escribir. Un texto ya se vio al instante (setLiveText), así
         * que la recarga completa, que acomoda todo lo demás, espera un poco más para no estorbar mientras se escribe.
         */
        schedulePreview() {
            // Mientras se suben los archivos al guardar la vista previa no se recarga: cada archivo
            // subido cambia la invitación y recargaba la vista previa una y otra vez
            if (this.mediaUploading) return;
            clearTimeout(this.previewTimer);
            this.previewLoading = true;
            const justPatched = Date.now() - this.livePatchedAt < 60;
            this.previewTimer = setTimeout(() => this.runPreviewUpdate(), justPatched ? this.previewTypingMs : this.previewDebounceMs);
        },


        /**
         * Manda lo que se está armando al servidor y la invitación ya dibujada aparece en el marco: una sola
         * petición (formulario hacia el marco), sin guardar nada en la sesión.
         */
        submitPreview(payload, seq) {
            const frame = this.$refs.previewFrame;
            const form = this.$refs.previewForm;
            const field = this.$refs.previewPayload;
            if (!frame || !form || !field) return;

            // Al recargar se vuelve a la misma altura: tipear no debe mover la invitación
            try {
                if (frame.contentWindow?.document?.body?.dataset.edReady) this.previewScrollY = frame.contentWindow.scrollY;
            } catch (error) {
                this.previewScrollY = null;
            }

            field.value = JSON.stringify(payload);
            this.frameSeq = seq;
            this.previewError = '';
            form.submit();
        },

        // ── En vivo: lo que se cambia aparece en la invitación al instante ──
        // Dibujarla de nuevo en el servidor toma casi un segundo; mientras tanto el cambio ya se ve aquí, y
        // la invitación completa llega después y acomoda todo lo demás (fechas, partes que dependen de otras…).

        /** Una letra escrita en cualquier campo del panel: se cambia ese texto en la invitación, donde aparezca. */
        onFieldInput(event) {
            const control = event.target;
            if (!(control instanceof HTMLInputElement || control instanceof HTMLTextAreaElement)) return;
            if (!/^(text|search|tel|email|url|)$/.test(control.type ?? '')) return;

            const fields = this.$refs.previewFrame?._edFields;
            if (!fields?.length) return;

            let hits = [];
            if (control.id?.startsWith('texto-')) {
                // Un texto de la plantilla: si se vacía, vuelve a decir el de la plantilla
                const key = control.id.slice('texto-'.length);
                const entry = this.templateTexts().find(item => item.key === key);
                const value = control.value.trim() || String(entry?.default ?? '');
                hits = fields.filter(field => field.copy?.key === key).map(field => ({ field, value }));
            } else if (control.value.trim() !== '') {
                hits = fields.filter(field => field.kind === 'text' && field.control === control).map(field => ({ field, value: control.value }));
            }

            if (hits.map(({ field, value }) => this.setLiveText(field, value)).some(Boolean)) {
                this.livePatchedAt = Date.now();
            }
        },

        /** Cambia el texto de un elemento de la invitación sin tocar lo demás (formato, animaciones, orden). */
        setLiveText(field, value) {
            const el = field.el;
            if (!el?.isConnected) return false;

            const previous = String(field.shown ?? '');
            const squash = (text) => String(text).replace(/\s+/g, ' ').trim().toLowerCase();
            let node = null;

            if (previous !== '') {
                const walker = el.ownerDocument.createTreeWalker(el, NodeFilter.SHOW_TEXT);
                while (walker.nextNode()) {
                    if (walker.currentNode.nodeValue.toLowerCase().includes(previous.toLowerCase())) {
                        node = walker.currentNode;
                        break;
                    }
                }
            }

            if (node) {
                // Solo cambia lo que era de este campo («Colección XV · 2027» conserva el año)
                const at = node.nodeValue.toLowerCase().indexOf(previous.toLowerCase());
                node.nodeValue = node.nodeValue.slice(0, at) + value + node.nodeValue.slice(at + previous.length);
            } else if (el.childElementCount === 0 || squash(el.textContent) === squash(previous)) {
                // Un título armado letra por letra: se reemplaza entero (la recarga completa devuelve su animación)
                el.textContent = value;
            } else {
                return false;
            }

            field.shown = value;
            return true;
        },

        /** Una foto cambiada (elegida o recortada): se cambia en la invitación donde se muestre, como foto o como fondo. */
        patchPreviewImage(fromUrl, toUrl) {
            const doc = this.$refs.previewFrame?.contentDocument;
            if (!doc?.body || !fromUrl || !toUrl) return false;

            const key = (url) => {
                try {
                    return (new URL(String(url), window.location.href).pathname.split('/').filter(Boolean).pop() ?? '').replace(/\.[a-z0-9]+$/i, '').toLowerCase();
                } catch (error) {
                    return '';
                }
            };
            const from = key(fromUrl);
            if (from === '') return false;

            let changed = 0;
            doc.querySelectorAll('img').forEach(img => {
                if (key(img.currentSrc || img.getAttribute('src') || '') !== from) return;
                img.removeAttribute('srcset');
                img.src = toUrl;
                changed++;
            });
            doc.querySelectorAll('[style*="url("]').forEach(el => {
                const found = /url\((['"]?)([^'")]+)\1\)/.exec(el.getAttribute('style') ?? '');
                if (!found || key(found[2]) !== from) return;
                el.style.backgroundImage = `url("${toUrl}")`;
                changed++;
            });

            if (changed > 0) this.livePatchedAt = Date.now();
            return changed > 0;
        },

        /** Los colores de «Colores y letras» se ven al mover cada uno (la invitación los lee de variables de CSS). */
        setLiveColors() {
            const root = this.$refs.previewFrame?.contentDocument?.documentElement;
            if (!root) return;
            const colors = this.modules.config?.colores ?? {};
            const variables = { primary: '--primary-color', secondary: '--secondary-color', accent: '--accent-color', text: '--text-color', background: '--bg-color' };
            Object.entries(variables).forEach(([name, variable]) => {
                if (colors[name]) root.style.setProperty(variable, colors[name]);
            });
            this.livePatchedAt = Date.now();
        },

        /** Las letras también: se pide la tipografía a Google Fonts y se aplica sin recargar. */
        setLiveFonts() {
            const doc = this.$refs.previewFrame?.contentDocument;
            if (!doc?.documentElement) return;
            const fonts = this.modules.config?.tipografias ?? {};
            const variables = { titulos: ['--font-titles', 'serif'], cuerpo: ['--font-body', 'sans-serif'], script: ['--font-script', 'cursive'] };
            Object.entries(variables).forEach(([name, [variable, fallback]]) => {
                const family = String(fonts[name] ?? '').trim();
                if (family === '') return;
                this.ensureLiveFont(doc, family);
                doc.documentElement.style.setProperty(variable, `'${family}', ${fallback}`);
            });
            this.livePatchedAt = Date.now();
        },

        ensureLiveFont(doc, family) {
            const id = 'ed-font-' + family.replace(/\W+/g, '-').toLowerCase();
            if (doc.getElementById(id)) return;
            const name = encodeURIComponent(family).replace(/%20/g, '+');
            const link = doc.createElement('link');
            link.id = id;
            link.rel = 'stylesheet';
            link.href = `https://fonts.googleapis.com/css2?family=${name}:wght@300;400;500;600;700&display=swap`;
            // Una letra que no trae todos los pesos: se pide solo la normal
            link.onerror = () => { link.onerror = null; link.href = `https://fonts.googleapis.com/css2?family=${name}&display=swap`; };
            doc.head.appendChild(link);
        },

        /** Al terminar de cargar el marco: se apaga «Actualizando…» y, si el servidor no pudo dibujarla, se dice. */
        onPreviewLoaded() {
            const body = this.$refs.previewFrame?.contentDocument?.body;
            if (this.frameSeq === this.previewSeq) this.previewLoading = false;
            if (body && !body.dataset.edReady && /^Error al renderizar/.test((body.textContent ?? '').trim())) {
                this.previewError = 'La vista previa no se pudo dibujar: revisa el nombre de la portada y las fechas.';
            } else if (body && !body.dataset.edReady && (body.querySelector('form[action$="/login"]') || /Page Expired|CSRF token mismatch/i.test(body.textContent ?? ''))) {
                // La sesión se venció: la vista previa mostraba «Ingresar» o «Page Expired» sin explicar nada
                this.previewError = 'Tu sesión se venció. Guarda lo que tengas y vuelve a entrar para seguir viendo los cambios.';
            }
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
                locale: this.meta.locale ?? 'es',
                event_date: this.meta.event_date ?? '',
                expires_at: this.meta.expires_at ?? '',
                package: this.meta.package ?? '',
                modulos,
            };
        },

        async runPreviewUpdate() {
            const seq = ++this.previewSeq;
            this.previewLoading = true;

            try {
                const payload = await this.buildPreviewState();
                // Si mientras tanto hubo otro cambio, ese es el que se dibuja
                if (seq !== this.previewSeq) return;
                this.submitPreview(payload, seq);
            } catch (error) {
                console.error('Preview error:', error);
                this.previewLoading = false;
                this.previewError = 'No se pudo actualizar la vista previa. Sigue editando: se reintenta con el próximo cambio.';
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
            this.keepLinkAliveUntilEvent();
        },

        /** ¿La fecha del evento ya pasó? Se avisa (al crear y al publicar), no se impide: puede ser un evento de ayer. */
        get eventInPast() {
            const date = new Date(String(this.meta.event_date ?? ''));
            return !Number.isNaN(date.getTime()) && date.getTime() < Date.now();
        },

        /** ¿El enlace deja de funcionar antes de que llegue el evento? Los invitados verían «vencida» su invitación. */
        get linkExpiresBeforeEvent() {
            const day = String(this.meta.event_date ?? '').slice(0, 10);
            return day !== '' && String(this.meta.expires_at ?? '') !== '' && String(this.meta.expires_at) < day;
        },

        /** Con una fecha lejana (o cambiada) el enlace sigue vivo hasta tres meses después del evento. */
        keepLinkAliveUntilEvent() {
            if (!this.linkExpiresBeforeEvent && this.meta.expires_at) return;
            const day = String(this.meta.event_date ?? '').slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(day)) return;
            const until = new Date(day + 'T12:00:00');
            until.setMonth(until.getMonth() + 3);
            this.meta.expires_at = [until.getFullYear(), String(until.getMonth() + 1).padStart(2, '0'), String(until.getDate()).padStart(2, '0')].join('-');
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

        /** Hay algo que elegir en ese producto (plantillas apagadas o fuera de temporada no cuentan). */
        kindAvailable(kind) {
            return this.templatesOfKind(kind).some(option => !option.disabledReason);
        },

        chooseKind(kind) {
            if ((this.profile.kind ?? 'invitation') === kind) return;
            const first = this.templatesOfKind(kind).find(option => !option.disabledReason);
            if (first) this.meta.template = first.value;
            // Las tarjetas no se venden por paquete: tienen su precio de temporada
            if (kind === 'card') this.meta.package = '';
            else if (!this.meta.package && this.config.editorMode === 'admin') this.meta.package = 'estandar';
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

        /**
         * Los tipos de evento del producto agrupados como en Ajustes: los de todo el año y cada
         * temporada con su ánimo (primaveral y romántico, tenebroso). Un grupo vacío no se muestra.
         */
        eventTypeGroups(kind) {
            const types = this.eventTypesOfKind(kind);
            return (this.config.eventCategories ?? [])
                .map(name => ({ name, types: types.filter(type => type.category === name) }))
                .filter(group => group.types.length);
        },

        /** Motivo por el que un tipo no se puede elegir: su temporada o todas sus plantillas apagadas. */
        eventTypeDisabledReason(type) {
            if (type.disabledReason) return type.disabledReason;
            const options = this.templateOptions.filter(option => option.event === type.code);
            return options.length && options.every(option => option.disabledReason) ? options[0].disabledReason : null;
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
            if (this.eventTypeDisabledReason(type) && String(this.meta.event_type_id) !== String(type.id)) return;
            this.meta.event_type_id = String(type.id);
            const current = this.templateOptions.find(option => option.value === String(this.meta.template ?? ''));

            if (type.code && current?.event !== type.code) {
                const first = this.templateOptions.find(option => option.event === type.code && !option.disabledReason);
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
            const previous = getUrl();
            this.clearMediaUrl(previous);
            this.pickLocalFile(event, type, context, setUrl, previous);
        },

        /**
         * ¿Se puede usar este archivo? Revisa el tipo y el peso al elegirlo y, si no, explica por qué y qué
         * hacer. Así nadie descubre al guardar que una canción de 30 MB no entraba.
         */
        acceptMediaFile(file, type) {
            const rule = MEDIA_RULES[type];
            if (!rule || !file) return true;

            const extension = String(file.name ?? '').split('.').pop().toLowerCase();
            const typeOk = file.type ? file.type.startsWith(type + '/') || file.type === 'application/ogg' || file.type === 'application/octet-stream' || (type === 'audio' && file.type === 'video/mp4') : true;
            if (!rule.extensions.includes(extension) || !typeOk) {
                alert(`«${file.name}» no se puede usar: elige ${rule.what}.${rule.tip ? ' ' + rule.tip : ''}`);
                return false;
            }

            const mb = file.size / 1048576;
            if (mb > rule.maxMb) {
                alert(`«${file.name}» pesa ${mb.toFixed(1).replace('.0', '')} MB y el máximo es ${rule.maxMb} MB.${rule.tip ? ' ' + rule.tip : ''}`);
                return false;
            }

            return true;
        },

        pickLocalFile(event, type, context, setUrl, previous = null) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file || !this.acceptMediaFile(file, type)) return;
            const blobUrl = URL.createObjectURL(file);
            this.pendingUploads.push({
                file,
                type,
                context,
                blobUrl,
                apply: (url) => setUrl(url),
            });
            setUrl(blobUrl);
            // La foto nueva se ve en la invitación al instante (si ya había otra, se cambia en su lugar)
            if (previous) this.patchPreviewImage(previous, blobUrl);
            this.schedulePreview();

            // Una foto para un espacio fijo se encuadra apenas se elige (el QR del banco se deja tal cual)
            if (type === 'image' && context !== 'qr-banco') {
                this.$nextTick(() => this.openImageCropper(blobUrl, context));
            }
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

            let res;
            try {
                res = await fetch(this.mediaUploadUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: fd,
                });
            } catch (error) {
                throw new Error(`No se pudo enviar «${file.name}»: se cortó la conexión. Revisa tu internet y vuelve a intentar; lo que cambiaste sigue aquí.`);
            }

            // Un error del servidor puede venir como página (no como JSON): se explica igual
            let data = null;
            try {
                data = await res.json();
            } catch (error) {
                data = null;
            }

            if (res.status === 413) throw new Error(`«${file.name}» pesa demasiado para subirlo. Elige uno más liviano.`);
            if (res.status === 429) throw new Error('Se subieron muchos archivos de golpe. Espera un minuto y vuelve a guardar: lo que ya se subió no se repite.');
            if (res.status === 419) throw new Error('Tu sesión se venció. Abre el editor de nuevo y vuelve a intentar.');
            if (!res.ok || !data?.success) {
                throw new Error(data?.errors?.file?.[0] || data?.message || `No se pudo subir «${file.name}» (error ${res.status}). Vuelve a intentar.`);
            }
            return data.url;
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

        /**
         * Marco real del espacio donde va la foto (App\Support\ImageFrames): la portada depende de la
         * plantilla elegida y el resto del módulo. {width, height, shape, label}.
         */
        imageFrame(context) {
            const frames = this.config.imageFrames ?? {};
            if (context === 'hero') {
                return frames.hero?.[String(this.meta.template ?? '')] ?? { width: 1080, height: 1350, shape: 'rect', label: 'Foto de portada' };
            }
            return frames.contexts?.[context] ?? { width: 1200, height: 1200, shape: 'rect', label: 'Foto' };
        },

        /** Proporción en palabras, para que se entienda sin saber qué es 4:5. */
        frameShapeLabel(frame) {
            const ratio = frame.width / frame.height;
            const orientation = Math.abs(ratio - 1) < 0.02 ? 'cuadrada' : (ratio < 1 ? 'vertical' : 'horizontal');
            const known = [[9, 16], [3, 4], [4, 5], [1, 1], [4, 3], [16, 10], [16, 9], [10, 11]]
                .find(([w, h]) => Math.abs(w / h - ratio) < 0.01);
            return known ? `${orientation} ${known[0]}:${known[1]}` : orientation;
        },

        openImageCropperFromGallery(index) {
            const photo = this.modules.galeria.fotos[index];
            this.openImageCropper(this.photoUrl(photo), 'gallery', this.replaceInListApplier(() => this.modules.galeria.fotos, this.photoUrl(photo)));
        },

        /**
         * Abre el recortador. Una foto recién elegida ya está esperando en pendingUploads; una que ya
         * estaba guardada se descarga para poder recortarla y, si se aplica el recorte, se vuelve a
         * subir al guardar (setUrl dice dónde va la foto nueva).
         */
        async openImageCropper(url, contextOverride = null, setUrl = null) {
            if (!url || this.cropperLoading) return;

            let entry = this.pendingUploads.find(p => p.blobUrl === url) ?? null;

            if (!entry) {
                if (typeof setUrl !== 'function') return;
                this.cropperLoading = true;
                try {
                    const response = await fetch(url, { mode: 'cors', credentials: 'omit' });
                    if (!response.ok) throw new Error(String(response.status));
                    const blob = await response.blob();
                    if (!blob.type.startsWith('image/')) throw new Error('No es una imagen');
                    const file = new File([blob], 'foto.' + (blob.type.split('/')[1] || 'jpg'), { type: blob.type });
                    entry = { file, type: 'image', context: contextOverride || 'general', blobUrl: URL.createObjectURL(file), apply: setUrl, isDraft: true, shownUrl: url };
                } catch (error) {
                    alert('No se pudo abrir esta foto para recortarla. Súbela de nuevo desde tu equipo y podrás encuadrarla.');
                    return;
                } finally {
                    this.cropperLoading = false;
                }
            }

            const frame = this.imageFrame(contextOverride || entry.context);
            this.cropperEntry = entry;
            this.cropperBlobUrl = entry.blobUrl;
            this.cropperFrame = frame;
            this.cropperAspect = frame.width / frame.height;
            this.cropperScale = this.cropperMinScale;
            this.cropperOffsetX = 0;
            this.cropperOffsetY = 0;
            this.cropperImageNaturalWidth = 0;
            this.cropperImageNaturalHeight = 0;
            this.cropperDragging = false;
            this.cropperPinch = null;
            this.cropperOpen = true;
            this.$nextTick(() => this.syncCropperFrameSize());
        },

        closeImageCropper() {
            // Una foto guardada que se abrió y no se recortó: su copia temporal no se usa
            if (this.cropperEntry?.isDraft) URL.revokeObjectURL(this.cropperEntry.blobUrl);
            this.cropperEntry = null;
            this.cropperOpen = false;
            this.cropperDragging = false;
            this.cropperPinch = null;
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

        /**
         * Mide el espacio disponible y dibuja el marco del recorte. Si el recortador todavía no tiene
         * tamaño (se acaba de abrir y su animación no terminó, o la foto cargó antes de mostrarse) espera
         * al siguiente cuadro y vuelve a medir: con el marco en 0 × 0 la foto se veía enorme y sin forma.
         */
        syncCropperFrameSize(attempt = 0) {
            const stage = this.$refs.cropperStage;
            if (!stage || !stage.clientWidth || !stage.clientHeight) {
                if (this.cropperOpen && attempt < 60) requestAnimationFrame(() => this.syncCropperFrameSize(attempt + 1));
                return;
            }
            const aspect = this.cropperAspect || 1;
            let width = stage.clientWidth * 0.8;
            let height = width / aspect;
            if (height > stage.clientHeight * 0.84) {
                height = stage.clientHeight * 0.84;
                width = height * aspect;
            }
            this.cropperFrameWidth = Math.round(width);
            this.cropperFrameHeight = Math.round(height);
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

            // Al 100 % la foto cubre el marco justo (como object-fit: cover en la invitación)
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
                return 'opacity:0';
            }
            const left = (this.cropperFrameWidth / 2) - (m.renderW / 2) + this.cropperOffsetX;
            const top = (this.cropperFrameHeight / 2) - (m.renderH / 2) + this.cropperOffsetY;
            return `width:${m.renderW}px;height:${m.renderH}px;left:${left}px;top:${top}px;`;
        },

        /** Parte de la foto original que entra en el marco, en píxeles reales. */
        cropperSourceRect() {
            const m = this.getCropperRenderMetrics();
            const iw = this.cropperImageNaturalWidth;
            const ih = this.cropperImageNaturalHeight;
            if (!m.renderW || !iw) return null;
            const left = (this.cropperFrameWidth / 2) - (m.renderW / 2) + this.cropperOffsetX;
            const top = (this.cropperFrameHeight / 2) - (m.renderH / 2) + this.cropperOffsetY;
            const scale = iw / m.renderW;
            const x = Math.max(0, -left * scale);
            const y = Math.max(0, -top * scale);
            return {
                x,
                y,
                width: Math.min(iw - x, this.cropperFrameWidth * scale),
                height: Math.min(ih - y, this.cropperFrameHeight * (ih / m.renderH)),
            };
        },

        /** Tamaño con el que se guarda: el del espacio, o menos si la foto no alcanza (nunca se estira). */
        cropperOutputSize() {
            const frame = this.cropperFrame;
            const source = this.cropperSourceRect();
            if (!frame || !source) return null;
            const factor = Math.min(1, source.width / frame.width);
            return { width: Math.round(frame.width * factor), height: Math.round(frame.height * factor) };
        },

        /** Si la parte elegida alcanza para verse nítida en ese espacio. */
        cropperQuality() {
            const frame = this.cropperFrame;
            const source = this.cropperSourceRect();
            if (!frame || !source) return null;
            const ratio = source.width / frame.width;
            if (ratio >= 0.85) return { level: 'good', text: 'Se verá nítida' };
            if (ratio >= 0.5) return { level: 'fair', text: 'Se verá bien en el celular' };
            return { level: 'low', text: 'La foto es chica para este espacio: puede verse borrosa. Aléjala o usa una más grande.' };
        },

        startCropDrag(event) {
            if (event.touches?.length === 2) {
                this.cropperDragging = false;
                this.cropperPinch = { distance: this.touchDistance(event.touches), scale: this.cropperScale };
                return;
            }
            const point = event.touches ? event.touches[0] : event;
            this.cropperDragging = true;
            this.cropperLastX = point.clientX;
            this.cropperLastY = point.clientY;
        },

        onCropDrag(event) {
            // Dos dedos: pellizcar para acercar o alejar, como en la galería del teléfono
            if (this.cropperPinch && event.touches?.length === 2) {
                const ratio = this.touchDistance(event.touches) / (this.cropperPinch.distance || 1);
                this.cropperScale = Math.max(this.cropperMinScale, Math.min(this.cropperMaxScale, this.cropperPinch.scale * ratio));
                return;
            }
            if (!this.cropperDragging) return;
            const point = event.touches ? (event.touches[0] || event.changedTouches?.[0]) : event;
            if (!point) return;
            this.cropperOffsetX += point.clientX - this.cropperLastX;
            this.cropperOffsetY += point.clientY - this.cropperLastY;
            this.clampCropperOffsets();
            this.cropperLastX = point.clientX;
            this.cropperLastY = point.clientY;
        },

        endCropDrag() {
            this.cropperDragging = false;
            this.cropperPinch = null;
        },

        touchDistance(touches) {
            return Math.hypot(touches[0].clientX - touches[1].clientX, touches[0].clientY - touches[1].clientY);
        },

        onCropWheel(event) {
            const delta = event.deltaY > 0 ? -0.08 : 0.08;
            this.cropperScale = Math.max(this.cropperMinScale, Math.min(this.cropperMaxScale, this.cropperScale + delta));
        },

        /** Flechas para mover de a poco y + / − para acercar, sin mouse. */
        onCropKey(event) {
            const step = event.shiftKey ? 20 : 6;
            const moves = { ArrowLeft: [step, 0], ArrowRight: [-step, 0], ArrowUp: [0, step], ArrowDown: [0, -step] };
            if (moves[event.key]) {
                event.preventDefault();
                this.cropperOffsetX += moves[event.key][0];
                this.cropperOffsetY += moves[event.key][1];
                this.clampCropperOffsets();
            } else if (event.key === '+' || event.key === '=') {
                this.cropperScale = Math.min(this.cropperMaxScale, this.cropperScale + 0.1);
            } else if (event.key === '-') {
                this.cropperScale = Math.max(this.cropperMinScale, this.cropperScale - 0.1);
            }
        },

        async applyImageCrop() {
            const entry = this.cropperEntry;
            try {
                if (!entry || !entry.file || !this.cropperBlobUrl) {
                    this.closeImageCropper();
                    return;
                }

                const image = new Image();
                const loaded = new Promise((resolve, reject) => {
                    image.onload = resolve;
                    image.onerror = reject;
                });
                image.src = this.cropperBlobUrl;
                await loaded;

                this.clampCropperOffsets();
                const source = this.cropperSourceRect();
                const output = this.cropperOutputSize();
                if (!source || !output) {
                    this.closeImageCropper();
                    return;
                }

                const canvas = document.createElement('canvas');
                canvas.width = output.width;
                canvas.height = output.height;
                const ctx = canvas.getContext('2d');
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(image, source.x, source.y, source.width, source.height, 0, 0, output.width, output.height);

                // PNG y WebP conservan su formato (un QR o un logo con fondo transparente); el resto va en JPG
                const mimeType = ['image/png', 'image/webp'].includes(entry.file.type) ? entry.file.type : 'image/jpeg';
                const blob = await new Promise((resolve) => canvas.toBlob(resolve, mimeType, 0.9));
                if (!blob) {
                    this.closeImageCropper();
                    return;
                }

                const extension = mimeType.split('/')[1].replace('jpeg', 'jpg');
                const baseName = (entry.file.name || 'foto').replace(/\.[^.]+$/, '');
                const newFile = new File([blob], `${baseName}.${extension}`, { type: blob.type });
                const newBlobUrl = URL.createObjectURL(newFile);

                // Lo que la invitación muestra hoy de esta foto: la original (ya guardada) o la que se acaba de elegir
                const shownBefore = entry.shownUrl ?? entry.blobUrl;
                URL.revokeObjectURL(entry.blobUrl);
                entry.file = newFile;
                entry.blobUrl = newBlobUrl;
                entry.shownUrl = newBlobUrl;

                // Una foto que ya estaba guardada pasa a subirse de nuevo, recortada, al guardar
                if (entry.isDraft) {
                    delete entry.isDraft;
                    this.pendingUploads.push(entry);
                }

                if (typeof entry.apply === 'function') {
                    entry.apply(newBlobUrl);
                }
                this.patchPreviewImage(shownBefore, newBlobUrl);

                this.cropperEntry = null;
                this.closeImageCropper();
                this.schedulePreview();
            } catch (e) {
                console.error(e);
                alert('No se pudo aplicar el recorte. Intenta de nuevo.');
                this.closeImageCropper();
            }
        },

        async uploadPendingMedia() {
            const queue = [...this.pendingUploads];
            let done = 0;
            for (const pending of queue) {
                this.uploadStatus = queue.length > 1 ? `Subiendo archivo ${done + 1} de ${queue.length}…` : 'Subiendo el archivo…';
                const url = await this._uploadFile(pending.file, pending.type, pending.context);
                pending.apply(url);
                URL.revokeObjectURL(pending.blobUrl);
                // Lo ya subido sale de la cola: si otro falla, al reintentar no se sube dos veces
                this.pendingUploads = this.pendingUploads.filter(item => item !== pending);
                done++;
            }
            this.uploadStatus = '';
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
                this.uploadStatus = 'Guardando…';
                // Ya no hay nada sin guardar: salir de la página no debe preguntar
                this.leaving = true;
                form.submit();
            } catch (err) {
                console.error('Error al guardar la invitación:', err);
                alert(err.message || 'No se pudo subir un archivo. Intenta de nuevo.');
                this.mediaUploading = false;
                this.uploadStatus = '';
                // El asistente de creación no se queda en «Creando…»
                this.wizardSaving = false;
                this.schedulePreview();
            }
        },

        uploadGalleryFiles(event) {
            const files = [...(event.target.files || [])];
            for (const file of files) {
                if (!this.acceptMediaFile(file, 'image')) continue;
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
                if (!this.acceptMediaFile(file, 'image')) continue;
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
            if (!file || !this.acceptMediaFile(file, 'image')) return;
            const entry = this.modules[code][key][index];
            if (entry.foto) this.clearMediaUrl(entry.foto);
            const blobUrl = URL.createObjectURL(file);
            entry.foto = blobUrl;
            // La foto actual de la entrada: cambia al recortarla (otro blob) y al subirla (la URL definitiva).
            // Sin seguirla, una foto recortada nunca se reemplazaba al guardar y el guardado fallaba.
            let current = blobUrl;
            this.pendingUploads.push({
                file,
                type: 'image',
                context: code,
                blobUrl,
                // Se busca la entrada por su foto: si se reordenan antes de guardar, el índice cambia
                apply: (url) => {
                    const target = this.modules[code][key].find(item => item.foto === current);
                    if (target) target.foto = url;
                    current = url;
                },
            });
            this.schedulePreview();
        },

        uploadPostEventPhotos(event) {
            const files = [...(event.target.files || [])];
            this.modules.post_evento.fotos ??= [];
            for (const file of files) {
                if (!this.acceptMediaFile(file, 'image')) continue;
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
            const link = this.modules.ubicacion.maps_url?.trim();
            return /^https?:\/\//i.test(link || '') ? link : this.buildMapsUrl(lat, lng);
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

                // Coordenadas sueltas ("-17.39,-66.16") no son un enlace: el botón «Cómo llegar» necesita uno de mapa
                const typedLink = this.modules.ubicacion.maps_url;
                this.setLocationCoordinates(coords.lat, coords.lng, {
                    mapsUrl: /^https?:\/\//i.test(typedLink || '') ? typedLink : this.buildMapsUrl(coords.lat, coords.lng),
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
