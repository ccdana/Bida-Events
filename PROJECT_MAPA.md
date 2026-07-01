# Mapa del proyecto Bida-Events

Este documento describe que hace cada carpeta y archivo relevante del proyecto, excluyendo `vendor/`, `node_modules/`, `.git/` y artefactos generados de `public/build/`.

## Resumen rapido

El proyecto es una aplicacion Laravel 12 enfocada en:

- Panel admin para crear y editar invitaciones.
- Portal cliente para ver invitaciones, exportar datos y descargar PDF/Excel.
- Vista publica de la invitacion con RSVP, playlist colaborativa, fotomural y encuestas.
- Carga de medios con Cloudinary o almacenamiento local.
- Caché y optimizaciones para reducir consultas y acelerar render.

## Raiz del repositorio

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `.editorconfig` | Normaliza indentacion y formato basico del editor. | Mantenerlo alineado con Pint y con el estilo del equipo. |
| `.env` | Variables locales de entorno. | No versionarlo con secretos reales; revisar que no haya credenciales comprometidas. |
| `.env.example` | Plantilla de variables de entorno. | Agregar aqui cualquier variable nueva que use `config/optimizations.php` o Cloudinary. |
| `.gitattributes` | Reglas de Git para normalizacion de fin de linea y exportaciones. | Definirlo segun plataforma objetivo si el repo crece. |
| `.gitignore` | Exclusiones de Git. | Asegurar que cubra logs, caches y compilados nuevos. |
| `artisan` | Entrada CLI de Laravel. | Ninguna especial; es el punto de acceso para comandos y tareas. |
| `composer.json` | Dependencias PHP, scripts y autoload. | Separar scripts pesados en comandos dedicados si el setup crece. |
| `composer.lock` | Versiones exactas de dependencias PHP. | Mantenerlo sincronizado para despliegues reproducibles. |
| `package.json` | Dependencias JS/Vite y scripts de frontend. | Si el JS crece, separar bundles o entradas por area. |
| `package-lock.json` | Versiones exactas de dependencias JS. | Mantenerlo versionado para evitar drift entre entornos. |
| `phpunit.xml` | Configuracion de pruebas. | Agregar grupos de pruebas o variables especificas por entorno si hace falta. |
| `README.md` | README base de Laravel. | Reemplazarlo por README del proyecto con setup, arquitectura y despliegue. |
| `vite.config.js` | Configuracion de Vite para assets. | Dividir entrada admin/public si se necesitan bundles mas pequenos. |

## `app/`

### `app/Controllers`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Http/Controllers/Controller.php` | Controlador base compartido. | Mantenerlo fino; mover logica comun a traits o servicios si crece. |
| `app/Http/Controllers/Admin/DashboardController.php` | Carga invitaciones para el dashboard admin. | Paginacion y filtros por estado para evitar traer todo en memoria. |
| `app/Http/Controllers/Admin/GuestController.php` | CRUD de invitados dentro de una invitacion. | Validar ownership por policy y usar operaciones masivas si crece la lista. |
| `app/Http/Controllers/Admin/InvitationController.php` | Crear, editar y sincronizar invitaciones y modulos. | Separar orquestacion en acciones/commands para hacerlo mas testeable. |
| `app/Http/Controllers/Admin/MapsController.php` | Busca y resuelve direcciones usando Nominatim/Google Maps. | Cachear resultados de geocoding y respetar rate limits externos. |
| `app/Http/Controllers/Admin/MediaUploadController.php` | Recibe archivos y delega a `MediaUploadService`. | Procesar imagenes grandes en cola si el trafico sube. |
| `app/Http/Controllers/Admin/PreviewController.php` | Guarda y renderiza la vista previa del editor. | Mover el estado de preview a cache/DB temporal si el editor se vuelve concurrente. |
| `app/Http/Controllers/Auth/LoginController.php` | Login y logout para admin/cliente. | Considerar throttling, MFA o rate limiting en login. |
| `app/Http/Controllers/Client/DashboardController.php` | Dashboard del cliente con invitaciones y resumenes. | Paginar por defecto y usar `withCount`/selects reducidos, como ya hace. |
| `app/Http/Controllers/Client/ExportController.php` | Exporta invitados a Excel y PDF, y exporta la invitacion. | Generar exports pesados en cola y almacenar temporalmente si crece el volumen. |
| `app/Http/Controllers/Public/ContributionController.php` | Playlist, fotomural y votos de encuestas en la vista publica. | Mover writes intensivos a jobs y revisar rate limiting por invitacion. |
| `app/Http/Controllers/Public/InvitationController.php` | Renderiza la invitacion publica con caché y metadatos HTTP. | Muy buen candidato para cache por slug; vigilar invalidez cuando se actualizan modulos. |
| `app/Http/Controllers/Public/RsvpController.php` | Confirma o rechaza asistencia y genera QR en JSON. | Aislar confirmacion en servicio/transaction para robustez y pruebas. |

### `app/Models`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Models/User.php` | Usuario autenticado, admin o cliente. | Agregar scopes como `admins()` o `clients()` para consultas claras. |
| `app/Models/Invitation.php` | Modelo central de invitacion con relaciones, scopes y atributos calculados. | Evitar `load()` repetidos y considerar un DTO para `modules`. |
| `app/Models/InvitationData.php` | Guarda JSON por modulo/feature de la invitacion. | Mantener unico por `invitation_id + feature_code` y validar schema del JSON. |
| `app/Models/Guest.php` | Invitados, pases, estado RSVP y token QR. | Ideal para indice compuesto por `invitation_id, status` si se filtra mucho por estado. |
| `app/Models/GuestContribution.php` | Aportes de invitados: canciones, fotos y mensajes. | Si crece mucho, particionar o archivar por fecha/evento. |
| `app/Models/PollVote.php` | Votos de encuestas por invitacion y votante. | Muy buen caso para indices compuestos y limpieza por expiracion. |
| `app/Models/Feature.php` | Catalogo de features disponibles. | Si el catalogo no cambia, se puede cachear en memoria o config. |
| `app/Models/EventType.php` | Tipo de evento maestro. | Mantenerlo como tabla chica y cacheable. |

### `app/Requests`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Http/Requests/Admin/Invitation/StoreInvitationRequest.php` | Valida creacion de invitacion. | Extraer reglas comunes con `UpdateInvitationRequest` para evitar duplicacion. |
| `app/Http/Requests/Admin/Invitation/UpdateInvitationRequest.php` | Valida actualizacion de invitacion. | Igual que arriba: compartir reglas base. |
| `app/Http/Requests/Admin/Invitation/StoreClientRequest.php` | Valida nuevo cliente. | Agregar verificacion de dominio o reglas de negocio si aplica. |
| `app/Http/Requests/Admin/Guest/StoreGuestRequest.php` | Valida alta de invitado. | Considerar reglas por tipo de evento o por plan. |
| `app/Http/Requests/Admin/Guest/UpdateGuestRequest.php` | Valida edicion de invitado. | Igual que store: compartir una clase base. |

### `app/Services`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Services/InvitationCacheService.php` | Invalida y precalienta cachés de invitaciones. | Conectar invalidacion a eventos de dominio para no olvidar refrescar cache. |
| `app/Services/InvitationModuleService.php` | Normaliza modulos, calcula resultados y genera URLs utiles. | Separar calculos puros de persistencia para facilitar tests unitarios. |
| `app/Services/InvitationPreviewSession.php` | Maneja el estado de preview del editor en session. | Si hay multiples pestañas o editores simultaneos, pasar a cache por usuario/token. |
| `app/Services/MediaUploadService.php` | Sube imagen/video/audio a Cloudinary o local y aplica transforms. | Procesar y redimensionar en cola; limitar peso y dimension por contexto. |

### `app/Support`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Support/InvitationDefaults.php` | Define modulos vacios, defaults de UI y templates disponibles. | Mover los defaults grandes a config o seeders si el catalogo sigue creciendo. |
| `app/Support/MapsLinkParser.php` | Extrae coordenadas desde links o textos de Maps. | Agregar tests con links reales y edge cases por proveedor. |
| `app/Support/YouTubeHelper.php` | Normaliza URLs de YouTube y obtiene titulos via oEmbed. | Cache ya existe; conviene fallback local si falla el servicio externo. |

### `app/ViewModels`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/ViewModels/Admin/DashboardViewData.php` | Prepara items y metricas del dashboard admin. | Corregir/normalizar estados si el dominio usa `draft`/`active` consistentemente. |
| `app/ViewModels/Admin/InvitationEditorViewData.php` | Arma la configuracion grande que consume el editor admin. | Dividir el payload por panel para reducir acoplamiento y peso de respuesta. |
| `app/ViewModels/Client/DashboardViewData.php` | Prepara tarjetas y estados del dashboard cliente. | Mantener el calculo de conteos en DB cuando el volumen suba. |
| `app/ViewModels/Client/InvitationDetailViewData.php` | Resume la invitacion y sus invitados para detalle cliente. | Si la coleccion crece, mover agregaciones a SQL. |
| `app/ViewModels/Client/InvitationExportViewData.php` | Construye stats, filas y datos para exportaciones. | Extraer generacion de PDFs/Excel a jobs o builders dedicados. |

### `app/Exports`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Exports/GuestsExport.php` | Exporta invitados a Excel usando una vista Blade. | Para grandes listas, considerar export por chunk o generacion asincrona. |

### `app/Providers`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Providers/AppServiceProvider.php` | Registra providers adicionales y fuerza HTTPS en ngrok. | Dejar solo bootstrap general; mover reglas de entorno a config si crecen. |
| `app/Providers/BladeServiceProvider.php` | Define directivas, componentes y `Blade::if` personalizados. | Revisar la directiva `@cache` para asegurar que el helper usado sea el correcto. |

### `app/Http/Middleware`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `app/Http/Middleware/EnsureUserIsAdmin.php` | Bloquea acceso al panel admin si no es admin. | Si hay mas roles, migrar a policies o un middleware de roles mas flexible. |
| `app/Http/Middleware/EnsureUserIsClient.php` | Redirige a login cliente si el usuario no es cliente. | Igual que arriba: una capa de autorizacion por roles mas formal ayudaria. |
| `app/Http/Middleware/CachePublicInvitations.php` | Ajusta headers de cache para invitaciones publicas. | Muy util; conviene alinear TTLs con la politica real de invalidez. |

## `bootstrap/`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `bootstrap/app.php` | Configura rutas, middleware y excepciones de la app. | Mantenerlo declarativo; si crece mucho, delegar aliases a un provider. |
| `bootstrap/providers.php` | Lista providers registrados por Laravel. | Añadir solo providers realmente necesarios para minimizar arranque. |
| `bootstrap/cache/.gitignore` | Evita versionar caches generados. | Correcto; mantenerlo asi. |

## `config/`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `config/app.php` | Configuracion general de la app. | Revisar `timezone`, `locale` y providers activos en produccion. |
| `config/auth.php` | Guardas, providers y passwords. | Si hay mas roles, formalizar guards o policies. |
| `config/cache.php` | Stores de cache, prefijos y drivers. | Si la app crece, pasar cache principal a Redis. |
| `config/cloudinary.php` | Variables de Cloudinary. | Validar estas variables en un health check de despliegue. |
| `config/database.php` | Conexiones SQL, Redis y opciones de DB. | Evitar consultas sin indice; revisar collation y `strict`. |
| `config/filesystems.php` | Discos local/public/s3 y symlink de storage. | Si hay mucho media, migrar assets publicos a S3/CDN. |
| `config/logging.php` | Canales y niveles de log. | En produccion usar rotacion y alertas para errores criticos. |
| `config/mail.php` | Transporte y settings de correo. | Usar provider transaccional y colas para envios masivos. |
| `config/optimizations.php` | Toggles de caché, DB, Blade y CDN. | Muy buen lugar para centralizar tuning por entorno. |
| `config/queue.php` | Conexiones y batch/failed jobs. | Si hay tareas largas, preferir workers dedicados y supervisados. |
| `config/services.php` | Credenciales de terceros. | Mantener aqui solo integraciones usadas de verdad. |
| `config/session.php` | Driver, lifetime y cookies de sesion. | Para escalabilidad real, preferir Redis sobre `database` si el trafico sube. |

## `database/`

### `database/factories`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `database/factories/UserFactory.php` | Fabrica usuarios para pruebas/seeds. | Agregar estados `admin()` y `client()` para simplificar tests. |

### `database/seeders`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `database/seeders/DatabaseSeeder.php` | Crea datos demo: admin, cliente, invitacion, invitados y aportes. | Separar seed demo de seed base para no mezclar datos de desarrollo con produccion. |
| `database/seeders/XvSofiaModuleData.php` | Payload demo completo de la invitacion `xv-sofia`. | Convertirlo en fixture JSON si el payload sigue creciendo. |

### `database/migrations`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Crea la tabla base `users`. | Correcto como migracion inicial; revisar columnas segun necesidades reales. |
| `database/migrations/0001_01_01_000001_create_cache_table.php` | Crea tabla de cache si se usa driver database. | Si el cache va a alto volumen, considerar Redis. |
| `database/migrations/0001_01_01_000002_create_jobs_table.php` | Crea tabla de jobs y failed jobs. | Muy util para exports, uploads y tareas diferidas. |
| `database/migrations/2026_06_07_030112_create_event_types_table.php` | Catalogo de tipos de evento. | Tabla pequena: cacheable y estable. |
| `database/migrations/2026_06_07_030113_create_features_table.php` | Catalogo de features. | Ideal para seed fijo y cache de lectura. |
| `database/migrations/2026_06_07_030114_create_plans_table.php` | Tabla de planes comerciales. | Ya fue removida despues; validar si aun tiene uso funcional. |
| `database/migrations/2026_06_07_030115_create_event_plan_feature_table.php` | Pivot entre tipo de evento, plan y feature. | Si ya no se usa, eliminar toda la rama de codigo relacionada para reducir complejidad. |
| `database/migrations/2026_06_07_030116_create_invitations_table.php` | Tabla central de invitaciones. | Muy importante: indices, estado e integridad de `slug`. |
| `database/migrations/2026_06_07_030117_create_invitation_features_table.php` | Pivot de features habilitadas por invitacion. | Asegurar unicidad por invitacion/feature si el dominio lo requiere. |
| `database/migrations/2026_06_07_030118_create_invitation_data_table.php` | Guarda el JSON por modulo. | Excelente candidato para versionado de schema del JSON por modulo. |
| `database/migrations/2026_06_07_030119_create_guests_table.php` | Tabla de invitados. | Tiene buen sentido de indices en `invitation_id` y `name`; revisar `status`. |
| `database/migrations/2026_06_07_030120_create_guest_contributions_table.php` | Tabla de contribuciones de invitados. | Si sube el trafico, indices por `invitation_id, type, created_at`. |
| `database/migrations/2026_06_07_030121_create_poll_votes_table.php` | Tabla de votos de encuestas. | Muy bien el `unique` por votante; revisar limpieza por expiracion. |
| `database/migrations/2026_06_12_000000_remove_plans_from_system.php` | Elimina planes y pivot relacionados. | Si ya no existe el concepto comercial, limpiar modelos, vistas y validaciones asociadas. |
| `database/migrations/2026_06_12_000001_add_performance_indexes.php` | Agrega indices para queries frecuentes. | Revisar que los indices realmente coincidan con las consultas actuales. |
| `database/migrations/2026_06_23_000000_remove_transporte_from_invitation_data.php` | Elimina datos obsoletos de transporte. | Bien como migracion de limpieza; documentar decisiones de producto para trazabilidad. |

## `public/`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `public/index.php` | Front controller de Laravel. | Correcto; no tocar salvo cambios de bootstrap. |
| `public/.htaccess` | Reglas Apache para enrutar solicitudes. | Mantenerlo si el hosting usa Apache. |
| `public/robots.txt` | Reglas de indexacion para bots. | Ajustar si la invitacion publica o admin requieren controles SEO distintos. |
| `public/favicon.ico` | Icono del sitio. | Reemplazarlo por un favicon real de marca si aun es placeholder. |
| `public/storage/` | Enlace simbolico a `storage/app/public`. | Correcto para assets publicos; considerar CDN si crece el trafico. |
| `public/build/` | Salida compilada por Vite. | No versionarla si es generada en deploy; servirla desde build pipeline. |

## `resources/`

### `resources/js`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/js/bootstrap.js` | Configura Axios con headers por defecto. | Agregar interceptores si hay manejo global de errores o refresh de token. |
| `resources/js/app.js` | Arranca Alpine y la app frontend. | Si el editor admin crece, dividir por entrypoints o modulos. |

### `resources/css`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/css/app.css` | Estilos globales de public, admin y client, con animaciones y temas. | Separar estilos por dominio para reducir peso y complejidad de mantenimiento. |

### `resources/views`

#### Raiz y auth

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/welcome.blade.php` | Vista default de Laravel. | Reemplazarla si ya no se usa como landing. |
| `resources/views/auth/login.blade.php` | Formulario de login. | Agregar UX de recuperacion de cuenta si el producto lo necesita. |

#### Layouts

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/layouts/admin.blade.php` | Layout base del panel admin. | Extraer nav, alerts y footer en componentes. |
| `resources/views/layouts/admin-editor.blade.php` | Layout del editor de invitaciones. | Muy grande por naturaleza; conviene mantenerlo modular. |
| `resources/views/layouts/client.blade.php` | Layout base del portal cliente. | Separar header y card shells reutilizables si crece la UI. |

#### Paginas admin

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/dashboard.blade.php` | Alias o vista del dashboard admin. | Evitar duplicidad con `pages/admin/dashboard.blade.php`. |
| `resources/views/pages/admin/dashboard.blade.php` | Dashboard admin principal. | Unificar con una sola ruta de vistas para no duplicar mantenimiento. |
| `resources/views/admin/guests/index.blade.php` | Lista y gestion de invitados de una invitacion. | Paginar si el total de invitados puede ser alto. |
| `resources/views/admin/invitations/create.blade.php` | Pantalla de crear invitacion. | Reutilizar el form parcial y minimizar logica duplicada. |
| `resources/views/admin/invitations/edit.blade.php` | Pantalla de editar invitacion. | Ideal para guardar cambios parciales/autosave si el editor es pesado. |
| `resources/views/admin/invitations/_form.blade.php` | Form compartido create/edit. | Muy bien como parcial; extraer mas piezas si aumenta la complejidad. |

#### Editor admin

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/invitations/editor/layout.blade.php` | Estructura principal del editor. | Dividir en bloques independientes para facilitar cambios visuales. |
| `resources/views/admin/invitations/editor/preview.blade.php` | Frame/panel de vista previa. | Ideal para cachearlo o hacerlo lazy si renderiza mucho. |
| `resources/views/admin/invitations/editor/script.blade.php` | Script principal del editor. | Si supera cierto tamano, moverlo a `resources/js/` como modulo dedicado. |
| `resources/views/admin/invitations/editor/sidebar.blade.php` | Barra lateral del editor. | Mantener la complejidad de UI fuera de Blade en lo posible. |

#### Paneles del editor

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/invitations/panels/general.blade.php` | Campos generales de la invitacion. | Puede beneficiarse de componentes de formulario reutilizables. |
| `resources/views/admin/invitations/panels/hero.blade.php` | Configura el hero/bienvenida. | Cargar imagenes solo bajo demanda. |
| `resources/views/admin/invitations/panels/ubicacion.blade.php` | Configura ubicacion y mapas. | Cachear geocoding y validar coordenadas. |
| `resources/views/admin/invitations/panels/itinerario.blade.php` | Edita el cronograma del evento. | Generar items dinamicamente desde schema, no a mano. |
| `resources/views/admin/invitations/panels/dress-code.blade.php` | Edita codigo de vestimenta. | Estandarizar arrays y presets para no repetir HTML. |
| `resources/views/admin/invitations/panels/destacados.blade.php` | Configura personas destacadas. | Reutilizar card components para listas repetidas. |
| `resources/views/admin/invitations/panels/galeria.blade.php` | Administra galeria de imagenes. | Subida por lotes y compresion previa mejorarian la UX. |
| `resources/views/admin/invitations/panels/musica.blade.php` | Configura musica de fondo. | Validar URLs con helper y cachear preview de metadata. |
| `resources/views/admin/invitations/panels/video.blade.php` | Configura video/save the date. | Guardar poster y metadata separadamente. |
| `resources/views/admin/invitations/panels/playlist.blade.php` | Configura playlist colaborativa. | Si hay muchos campos, convertirlo en subcomponente. |
| `resources/views/admin/invitations/panels/hashtag.blade.php` | Configura hashtag oficial. | Muy simple; puede permanecer como parcial. |
| `resources/views/admin/invitations/panels/encuestas.blade.php` | Configura encuestas. | Si crecen preguntas/opciones, usar builder con validacion fuerte. |
| `resources/views/admin/invitations/panels/regalos.blade.php` | Configura regalos, sobres y banco. | Mejorar seguridad ocultando datos sensibles segun rol. |
| `resources/views/admin/invitations/panels/post-evento.blade.php` | Configura contenido post-evento. | Puede cargar fotos desde media manager asincrono. |
| `resources/views/admin/invitations/panels/rsvp.blade.php` | Configura textos de RSVP. | Mantener mensajes cortos y versionables por plantilla. |
| `resources/views/admin/invitations/panels/countdown.blade.php` | Configura cuenta regresiva. | Evitar logica de tiempo duplicada en frontend y backend. |
| `resources/views/admin/invitations/panels/agendar.blade.php` | Configura boton de calendario. | Generar URL una sola vez desde backend. |
| `resources/views/admin/invitations/panels/fotomural.blade.php` | Configura fotomural. | Ideal para controles de moderacion si sube el uso. |
| `resources/views/admin/invitations/panels/estetica.blade.php` | Ajusta colores, tipografias y estilo. | Podria beneficiarse de presets y preview en vivo. |

#### Parciales admin

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/admin/partials/cloudinary-upload.blade.php` | Componente/partial para subir a Cloudinary. | Reutilizable; desacoplar de la logica del editor. |
| `resources/views/admin/partials/icon-picker.blade.php` | Selector de iconos. | Ideal para una lista cacheada o componente JS ligero. |

#### Client

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/client/dashboard.blade.php` | Dashboard cliente base. | Separar tarjetas y tablas en componentes. |
| `resources/views/client/invitation.blade.php` | Vista detalle de una invitacion para cliente. | Puede ser pesada; usar fragmentacion por seccion. |
| `resources/views/client/exports/guests-pdf.blade.php` | Plantilla PDF de invitados. | Mantenerla liviana porque DomPDF penaliza layouts complejos. |
| `resources/views/client/exports/guests-excel.blade.php` | Plantilla Excel en vista. | Reducir estilos excesivos para exportacion mas rapida. |
| `resources/views/client/exports/invitation-pdf.blade.php` | Plantilla PDF de invitacion. | Simplificar assets y fuentes para menor tiempo de render. |

#### Pages duplicadas

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/pages/client/dashboard.blade.php` | Variante del dashboard cliente. | Unificar con `resources/views/client/dashboard.blade.php` si no hay motivo de duplicidad. |
| `resources/views/pages/client/invitation.blade.php` | Variante del detalle cliente. | Igual: consolidar una sola fuente de verdad. |
| `resources/views/pages/client/exports/guests-pdf.blade.php` | Variante de export PDF de invitados. | Evitar duplicacion de plantillas. |
| `resources/views/pages/client/exports/guests-excel.blade.php` | Variante de export Excel. | Unificar con la ruta canonical si existe. |
| `resources/views/pages/client/exports/invitation-pdf.blade.php` | Variante de export de invitacion. | Consolidar para simplificar mantenimiento. |
| `resources/views/pages/admin/dashboard.blade.php` | Variante del dashboard admin. | Igual: dejar solo una version. |
| `resources/views/pages/invitations/templates/xv-premium.blade.php` | Variante de la plantilla publica premium. | Consolidar con `resources/views/invitations/templates/xv-premium.blade.php`. |

#### Invitaciones: template y parciales

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/invitations/templates/xv-premium.blade.php` | Template principal de la invitacion XV premium. | Mantenerlo como unica plantilla canonical y cargar secciones por parcial. |
| `resources/views/invitations/partials/countdown.blade.php` | Bloque de cuenta regresiva. | Ideal para reutilizar entre preview y publico. |
| `resources/views/invitations/partials/destacados.blade.php` | Bloque de destacados. | Reutilizar el mismo parcial en template y editor preview. |
| `resources/views/invitations/partials/dress-code.blade.php` | Bloque de dress code. | Mantener render defensivo ante arrays vacios. |
| `resources/views/invitations/partials/fotomural.blade.php` | Bloque de fotomural. | Cargar imagenes progresivamente. |
| `resources/views/invitations/partials/gallery-stack.blade.php` | Galeria en stack. | Buen lugar para lazy loading y skeletons. |
| `resources/views/invitations/partials/hashtag.blade.php` | Bloque de hashtag. | Muy simple; usarlo como fragmento reutilizable. |
| `resources/views/invitations/partials/icon.blade.php` | Render de iconos. | Centralizar aqui la fuente de iconos para consistencia. |
| `resources/views/invitations/partials/itinerary.blade.php` | Bloque de itinerario. | Si el cronograma crece, generar desde coleccion tipada. |
| `resources/views/invitations/partials/location.blade.php` | Bloque de ubicacion y mapas. | Aprovechar enlaces generados por backend. |
| `resources/views/invitations/partials/music-player.blade.php` | Reproductor de musica. | Iniciar audio con consentimiento del usuario. |
| `resources/views/invitations/partials/playlist.blade.php` | Bloque de playlist colaborativa. | Añadir estados vacio/cargando y limites de envio. |
| `resources/views/invitations/partials/polls.blade.php` | Bloque de encuestas. | Muy buen candidato para actualizacion parcial via JSON. |
| `resources/views/invitations/partials/post-event.blade.php` | Bloque post-evento. | Puede ser solo lectura y cacheable por largo tiempo. |
| `resources/views/invitations/partials/regalos.blade.php` | Bloque de regalos. | Tratar datos bancarios con cuidado y mostrar segun permiso. |
| `resources/views/invitations/partials/rsvp.blade.php` | Bloque de confirmacion RSVP. | Evitar duplicar logica de validacion en frontend. |
| `resources/views/invitations/partials/video.blade.php` | Bloque de video. | Lazy load del player para no penalizar la carga inicial. |
| `resources/views/invitations/partials/gallery-stack.blade.php` | Galeria estilo stack. | Optimizar imagenes con tamanos adecuados. |

#### Invitaciones: modules wrappers

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `resources/views/invitations/modules/countdown.blade.php` | Wrapper/modulo de cuenta regresiva. | Mantener consistencia con el parcial homologo. |
| `resources/views/invitations/modules/destacados.blade.php` | Wrapper/modulo de destacados. | Igual: que sea un wrapper del componente real. |
| `resources/views/invitations/modules/dress-code.blade.php` | Wrapper/modulo de dress code. | Evitar duplicar logica respecto a `partials`. |
| `resources/views/invitations/modules/fotomural.blade.php` | Wrapper/modulo de fotomural. | Mantener simple y reutilizable. |
| `resources/views/invitations/modules/gallery-stack.blade.php` | Wrapper de galeria. | Ideal para version unica de presentacion. |
| `resources/views/invitations/modules/hashtag.blade.php` | Wrapper de hashtag. | Mantenerlo alineado con parcial. |
| `resources/views/invitations/modules/icon.blade.php` | Wrapper de iconos. | Centralizar iconos para no duplicar SVGs. |
| `resources/views/invitations/modules/itinerary.blade.php` | Wrapper de itinerario. | Igual, preferir composicion. |
| `resources/views/invitations/modules/location.blade.php` | Wrapper de ubicacion. | Crear un unico source of truth para maps URL. |
| `resources/views/invitations/modules/music-player.blade.php` | Wrapper de musica. | Cargar solo cuando el modulo esta habilitado. |
| `resources/views/invitations/modules/playlist.blade.php` | Wrapper de playlist. | Mantenerlo ligero. |
| `resources/views/invitations/modules/polls.blade.php` | Wrapper de encuestas. | Muy bueno para incremental enhancement. |
| `resources/views/invitations/modules/post-event.blade.php` | Wrapper de post-evento. | Puede quedar casi estatico. |
| `resources/views/invitations/modules/regalos.blade.php` | Wrapper de regalos. | Validar campos antes de renderizar. |
| `resources/views/invitations/modules/rsvp.blade.php` | Wrapper de RSVP. | Conectar con endpoints JSON y fallback HTML. |
| `resources/views/invitations/modules/video.blade.php` | Wrapper de video. | Lazy load y poster optimizado. |

## `routes/`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `routes/web.php` | Rutas web principales: login, admin, client y public. | Ya esta bien segmentado; si crece, separarlo por dominio en mas archivos. |
| `routes/console.php` | Comandos de consola Artisan. | Agregar aqui tareas recurrentes simples; dejar lo pesado en jobs/commands. |

## `storage/`

| Carpeta | Que hace | Sugerencia |
| --- | --- | --- |
| `storage/app/` | Archivos privados y publicos administrados por Laravel. | Verificar politicas de retencion y limpieza. |
| `storage/framework/` | Cache, vistas compiladas, sesiones y testing cache. | Mantener limpio en deploys y no versionar contenido generado. |
| `storage/logs/` | Logs de la aplicacion. | Configurar rotacion y alertas si el proyecto va a produccion seria. |

## `tests/`

| Archivo | Que hace | Sugerencia |
| --- | --- | --- |
| `tests/TestCase.php` | Base de pruebas Laravel. | Agregar helpers comunes si aumentan los tests de feature. |
| `tests/Feature/ExampleTest.php` | Test basico de ejemplo para la ruta raiz. | Reemplazarlo por pruebas reales del flujo public/admin/client. |
| `tests/Unit/ExampleTest.php` | Test unitario de ejemplo. | Empezar a cubrir servicios puros: cache, parsing, normalizacion, exports. |

## Recomendaciones de escalabilidad y rendimiento

1. Mover procesos pesados a cola: exportaciones PDF/Excel, redimensionado de imagenes, warmups y notificaciones.
2. Centralizar invalidacion de caché con eventos de dominio: invitacion actualizada, contribucion agregada, voto emitido, RSVP confirmado.
3. Revisar indices y consultas: `guests`, `guest_contributions`, `poll_votes`, `invitation_data` e `invitations` ya son los puntos calientes.
4. Evitar duplicacion de vistas entre `resources/views/*` y `resources/views/pages/*`; consolidar una sola ruta canonical.
5. Separar frontend por dominio si el bundle crece: admin, client y public pueden compilarse de forma mas independiente.
6. Tipar mejor la capa de modulos: un schema versionado por modulo ayudaria a validar JSON y a evolucionar sin romper vistas.
7. Guardar y servir media desde CDN cuando haya volumen real; local esta bien para dev, pero escala peor.
8. Agregar mas pruebas de integracion sobre rutas criticas: login, dashboard, preview, RSVP, votos y uploads.
9. Introducir policies o un sistema de permisos mas formal si aparecen mas roles o subroles.
10. Crear un README propio con arquitectura, flujos, convenciones y comandos frecuentes para onboardings mas rapidos.

