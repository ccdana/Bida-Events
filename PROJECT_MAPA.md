# Mapa del proyecto Bida Events

Guía del repositorio: qué hace cada archivo, cómo fluye la información y qué conviene mejorar.
Está escrita para que alguien que nunca vio el proyecto pueda ubicarse, y para que quien ya lo conoce
sepa dónde tocar sin romper nada.

- **Qué es:** una aplicación Laravel 12 que crea, edita y publica invitaciones digitales de eventos
  (bodas, bautizos, cumpleaños y XV años).
- **Cómo se usa:** el equipo arma la invitación en un editor, entrega un enlace general y, según el
  paquete, un enlace personal por invitado. Los invitados confirman asistencia, votan encuestas,
  sugieren canciones y suben fotos desde el celular.
- **Stack:** PHP 8.4, Laravel 12, Blade, Alpine 3, Tailwind 4, Vite 7, MySQL/MariaDB (SQLite en
  pruebas), Cloudinary para medios, DomPDF y Maatwebsite Excel para reportes.

## Índice

1. [Cómo se ejecuta](#1-cómo-se-ejecuta)
2. [Las cuatro superficies](#2-las-cuatro-superficies)
3. [Flujo de datos de una invitación](#3-flujo-de-datos-de-una-invitación)
4. [Rutas](#4-rutas)
5. [Mapa de archivos](#5-mapa-de-archivos)
6. [Estado de la normalización de datos](#6-estado-de-la-normalización-de-datos)
7. [Sugerencias de mejora](#7-sugerencias-de-mejora)
8. [Hoja de ruta sugerida](#8-hoja-de-ruta-sugerida)
9. [Cómo mantener este documento](#9-cómo-mantener-este-documento)

---

## 1. Cómo se ejecuta

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed        # admin + tipos de evento + invitaciones de muestra
npm run build                     # o: npm run dev
php artisan test
php artisan optimize:clear        # tras cambiar vistas, rutas o configuración
```

En este equipo (Laragon) los binarios son
`C:\laragon\bin\php\php-8.4.24-Win32-vs17-x64\php.exe` y `C:\laragon\bin\nodejs\node-v22\node.exe`,
y el sitio responde en `http://bida-events.test`.

| Comando | Para qué |
| --- | --- |
| `php artisan migrate:status` | Ver qué migraciones faltan |
| `php artisan invitations:migrate-json --dry-run` | Simular el paso de módulos JSON a tablas |
| `php artisan invitations:migrate-json --invitation=xv-isabella` | Migrar una sola invitación |
| `php artisan db:seed --class=ShowcaseInvitationsSeeder` | Rehacer las invitaciones de muestra |
| `php artisan optimize:clear` | Limpiar caché de vistas, rutas y configuración |
| `php artisan invitations:purge-contributions --dry-run` | Ver qué fotos viejas se borrarían |
| `php artisan bida:medir --guardar` | Medir las pantallas públicas y guardar el resultado |
| `php artisan queue:work` | Procesar los archivos que piden los clientes (en producción, con supervisor) |
| `php artisan test` | Suite completa |
| `npm run build` | Compilar CSS y JS |

---

## 2. Las cuatro superficies

| Superficie | Quién entra | Rutas | Vistas |
| --- | --- | --- | --- |
| **Sitio público** | Cualquiera | `/`, `/login` | `home.blade.php`, `auth/login.blade.php`, `layouts/site.blade.php` |
| **Invitación pública** | Invitados con enlace | `/p/{slug}`, `/p/{slug}/i/{token}` | `invitations/templates/*` |
| **Muestras interactivas** | Visitantes de la home | `/muestra/{slug}` | Las mismas plantillas, en modo muestra |
| **Panel admin** | Usuario con `is_admin` | `/admin/**` | `admin/**`, `layouts/admin*.blade.php` |
| **Panel cliente** | Dueño de la invitación | `/client/**` | `client/**`, `layouts/client.blade.php` |

---

## 3. Flujo de datos de una invitación

1. **Creación.** El admin crea la invitación (`Admin\InvitationController@store`) y, si hace falta, el
   usuario cliente (`ClientCredentials`).
2. **Edición.** El editor (`admin/invitations/editor/*`) envía todos los módulos como JSON.
   `UpdateInvitationRequest` los valida con las reglas de `InvitationModuleRules`.
3. **Guardado.** `InvitationModuleService::syncAllModules` escribe cada módulo. Los módulos ya
   normalizados (configuración, itinerario, galería y encuestas) pasan además por
   `InvitationStructuredDataService`, que los guarda en tablas propias.
4. **Invalidación de caché.** El evento `InvitationUpdated` dispara `RefreshInvitationCache`, que
   limpia y recalienta lo cacheado de esa invitación.
5. **Publicación.** `Public\InvitationController@show` arma los módulos (tablas primero, JSON como
   respaldo), resuelve plantilla, encuestas, playlist y fotomural, y renderiza la plantilla.
6. **Interacción del invitado.** RSVP, votos, canciones y fotos entran por
   `Public\RsvpController` y `Public\ContributionController`, con límite de peticiones por ruta.
   Cada aporte dispara eventos que vuelven a invalidar la caché.
7. **Seguimiento.** El cliente ve sus invitados en su panel y exporta Excel o PDF.

---

## 4. Rutas

Todas están en `routes/web.php`.

| Método y ruta | Nombre | Controlador | Notas |
| --- | --- | --- | --- |
| `GET /` | `home` | `HomeController` | Portada pública |
| `GET /muestra/{slug}` | `invitation.demo` | `Public\InvitationController@demo` | Solo las invitaciones de `bida.demo_invitations`; nada se guarda; `noindex` |
| `GET /dashboard` | `dashboard` | Cierre en rutas | Redirige a admin o cliente según el rol |
| `GET/POST /login`, `POST /logout` | `login`, `logout` | `Auth\LoginController` | `throttle:login` |
| `GET /p/{slug}` | `invitation.show` | `Public\InvitationController@show` | Invitación general |
| `GET /p/{slug}/i/{token}` | `invitation.guest` | `Public\InvitationController@show` | Invitación personal |
| `POST /p/{slug}/i/{token}/confirm` | `invitation.rsvp` | `Public\RsvpController@confirm` | `throttle:invitation-rsvp` |
| `GET/POST /p/{slug}/playlist` | `invitation.playlist*` | `Public\ContributionController` | `throttle:invitation-songs` |
| `GET/POST /p/{slug}/fotomural` | `invitation.fotomural*` | `Public\ContributionController` | `throttle:invitation-photos` |
| `POST /p/{slug}/polls/{pollId}/vote` | `invitation.poll.vote` | `Public\ContributionController@votePoll` | `throttle:invitation-votes` |
| `/admin/**` | `admin.*` | `Admin\*` | Middleware `auth` + `admin` |
| `/client/**` | `client.*` | `Client\*` | Middleware `auth` + `client`, con policies |

El grupo `/p` pasa por el middleware `cache.public.invitations`. La ruta `/muestra` queda fuera a
propósito y responde `no-store`.

---

## 5. Mapa de archivos

### 5.1 Raíz

| Archivo | Qué hace |
| --- | --- |
| `artisan` | Entrada de los comandos de consola |
| `composer.json` / `composer.lock` | Dependencias PHP y scripts (`setup`, `dev`, `test`) |
| `package.json` / `package-lock.json` | Dependencias de frontend y scripts de Vite |
| `vite.config.js` | Compilación de `app.css` y `app.js`; separa video.js, lottie-web y motion en chunks propios |
| `phpunit.xml` | Configuración de pruebas (base SQLite en memoria y entorno de test) |
| `.env` / `.env.example` | Configuración por entorno. `.env` nunca se versiona; toda variable nueva se documenta en el ejemplo |
| `README.md` | Presentación corta del proyecto |
| `PROJECT_MAPA.md` | Este documento |
| `docs/rendimiento.md` | Última medición de tiempos, consultas y peso (la genera `php artisan bida:medir --guardar`) |
| `.claude/skills/taste-skill/SKILL.md` | Guía de criterio visual usada al diseñar el sitio |

### 5.2 Arranque y configuración

| Archivo | Qué hace |
| --- | --- |
| `bootstrap/app.php` | Arma la aplicación: rutas web y de consola, ruta de salud `/up` y alias de middleware (`admin`, `client`, `cache.public.invitations`) |
| `bootstrap/providers.php` | Lista de proveedores propios (`AppServiceProvider`) |
| `config/app.php` | Nombre, entorno, zona horaria e idioma (`es`) |
| `config/auth.php` | Guard de sesión y proveedor de usuarios |
| `config/bida.php` | **Datos públicos del negocio**: marca, ciudad, WhatsApp, correo, Instagram, Facebook, TikTok, invitación de portada, invitaciones de muestra, eventos que rotan, tipos de evento, fotos del sitio y paquetes con precios |
| `config/cache.php` | Almacenes de caché disponibles |
| `config/cloudinary.php` | Credenciales y opciones de Cloudinary |
| `config/database.php` | Conexiones MySQL/MariaDB/SQLite |
| `config/filesystems.php` | Discos locales y públicos |
| `config/logging.php` | Canales de log |
| `config/mail.php` | Envío de correo |
| `config/optimizations.php` | Interruptores propios: caché de invitaciones y TTL, aviso de lecturas JSON, límites por minuto de login, RSVP, canciones, fotos y votos, cabeceras HTTP de caché y CDN |
| `config/queue.php` | Colas y conexión por defecto |
| `config/security.php` | Proxies de confianza (`TRUSTED_PROXIES`) y política de contenido (`CSP_ENABLED`, `CSP_ENFORCE`) |
| `config/services.php` | Credenciales de terceros |
| `config/session.php` | Driver, duración y cookies de sesión |
| `routes/web.php` | Todas las rutas (tabla anterior) |
| `routes/console.php` | Solo el comando de ejemplo `inspire` |

### 5.3 Controladores

| Archivo | Qué hace |
| --- | --- |
| `app/Http/Controllers/Controller.php` | Controlador base |
| `HomeController.php` | Portada: paquetes con enlace de WhatsApp prellenado, invitación de la portada y lista de muestras (con su URL interactiva y su URL de apertura automática) |
| `Auth/LoginController.php` | Formulario de acceso, login con límite de intentos, regeneración de sesión y salida |
| `Public/InvitationController.php` | Renderiza la invitación pública: carga módulos (tablas o JSON), resultados de encuestas, playlist, fotomural y URL de calendario. También sirve `/muestra/{slug}` con un invitado ficticio que no se guarda |
| `Public/RsvpController.php` | Confirmación de asistencia por invitado y generación del pase |
| `Public/ContributionController.php` | Lista y recibe canciones y fotos, y registra votos de encuestas |
| `Admin/DashboardController.php` | Panel del administrador con el estado de las invitaciones |
| `Admin/InvitationController.php` | Crear, editar y actualizar invitaciones; crear el usuario cliente |
| `Admin/GuestController.php` | Alta, edición y baja de invitados de una invitación |
| `Admin/MediaUploadController.php` | Subida de imágenes y videos a Cloudinary desde el editor |
| `Admin/MapsController.php` | Búsqueda y resolución de enlaces de Google Maps |
| `Admin/PreviewController.php` | Vista previa del editor sin guardar (usa la sesión) |
| `Client/DashboardController.php` | Panel del cliente: sus invitaciones e invitados |
| `Client/ExportController.php` | Exporta invitados en Excel y PDF, y la invitación en PDF |
| `Client/ContributionController.php` | El cliente oculta o vuelve a mostrar una foto o una canción de sus invitados (no borra nada) |
| `Client/ExportController.php` | Pide un archivo, consulta su estado y lo descarga cuando está listo |

### 5.4 Middleware, validación y autorización

| Archivo | Qué hace |
| --- | --- |
| `app/Http/Middleware/EnsureUserIsAdmin.php` | Solo deja pasar a usuarios con `is_admin` |
| `app/Http/Middleware/EnsureUserIsClient.php` | Solo deja pasar a clientes |
| `app/Http/Middleware/CachePublicInvitations.php` | Cabeceras de caché de las invitaciones: `public` para el enlace general, `private` para el personal y `no-store` si la caché está apagada |
| `app/Http/Middleware/SecurityHeaders.php` | Cabeceras de seguridad de todas las respuestas y política de contenido (en modo reporte hasta activar `CSP_ENFORCE`) |
| `app/Http/Requests/Admin/Invitation/StoreInvitationRequest.php` | Validación al crear una invitación |
| `app/Http/Requests/Admin/Invitation/UpdateInvitationRequest.php` | Validación al guardar el editor completo |
| `app/Http/Requests/Admin/Invitation/Concerns/ValidatesInvitationModules.php` | Reglas compartidas para los módulos que llegan como JSON |
| `app/Http/Requests/Admin/Invitation/StoreClientRequest.php` | Validación al crear el usuario cliente |
| `app/Http/Requests/Admin/Guest/StoreGuestRequest.php` | Validación al crear un invitado |
| `app/Http/Requests/Admin/Guest/UpdateGuestRequest.php` | Validación al editar un invitado |
| `app/Policies/InvitationPolicy.php` | Permisos por invitación: `before` (el admin puede todo), `view`, `export`, `update`, `manageGuests` |

### 5.5 Modelos

| Archivo | Qué representa |
| --- | --- |
| `User.php` | Administradores y clientes (`is_admin`, usuario y contraseña de acceso) |
| `Invitation.php` | La invitación: dueño, tipo de evento, plantilla, slug, fecha, estado y vencimiento. Concentra las relaciones (`settings`, `itineraryItems`, `galleryImages`, `polls`, `guests`, `contributions`, `pollVotes`, `modulesData`, `features`), los scopes `active`, `published` y `withAllData`, y el accessor `modules` con su caché en memoria |
| `InvitationData.php` | Una fila por módulo con su JSON (`json_data`): es el formato antiguo y hoy actúa como respaldo |
| `InvitationSetting.php` | Configuración normalizada: plantilla, colores, tipografías y visibilidad de módulos |
| `InvitationItineraryItem.php` | Cada momento del itinerario, con orden |
| `InvitationGalleryImage.php` | Cada imagen, por colección (galería, portada, post-evento) y orden |
| `InvitationPoll.php` | Pregunta de encuesta, con su clave estable y sus opciones |
| `InvitationPollOption.php` | Opción de una encuesta |
| `PollVote.php` | Voto: invitación, encuesta (por su fila), opción y votante |
| `InvitationLocation.php` | Sede del evento: nombre, dirección, coordenadas, mapa y foto |
| `InvitationFeaturedPerson.php` | Personas destacadas por grupo (chambelanes, damitas, padrinos…) |
| `InvitationDressCodeItem.php` | Código de vestimenta: sugerencias, colores permitidos y qué evitar |
| `InvitationGiftOption.php` | Opciones de la mesa de regalos |
| `InvitationMedia.php` | Catálogo de medios: canción de fondo y video |
| `InvitationExport.php` | Pedido de archivo del cliente: tipo, estado y ruta del archivo generado |
| `Guest.php` | Invitado: nombre, pases asignados y confirmados, estado, mesa, restricciones y token del enlace personal |
| `GuestContribution.php` | Aporte del invitado: canción o foto del fotomural |
| `EventType.php` | Catálogo de tipos de evento |
| `Feature.php` | Catálogo de módulos disponibles (tabla pivote `invitation_features`) |

### 5.6 Servicios

| Archivo | Qué hace |
| --- | --- |
| `Services/InvitationModuleService.php` | Corazón de los módulos: normaliza lo que llega del editor, resuelve lo que se muestra, guarda módulo por módulo, calcula resultados de encuestas, arma la URL de Google Calendar y genera tokens de invitado |
| `Services/InvitationStructuredDataService.php` | Puente entre JSON y tablas: dice si una invitación ya está migrada, hidrata desde tablas, sincroniza, verifica diferencias y arma las filas de configuración, itinerario, galería y encuestas |
| `Services/InvitationCacheService.php` | Encendido/apagado de la caché, TTL, invalidación por invitación y olvido puntual de encuestas, playlist y fotomural |
| `Services/InvitationPreviewSession.php` | Guarda en sesión el borrador del editor para la vista previa |
| `Services/MediaUploadService.php` | Valida y sube imágenes y videos a Cloudinary con transformaciones por contexto (portada, galería, ubicación, dress code, video, fotomural, QR bancario) |

### 5.7 Clases de apoyo (`app/Support`)

| Archivo | Qué hace |
| --- | --- |
| `InvitationPage.php` | Objeto que usa toda plantilla pública: paleta, fuentes, módulos visibles, orden de secciones, nombres, edad, iniciales y textos de la plantilla |
| `InvitationTemplates.php` | Catálogo de las cuatro plantillas: etiqueta, descripción, evento, textos propios y orden de módulos |
| `InvitationDefaults.php` | Códigos de módulos, pestañas del editor, visibilidad por defecto, módulos vacíos y resolución de plantilla |
| `InvitationModuleRules.php` | Esquema de validación de cada módulo del editor |
| `ItineraryIcons.php` | Catálogo de íconos del itinerario |
| `CloudinaryImage.php` | Arma variantes responsivas (`f_auto`, `q_auto`, ancho) y `srcset` |
| `SiteImage.php` | Fotos del sitio público; si el archivo no existe usa un marcador |
| `MapsLinkParser.php` | Interpreta enlaces de Google Maps y extrae coordenadas |
| `YouTubeHelper.php` | Detecta enlaces de YouTube y da formato a las canciones sugeridas |
| `ClientCredentials.php` | Genera usuario y contraseña del cliente al crearlo desde el editor |
| `Pdf/PdfAssets.php` | Incrusta fuentes e imágenes en los PDF, porque DomPDF no descarga archivos remotos |

### 5.8 Vistas de datos, exportaciones, eventos y consola

| Archivo | Qué hace |
| --- | --- |
| `ViewModels/Admin/DashboardViewData.php` | Cifras y listas del panel admin |
| `ViewModels/Admin/InvitationEditorViewData.php` | Todo lo que necesita el editor en una sola estructura |
| `ViewModels/Client/DashboardViewData.php` | Resumen del panel del cliente |
| `ViewModels/Client/InvitationDetailViewData.php` | Detalle de una invitación para el cliente |
| `ViewModels/Client/GuestReportData.php` | Cifras y grupos del reporte de invitados (Excel y PDF) |
| `ViewModels/Client/InvitationPrintData.php` | Contenido de la invitación impresa en PDF |
| `Exports/GuestReportExport.php` | Libro de Excel con varias hojas |
| `Exports/Sheets/ReportSheet.php` | Base común de las hojas (estilos y utilidades) |
| `Exports/Sheets/SummarySheet.php` | Hoja de resumen |
| `Exports/Sheets/GuestListSheet.php` | Hoja con la lista de invitados |
| `Exports/Sheets/PendingGuestsSheet.php` | Hoja de pendientes por confirmar |
| `Exports/Sheets/DietarySheet.php` | Hoja de restricciones alimentarias |
| `Events/InvitationUpdated.php` | Se emite al guardar una invitación |
| `Events/GuestContributionSubmitted.php` | Se emite al recibir una canción o una foto |
| `Events/PollVoteSubmitted.php` | Se emite al registrar un voto |
| `Listeners/RefreshInvitationCache.php` | Escucha los tres eventos e invalida la caché de forma síncrona |
| `Providers/AppServiceProvider.php` | Registro de servicios, límites de peticiones y ajustes globales |
| `Providers/BladeServiceProvider.php` | Directivas y componentes propios de Blade |
| `Console/Commands/MigrateInvitationJsonModules.php` | Comando `invitations:migrate-json`, con `--dry-run`, `--invitation` y `--force` |
| `Console/Commands/PurgeOldContributions.php` | Comando `invitations:purge-contributions`: borra fotos de eventos viejos (con su archivo en Cloudinary) y exportaciones vencidas |
| `Console/Commands/MeasurePerformance.php` | Comando `bida:medir`: tiempo, consultas, memoria y peso del HTML de las pantallas públicas |
| `Jobs/GenerateInvitationExport.php` | Arma en segundo plano el Excel o el PDF que pidió el cliente |

### 5.9 Base de datos

**Migraciones** (`database/migrations`), en orden:

| Archivo | Qué crea o cambia |
| --- | --- |
| `0001_01_01_000000_create_users_table` | Usuarios, restablecimiento de contraseña y sesiones |
| `0001_01_01_000001_create_cache_table` | Caché en base de datos |
| `0001_01_01_000002_create_jobs_table` | Colas y trabajos fallidos |
| `2026_06_07_030112_create_event_types_table` | Tipos de evento |
| `2026_06_07_030113_create_features_table` | Catálogo de módulos |
| `2026_06_07_030114_create_plans_table` | Planes (eliminados después) |
| `2026_06_07_030115_create_event_plan_feature_table` | Pivote de planes y módulos |
| `2026_06_07_030116_create_invitations_table` | Invitaciones |
| `2026_06_07_030117_create_invitation_features_table` | Módulos activos por invitación |
| `2026_06_07_030118_create_invitation_data_table` | Módulos como JSON |
| `2026_06_07_030119_create_guests_table` | Invitados y su token |
| `2026_06_07_030120_create_guest_contributions_table` | Canciones y fotos |
| `2026_06_07_030121_create_poll_votes_table` | Votos de encuestas |
| `2026_06_12_000000_remove_plans_from_system` | Quita los planes |
| `2026_06_12_000001_add_performance_indexes` | Primeros índices |
| `2026_06_23_000000_remove_transporte_from_invitation_data` | Retira un módulo descontinuado |
| `2026_09_12_000001_create_invitation_settings_table` | Configuración normalizada |
| `2026_09_12_000002_create_invitation_itinerary_items_table` | Itinerario normalizado |
| `2026_09_12_000003_create_invitation_gallery_images_table` | Galería normalizada |
| `2026_09_12_000004_create_invitation_polls_table` | Encuestas y opciones normalizadas |
| `2026_09_12_000005_add_invitation_poll_id_to_poll_votes_table` | Relación real del voto con la encuesta |
| `2026_09_12_000006_refine_performance_indexes` | Ajuste de índices |
| `2026_09_12_000007_normalize_invitation_template_names` | Unifica los nombres de plantilla |
| `2026_09_13_000001_add_username_and_access_password_to_users_table` | Acceso del cliente |
| `2026_09_13_000002_simplify_invitation_status` | Simplifica los estados de la invitación |
| `2026_09_15_000001_drop_access_password_from_users_table` | La contraseña del cliente deja de guardarse descifrable |
| `2026_09_15_000002_add_moderation_status_to_guest_contributions_table` | Permite ocultar fotos y canciones sin borrarlas |
| `2026_09_15_000003_create_invitation_locations_table` | Sedes del evento |
| `2026_09_15_000004_create_invitation_featured_people_table` | Personas destacadas por grupo |
| `2026_09_15_000005_create_invitation_dress_code_items_table` | Código de vestimenta |
| `2026_09_15_000006_create_invitation_gift_options_table` | Opciones de regalo |
| `2026_09_15_000007_create_invitation_media_table` | Canción y video de la invitación |
| `2026_09_15_000008_drop_textual_poll_id_from_poll_votes_table` | El voto apunta a la encuesta por su fila |
| `2026_09_15_000009_create_invitation_exports_table` | Pedidos de Excel y PDF generados en segundo plano |

**Semillas y datos de ejemplo:**

| Archivo | Qué hace |
| --- | --- |
| `seeders/DatabaseSeeder.php` | Crea el administrador y llama a los demás |
| `seeders/EventTypeSeeder.php` | Tipos de evento base |
| `seeders/ShowcaseInvitationsSeeder.php` | Recrea las cuatro invitaciones de muestra completas, con invitados, aportes y votos; es idempotente |
| `seeders/showcase/xv-isabella.php` | Datos de la muestra de XV años |
| `seeders/showcase/boda-camila-andres.php` | Datos de la muestra de boda |
| `seeders/showcase/bautizo-emilia.php` | Datos de la muestra de bautizo |
| `seeders/showcase/cumple-daniela-30.php` | Datos de la muestra de cumpleaños |
| `seeders/XvSofiaModuleData.php` | Módulos de ejemplo de XV usados por las pruebas |
| `seeders/BodaJardinDemoSeeder.php` | Módulos de ejemplo de boda |
| `seeders/BautizoCieloDemoSeeder.php` | Módulos de ejemplo de bautizo |
| `seeders/CumpleFiestaDemoSeeder.php` | Módulos de ejemplo de cumpleaños |
| `factories/UserFactory.php` | Usuarios de prueba |

### 5.10 Vistas

**Layouts y componentes compartidos**

| Archivo | Qué hace |
| --- | --- |
| `layouts/site.blade.php` | Layout del sitio público y del login |
| `layouts/admin.blade.php` | Layout del panel admin |
| `layouts/admin-editor.blade.php` | Layout del editor, con las fuentes elegibles |
| `layouts/client.blade.php` | Layout del panel del cliente |
| `layouts/partials/panel-head.blade.php` | Cabecera común de los paneles |
| `layouts/partials/panel-actions.blade.php` | Acciones de la cabecera (tema y salir) |
| `layouts/partials/theme-script.blade.php` | Tema claro/oscuro compartido; sin preferencia sigue al sistema |
| `layouts/partials/theme-toggle.blade.php` | Botón de cambio de tema |
| `layouts/partials/pagination.blade.php` | Paginación de los paneles (anterior, posición y siguiente) |
| `components/brand/logo.blade.php` | Logo con el nombre de la marca |
| `components/brand/mark.blade.php` | Isotipo suelto |
| `components/site/image.blade.php` | Foto del sitio con tamaños y respaldo |
| `welcome.blade.php` | Vista por defecto de Laravel; hoy no la usa ninguna ruta |

**Sitio público**

| Archivo | Qué hace |
| --- | --- |
| `home.blade.php` | Portada completa: navegación, encabezado con el teléfono que recorre las aperturas, franja de tipos de evento, servicios en filas numeradas, sección de plantillas con la muestra interactiva, pasos de trabajo, precios en columnas, preguntas frecuentes, contacto y pie |
| `auth/login.blade.php` | Acceso al panel, con el mismo rotador de la portada |

**Invitación pública: plantillas**

| Archivo | Qué hace |
| --- | --- |
| `invitations/templates/xv-premium.blade.php` | XV Años Elegante: telones de apertura, destellos dorados y marco editorial |
| `invitations/templates/boda-jardin.blade.php` | Boda Jardín: sobre con sello de cera, ramas y pétalos |
| `invitations/templates/bautizo-cielo.blade.php` | Bautizo Cielo: pila bautismal con jarra, nubes, palomas y burbujas |
| `invitations/templates/cumple-fiesta.blade.php` | Cumpleaños Fiesta: pastel con velas, confeti, globos y banderines |

**Estructura común de la invitación (`invitations/partials/shell`)**

| Archivo | Qué hace |
| --- | --- |
| `shell/head.blade.php` | Metadatos, assets, fuentes, paleta del evento y banderas del modo muestra |
| `shell/cover-script.blade.php` | Deja la portada en espera hasta que el invitado toca; en la muestra de la portada abre sola |
| `shell/cover-component.blade.php` | Lógica compartida de apertura de XV y bautizo (etapas y tiempos) |
| `shell/nav.blade.php` | Menú de secciones a pantalla completa |
| `shell/modules.blade.php` | Arma las secciones en el orden que define cada plantilla |
| `shell/footer.blade.php` | Pie con nombre, fecha, volver arriba y crédito |
| `shell/scripts.blade.php` | Utilidades compartidas: calendario, copiar al portapapeles y revelado al hacer scroll |

**Módulos de la invitación**

| Archivo | Qué hace |
| --- | --- |
| `partials/hero.blade.php` | Portada genérica (la usa XV) |
| `partials/guest-banner.blade.php` | Saludo al invitado con sus pases y estado |
| `partials/countdown.blade.php` | Cuenta regresiva y botón de agendar |
| `partials/location.blade.php` | Ubicación, mapa y cómo llegar |
| `partials/itinerary.blade.php` | Itinerario con línea de tiempo |
| `partials/itinerary-icon.blade.php` | Ícono de cada momento |
| `partials/rsvp.blade.php` | Confirmación de asistencia y pase con QR |
| `partials/dress-code.blade.php` | Código de vestimenta, desplegable por sugerencia |
| `partials/destacados.blade.php` | Padrinos y personas destacadas, por grupos |
| `partials/gallery-stack.blade.php` | Galería en pila de fotos |
| `partials/video.blade.php` | Video del evento |
| `partials/regalos.blade.php` | Mesa de regalos y datos bancarios |
| `partials/playlist.blade.php` | Playlist colaborativa, paginada de cinco en cinco |
| `partials/polls.blade.php` | Encuestas, una pregunta a la vez |
| `partials/hashtag.blade.php` | Hashtag del evento, ajustado al ancho |
| `partials/fotomural.blade.php` | Fotomural en vivo |
| `partials/post-event.blade.php` | Mensaje y galería después del evento |
| `partials/section-header.blade.php` | Encabezado de sección (con variante compacta) |
| `partials/icon.blade.php` | Íconos SVG simples |
| `partials/lottie-icon.blade.php` / `lottie-framed-icon.blade.php` | Íconos animados, sueltos o en medallón |
| `partials/music-player.blade.php` | Reproductor de música; en las muestras nunca arranca solo |
| `partials/particles.blade.php` | Partículas de fondo |

**Piezas por plantilla**

| Archivo | Qué hace |
| --- | --- |
| `partials/xv/intro.blade.php` | Telones de terciopelo hechos de franjas que se recogen con su lazo |
| `partials/xv/crown.blade.php` | Corona que se dibuja |
| `partials/xv/glints.blade.php` | Destellos dorados de fondo |
| `partials/boda/cover.blade.php` | Sobre con sello de cera que se abre por etapas |
| `partials/boda/hero.blade.php` | Portada con foto en arco |
| `partials/boda/branch.blade.php` | Rama que se dibuja |
| `partials/boda/ambient.blade.php` | Pétalos y fondo |
| `partials/bautizo/intro.blade.php` | Pila bautismal y jarra que vierte agua; la invitación aparece en la onda |
| `partials/bautizo/hero.blade.php` | Portada con medallón y paloma |
| `partials/bautizo/dove.blade.php` | Paloma con rama de olivo |
| `partials/bautizo/cloud.blade.php` / `clouds-band.blade.php` | Nubes sueltas y franja de nubes |
| `partials/bautizo/ambient.blade.php` | Nubes, palomas, burbujas y destellos de fondo |
| `partials/cumple/intro.blade.php` | Pastel de dos pisos con velas que se soplan |
| `partials/cumple/hero.blade.php` | Portada con la edad gigante |
| `partials/cumple/balloon.blade.php` / `bunting.blade.php` | Globos y banderines |
| `partials/cumple/ambient.blade.php` | Confeti y fondo de fiesta |

**Panel admin y editor**

| Archivo | Qué hace |
| --- | --- |
| `admin/dashboard.blade.php` | Listado y estado de las invitaciones |
| `admin/invitations/create.blade.php` / `edit.blade.php` / `_form.blade.php` | Alta y edición de la invitación |
| `admin/invitations/editor/layout.blade.php` | Estructura del editor, con recorte de imágenes |
| `admin/invitations/editor/sidebar.blade.php` | Datos principales enlazados a Alpine |
| `admin/invitations/editor/preview.blade.php` | Vista previa embebida |
| `admin/invitations/editor/script.blade.php` | Lógica del editor: estado, subidas y guardado |
| `admin/invitations/panels/*.blade.php` | Un panel por módulo: general, hero, estética, countdown, agendar, ubicación, itinerario, dress code, destacados, galería, video, regalos, rsvp, playlist, encuestas, hashtag, fotomural, música y post-evento |
| `admin/partials/cloudinary-upload.blade.php` | Campo de subida a Cloudinary |
| `admin/partials/date-field.blade.php` | Campo de fecha y hora |
| `admin/partials/icon-picker.blade.php` / `itinerary-icon-picker.blade.php` | Selectores de íconos |
| `admin/partials/panel-intro.blade.php` | Encabezado explicativo de cada panel |
| `admin/guests/index.blade.php` | Gestión de invitados |

**Panel del cliente**

| Archivo | Qué hace |
| --- | --- |
| `client/dashboard.blade.php` | Resumen de sus invitaciones |
| `client/invitation.blade.php` | Detalle con invitados y confirmaciones |
| `client/exports/guests-pdf.blade.php` | PDF de invitados, con títulos que no se separan de su tabla |
| `client/exports/invitation-pdf.blade.php` | PDF de la invitación |
| `client/partials/export-buttons.blade.php` | Botones para pedir cada archivo |
| `client/partials/export-status.blade.php` | Aviso del archivo en preparación, con su enlace de descarga |

### 5.11 Estilos y scripts

| Archivo | Qué hace |
| --- | --- |
| `resources/css/app.css` | Punto de entrada: Tailwind y los demás archivos |
| `resources/css/site/site.css` | Sitio público: paleta clara/oscura, botones, teléfono de la portada, filas de servicios, lista de plantillas, columnas de precios, filas de contacto y animaciones |
| `resources/css/site/brand.css` | Logo e isotipo |
| `resources/css/admin/admin.css` | Paneles y editor |
| `resources/css/invitation/base.css` | Base común de las invitaciones (tipografía, botones, listas, formularios) |
| `resources/css/invitation/hero.css` | Portadas |
| `resources/css/invitation/modules.css` | Módulos: encuestas, playlist, regalos, fotomural, hashtag, ubicación |
| `resources/css/invitation/countdown.css` | Cuenta regresiva |
| `resources/css/invitation/gallery.css` | Galería en pila |
| `resources/css/invitation/itinerary.css` | Itinerario |
| `resources/css/invitation/nav-player.css` | Menú y reproductor |
| `resources/css/invitation/ambient.css` | Fondos animados compartidos |
| `resources/css/invitation/themes/xv.css` | Tema de XV: telones, dorados y ornamentos |
| `resources/css/invitation/themes/boda.css` | Tema de boda: sobre, ramas y tipografía caligráfica |
| `resources/css/invitation/themes/bautizo.css` | Tema de bautizo: cielo, pila, jarra, olas y destellos |
| `resources/css/invitation/themes/cumple.css` | Tema de cumpleaños: pastel, confeti, globos y bordes marcados |
| `resources/js/app.js` | Entrada: Alpine, axios, barra de progreso y carga dinámica de los demás módulos según lo que exista en la página |
| `resources/js/bootstrap.js` | Configura axios |
| `resources/js/site.js` | Sitio público: revelado al hacer scroll, rotador de la portada, botones magnéticos, cabecera al bajar y teléfono que recorre las aperturas |
| `resources/js/gallery-stack.js` | Galería en pila con gestos |
| `resources/js/itinerary-scroll.js` | Luz que sigue la lectura del itinerario |
| `resources/js/lottie-icons.js` | Carga cada ícono animado solo si se usa |
| `resources/js/video-player.js` | Reproductor de video (video.js), en chunk aparte |
| `resources/lottie-icons/*.json` | Veinte íconos animados (reloj, corona, pastel, paloma, anillos, cámara, regalo, etc.) |

### 5.12 Público y pruebas

| Archivo | Qué hace |
| --- | --- |
| `public/index.php` | Entrada HTTP |
| `public/.htaccess` | Reescritura de URLs |
| `public/robots.txt` | Reglas para buscadores |
| `public/favicon.svg` / `favicon.ico` | Íconos del sitio |
| `public/images/site/event-*.webp` | Fotos de los cuatro eventos de la portada |
| `public/images/site/servicio-enlace.webp` / `servicio-fotomural.webp` | Fotos de la sección de servicios |
| `public/images/site/nosotros.webp` | Foto de una sección que ya se quitó |
| `tests/TestCase.php` | Base de las pruebas |
| `tests/Concerns/CreatesInvitations.php` | Utilidad para crear invitaciones de prueba |
| `tests/Feature/HomePageTest.php` | Portada: paquetes, WhatsApp, redes, plantillas y sincronía del teléfono |
| `tests/Feature/DemoInvitationTest.php` | Muestras interactivas: no guardan nada, apertura automática y acceso restringido |
| `tests/Feature/SecurityHardeningTest.php` | `noindex`, caché privada del enlace personal, cabeceras de seguridad y rechazo de SVG |
| `tests/Feature/GuestLinksAndModerationTest.php` | Regenerar el enlace de un invitado y ocultar aportes sin borrarlos |
| `tests/Feature/StructuredModulesRoundTripTest.php` | Los módulos de las cuatro plantillas vuelven iguales desde las tablas |
| `tests/Feature/ModuleSyncAndRetentionTest.php` | Guardado todo o nada, reutilización de filas y purga de fotos viejas |
| `tests/Feature/AdminPanelPerformanceTest.php` | El panel pagina, cuenta en la base y no crece en consultas |
| `tests/Feature/AccessControlTest.php` | Separación de admin y cliente |
| `tests/Feature/ClientCredentialsTest.php` | Alta y acceso del cliente |
| `tests/Feature/ClientExportsTest.php` | Exportaciones Excel y PDF |
| `tests/Feature/InvitationEditorTest.php` | Guardado del editor |
| `tests/Feature/InvitationStructuredModulesTest.php` | Módulos normalizados y respaldo JSON |
| `tests/Feature/PublicInteractionsTest.php` | RSVP, votos, canciones y fotos |
| `tests/Feature/WeddingTemplateTest.php` | Plantillas de boda y XV |
| `tests/Feature/BaptismTemplateTest.php` | Plantilla de bautizo |
| `tests/Feature/BirthdayTemplateTest.php` | Plantilla de cumpleaños |
| `tests/Feature/ShowcaseInvitationsSeederTest.php` | La semilla de muestras es repetible |
| `tests/Feature/ItineraryIconsTest.php` | Catálogo de íconos |
| `tests/Unit/CloudinaryImageTest.php` | Variantes responsivas de imagen |
| `tests/Feature/ExampleTest.php` / `tests/Unit/ExampleTest.php` | Pruebas de ejemplo de Laravel |

---

## 6. Estado de la normalización de datos

Los módulos nacieron como un JSON por invitación (`invitation_data.json_data`). Hoy **todas las listas
viven en tablas** y el JSON guarda solo la configuración de cada módulo (títulos, textos y bloques que
no son listas), además de seguir como respaldo mientras una invitación no tenga fila en
`invitation_settings`.

| Área | Dónde vive hoy | Nota |
| --- | --- | --- |
| Configuración y visibilidad | `invitation_settings` | Una fila por invitación |
| Itinerario | `invitation_itinerary_items` | Con orden estable |
| Galería y fotos post evento | `invitation_gallery_images` | Separadas por `collection` |
| Encuestas | `invitation_polls` + `invitation_poll_options` | El voto usa `invitation_poll_id` |
| Ubicación | `invitation_locations` | Preparada para varias sedes |
| Personas destacadas | `invitation_featured_people` | Guarda el grupo y con qué nombre llegó el campo |
| Código de vestimenta | `invitation_dress_code_items` | Sugerencias, colores y qué evitar |
| Opciones de regalo | `invitation_gift_options` | Los datos bancarios siguen en el JSON del módulo |
| Canción y video | `invitation_media` | Un registro por tipo |
| Playlist y fotomural | `guest_contributions` | Con estado de moderación |
| RSVP | `guests` | — |

**Regla:** en tablas va lo que se repite, se ordena, se filtra o se consulta; en JSON queda la
configuración pequeña y propia de una plantilla (colores, textos, interruptores, datos bancarios).

**Lo que falta para cerrar el ciclo:** hoy se escribe en tablas *y* en JSON. Cuando ninguna
instalación dependa del respaldo, toca dejar de escribir el JSON y crear una migración de limpieza.

---
## 7. Sugerencias de mejora

Cada punto dice **qué pasa hoy** (con el archivo), **por qué importa**, **cómo resolverlo** y **cómo
verificar** que quedó bien. La etiqueta indica impacto y esfuerzo estimado.

Lo que ya está bien y conviene no romper: los resultados de encuestas se calculan con una sola
consulta agrupada (`InvitationModuleService::pollResultsFor`), los endpoints públicos tienen límite
por IP e invitación (`AppServiceProvider::configureRateLimiting`), el panel del cliente pagina con
`withCount`, la caché se invalida por eventos y las muestras de la home no escriben nada.

### 7.1 Seguridad — implementada

Los once puntos de seguridad ya están en el código. Queda solo lo que depende del entorno real
(marcado como pendiente al final de cada punto).

| # | Qué se hizo | Dónde |
| --- | --- | --- |
| 1 | Las invitaciones y las muestras no se indexan: `robots.txt` las bloquea y cada plantilla envía `noindex, nofollow` | `public/robots.txt`, `shell/head.blade.php` |
| 2 | El enlace personal se marca `private` con `Vary: Cookie`; solo el enlace general puede guardarse en una caché compartida | `CachePublicInvitations` |
| 3 | Solo se confía en los proxies declarados en `TRUSTED_PROXIES`; vacío = ninguno | `config/security.php`, `AppServiceProvider::configureTrustedProxies` |
| 4 | Dos votos simultáneos ya no revientan: la violación de la clave única se traduce en "ya votaste" | `Public\ContributionController@votePoll` |
| 5 | El voto exige una encuesta de esa invitación (404) y una opción que exista (422) | `Public\ContributionController@votePoll` |
| 6 | La contraseña del cliente solo se guarda como hash, se muestra una vez al crearla y se puede generar otra desde el editor | Migración `drop_access_password`, `Admin\InvitationController@regenerateClientPassword`, panel General |
| 7 | Las subidas ya no aceptan SVG y limitan dimensiones (8000 px) además del peso | `MediaUploadService::validateFile` |
| 8 | `SESSION_SECURE_COOKIE` y `SESSION_ENCRYPT` quedaron documentados para producción | `.env.example` |
| 9 | Todas las respuestas llevan `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy` y CSP en modo reporte | `SecurityHeaders`, `config/security.php` |
| 10 | El administrador puede generar un enlace personal nuevo para un invitado; el anterior deja de funcionar | `Admin\GuestController@regenerateToken`, vista de invitados |
| 11 | El cliente puede ocultar una foto o una canción sin borrarla, y las lecturas públicas solo muestran lo visible | Migración `add_moderation_status`, `Client\ContributionController`, panel del cliente |

Pruebas que lo respaldan: `SecurityHardeningTest`, `GuestLinksAndModerationTest`,
`PublicInteractionsTest` (votos) y `ClientCredentialsTest` (contraseñas).

**Pendiente de configurar o decidir**

- **Proxies (punto 3):** en producción hay que poner en `TRUSTED_PROXIES` las IP del hosting o del CDN;
  si queda vacío detrás de un proxy, todos los visitantes comparten la IP del proxy para los límites
  por minuto.
- **CSP (punto 9):** sale en modo reporte. Hay que revisar los avisos del navegador en las cuatro
  plantillas y en el editor y recién entonces poner `CSP_ENFORCE=true`. Mientras `script-src` necesite
  `'unsafe-eval'` (lo pide Alpine), la política protege menos: evaluar la compilación CSP de Alpine.
- **Cookies (punto 8):** `SESSION_SECURE_COOKIE=true` solo tiene sentido con HTTPS; hay que fijarlo en
  el `.env` del servidor.
- **Contraseñas (punto 6):** al quitar la columna se perdieron las contraseñas guardadas de clientes
  creados antes; para esas cuentas hay que generar una nueva desde el editor.
- **Tokens (punto 10):** ya se pueden rotar, pero todavía no caducan solos después del evento.
- **Moderación (punto 11):** el cliente ve los últimos 60 aportes; si un evento genera muchos más,
  conviene paginar esa lista.
### 7.2 Datos y base de datos — implementada

| # | Qué se hizo | Dónde |
| --- | --- | --- |
| 12 | Ubicación, personas destacadas, código de vestimenta, opciones de regalo, medios y fotos post evento pasaron a tablas propias, con orden e índices | Cinco migraciones nuevas, cinco modelos y `InvitationStructuredDataService` |
| 13 | El voto se relaciona con la encuesta por su fila: se completó la relación, se movió la clave única a `(invitation_poll_id, voter_key)` y se eliminó el `poll_id` textual | Migración `drop_textual_poll_id`, `ContributionController@votePoll`, `InvitationModuleService::pollResultsFor` |
| 14 | El guardado del editor ya era una transacción; ahora además reutiliza las filas en vez de borrarlas y recrearlas, así los ids no cambian | `InvitationStructuredDataService::replaceOrdered` |
| 15 | Al borrar una foto se borra su archivo en Cloudinary, y hay un comando de retención para eventos viejos | `GuestContribution::booted`, `MediaUploadService::delete`, `invitations:purge-contributions` |

**Cómo se comprobó**

- `invitations:migrate-json --dry-run --force` sobre las cuatro invitaciones reales: 0 diferencias.
- `StructuredModulesRoundTripTest`: lo guardado vuelve idéntico desde las tablas en las cuatro
  plantillas, y la invitación sigue mostrando todo aunque se borre el JSON.
- `ModuleSyncAndRetentionTest`: si algo falla a la mitad no se guarda nada, al reguardar los ids no
  cambian y la purga borra solo lo viejo.
- En la base real: 200 votos quedaron enlazados a su encuesta, la columna `poll_id` ya no existe y las
  tablas nuevas tienen 4 ubicaciones, 36 personas destacadas, 35 elementos de vestimenta, 8 medios y
  11 fotos post evento.

**Pendiente**

- **Dejar de escribir el JSON (punto 12).** Hoy cada guardado escribe tablas y JSON. Cuando se
  confirme que ninguna instalación lee el respaldo, hay que quitar la doble escritura y limpiar
  `invitation_data`.
- **Votos sin encuesta (punto 13).** La migración eliminó los votos que apuntaban a encuestas que ya
  no existían (en esta base no había ninguno). Si al desplegar en producción hay muchos, conviene
  guardarlos antes en una tabla de archivo.
- **Una invitación sin normalizar ya no acepta votos (punto 13).** El endpoint responde 404 si la
  encuesta no tiene fila. Antes de desplegar hay que correr `invitations:migrate-json`.
- **Retención más allá de las fotos (punto 15).** Falta decidir qué pasa con los datos de invitados y
  los reportes después del evento, y programar el comando (por ejemplo, mensual).
- **Varias sedes (punto 12).** La tabla de ubicaciones ya lo permite, pero el editor y las plantillas
  todavía manejan una sola.
### 7.3 Rendimiento y escalabilidad — implementada

| # | Qué se hizo | Dónde |
| --- | --- | --- |
| 16 | El panel admin pagina de 20 en 20, cuenta los invitados en la base (`withCount`) y las cifras salen de consultas agregadas | `Admin\DashboardController`, `DashboardViewData`, `layouts/partials/pagination` |
| 17 | El listado de invitados pagina de 50 en 50 y tiene buscador por nombre y filtro por estado, que viajan en la URL | `Admin\GuestController@index` y su vista |
| 18 | La caché de invitaciones se arma con candado: quien llega primero la genera y el resto espera su resultado | `InvitationCacheService::remember`, usado en las cuatro lecturas del controlador público |
| 19 | Los Excel y PDF se generan en cola: el panel muestra "preparando…" y aparece el enlace cuando está listo | `GenerateInvitationExport`, `Client\ExportController`, tabla `invitation_exports` |
| 20 | El fotomural y las fotos post evento piden variantes por tamaño (`srcset` y `sizes`), además de carga diferida | `CloudinaryImage::srcset` en el payload del fotomural y en `post-event` |
| 21 | Comando de medición propio que deja el número por escrito | `bida:medir`, resultado en `docs/rendimiento.md` |

**Lo que se midió** (6 repeticiones por pantalla, base local con las cuatro invitaciones de muestra):

| Pantalla | Consultas antes | Consultas después | Tiempo P50 antes | Tiempo P50 después | Memoria antes | Memoria después |
| --- | --- | --- | --- | --- | --- | --- |
| Invitación (`/p/{slug}`) | 22 | 8 | 31 ms | 17 ms | 493 KB | 227 KB |
| Muestra (`/muestra/{slug}`) | 22 | 8 | 37 ms | 21 ms | 516 KB | 236 KB |
| Portada | 4 | 4 | 24 ms | 23 ms | 113 KB | 113 KB |

El "después" es con `CACHE_OPTIMIZATIONS_ENABLED=true`. En el panel admin, una prueba fija el
comportamiento: con 25 invitaciones y 500 invitados hace menos de 15 consultas y muestra 20 filas.

**Pendiente**

- **Encender la caché en producción (punto 18).** Sigue apagada por defecto. Hay que poner
  `CACHE_OPTIMIZATIONS_ENABLED=true` y, si hay más de una instancia, `CACHE_STORE=redis`: el candado
  necesita un almacén compartido.
- **Un worker para la cola (punto 19).** Con `QUEUE_CONNECTION=sync` el archivo se arma en la misma
  petición, así que en producción hay que poner `QUEUE_CONNECTION=database` (o redis) y correr
  `queue:work` con supervisor. Sin worker, el panel se queda en "preparando…".
- **Programar la limpieza.** `invitations:purge-contributions` borra fotos viejas y exportaciones
  vencidas, pero todavía nadie lo ejecuta solo: falta agregarlo al programador de tareas.
- **Medir con datos reales (punto 21).** La medición local usa cuatro invitaciones; conviene repetirla
  con un volumen parecido al de producción.
### 7.4 Frontend, diseño y accesibilidad

#### 22. La invitación depende de JavaScript — *impacto medio, esfuerzo medio*

- **Hoy:** varias secciones se muestran con Alpine (`x-show`, `x-cloak`) y la apertura bloquea el
  scroll hasta que se toca.
- **Por qué importa:** si el script falla o tarda en una conexión lenta, el invitado no ve fecha,
  lugar ni itinerario.
- **Cómo:** que el contenido esencial esté en el HTML y el JavaScript solo lo mejore; `<noscript>` ya
  oculta la apertura, falta revisar módulos.
- **Verificar:** abrir una invitación con JavaScript desactivado y comprobar que se lee lo esencial.

#### 23. Accesibilidad — *impacto medio, esfuerzo medio*

- **Hoy:** hay foco visible y se respeta `prefers-reduced-motion`, pero no está verificado el
  contraste de los cuatro temas ni el recorrido con teclado del menú y la galería. Las fotos que sube
  el cliente van con `alt` vacío.
- **Cómo:** revisar contraste (4.5:1 en texto), permitir `alt` por foto en el editor, comprobar que
  todo control sea alcanzable con teclado y que el menú devuelva el foco al cerrarse.
- **Verificar:** recorrer una invitación completa solo con teclado y pasar un revisor automático.

#### 24. Sistema visual documentado — *impacto medio, esfuerzo bajo*

- **Hoy:** conviven dos sistemas: los tokens del sitio (`site.css`) y los de la invitación
  (`base.css`), más cuatro temas.
- **Cómo:** una página interna que muestre colores, tipografías, botones, campos y tarjetas de cada
  sistema, para no reinventar estilos en cada pantalla nueva.

#### 25. El editor necesita el mismo cuidado que el sitio — *impacto medio, esfuerzo alto*

- **Hoy:** diecinueve paneles al mismo nivel, sin indicación de progreso ni de qué falta para
  publicar.
- **Cómo:** agrupar por etapas (identidad, contenido, interacción, cierre), mostrar qué módulos están
  incompletos, vista previa siempre visible en pantallas grandes y un botón claro de publicar.

### 7.5 Compartir, SEO y marketing

#### 26. No hay vista previa al compartir — *impacto alto, esfuerzo bajo*

- **Hoy:** ninguna vista tiene etiquetas `og:` ni `twitter:`; al pegar el enlace en WhatsApp no
  aparece imagen ni descripción.
- **Por qué importa:** WhatsApp es el canal principal de distribución de este producto.
- **Cómo:** en `shell/head.blade.php` añadir `og:title` (nombre del evento), `og:description` (fecha y
  lugar), `og:image` (la foto de portada por Cloudinary, recortada a 1200×630) y `og:url`. Hacer lo
  mismo en la portada del sitio.
- **Verificar:** pegar el enlace en un chat de prueba y revisar la tarjeta.

#### 27. Páginas por tipo de evento — *impacto medio, esfuerzo medio*

- **Cómo:** `/invitaciones-de-boda`, `/invitaciones-xv-anos`, etc., cada una con su muestra embebida,
  preguntas propias y llamado a WhatsApp. Da material para buscadores y para anuncios.

#### 28. Saber de dónde viene cada cliente — *impacto medio, esfuerzo bajo*

- **Cómo:** añadir UTM a los enlaces de campañas y un código corto en el mensaje prellenado de
  WhatsApp (`HomeController`), para identificar el origen al responder.

### 7.6 Pruebas, calidad y operación

#### 29. Pruebas que faltan — *impacto alto, esfuerzo medio*

- Policies: un cliente no ve, edita, exporta ni gestiona invitados de otro (una prueba por método).
- Concurrencia: dos votos simultáneos, dos confirmaciones simultáneas.
- Subidas: archivo grande, tipo no permitido, SVG con script.
- Límites: que el throttle responda 429 con el mensaje esperado.
- Regresión visual mínima: que las cuatro plantillas rendericen con módulos vacíos y completos.

#### 30. Observabilidad y respaldos — *impacto alto, esfuerzo medio*

- **Cómo:** registrar tiempos y errores, alertar sobre `failed_jobs`, respaldar base de datos y medios
  con prueba de restauración, y dejar escrito el procedimiento de despliegue (`migrate --force`,
  `optimize`, `npm run build`, limpieza de caché).

#### 31. Integración continua — *impacto medio, esfuerzo bajo*

- **Cómo:** un flujo que ejecute `php artisan test`, `./vendor/bin/pint --test` y `npm run build` en
  cada rama, para no depender de correrlo a mano.

### 7.7 Limpieza pendiente

| Elemento | Situación | Acción |
| --- | --- | --- |
| `resources/views/welcome.blade.php` | Ninguna ruta lo usa | Eliminar |
| `public/images/site/nosotros.webp` | La sección se quitó de la portada | Eliminar |
| `routes/console.php` | Solo trae el comando `inspire` de ejemplo | Dejarlo o reemplazarlo por comandos propios |
| `config/optimizations.php` → `blade`, `database`, `cdn` | Claves declaradas que ningún código lee | Quitarlas o implementarlas |
| `app/Providers/BladeServiceProvider.php` | No está registrado en `bootstrap/providers.php` y su registro está comentado en `AppServiceProvider` | Registrarlo o eliminarlo |
| Plantillas de ejemplo (`BodaJardinDemoSeeder`, `XvSofiaModuleData`, …) | Se usan en pruebas | Mantener, documentando que son datos de prueba |
---

## 8. Hoja de ruta sugerida

| Fase | Foco | Trabajos |
| --- | --- | --- |
| **1. Seguridad** | ✔ Hecho | Indexación, caché del enlace personal, proxies, votos, contraseña del cliente, subidas, cabeceras, tokens y moderación (ver 7.1). Falta configurar `TRUSTED_PROXIES` y exigir la CSP en producción |
| **2. Datos** | ✔ Hecho | Tablas para ubicación, destacados, vestimenta, regalos y medios; voto por relación real; guardado que reutiliza filas; borrado y retención de archivos. Falta dejar de escribir el JSON de respaldo |
| **3. Rendimiento** | ✔ Hecho | Paginación y conteos en la base, caché con candado, exportaciones en cola, imágenes por tamaño y comando de medición. Falta encender la caché y correr un worker en producción |
| **4. Experiencia** | Producto | Editor por pasos, accesibilidad, Open Graph por invitación, guía visual, limpieza de vistas sin uso |
| **5. Crecimiento** | Captación | Páginas por evento, UTM y medición, contenido con las muestras |

---

## 9. Cómo mantener este documento

- Cuando se agregue un archivo importante, sumar su fila en la sección 5.
- Cuando cambie el flujo de datos o se migre un módulo, actualizar las secciones 3 y 6.
- Cuando una sugerencia se implemente, moverla de la sección 7 a una línea de historial o borrarla.
- Este documento describe el estado real del repositorio: si algo aquí ya no coincide con el código,
  el código manda y el documento se corrige.
