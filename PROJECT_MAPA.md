# Mapa del proyecto Bida-Events

Este documento describe qué hace cada carpeta y archivo relevante del proyecto, excluyendo `vendor/`, `node_modules/`, `.git/` y artefactos generados de `public/build/` y `storage/framework/`.

## Resumen rápido

El proyecto es una aplicación Laravel 12 enfocada en:

- **Panel admin**: Crear, editar y personalizar invitaciones digitales con un editor modular en tiempo real.
- **Portal cliente**: Dashboard donde los clientes consultan el resumen de su evento, exportan listas de invitados y descargan reportes PDF/Excel.
- **Vista pública de la invitación**: Interfaz interactiva de alto impacto visual con confirmación RSVP, playlist colaborativa, fotomural en vivo, encuestas, reproductor de video/música, itinerario animado por scroll y galería fotográfica en stack swipeable.
- **Carga dinámica de assets y medios**: Code-splitting frontend basado en DOM (Video.js, Lottie icons, Motion One), soporte de imágenes/medios locales y almacenamiento en Cloudinary.
- **Caché y optimizaciones**: Métricas agregadas, invalidación de caché por invitación y metadatos HTTP para respuestas veloces.

---

## Raíz del repositorio

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `.editorconfig` | Normaliza sangría, codificación y formato básico del editor. | Mantenerlo alineado con Laravel Pint y la configuración del equipo. |
| `.env` | Variables locales de entorno (DB, Cloudinary, Mail, Cache). | No versionarlo jamás con secretos reales; verificar credenciales locales. |
| `.env.example` | Plantilla base de variables de entorno. | Incluir cualquier variable nueva usada por `config/optimizations.php` o servicios de terceros. |
| `.gitattributes` | Reglas de Git para encriptación, fin de línea (LF/CRLF) y exportaciones. | Asegurar consistencia entre desarrolladores Windows y Linux. |
| `.gitignore` | Exclusiones de Git (logs, caches, builds, uploads locales). | Garantizar que cubra compilados de Vite, logs y sesiones temporales. |
| `artisan` | Interfaz de línea de comandos (CLI) de Laravel. | Punto de entrada para ejecutar migraciones, seeders, limpiadores de caché y tareas diferidas. |
| `composer.json` | Dependencias PHP, scripts y autoloader PSR-4. | Mantener paquetes actualizados y separar comandos de mantenimiento. |
| `composer.lock` | Registro de versiones exactas de dependencias PHP. | Sincronizar siempre en el repositorio para despliegues reproducibles. |
| `package.json` | Dependencias JS/CSS y scripts de construcción con Vite (`dev`, `build`). | Mantener limpias las dependencias de frontend y configurar entradas de Vite. |
| `package-lock.json` | Registro de versiones exactas de paquetes npm. | Versionarlo para evitar discrepancias en compilación entre entornos. |
| `phpunit.xml` | Configuración de la suite de pruebas PHPUnit/Pest. | Definir variables de entorno en memoria (`DB_CONNECTION=sqlite`) para tests rápidos. |
| `PROJECT_MAPA.md` | Documento de mapeo y arquitectura del proyecto. | Mantener actualizado ante la adición de nuevos módulos, modelos o servicios. |
| `README.md` | Documentación inicial o guía del proyecto. | Personalizar con pasos de instalación local, variables requeridas y comandos de inicio. |
| `vite.config.js` | Configuración del empaquetador Vite para assets (CSS/JS). | Ajustar si se requieren nuevos entrypoints o alias de rutas. |

---

## `app/`

### `app/Http/Controllers`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Http/Controllers/Controller.php` | Controlador base de Laravel. | Mantenerlo ligero; abstraer lógica compartida hacia traits, servicios o ViewModels. |
| `app/Http/Controllers/Admin/DashboardController.php` | Carga el resumen de invitaciones y métricas para el panel administrativo. | Utilizar paginación y consultas eficientes si la lista de eventos crece. |
| `app/Http/Controllers/Admin/GuestController.php` | CRUD completo de invitados dentro de una invitación (alta, edición, eliminación, estado). | Validar pertenencia por Policies y soportar importación/operaciones masivas. |
| `app/Http/Controllers/Admin/InvitationController.php` | Crear, editar y sincronizar invitaciones y sus módulos configurables. | Delegar la normalización y guardado de módulos a `InvitationModuleService`. |
| `app/Http/Controllers/Admin/MapsController.php` | Busca y resuelve direcciones/coordenadas usando APIs de geocodificación (Nominatim/Google Maps). | Cachear respuestas geográficas para evitar límites de tasa (rate limits). |
| `app/Http/Controllers/Admin/MediaUploadController.php` | Recibe archivos multimedia y los transfiere a `MediaUploadService`. | Procesar archivos pesados o videos de forma asíncrona si el volumen aumenta. |
| `app/Http/Controllers/Admin/PreviewController.php` | Guarda temporalmente y renderiza la vista previa del editor en tiempo real. | Gestionar el estado de preview en caché de sesión para evitar colisiones. |
| `app/Http/Controllers/Auth/LoginController.php` | Controla la autenticación (login y logout) de administradores y clientes. | Implementar protección contra fuerza bruta (rate limiting/throttling) en el login. |
| `app/Http/Controllers/Client/DashboardController.php` | Dashboard del cliente con el estado de sus invitaciones y resumen de invitados. | Continuar utilizando `withCount` y ViewModels dedicados (`DashboardViewData`). |
| `app/Http/Controllers/Client/ExportController.php` | Genera y descarga exportaciones de invitados a PDF y Excel. | Procesar reportes pesados en cola asíncrona si las listas superan cientos de invitados. |
| `app/Http/Controllers/Public/ContributionController.php` | Recibe sugerencias de canciones, fotos para el fotomural y votos de encuestas públicas. | Aplicar throttling por IP e invitación para evitar spam o abusos. |
| `app/Http/Controllers/Public/InvitationController.php` | Renderiza la invitación pública resolviendo el slug, metadatos HTTP y caché. | Aprovechar `InvitationCacheService` y headers de caché HTTP para máxima velocidad. |
| `app/Http/Controllers/Public/RsvpController.php` | Procesa la confirmación/rechazo de asistencia de invitados y genera tokens QR. | Encapsular la confirmación en transacciones de DB y enviar notificaciones. |

### `app/Models`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Models/User.php` | Modelo de usuario (administrador o cliente) con autenticación y relaciones. | Definir scopes útiles como `scopeAdmins()` y `scopeClients()`. |
| `app/Models/Invitation.php` | Modelo central de la invitación: almacena fechas, slug, estado, tema y relaciones. | Mantener mutadores/accesorios limpios y evitar consultas `N+1` en relaciones. |
| `app/Models/InvitationData.php` | Guarda la configuración en formato JSON por módulo (`feature_code`) de una invitación. | Garantizar clave única `(invitation_id, feature_code)` y validar esquemas JSON. |
| `app/Models/Guest.php` | Modelo de invitado: pase, acompañantes, estado RSVP, teléfono y token/QR. | Mantener índices en `invitation_id` y `status` para búsquedas rápidas. |
| `app/Models/GuestContribution.php` | Guarda los aportes de invitados: fotos de fotomural, canciones sugeridas y mensajes. | Indexar por `(invitation_id, type)` para agrupar contribuciones eficientemente. |
| `app/Models/PollVote.php` | Registra los votos emitidos en encuestas públicas por invitación e invitado. | Mantener restricción de unicidad por votante para evitar múltiples votos. |
| `app/Models/Feature.php` | Catálogo de módulos/funcionalidades disponibles en el sistema. | Almacenar en caché de lectura si la lista de features es estática. |
| `app/Models/EventType.php` | Catálogo de tipos de eventos (XV Años, Bodas, Cumpleaños, etc.). | Mantenerlo como tabla maestra liviana y cacheable. |

### `app/Http/Requests`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Http/Requests/Admin/Invitation/StoreInvitationRequest.php` | Valida los datos requeridos para crear una invitación. | Extraer reglas comunes en un trait o FormRequest base con update. |
| `app/Http/Requests/Admin/Invitation/UpdateInvitationRequest.php` | Valida los cambios al actualizar una invitación existente. | Mantener reglas alineadas con el guardado de módulos del editor. |
| `app/Http/Requests/Admin/Invitation/StoreClientRequest.php` | Valida la creación de una cuenta de usuario cliente. | Verificar unicidad de email y reglas de formato de contraseña. |
| `app/Http/Requests/Admin/Guest/StoreGuestRequest.php` | Valida el registro individual de un nuevo invitado. | Validar número de pases permitidos y formato telefónico. |
| `app/Http/Requests/Admin/Guest/UpdateGuestRequest.php` | Valida la edición de los datos de un invitado. | Compartir lógica de validación con el Request de creación. |

### `app/Services`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Services/InvitationCacheService.php` | Gestiona el almacenamiento en caché y la invalidación de invitaciones públicas. | Vincular a eventos de modelo (`saved`, `deleted`) para invalidación automática. |
| `app/Services/InvitationModuleService.php` | Normaliza el payload de módulos, calcula estados y prepara configuraciones. | Mantener funciones puras para facilitar pruebas unitarias del procesador de JSON. |
| `app/Services/InvitationPreviewSession.php` | Administra en la sesión HTTP los borramientos y cambios del editor sin guardar en DB. | Escalar hacia un driver de caché distribuido si se edita de forma concurrente. |
| `app/Services/MediaUploadService.php` | Gestiona la carga y optimización de imágenes/archivos a Cloudinary o disco local. | Aplicar límites de dimensión/peso y considerar procesamiento en cola. |

### `app/Support`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Support/InvitationDefaults.php` | Define estructuras JSON por defecto para cada módulo y valores predeterminados de UI. | Mover configuraciones complejas a archivos de configuración si el catálogo crece. |
| `app/Support/MapsLinkParser.php` | Parsea enlaces o texto de mapas (Google Maps / Waze) para extraer coordenadas lat/lng. | Incluir pruebas unitarias con diversos formatos de URLs de navegación. |
| `app/Support/YouTubeHelper.php` | Parsea URLs de YouTube, extrae IDs de video y obtiene datos mediante oEmbed. | Utilizar la caché integrada para evitar llamadas repetidas a la API externa. |

### `app/ViewModels`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/ViewModels/Admin/DashboardViewData.php` | Prepara las métricas, contadores y lista de invitaciones para el dashboard admin. | Optimizar consultas agregadas en la base de datos. |
| `app/ViewModels/Admin/InvitationEditorViewData.php` | Estructura la configuración masiva y catálogo de módulos para el editor. | Separar datos por pestaña para reducir el peso de la respuesta si es necesario. |
| `app/ViewModels/Client/DashboardViewData.php` | Organiza la vista del cliente con tarjetas de resumen de eventos e invitados. | Utilizar conteos optimizados `withCount()` para agilizar el render. |
| `app/ViewModels/Client/InvitationDetailViewData.php` | Prepara los detalles de la invitación y la lista de invitados para la vista cliente. | Paginar la lista de invitados si el volumen por evento supera los cientos. |
| `app/ViewModels/Client/InvitationExportViewData.php` | Construye las filas, métricas y formato requeridos para las exportaciones PDF/Excel. | Mantener desacoplada la preparación de datos del generador de archivos. |

### `app/Exports`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Exports/GuestsExport.php` | Exporta la lista de invitados a Excel utilizando una vista Blade formateada. | Para miles de registros, considerar exportación por fragmentos (chunking). |

### `app/Providers`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Providers/AppServiceProvider.php` | Inicializa servicios globales y fuerza HTTPS en entornos ngrok/desarrollo. | Mantenerlo enfocado en configuración global de la aplicación. |
| `app/Providers/BladeServiceProvider.php` | Registra directivas Blade personalizadas, componentes de UI y condicionales `Blade::if`. | Asegurar que la directiva `@cache` maneje correctamente las claves de invalidación. |

### `app/Http/Middleware`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `app/Http/Middleware/EnsureUserIsAdmin.php` | Restringe el acceso a rutas administrativas únicamente a usuarios administradores. | Considerar migración a un sistema de roles y permisos más expresivo si se crean subroles. |
| `app/Http/Middleware/EnsureUserIsClient.php` | Protege las rutas del portal de clientes verificando el rol correspondiente. | Mantener alineado con el guard de autenticación por defecto. |
| `app/Http/Middleware/CachePublicInvitations.php` | Configura encabezados HTTP de respuesta para el almacenamiento en caché de invitaciones públicas. | Ajustar tiempos de vida (`max-age`, `s-maxage`) de acuerdo a las necesidades de actualización. |

---

## `bootstrap/`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `bootstrap/app.php` | Configura el enrutamiento, middleware global, alias y manejo de excepciones en Laravel 12. | Mantener la configuración limpia y declarativa. |
| `bootstrap/providers.php` | Registra los Service Providers activos en la aplicación. | Registrar únicamente providers requeridos para optimizar el tiempo de arranque. |
| `bootstrap/cache/.gitignore` | Evita el seguimiento de archivos de caché generados en desarrollo. | Mantenerlo en el repositorio. |

---

## `config/`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `config/app.php` | Configuración general de la aplicación (nombre, entorno, zona horaria, idioma, clave de cifrado). | Verificar `timezone` (`America/Argentina/Buenos_Aires` o local) y `locale`. |
| `config/auth.php` | Definición de guards de autenticación, providers de usuarios y reinicio de contraseñas. | Ajustar si se agregan nuevos guards o proveedores de identidad. |
| `config/cache.php` | Configuración de drivers de almacenamiento en caché (file, database, redis). | En entornos de producción con alto tráfico, cambiar el driver a Redis. |
| `config/cloudinary.php` | Variables de credenciales y configuración del SDK de Cloudinary. | Validar la existencia de claves en los chequeos de salud de despliegue. |
| `config/database.php` | Conexiones de bases de datos (MySQL/MariaDB, SQLite, Redis). | Verificar conjuntos de caracteres, cotejamiento (`utf8mb4_unicode_ci`) y modo estricto. |
| `config/filesystems.php` | Configuración de discos de almacenamiento (local, public, s3). | Mantener el enlace simbólico `storage` en despliegues. |
| `config/logging.php` | Canales de registro de logs y niveles de severidad. | Configurar rotación diaria de logs (`daily`) y alertas por correo en errores críticos. |
| `config/mail.php` | Configuración de transporte de correo electrónico (SMTP, SES, Mailgun). | Utilizar servicios transaccionales dedicados para el envío de notificaciones. |
| `config/optimizations.php` | Modos y conmutadores (toggles) para optimizaciones de caché, minificación y CDN. | Excelente punto central para ajustar rendimiento por entorno. |
| `config/queue.php` | Configuración de la conexión de colas de trabajo (sync, database, redis). | Cambiar de `sync` a `database` o `redis` en producción para tareas pesadas. |
| `config/services.php` | Credenciales de servicios externos de terceros (Maps, YouTube, etc.). | Mantener únicamente credenciales activas y consumidas. |
| `config/session.php` | Configuración del driver de sesión, tiempo de vida y opciones de cookies. | Utilizar almacenamiento seguro y consistente de sesiones HTTP. |

---

## `database/`

### `database/factories`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `database/factories/UserFactory.php` | Genera usuarios de prueba con datos aleatorios. | Definir estados específicos como `admin()` y `client()` para pruebas automatizadas. |

### `database/seeders`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `database/seeders/DatabaseSeeder.php` | Seeder principal que puebla la base de datos con usuarios admin/cliente e invitación de prueba. | Separar datos iniciales maestros de datos demo de desarrollo. |
| `database/seeders/XvSofiaModuleData.php` | Payload demo completo con todos los módulos y contenidos para la invitación `xv-sofia`. | Mantener como referencia de estructura JSON para todos los módulos. |

### `database/migrations`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Estructura inicial de la tabla `users` y reinicio de contraseñas. | Mantener campos base y roles requeridos. |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | Crea la tabla `cache` para el almacenamiento en base de datos. | Requerida si se usa el driver de caché `database`. |
| `database/migrations/0001_01_01_000002_create_jobs_table.php` | Tabla para la gestión de tareas diferidas (`jobs`) y fallidas (`failed_jobs`). | Fundamental para el procesamiento en segundo plano. |
| `database/migrations/2026_06_07_030112_create_event_types_table.php` | Tabla del catálogo de tipos de evento (XV Años, Boda, etc.). | Tabla maestra estable. |
| `database/migrations/2026_06_07_030113_create_features_table.php` | Tabla del catálogo de módulos/features habilitables. | Mantener sincronizada con los identificadores de módulos en código. |
| `database/migrations/2026_06_07_030114_create_plans_table.php` | Estructura obsoleta de planes comerciales. | Removida conceptualmente en migraciones posteriores. |
| `database/migrations/2026_06_07_030115_create_event_plan_feature_table.php` | Tabla pivote obsoleta entre planes y features. | Removida en la limpieza de planes. |
| `database/migrations/2026_06_07_030116_create_invitations_table.php` | Tabla principal de invitaciones con slug, usuario, fechas y estado. | Preservar índices únicos en `slug` e índices clave de búsqueda. |
| `database/migrations/2026_06_07_030117_create_invitation_features_table.php` | Tabla pivote de módulos activados por invitación. | Asegurar clave única compuesta `(invitation_id, feature_id)`. |
| `database/migrations/2026_06_07_030118_create_invitation_data_table.php` | Almacena la configuración JSON de cada módulo por invitación. | Clave única `(invitation_id, feature_code)` para evitar duplicados. |
| `database/migrations/2026_06_07_030119_create_guests_table.php` | Tabla de invitados, pases, confirmación RSVP y código de pase. | Indizada por `invitation_id`, `status` y `pass_code`. |
| `database/migrations/2026_06_07_030120_create_guest_contributions_table.php` | Tabla de canciones, mensajes y fotos subidas por los invitados. | Indizada por `invitation_id` y `type`. |
| `database/migrations/2026_06_07_030121_create_poll_votes_table.php` | Tabla de registros de votos en las encuestas de la invitación. | Restricción de unicidad para evitar votos duplicados. |
| `database/migrations/2026_06_12_000000_remove_plans_from_system.php` | Elimina las tablas y referencias obsoletas del sistema de planes. | Migración de limpieza del esquema. |
| `database/migrations/2026_06_12_000001_add_performance_indexes.php` | Añade índices de rendimiento para optimizar las consultas frecuentes. | Verificar periódicamente con `EXPLAIN` en consultas lentas. |
| `database/migrations/2026_06_23_000000_remove_transporte_from_invitation_data.php` | Limpia datos obsoletos del módulo de transporte descontinuado. | Migración de mantenimiento de datos. |

---

## `public/`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `public/index.php` | Front controller y punto de entrada HTTP de Laravel. | No modificar; gestiona el arranque del framework. |
| `public/.htaccess` | Reglas de reescritura del servidor Apache. | Mantener si se despliega en servidores Apache/LiteSpeed. |
| `public/robots.txt` | Instrucciones de indexación para motores de búsqueda. | Configurar para evitar la indexación no deseada de paneles administrativos. |
| `public/favicon.ico` | Icono representativo del sitio web. | Reemplazar por el favicon oficial de la marca. |
| `public/storage/` | Enlace simbólico hacia `storage/app/public`. | Requerido para servir archivos multimedia locales subidos. |

---

## `resources/`

### `resources/js`

El frontend del proyecto utiliza **Alpine.js** y una arquitectura de **Code-Splitting inteligente por DOM**. El archivo `app.js` sólo carga de forma síncrona el core (~30 kB) e importa asíncronamente los módulos pesados únicamente cuando sus elementos existen en el DOM, mostrando una barra de progreso progresiva superior.

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/js/app.js` | Entry point principal. Carga Alpine.js y Axios; detecta elementos en el DOM para realizar `import()` dinámico de scripts pesados (`video-player`, `lottie-icons`, `gallery-stack`, `itinerary-scroll`) y gestiona la barra de progreso de carga. | Excelente arquitectura de rendimiento; mantener los imports dinámicos encapsulados y sin dependencias cruzadas. |
| `resources/js/bootstrap.js` | Inicializa Axios y configura cabeceras HTTP automáticas (CSRF-TOKEN y X-Requested-With). | Añadir interceptores globales si se requiere un manejo centralizado de errores HTTP. |
| `resources/js/gallery-stack.js` | Galería en pila con física de gesto: escribe las transformaciones directo en el DOM, mide la velocidad real del dedo, rota según el punto de agarre, adelanta las cartas de atrás en proporción al arrastre y lanza/devuelve cartas con resortes de Motion que heredan la velocidad. Soporta flechas, teclado y movimiento reducido. | Cargar dinámicamente sólo si existe `x-data*="galleryStack"`. Mantener las cartas renderizadas en Blade: el JS solo las anima. |
| `resources/js/itinerary-scroll.js` | Luz del itinerario: sigue la línea de lectura con un resorte críticamente amortiguado, deriva la velocidad para la estela y enciende cada nodo con un destello gaussiano que deja un resplandor residual. El bucle `requestAnimationFrame` solo corre mientras la sección es visible y la luz no se asentó. | Escribe variables CSS (`--a`, `--glow`, `--trail`) directo en el DOM; no reintroducir bindings reactivos por frame. |
| `resources/js/lottie-icons.js` | Carga cada ícono Lottie bajo demanda (`import.meta.glob`, un chunk por JSON), escribe el color primario y el grosor opcional (`data-lottie-stroke`) en la capa `control`, pausa las animaciones fuera de pantalla y respeta `prefers-reduced-motion`. | Todo ícono nuevo debe llamarse `<nombre>-loop-icon.json` y tener la capa `control`. |
| `resources/js/video-player.js` | **[NUEVO]** Chunk dinámico para reproductores de video (`video.js`). Agrega controles de reproducción personalizados, desvanecimiento automático por inactividad del cursor y estado idle. | Se descarga sólo en páginas que contienen el atributo `[data-video-player]`. |

### `resources/css`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/css/app.css` | Entrada de Tailwind: importa los estilos de la invitación y contiene login, editor admin, portal cliente, animaciones de íconos SVG y microinteracciones globales (limitadas a fuera de `.inv-page`). | Mantener aquí solo estilos de admin/cliente; lo público va en `resources/css/invitation/`. |
| `resources/css/invitation/base.css` | Tokens y piezas comunes de la invitación mobile-first: sección, encabezado, botones, listas con filete, campos, hoja inferior, aparición al scroll y footer. | Reutilizar `.inv-*` antes de crear estilos nuevos por módulo. |
| `resources/css/invitation/hero.css` | Portada con foto, velo de legibilidad e indicador de scroll. | — |
| `resources/css/invitation/countdown.css` | Cuenta regresiva y banner del invitado. | — |
| `resources/css/invitation/gallery.css` | Pila de fotos (las transformaciones las escribe `gallery-stack.js`). | — |
| `resources/css/invitation/itinerary.css` | Línea de tiempo con luz de caída suave, estela y bloom por nodo, controlados por variables CSS. | — |
| `resources/css/invitation/modules.css` | Pestañas, video, dress code, cortejo, ubicación, hashtag, encuestas, playlist, regalos, RSVP y fotomural. | — |
| `resources/css/invitation/nav-player.css` | Menú de secciones numerado y reproductor de música fijo. | — |

### `resources/lottie-icons`

Animaciones vectoriales Lottie en formato JSON utilizadas en los módulos del evento para enriquecer la experiencia visual.

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/lottie-icons/calendar-loop-icon.json` | **[NUEVO]** Animación de calendario en bucle para el módulo de fecha y agendado. | Optimizado para inyección dinámica de color primario. |
| `resources/lottie-icons/clock-loop-icon.json` | **[NUEVO]** Animación de reloj en bucle para la cuenta regresiva e itinerario. | Mantener tamaños de vector reducidos. |
| `resources/lottie-icons/eye-image-loop-icon.json` | **[NUEVO]** Animación de ojo/fotografía para el fotomural y galería fotográfica. | Utilizado en encabezados de módulos visuales. |
| `resources/lottie-icons/invitation-loop-icon.json` | **[NUEVO]** Animación de sobre de invitación para el banner de bienvenida y sección de RSVP. | Icono principal de bienvenida al invitado. |
| `resources/lottie-icons/itinerar-people-loop-icon.json` | **[NUEVO]** Animación de personas/evento en bucle para la cronología del itinerario. | Utilizado en la cabecera de la sección de itinerario. |
| `resources/lottie-icons/video-loop-icon.json` | **[NUEVO]** Animación de claustro/cámara de video para el reproductor de video / Save The Date. | Icono representativo del módulo multimedia. |
| `resources/lottie-icons/{location,crown,dress,hashtag,poll,music,gift,rsvp,camera,heart}-loop-icon.json` | Íconos propios (ubicación, cortejo, dress code, hashtag, encuestas, playlist, regalos, RSVP, fotomural y post-evento) con la misma estructura Lordicon: capa `control` para color/grosor y loop que cierra en la misma pose. | No editarlos a mano: se generan con `scripts/lottie/build-icons.mjs`. |

### `resources/views`

#### Raíz y autenticación

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/welcome.blade.php` | Vista de bienvenida por defecto. | Personalizar o redirigir al login si no se usa como landing page pública. |
| `resources/views/auth/login.blade.php` | Formulario de autenticación para administradores y clientes. | Mantener diseño limpio y responsivo. |

#### Layouts

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/layouts/admin.blade.php` | Layout principal del panel administrativo con barra de navegación y contenedores. | Desacoplar alertas y encabezados en componentes reutilizables. |
| `resources/views/layouts/admin-editor.blade.php` | Layout a pantalla completa específico para el editor interactivo de invitaciones. | Optimizado para trabajar con el panel lateral y el área de vista previa. |
| `resources/views/layouts/client.blade.php` | Layout del portal de cliente con cabecera y tarjetas de gestión. | Mantener liviano para una carga rápida en teléfonos móviles. |

#### Páginas admin

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/dashboard.blade.php` | Vista del dashboard administrativo (plantilla directa o fallback). | Consolidar con `resources/views/pages/admin/dashboard.blade.php`. |
| `resources/views/admin/guests/index.blade.php` | Pantalla de administración de invitados de una invitación (lista, estado RSVP, pases). | Implementar búsqueda rápida y filtros por estado. |
| `resources/views/admin/invitations/create.blade.php` | Pantalla para la creación de una nueva invitación. | Reutilizar el formulario parcial `_form.blade.php`. |
| `resources/views/admin/invitations/edit.blade.php` | Pantalla contenedora del editor interactivo de invitaciones. | Carga la estructura modular y los paneles de personalización. |
| `resources/views/admin/invitations/_form.blade.php` | Formulario parcial compartido para datos básicos de la invitación (título, fecha, cliente). | Mantener validaciones claras en pantalla. |

#### Editor admin

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/invitations/editor/layout.blade.php` | Estructura principal del editor dividida en sidebar de controles y frame de preview. | Garantizar responsividad al alternar vistas móvil/desktop. |
| `resources/views/admin/invitations/editor/preview.blade.php` | Contenedor de la vista previa en tiempo real que procesa los módulos modificados. | Renderiza los cambios en caliente mediante llamadas de sesión. |
| `resources/views/admin/invitations/editor/script.blade.php` | Scripts Alpine.js y controladores del cliente para la interactividad del editor. | Si el script supera las 500 líneas, abstraerlo a un módulo JS dedicado en `resources/js/`. |
| `resources/views/admin/invitations/editor/sidebar.blade.php` | Barra lateral con el acordeón de paneles de configuración de la invitación. | Organizar los paneles de manera intuitiva por secciones. |

#### Paneles del editor

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/invitations/panels/agendar.blade.php` | Configura el botón para agregar el evento al calendario (Google/iCal). | Generar URLs dinámicas de calendario. |
| `resources/views/admin/invitations/panels/countdown.blade.php` | Configuración de la fecha objetivo y textos de la cuenta regresiva. | Permitir personalizar sufijos y títulos. |
| `resources/views/admin/invitations/panels/destacados.blade.php` | Configuración de la lista de personas destacadas (padrinos, honor). | Soportar ordenamiento dinámico de tarjetas. |
| `resources/views/admin/invitations/panels/dress-code.blade.php` | Configuración de vestimenta sugerida, colores y recomendaciones. | Incluir selectores visuales de paleta de color. |
| `resources/views/admin/invitations/panels/encuestas.blade.php` | Editor de preguntas y opciones para las encuestas interactivas. | Validar que cada pregunta tenga al menos dos opciones. |
| `resources/views/admin/invitations/panels/estetica.blade.php` | Ajuste de tipografías, esquema de colores primarios y estilo visual. | Ofrecer vistas previas rápidas de la paleta. |
| `resources/views/admin/invitations/panels/fotomural.blade.php` | Controles de moderación y activación del fotomural colaborativo. | Permitir aprobar o eliminar fotos subidas por invitados. |
| `resources/views/admin/invitations/panels/galeria.blade.php` | Administración de las imágenes de la galería interactiva en stack. | Soportar subida múltiple e integración con Cloudinary. |
| `resources/views/admin/invitations/panels/general.blade.php` | Ajustes generales de la invitación (título, slug, tipo de evento). | Validar unicidad del slug en tiempo real. |
| `resources/views/admin/invitations/panels/hashtag.blade.php` | Configuración del hashtag oficial para redes sociales. | Limpiar caracteres especiales automáticamente. |
| `resources/views/admin/invitations/panels/hero.blade.php` | Configuración del header principal (imagen de fondo, frase de bienvenida). | Recomendar tamaños óptimos de imagen de portada. |
| `resources/views/admin/invitations/panels/itinerario.blade.php` | Editor del cronograma de eventos del día con horas, títulos e iconos. | Permitir añadir/quitar hitos dinámicamente. |
| `resources/views/admin/invitations/panels/musica.blade.php` | Configuración de la música de fondo (URL MP3 o YouTube) y auto-play. | Validar reproductibilidad y permisos de navegador. |
| `resources/views/admin/invitations/panels/playlist.blade.php` | Configuración de la playlist colaborativa de invitados. | Establecer límites de sugerencias por invitado. |
| `resources/views/admin/invitations/panels/post-evento.blade.php` | Configuración del mensaje y galería de agradecimiento post-evento. | Activar automáticamente tras pasar la fecha del evento. |
| `resources/views/admin/invitations/panels/regalos.blade.php` | Configuración de mesas de regalos, datos de transferencia bancaria y sobres. | Ofrecer opción de ocultar datos bancarios sensibles. |
| `resources/views/admin/invitations/panels/rsvp.blade.php` | Configuración del formulario de confirmación de asistencia e instrucciones. | Configurar fecha límite de confirmación. |
| `resources/views/admin/invitations/panels/ubicacion.blade.php` | Configuración de sedes, direcciones, mapas interactivos y links de GPS. | Utilizar el helper de mapeo para resolver coordenadas. |
| `resources/views/admin/invitations/panels/video.blade.php` | Configuración del video principal o Save The Date (video local o YouTube). | Soportar poster de vista previa personalizado. |

#### Parciales admin

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/partials/cloudinary-upload.blade.php` | Componente reutilizable para la carga de archivos multimedia a Cloudinary. | Mantener desacoplado para ser usado en cualquier panel del editor. |
| `resources/views/admin/partials/panel-intro.blade.php` | Encabezado de cada panel del editor: qué es el módulo, qué ve el invitado, consejo y estado visible/oculto. | Describir siempre el resultado que verá el invitado, no el campo técnico. |
| `resources/views/admin/partials/icon-picker.blade.php` | Selector visual de iconos vectoriales para itinerarios y módulos. | Renderizar lista de iconos de manera diferida para acelerar la interfaz. |

#### Portal Cliente

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/client/dashboard.blade.php` | Dashboard principal del cliente (alternativa o vista directa). | Consolidar con `resources/views/pages/client/dashboard.blade.php`. |
| `resources/views/client/invitation.blade.php` | Detalle y métricas de una invitación para el cliente. | Consolidar con `resources/views/pages/client/invitation.blade.php`. |
| `resources/views/client/exports/guests-excel.blade.php` | Vista HTML procesada por Maatwebsite/Excel para la exportación de invitados. | Mantener estilos en celdas simples sin CSS complejo. |
| `resources/views/client/exports/guests-pdf.blade.php` | Plantilla Blade renderizada a PDF con la lista de invitados y sus pases. | Evitar propiedades CSS3 no soportadas por DomPDF. |
| `resources/views/client/exports/invitation-pdf.blade.php` | Plantilla PDF con el resumen completo de la configuración del evento. | Mantener fuentes integradas y diseño compacto. |

#### Páginas utilizadas por controladores (Estructura `pages/`)

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/pages/admin/dashboard.blade.php` | Vista del dashboard administrativo alimentada por `DashboardViewData`. | **Vista activa consumida por el controlador.** |
| `resources/views/pages/client/dashboard.blade.php` | Vista del dashboard de cliente alimentada por `Client\DashboardViewData`. | **Vista activa consumida por el controlador.** |
| `resources/views/pages/client/invitation.blade.php` | Vista de detalle de invitación para cliente alimentada por `InvitationDetailViewData`. | **Vista activa consumida por el controlador.** |
| `resources/views/pages/client/exports/guests-excel.blade.php` | Plantilla de exportación Excel de invitados. | Usada directamente por `GuestsExport`. |
| `resources/views/pages/client/exports/guests-pdf.blade.php` | Plantilla de exportación PDF de invitados. | Usada directamente por `ExportController`. |
| `resources/views/pages/client/exports/invitation-pdf.blade.php` | Plantilla de exportación PDF del resumen del evento. | Usada directamente por `ExportController`. |
| `resources/views/pages/invitations/templates/xv-premium.blade.php` | **Plantilla pública canónica** de la invitación XV Premium. | Es el punto de entrada que compone todos los parciales de la invitación pública. |

#### Invitaciones: plantilla pública y parciales

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/invitations/templates/xv-premium.blade.php` | Variante o plantilla directa de la invitación XV Premium. | Consolidar con la versión en `pages/invitations/templates/` para mantener una única fuente. |
| `resources/views/invitations/partials/countdown.blade.php` | Parcial que renderiza el temporizador de cuenta regresiva. | Ejecuta cálculo de tiempo en cliente con Alpine.js. |
| `resources/views/invitations/partials/destacados.blade.php` | Parcial que presenta las tarjetas de personas destacadas. | Diseño adaptable según el número de integrantes. |
| `resources/views/invitations/partials/dress-code.blade.php` | Parcial con los detalles del código de vestimenta y paleta de colores. | Incluye render defensivo ante campos vacíos. |
| `resources/views/invitations/partials/fotomural.blade.php` | Parcial del fotomural colaborativo con carga diferida de imágenes. | Integrado con el formulario de envío de fotos. |
| `resources/views/invitations/partials/gallery-stack.blade.php` | Parcial de la galería fotográfica interactiva en formato stack/baraja. | Vinculado con el componente Alpine `galleryStack()`. |
| `resources/views/invitations/partials/guest-banner.blade.php` | **[NUEVO]** Banner de bienvenida personalizado para el invitado según el token de pase o nombre del invitado. | Muestra mensajes adaptados al estado de asistencia. |
| `resources/views/invitations/partials/hashtag.blade.php` | Parcial para copiar o consultar el hashtag oficial del evento. | Incluye botón de copiado rápido al portapapeles. |
| `resources/views/invitations/partials/hero.blade.php` | **[NUEVO]** Encabezado principal/hero con imagen de fondo, velo de brillo, partículas flotantes y tipografía animada. | Diseñado con la directiva `fetchpriority="high"` en la imagen de portada. |
| `resources/views/invitations/partials/icon.blade.php` | Renderizador centralizado de iconos SVG inline de la aplicación. | Garantizar que los nombres de iconos coincidan con los SVGs definidos. |
| `resources/views/invitations/partials/itinerary.blade.php` | Parcial de la cronología/itinerario interactivo con animación por scroll. | Vinculado con `scrollItinerary()` e iconos Lottie. |
| `resources/views/invitations/partials/location.blade.php` | Parcial con direcciones, horarios, botón de agendado y enlaces a Waze/Google Maps. | Renderiza mapas y accesos rápidos a GPS. |
| `resources/views/invitations/partials/lottie-framed-icon.blade.php` | **[NUEVO]** Envoltorio ornamental con líneas divisorias que encuadra un icono Lottie animado. | Componente decorativo elegante para separar secciones. |
| `resources/views/invitations/partials/lottie-icon.blade.php` | **[NUEVO]** Parcial base para renderizar elementos de icono Lottie (`span[data-lottie-icon]`). | Leído automáticamente por la librería de inicialización Lottie. |
| `resources/views/invitations/partials/music-player.blade.php` | Reproductor flotante o integrado de música de fondo con botón mute/play. | Respetar las políticas de reproducción automática (autoplay) del navegador. |
| `resources/views/invitations/partials/playlist.blade.php` | Parcial para sugerir canciones y visualización de la lista colaborativa. | Conectado con `Public\ContributionController`. |
| `resources/views/invitations/partials/polls.blade.php` | Parcial de encuestas interactivas con votación en tiempo real. | Muestra porcentajes de resultados tras emitir el voto. |
| `resources/views/invitations/partials/post-event.blade.php` | Parcial con mensaje especial desplegado al finalizar el evento. | Estático y altamente optimizable en caché. |
| `resources/views/invitations/partials/regalos.blade.php` | Parcial para visualizar datos bancarios, sobres o enlaces a tiendas de regalos. | Incluye modal/acordeón para ocultar datos sensibles. |
| `resources/views/invitations/partials/section-header.blade.php` | Encabezado común de sección: lottie enmarcado, eyebrow, título, filete y texto de ayuda. | Usarlo en todo módulo nuevo para mantener la jerarquía visual. |
| `resources/views/invitations/partials/rsvp.blade.php` | Formulario interactivo de confirmación de asistencia (RSVP) con pase y acompañantes. | Procesa el envío mediante AJAX o submit estándar con validación. |
| `resources/views/invitations/partials/video.blade.php` | Bloque de reproductor de video / Save The Date con carátula y Video.js. | Vinculado con el chunk JS dinámico de video. |

#### Invitaciones: wrappers de módulos

Los archivos en `resources/views/invitations/modules/` actúan como wrappers livianos que evalúan la activación de cada módulo e incluyen su parcial correspondiente.

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `resources/views/invitations/modules/countdown.blade.php` | Wrapper del módulo de cuenta regresiva. | Verifica si el módulo está activo antes de renderizar. |
| `resources/views/invitations/modules/destacados.blade.php` | Wrapper del módulo de personas destacadas. | Evita incluir el parcial si no hay datos configurados. |
| `resources/views/invitations/modules/dress-code.blade.php` | Wrapper del módulo de dress code. | Garantizar fallback defensivo ante arrays vacíos. |
| `resources/views/invitations/modules/fotomural.blade.php` | Wrapper del módulo de fotomural. | Mantener limpio y delegar al parcial. |
| `resources/views/invitations/modules/gallery-stack.blade.php` | Wrapper del módulo de galería en stack. | Validar que exista al menos una imagen en la lista. |
| `resources/views/invitations/modules/hashtag.blade.php` | Wrapper del módulo de hashtag. | Renderiza únicamente si el texto del hashtag no está vacío. |
| `resources/views/invitations/modules/icon.blade.php` | Wrapper de utilidad para iconos de módulo. | Centralizar llamadas a iconografía. |
| `resources/views/invitations/modules/itinerary.blade.php` | Wrapper del módulo de itinerario. | Pasa la colección de hitos al parcial. |
| `resources/views/invitations/modules/location.blade.php` | Wrapper del módulo de ubicación y mapa. | Valida coordenadas o dirección antes de mostrar. |
| `resources/views/invitations/modules/music-player.blade.php` | Wrapper del módulo de música de fondo. | Carga el reproductor sólo si la música está habilitada. |
| `resources/views/invitations/modules/playlist.blade.php` | Wrapper del módulo de playlist colaborativa. | Comprobar si se permiten sugerencias activas. |
| `resources/views/invitations/modules/polls.blade.php` | Wrapper del módulo de encuestas. | Pasa el listado de encuestas activas. |
| `resources/views/invitations/modules/post-event.blade.php` | Wrapper del módulo post-evento. | Mostrar condicionalmente según la fecha actual. |
| `resources/views/invitations/modules/regalos.blade.php` | Wrapper del módulo de mesas de regalos. | Evalúa si hay métodos de regalo configurados. |
| `resources/views/invitations/modules/rsvp.blade.php` | Wrapper del módulo de RSVP. | Garantiza la estructura del formulario de confirmación. |
| `resources/views/invitations/modules/video.blade.php` | Wrapper del módulo de video / Save The Date. | Incluye el reproductor si existe URL de video. |

---

## `scripts/`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `scripts/lottie/build-icons.mjs` | Genera los íconos Lottie propios desde primitivas (paths SVG, arcos) y valida todos los JSON de `resources/lottie-icons` con `--check` (capa control, expresiones y cierre del loop). | Ejecutar con Node: `node scripts/lottie/build-icons.mjs` y luego `--check`. |

---

## `routes/`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `routes/web.php` | Rutas web de la aplicación: autenticación, panel administrativo, portal de cliente, previsualizaciones y vistas públicas de invitaciones. | Mantiene los grupos de rutas bien estructurados con middlewares de seguridad. |
| `routes/console.php` | Comandos personalizados de consola Artisan y tareas programadas (Laravel Scheduler). | Registrar aquí tareas livianas de limpieza de temporales o llamadas periódicas. |

---

## `storage/`

| Carpeta | Qué hace | Sugerencia |
| --- | --- | --- |
| `storage/app/` | Archivos privados del sistema y archivos públicos (`storage/app/public`) accesibles vía enlace simbólico. | Configurar políticas de limpieza periódica para archivos subidos temporales. |
| `storage/framework/` | Almacenamiento interno de Laravel: cachés de framework, vistas compiladas de Blade, sesiones activas y caché de testing. | No versionar en Git; limpiar con `artisan cache:clear` o `view:clear` en despliegues. |
| `storage/logs/` | Archivos de registros de errores y eventos de la aplicación (`laravel.log`). | Configurar la rotación de logs diaria (`daily`) para evitar archivos colosales. |

---

## `tests/`

| Archivo | Qué hace | Sugerencia |
| --- | --- | --- |
| `tests/TestCase.php` | Clase base para la suite de pruebas unitarias y de integración de Laravel. | Incluir helpers personalizados de autenticación (`actingAsAdmin`, `actingAsClient`). |
| `tests/Feature/ExampleTest.php` | Test básico de integración que verifica la respuesta HTTP de la ruta raíz. | Reemplazar o complementar con pruebas de los flujos críticos de la aplicación. |
| `tests/Unit/ExampleTest.php` | Test unitario de ejemplo. | Incorporar pruebas sobre servicios puros (`InvitationModuleService`, `MapsLinkParser`, `YouTubeHelper`). |

---

## Recomendaciones principales: reestructuración de base de datos

> **Prioridad 0:** Antes de optimizar frontend, caché o índices aislados, conviene corregir la forma en que se almacenan los módulos. La tabla `invitation_data` concentra en `json_data` configuraciones pequeñas junto con listas que deberían ser registros relacionados. Esta estructura obliga a leer, deserializar y guardar bloques completos para modificar un solo elemento, dificulta los índices y aumenta el riesgo de sobrescribir cambios.

### 1. Adoptar un modelo híbrido y reducir el uso de JSON
- **Decisión recomendada**: No eliminar todo el JSON de inmediato. Mantenerlo únicamente para configuración flexible, experimental o específica de una plantilla, y trasladar a tablas normales los datos que se listan, ordenan, filtran, paginan o actualizan individualmente.
- **Debe salir de `invitation_data.json_data`**: itinerario, galería, personas destacadas, código de vestimenta, encuestas, opciones de regalos y medios multimedia.
- **Puede permanecer temporalmente en JSON**: colores, tipografías, visibilidad de módulos, textos simples del hero, hashtag, mensajes RSVP y configuraciones que no tengan una estructura estable.
- **Beneficio**: Menos memoria y transferencia, consultas parciales, relaciones claras, actualizaciones pequeñas, mejores índices y menor riesgo de perder cambios concurrentes.
- **Prioridad**: Crítica. Es la decisión arquitectónica que condiciona las optimizaciones posteriores.

### 2. Crear tablas normalizadas por responsabilidad
La estructura objetivo debería partir de `invitations` y separar configuración, contenido administrable y actividad pública:

```text
invitations
├── invitation_settings
├── invitation_locations
├── invitation_itinerary_items
├── invitation_gallery_images
├── invitation_featured_people
├── invitation_dress_code_items
├── invitation_polls
│   └── invitation_poll_options
├── invitation_gift_options
└── invitation_media
```

Tablas recomendadas:

| Tabla | Datos que debe contener | Relación principal |
| --- | --- | --- |
| `invitation_settings` | Colores, tipografías, plantilla y visibilidad de módulos. | `invitations hasOne invitationSettings` |
| `invitation_locations` | Lugar, dirección, coordenadas, enlaces de mapas y notas. | `invitations hasMany invitationLocations` |
| `invitation_itinerary_items` | Hora, título, icono, descripción y `sort_order`. | `invitations hasMany itineraryItems` |
| `invitation_gallery_images` | URL, tipo, texto alternativo, portada, estado y `sort_order`. | `invitations hasMany galleryImages` |
| `invitation_featured_people` | Grupo, nombre, iniciales, rol, detalle, mensaje y `sort_order`. | `invitations hasMany featuredPeople` |
| `invitation_dress_code_items` | Tipo de elemento, título, descripción, ejemplo, color y `sort_order`. | `invitations hasMany dressCodeItems` |
| `invitation_polls` | Identificador estable, pregunta, tipo, estado y orden. | `invitations hasMany polls` |
| `invitation_poll_options` | Texto de opción, valor y `sort_order`. | `polls hasMany options` |
| `invitation_gift_options` | Título, descripción, enlace, categoría y orden. | `invitations hasMany giftOptions` |
| `invitation_media` | Tipo (`audio`, `video`, `poster`, `hero`), URL, proveedor y metadatos. | `invitations hasMany media` |

Las tablas públicas existentes deben continuar separadas porque representan actividad y no configuración:

- `guests` pertenece a `invitations`.
- `guest_contributions` pertenece a `invitations` y opcionalmente a `guests`.
- `poll_votes` pertenece a `invitations`, `invitation_polls` y opcionalmente a `guests`.

### 3. Definir correctamente las relaciones y claves foráneas
- Todas las tablas nuevas deben tener `invitation_id` con `foreignId()->constrained('invitations')->cascadeOnDelete()`.
- Los elementos hijos deben tener `sort_order` y un índice `(invitation_id, sort_order, id)` para devolverlos ordenados sin ordenar grandes colecciones en PHP.
- `invitation_poll_options` debe tener `poll_id` con eliminación en cascada; `poll_votes` debe referenciar `poll_id` en vez de guardar únicamente un `poll_id` textual.
- Las relaciones opcionales con `guests` deben usar `nullOnDelete()` para conservar el registro de la interacción aunque se elimine el invitado.
- Usar nombres de relaciones consistentes en los modelos: `settings`, `locations`, `itineraryItems`, `galleryImages`, `featuredPeople`, `dressCodeItems`, `polls`, `giftOptions` y `media`.
- Definir restricciones únicas donde corresponda: `(invitation_id, code)` para opciones estables y `(poll_id, sort_order)` para el orden de opciones.
- **No** usar `cascadeOnDelete()` sobre datos históricos que deban conservarse por auditoría; en ese caso agregar `deleted_at` y aplicar Soft Deletes.

### 4. Índices que deben acompañar el nuevo diseño
- `invitation_settings`: `unique(invitation_id)`.
- `invitation_locations`: `(invitation_id, sort_order)` y, si se buscan sedes por nombre, `(invitation_id, name)`.
- `invitation_itinerary_items`, `invitation_gallery_images`, `invitation_featured_people`, `invitation_dress_code_items` y `invitation_gift_options`: `(invitation_id, sort_order, id)`.
- `invitation_polls`: `unique(invitation_id, poll_key)` e índice `(invitation_id, is_enabled, sort_order)`.
- `invitation_poll_options`: `(poll_id, sort_order, id)`.
- `invitation_media`: `(invitation_id, type, sort_order)`.
- Mantener los índices de `guests`, `guest_contributions` y `poll_votes` sólo después de verificar sus planes con `EXPLAIN`; evitar duplicar índices ya creados por claves únicas o claves foráneas.

### 5. Migrar sin romper el sistema actual
La migración debe ser gradual y reversible:

1. Crear las tablas nuevas, modelos, relaciones y migraciones sin eliminar `invitation_data`.
2. Crear un comando Artisan, por ejemplo `invitations:migrate-json`, que lea cada módulo, valide su estructura y copie sus elementos a las tablas correspondientes.
3. Ejecutar el comando en modo simulación y generar un reporte por invitación: filas detectadas, filas creadas, errores y elementos omitidos.
4. Comparar conteos y contenido entre JSON y tablas, incluyendo orden, URLs, identificadores de encuestas y relaciones con invitados.
5. Cambiar `InvitationModuleService` para leer primero de las relaciones y usar JSON sólo como fallback temporal.
6. Actualizar editor, preview, plantilla pública y exportaciones para utilizar los modelos relacionados.
7. Mantener el fallback durante un periodo de verificación; registrar cualquier lectura que todavía dependa del JSON.
8. Crear una migración de limpieza únicamente después de validar producción. No modificar migraciones históricas ya ejecutadas.

### 6. Reestructurar también los modelos y servicios
- `Invitation` debe exponer relaciones dedicadas en lugar de resolver todos los módulos mediante el accessor `modules`.
- `InvitationModuleService` debe dividirse por responsabilidad o utilizar repositorios/servicios específicos para guardar settings, itinerario, galería, encuestas y medios.
- El guardado del editor debe usar transacciones: actualizar el módulo y sus hijos en una sola operación consistente.
- Para listas completas usar `upsert()` cuando sea seguro; para cambios individuales usar `update()`/`delete()` sobre el registro correspondiente.
- La vista pública debe cargar sólo las relaciones de los módulos habilitados y seleccionar únicamente las columnas necesarias.
- `pollResults()` debe agrupar votos por `poll_id` y `option_id` en SQL, sin traer todos los votos a PHP.

### 7. Qué no conviene normalizar todavía
No es necesario crear una tabla para cada texto simple. Colores, tipografías, toggles, textos del hero o mensajes RSVP pueden vivir en `invitation_settings` como columnas explícitas si son estables, o en un JSON pequeño dentro de esa tabla si son específicos de la plantilla. La regla debe ser: **tabla para datos repetibles y consultables; JSON para configuración flexible y poco consultada**.

### 8. Orden de prioridad para esta reestructuración
1. Crear `invitation_settings`, `invitation_itinerary_items`, `invitation_gallery_images`, `invitation_polls` y `invitation_poll_options`.
2. Migrar y verificar los datos actuales de `itinerario`, `galeria` y `encuestas`.
3. Crear relaciones Eloquent, claves foráneas, restricciones únicas e índices.
4. Cambiar la lectura pública y el editor para utilizar las nuevas tablas.
5. Migrar `destacados`, `dress_code`, `regalos`, `ubicacion` y `media`.
6. Mantener JSON sólo como fallback y retirarlo cuando no existan lecturas activas.
7. Después de lo anterior, optimizar caché, paginación, colas y frontend.

---

## Recomendaciones complementarias de escalabilidad, arquitectura y rendimiento

### 1. Frontend y Carga Dinámica de Chunks (Code-Splitting)
- **Logro actual**: `resources/js/app.js` implementa un excelente patrón de empaquetado donde el bundle inicial es liviano (~30 kB) y las librerías pesadas (`video.js`, `lottie-web`, Motion One, scripts de itinerario) se descargan de manera asíncrona mediante `import()` condicional según los elementos detectados en el DOM.
- **Próximos pasos**: Mantener este estándar en nuevos módulos JS. Asegurar en la configuración de Vite que los chunks generados tengan nombres deterministas e instruir al servidor web (Nginx/Apache) para aplicar compresión **Brotli/Gzip** y cabeceras de caché de largo plazo (`Cache-Control: max-age=31536000, immutable`) sobre la carpeta `public/build/`.

### 2. Consolidación de la Jerarquía de Vistas (`pages/` vs `views/`)
- **Estado actual**: Los controladores de la aplicación apuntan activamente a la subcarpeta `resources/views/pages/*` (`pages.admin.dashboard`, `pages.client.dashboard`, `pages.invitations.templates.xv-premium`), pero coexisten archivos en las carpetas raíz `resources/views/admin/` y `resources/views/client/`.
- **Recomendación**: Consolidar y refactorizar la estructura de carpetas hacia un único estándar canónico (por ejemplo, migrar todas las vistas a `resources/views/admin/*`, `resources/views/client/*` y `resources/views/invitations/*`) para evitar confusiones o duplicidad de mantenimiento en el futuro.

### 3. Optimización de Iconos Lottie y Renderizado Visual
- **Estado actual**: Se utilizan animaciones Lottie vectoriales (`.json`) cuyos colores primarios son parseados y transformados dinámicamente en tiempo de ejecución en JS (`lottie-icons.js`).
- **Recomendación**: Asegurar que las animaciones Lottie que queden fuera de la pantalla (off-screen) entren en pausa automática (`animation.pause()`) mediante un `IntersectionObserver`, reduciendo el consumo de CPU y batería en teléfonos inteligentes de gama media o baja.

### 4. Gestión de Medios y Optimización de Imágenes (Cloudinary / CDN)
- **Estado actual**: Se cuenta con soporte de carga local y a Cloudinary vía `MediaUploadService`.
- **Recomendación**: Para las imágenes de la galería, fotomural y portadas hero, aplicar parámetros de transformación automática de Cloudinary (`f_auto,q_auto,w_1200`) y construir atributos `srcset` en Blade. Esto permitirá servir formatos ultra compactos como **WebP** o **AVIF** adaptados al dispositivo.

### 5. Procesamiento Asíncrono en Colas (Queue Workers)
- **Estado actual**: Exportaciones a PDF/Excel y procesamiento de archivos multimedia se realizan durante la solicitud HTTP síncrona.
- **Recomendación**: Configurar el driver de colas (`QUEUE_CONNECTION=database` o `redis`) en entornos de producción y desacoplar la generación pesada de reportes PDF (DomPDF/Browsershot) a tareas en segundo plano (`Jobs`), enviando una notificación o enlace de descarga cuando el archivo esté listo.

### 6. Estrategia de Caché e Invalidación por Eventos
- **Estado actual**: `InvitationCacheService` y `CachePublicInvitations` controlan el almacenamiento en caché de la vista pública.
- **Recomendación**: Desacoplar la invalidación de la caché mediante **Eventos y Listeners de Laravel** (por ejemplo, `InvitationUpdated`, `GuestContributionSubmitted`, `RsvpConfirmed`). De esta forma, cualquier modificación desde el editor admin o interacción pública refrescará la caché de forma automática e inequívoca.

### 7. Rendimiento de Base de Datos e Índices Compuestos
- **Estado actual**: Existen migraciones dedicadas a añadir índices de rendimiento (`add_performance_indexes`).
- **Recomendación**: Verificar mediante análisis de registros lentos (Slow Query Logs) e `EXPLAIN` que las consultas sobre `guests` `(invitation_id, status)`, `invitation_data` `(invitation_id, feature_code)`, `guest_contributions` `(invitation_id, type)` y `poll_votes` `(invitation_id, guest_id)` aprovechen los índices compuestos sin realizar escaneos completos de tabla.

### 8. Esquema de Validación (JSON Schemas / DTOs) para Módulos
- **Estado actual**: Los datos de cada módulo se guardan en estructuras JSON flexibles en la tabla `invitation_data`.
- **Recomendación**: Implementar validación de esquemas (JSON Schema o DTOs en PHP) al procesar la configuración desde el editor administrativo. Esto evitará que guardados parciales o corruptos generen inconsistencias o errores de ejecución al renderizar la vista pública.

### 9. Autorización Formal (Policies) y Protección contra Abusos (Throttling)
- **Estado actual**: Los middlewares `EnsureUserIsAdmin` y `EnsureUserIsClient` aplican restricciones básicas de rol.
- **Recomendación**: Implementar **Laravel Policies** (`InvitationPolicy`, `GuestPolicy`) para garantizar la verificación de propiedad sobre cada recurso, y agregar **Rate Limiting** estricto en los endpoints públicos de confirmación RSVP, sugerencias de canciones y votación en encuestas para prevenir ataques de spam o denegación de servicio.

### 10. Cobertura de Pruebas Automatizadas e Integración
- **Estado actual**: Se cuenta con la infraestructura inicial de PHPUnit/Pest.
- **Recomendación**: Ampliar la cobertura de pruebas de integración (`Feature Tests`) enfocándose en los flujos críticos de negocio: inicio de sesión, guardado de módulos en el editor, flujo completo de confirmación RSVP, sugerencias de playlist y generación de reportes en PDF/Excel.

---