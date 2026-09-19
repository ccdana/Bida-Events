# Mapa del proyecto Bida Events

Guía del repositorio: qué hace cada archivo, cómo fluye la información y qué conviene mejorar.
Está escrita para que alguien que nunca vio el proyecto pueda ubicarse, y para que quien ya lo conoce
sepa dónde tocar sin romper nada.

- **Qué es:** una aplicación Laravel 12 que crea, edita y publica invitaciones digitales de eventos
  (bodas, bautizos, cumpleaños y XV años) y tarjetas de temporada de una persona a otra (Día del Amor;
  después Halloween, Día de Muertos, Navidad). Cómo sumar una temporada: `docs/temporadas.md`.
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

Con Docker, que es la forma recomendada y la única que no pide instalar nada más:

```bash
docker compose up -d              # levanta PHP, PostgreSQL, la cola y Vite
docker compose logs -f app        # seguir el arranque hasta "Listo: http://localhost:8000"
```

La primera vez construye la imagen, instala las dependencias, crea el `.env`, genera la `APP_KEY`,
migra y carga los datos de ejemplo. Los comandos de abajo se ejecutan con
`docker compose exec app ...` delante. El detalle está en [`docs/docker.md`](docs/docker.md).

Sin Docker, con PHP 8.4, Composer, Node 22 y PostgreSQL ya instalados:

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed        # admin + tipos de evento + invitaciones de muestra + cliente de prueba
npm run build                     # o: npm run dev
php artisan test
php artisan optimize:clear        # tras cambiar vistas, rutas o configuración
```

En este equipo (Laragon) los binarios son
`C:\laragon\bin\php\php-8.4.24-Win32-vs17-x64\php.exe` y `C:\laragon\bin\nodejs\node-v22\node.exe`,
y el sitio responde en `http://bida-events.test`. La base local es PostgreSQL; `pg_dump` y `psql` están en
`C:\laragon\bin\postgresql\pgsql\bin` (variables `BACKUP_PG_DUMP` y `BACKUP_PSQL`).

Despliegue: `docs/despliegue.md`. Registros, alertas y respaldos: `docs/operacion.md`. Temporadas y
módulos nuevos: `docs/temporadas.md`.

| Comando | Para qué |
| --- | --- |
| `php artisan migrate:status` | Ver qué migraciones faltan |
| `php artisan db:seed --class=ShowcaseInvitationsSeeder` | Rehacer las invitaciones y tarjetas de muestra |
| `php artisan optimize:clear` | Limpiar caché de vistas, rutas y configuración |
| `php artisan invitations:purge-contributions --dry-run` | Ver qué fotos viejas se borrarían |
| `php artisan bida:medir --guardar` | Medir las pantallas públicas y guardar el resultado |
| `php artisan bida:imagenes-compartir` | Regenerar las tarjetas para compartir después de cambiar una foto del sitio |
| `php artisan bida:enlace-campana invitaciones-de-boda --fuente=facebook --medio=anuncio --campana=mayo` | Armar el enlace de una campaña y ver su código |
| `php artisan queue:work` | Procesar los archivos que piden los clientes (en producción, con supervisor) |
| `php artisan db:seed --class=ClientUserSeeder` | Crear el cliente de prueba `cliente.prueba` (muestra la contraseña una vez) |
| `php artisan bida:respaldo` | Respaldar base y medios en `storage/app/backups` |
| `php artisan bida:probar-respaldo` | Restaurar el último respaldo en una base temporal y verificarlo |
| `php artisan bida:salud` | Revisar colas, respaldos y disco |
| `php artisan schedule:list` | Ver las tareas programadas |
| `vendor/bin/pint --test` | Revisar el estilo del código (lo exige la integración continua) |
| `php artisan test` | Suite completa |
| `npm run build` | Compilar CSS y JS |

---

## 2. Las cuatro superficies

| Superficie | Quién entra | Rutas | Vistas |
| --- | --- | --- | --- |
| **Sitio público** | Cualquiera | `/`, `/invitaciones-de-*`, `/login` | `home.blade.php`, `landing.blade.php`, `site/partials/*`, `auth/login.blade.php`, `layouts/site.blade.php` |
| **Invitación pública** | Invitados con enlace | `/p/{slug}`, `/p/{slug}/i/{token}` | `invitations/templates/*` |
| **Muestras interactivas** | Visitantes de la home | `/muestra/{slug}` | Las mismas plantillas, en modo muestra |
| **Panel admin** | Usuario con `is_admin` | `/admin/**` | `admin/**`, `layouts/admin*.blade.php` |
| **Panel cliente** | Dueño de la invitación | `/client/**` | `client/**`, `layouts/client.blade.php` |

---

## 3. Flujo de datos de una invitación

1. **Creación.** El admin crea la invitación (`Admin\InvitationController@store`) y, si hace falta, el
   usuario cliente (`ClientCredentials`).
2. **Edición.** El editor (`admin/invitations/editor/*`) se adapta al perfil de la plantilla
   (`App\EventProfiles`): pestañas, campos de portada, grupos de destacados y avisos de lo que falta.
   Envía cada módulo como JSON en el formulario (solo formato de envío). `UpdateInvitationRequest`
   los valida con `InvitationModuleRules`, que suma las reglas de cada módulo.
3. **Guardado.** `InvitationModuleService::syncAllModules` llama a `ModuleRegistry::save` dentro de
   una transacción: cada módulo (`app/Modules`) escribe sus propias tablas. No hay columnas JSON.
4. **Invalidación de caché.** El evento `InvitationUpdated` dispara `RefreshInvitationCache`, que
   limpia y recalienta lo cacheado de esa invitación.
5. **Publicación.** `Public\InvitationController@show` lee los módulos desde sus tablas
   (`ModuleRegistry::load`, una carga de relaciones), resuelve plantilla, encuestas, playlist y
   fotomural, y renderiza la plantilla.
6. **Interacción del invitado.** RSVP, votos, canciones, fotos y respuestas de tarjeta entran por
   `Public\RsvpController` y `Public\ContributionController`, con límite de peticiones por ruta.
   Cada aporte dispara eventos que vuelven a invalidar la caché.
7. **Seguimiento.** El cliente ve sus invitados en su panel y exporta Excel o PDF.

---

## 4. Rutas

Todas están en `routes/web.php`.

| Método y ruta | Nombre | Controlador | Notas |
| --- | --- | --- | --- |
| `GET /` | `home` | `HomeController` | Portada pública; middleware `lead.source` |
| `GET /invitaciones-de-boda`, `/invitaciones-xv-anos`, `/invitaciones-de-bautizo`, `/invitaciones-de-cumpleanos`, `/tarjetas-dia-del-amor` | `landing` | `EventLandingController` | Página por tipo de evento (contenido en `config/bida.php`, clave `landings`); middleware `lead.source` |
| `GET /sitemap.xml` | `sitemap` | `EventLandingController@sitemap` | Portada y páginas por evento |
| `GET /muestra/{slug}` | `invitation.demo` | `Public\InvitationController@demo` | Solo las invitaciones de `bida.demo_invitations`; nada se guarda; `noindex` |
| `GET /dashboard` | `dashboard` | Cierre en rutas | Redirige a admin o cliente según el rol |
| `GET/POST /login`, `POST /logout` | `login`, `logout` | `Auth\LoginController` | `throttle:login` |
| `GET /p/{slug}` | `invitation.show` | `Public\InvitationController@show` | Invitación general |
| `GET /p/{slug}/i/{token}` | `invitation.guest` | `Public\InvitationController@show` | Invitación personal |
| `POST /p/{slug}/i/{token}/confirm` | `invitation.rsvp` | `Public\RsvpController@confirm` | `throttle:invitation-rsvp` |
| `GET/POST /p/{slug}/playlist` | `invitation.playlist*` | `Public\ContributionController` | `throttle:invitation-songs` |
| `GET/POST /p/{slug}/fotomural` | `invitation.fotomural*` | `Public\ContributionController` | `throttle:invitation-photos` |
| `POST /p/{slug}/polls/{pollId}/vote` | `invitation.poll.vote` | `Public\ContributionController@votePoll` | `throttle:invitation-votes` |
| `POST /p/{slug}/respuesta` | `invitation.reply` | `Public\ContributionController@storeReply` | `throttle:invitation-replies`; 404 si el módulo `respuesta` está apagado |
| `GET /admin/sistema-visual` | `admin.design-system` | `Admin\DesignSystemController` | Referencia interna de colores, tipografía y componentes |
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
| `docs/despliegue.md` | Primera instalación, publicación de una versión, vuelta atrás, cron y worker |
| `docs/operacion.md` | Registros, alertas de `bida:salud`, respaldos, prueba y procedimiento de restauración |
| `.github/workflows/ci.yml` | Integración continua: compila, revisa estilo con Pint y corre las pruebas en cada push |
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
| `config/optimizations.php` | Interruptores propios: caché de invitaciones y TTL, retención, límites por minuto de login, RSVP, canciones, fotos, votos y respuestas de tarjeta, y cabeceras HTTP de caché |
| `config/modules.php` | Lista de módulos registrados (`app/Modules`); el orden es el orden de guardado |
| `config/event_profiles.php` | Perfiles de evento y temporada (`app/EventProfiles`) |
| `config/operations.php` | Umbral de peticiones lentas, alertas, carpeta y ejecutables de respaldo |
| `config/queue.php` | Colas y conexión por defecto |
| `config/security.php` | Proxies de confianza (`TRUSTED_PROXIES`) y política de contenido (`CSP_ENABLED`, `CSP_ENFORCE`) |
| `config/services.php` | Credenciales de terceros |
| `config/session.php` | Driver, duración y cookies de sesión |
| `routes/web.php` | Todas las rutas (tabla anterior) |
| `routes/console.php` | Tareas programadas: respaldo diario, prueba de restauración semanal, purga de fotos, revisión de salud cada hora y limpieza de trabajos fallidos |

### 5.3 Controladores

| Archivo | Qué hace |
| --- | --- |
| `app/Http/Controllers/Controller.php` | Controlador base |
| `HomeController.php` | Portada: paquetes con enlace de WhatsApp prellenado y código de origen, invitación de la portada, muestras y tarjeta para compartir |
| `EventLandingController.php` | Páginas por tipo de evento y `sitemap.xml` |
| `Auth/LoginController.php` | Formulario de acceso, login con límite de intentos, regeneración de sesión y salida |
| `Public/InvitationController.php` | Renderiza la invitación pública: carga módulos (tablas o JSON), resultados de encuestas, playlist, fotomural y URL de calendario. También sirve `/muestra/{slug}` con un invitado ficticio que no se guarda |
| `Public/RsvpController.php` | Confirmación de asistencia por invitado y generación del pase |
| `Public/ContributionController.php` | Lista y recibe canciones y fotos, y registra votos de encuestas |
| `Admin/DashboardController.php` | Panel del administrador con el estado de las invitaciones |
| `Admin/DesignSystemController.php` | Página interna del sistema visual: tokens, tipografía, componentes y contraste de cada tema |
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
| `app/Http/Middleware/CaptureLeadSource.php` | Recuerda 30 días el origen de campaña (`utm_*` o `?ref=`) en la cookie `bida_origen` |
| `app/Http/Middleware/LogSlowRequests.php` | Registra en `performance-*.log` las peticiones lentas, con el nombre de la ruta y sin tokens |
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
| `Invitation.php` | La invitación: dueño, tipo de evento, plantilla, slug, fecha, estado y vencimiento. Concentra las relaciones de cada módulo (`theme`, `hero`, `sections`, `features`, `itineraryItems`, `galleryImages`, `polls`, `hashtag`, `rsvpSetting`, `bankAccounts`, `dedication`, `milestones`…), `guests`, `contributions`, `pollVotes` y los scopes `active`, `published` y `withAllData` |
| `InvitationTheme.php` | Colores y tipografías (1 a 1) |
| `InvitationHero.php` | Portada: nombres separados (`primary_name`, `secondary_name`), edad, subtítulo, mensaje, foto y texto post evento |
| `InvitationSection.php` | Textos de una sección (título, subtítulo, introducción, ejemplo, botón y enlace), una fila por invitación y módulo |
| `InvitationHashtag.php` / `InvitationRsvpSetting.php` / `InvitationBankAccount.php` | Hashtag, textos del RSVP y cuentas bancarias con su QR |
| `CardDedication.php` / `CardMilestone.php` | Tarjetas: de, para, mensaje y firma; fecha de «juntos desde» |
| `InvitationItineraryItem.php` | Cada momento del itinerario, con orden |
| `InvitationGalleryImage.php` | Cada imagen, por colección (galería, portada, post-evento) y orden |
| `InvitationPoll.php` | Pregunta de encuesta, con su clave estable y sus opciones |
| `InvitationPollOption.php` | Opción de una encuesta |
| `PollVote.php` | Voto: invitación, encuesta (por su fila), opción y votante |
| `InvitationLocation.php` | Sede del evento: nombre, dirección, coordenadas, mapa y foto |
| `InvitationFeaturedPerson.php` | Personas destacadas por grupo (chambelanes, damitas, padrinos…) |
| `InvitationDressCodeItem.php` | Código de vestimenta: sugerencias, colores permitidos y qué evitar, con sus ejemplos en `InvitationDressCodeExample` |
| `InvitationGiftOption.php` | Regalos por `type`: opciones, sobres y tienda (con `address`) |
| `InvitationMedia.php` | Canción de fondo (con `artist`) y video |
| `InvitationExport.php` | Pedido de archivo del cliente: tipo, estado y ruta del archivo generado |
| `Guest.php` | Invitado: nombre, pases asignados y confirmados, estado, mesa, restricciones y token del enlace personal |
| `GuestContribution.php` | Aporte del invitado: canción, foto del fotomural o respuesta de tarjeta (`type` es texto: cada módulo declara el suyo) |
| `EventType.php` | Catálogo de tipos de evento con `code` (el del perfil), `kind` (`invitation` o `card`) y `season` |
| `Feature.php` | Catálogo de módulos; `invitation_features.is_enabled` guarda qué módulos se ven en cada invitación |

### 5.6 Servicios

| Archivo | Qué hace |
| --- | --- |
| `Services/InvitationModuleService.php` | Normaliza lo que llega del editor, resuelve lo que se muestra, guarda todo con `ModuleRegistry` en una transacción, calcula resultados de encuestas, arma la URL de Google Calendar y genera tokens de invitado |
| `Services/BackupService.php` | Volcado (`pg_dump`, `mysqldump`, SQLite), compresión, manifiesto de medios, retención y prueba de restauración |
| `Services/InvitationCacheService.php` | Encendido/apagado de la caché, TTL, invalidación por invitación y olvido puntual de encuestas, playlist y fotomural |
| `Services/InvitationPreviewSession.php` | Guarda en sesión el borrador del editor para la vista previa |
| `Services/MediaUploadService.php` | Valida y sube imágenes y videos a Cloudinary con transformaciones por contexto (portada, galería, ubicación, dress code, video, fotomural, QR bancario) |

### 5.6.1 Módulos y perfiles

| Archivo | Qué hace |
| --- | --- |
| `Modules/Module.php` | Contrato de un módulo: código, nombre, tipos (`invitation`/`card`), forma vacía, `load`, `save`, relaciones, reglas, vista pública y panel |
| `Modules/ModuleRegistry.php` | Reúne los módulos de `config/modules.php`: carga y guardado de todos, reglas, visibilidad por defecto y módulos por tipo |
| `Modules/Concerns/*` | Lectura de valores, reemplazo de listas reutilizando filas, textos de sección y fotos de galería |
| `Modules/Invitation/*Module.php` | Configuración, portada, ubicación, itinerario, vestimenta, destacados, galería, audio, video, playlist, hashtag, encuestas, regalos, post evento, RSVP y los interruptores (`ToggleModule`) |
| `Modules/Card/*Module.php` | Tarjetas: `dedicatoria`, `juntos_desde` y `respuesta` (aporte `card_reply`) |
| `EventProfiles/EventProfile.php` | Perfil de un evento o temporada: tipo, módulos, encendidos por defecto, campos de portada, grupos de destacados, obligatorios y ejemplo |
| `EventProfiles/EventProfiles.php` | Registro de `config/event_profiles.php`; `forTemplate()` da el perfil de una plantilla |
| `EventProfiles/{Xv,Wedding,Baptism,Birthday,LoveCard}Profile.php` | XV, boda (dos nombres), bautizo (padrinos), cumpleaños (edad) y tarjeta del Día del Amor |

### 5.7 Clases de apoyo (`app/Support`)

| Archivo | Qué hace |
| --- | --- |
| `InvitationPage.php` | Objeto que usa toda plantilla pública: perfil, paleta, fuentes, módulos visibles, orden de secciones, nombres, edad, dedicatoria, iniciales y textos de la plantilla |
| `InvitationTemplates.php` | Catálogo de plantillas (cuatro invitaciones y la tarjeta «Carta de amor»): etiqueta, descripción, perfil (`event`), paleta por defecto, textos propios y orden de módulos |
| `ColorContrast.php` | Contraste WCAG y las mezclas de color de la invitación; lo usan la página del sistema visual y las pruebas |
| `InvitationDefaults.php` | Pestañas del editor y resolución de plantilla; códigos, visibilidad y módulos vacíos los toma de `ModuleRegistry` |
| `InvitationModuleRules.php` | Esquema de validación de cada módulo del editor |
| `ItineraryIcons.php` | Catálogo de íconos del itinerario |
| `CloudinaryImage.php` | Arma variantes responsivas (`f_auto`, `q_auto`, ancho), `srcset` y el recorte 1200×630 en JPG para compartir |
| `ShareMeta.php` | Título, descripción e imagen Open Graph de invitaciones, tarjetas («Para Ana, de Luis»), portada y páginas por evento |
| `LeadSource.php` | Origen de campaña y código corto del mensaje de WhatsApp (`Ref. BODA-FB-MAYO`) |
| `ShowcaseDemos.php` | Invitaciones de muestra activas, para la portada y las páginas por evento |
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
| `Events/GuestContributionSubmitted.php` | Se emite al recibir una canción, una foto o una respuesta de tarjeta |
| `Events/PollVoteSubmitted.php` | Se emite al registrar un voto |
| `Listeners/RefreshInvitationCache.php` | Escucha los tres eventos e invalida la caché de forma síncrona |
| `Providers/AppServiceProvider.php` | Registro de servicios, límites de peticiones y ajustes globales |
| `Console/Commands/BackupCommand.php` | Comando `bida:respaldo`: base, medios locales y manifiesto de Cloudinary |
| `Console/Commands/TestBackupRestore.php` | Comando `bida:probar-respaldo`: restaura en una base temporal y compara filas |
| `Console/Commands/HealthCheck.php` | Comando `bida:salud`: trabajos fallidos, cola, exportaciones, respaldos y disco; avisa por correo |
| `Console/Commands/PurgeOldContributions.php` | Comando `invitations:purge-contributions`: borra fotos de eventos viejos (con su archivo en Cloudinary) y exportaciones vencidas |
| `Console/Commands/GenerateShareImages.php` | Comando `bida:imagenes-compartir`: recorta las tarjetas de 1200×630 en `public/images/share` |
| `Console/Commands/CampaignLink.php` | Comando `bida:enlace-campana`: arma un enlace con UTM y muestra el código que llegará por WhatsApp |
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
| `2026_09_17_000001_add_catalog_columns_to_event_types_table` | `code`, `kind` y `season` en los tipos de evento |
| `2026_09_17_000002_create_invitation_themes_table` | Colores y tipografías |
| `2026_09_17_000003_create_invitation_heroes_table` | Portada con nombres separados |
| `2026_09_17_000004_create_invitation_sections_table` | Textos de cada sección |
| `2026_09_17_000005_create_invitation_detail_tables` | Hashtag, textos del RSVP y cuentas bancarias |
| `2026_09_17_000006_normalize_list_tables` | Tipo y dirección en regalos, artista en medios, ejemplos de vestimenta en su tabla, sin columnas `meta` |
| `2026_09_17_000007_create_card_tables` | Dedicatoria y «juntos desde» de las tarjetas |
| `2026_09_17_000008_drop_json_module_storage` | Elimina `invitation_data` e `invitation_settings` |
| `2026_09_17_000009_allow_module_contribution_types` | `guest_contributions.type` deja de ser enum para aceptar `card_reply` y futuros tipos |

**Semillas y datos de ejemplo:**

| Archivo | Qué hace |
| --- | --- |
| `seeders/DatabaseSeeder.php` | Crea el administrador y llama a los demás |
| `seeders/ClientUserSeeder.php` | Cliente de prueba `cliente.prueba` con una invitación de muestra sin dueño; la contraseña sale de `SEED_CLIENT_PASSWORD` o se genera y se muestra una vez |
| `seeders/EventTypeSeeder.php` | Tipos de evento base con su código, tipo y temporada (incluye Día del Amor) |
| `seeders/ShowcaseInvitationsSeeder.php` | Recrea las cuatro invitaciones y la tarjeta de muestra completas, con invitados, aportes y votos; es idempotente |
| `seeders/showcase/xv-isabella.php` | Datos de la muestra de XV años |
| `seeders/showcase/boda-camila-andres.php` | Datos de la muestra de boda |
| `seeders/showcase/bautizo-emilia.php` | Datos de la muestra de bautizo |
| `seeders/showcase/cumple-daniela-30.php` | Datos de la muestra de cumpleaños |
| `seeders/showcase/tarjeta-ana-luis.php` | Tarjeta del Día del Amor de muestra (reusa las fotos de la boda) |
| `seeders/XvSofiaModuleData.php` | Datos de prueba: invitación completa de XV que usan los tests (no son las muestras de la portada) |
| `seeders/BodaJardinDemoSeeder.php` | Datos de prueba: invitación completa de boda |
| `seeders/BautizoCieloDemoSeeder.php` | Datos de prueba: invitación completa de bautizo |
| `seeders/CumpleFiestaDemoSeeder.php` | Datos de prueba: invitación completa de cumpleaños |
| `factories/UserFactory.php` | Usuarios de prueba |

### 5.10 Vistas

**Layouts y componentes compartidos**

| Archivo | Qué hace |
| --- | --- |
| `layouts/site.blade.php` | Layout del sitio público y del login, con las etiquetas para compartir |
| `layouts/partials/share-meta.blade.php` | Etiquetas `og:` y `twitter:` (las usan el sitio y las invitaciones) |
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

**Sitio público**

| Archivo | Qué hace |
| --- | --- |
| `landing.blade.php` | Página por tipo de evento: portada con su foto y la apertura, qué incluye, muestra interactiva, precios, preguntas (con datos estructurados `FAQPage`) y enlaces a los otros eventos |
| `site/partials/header.blade.php`, `plans.blade.php`, `faqs.blade.php`, `footer.blade.php` | Cabecera, precios, preguntas y pie compartidos por la portada y las páginas por evento |
| `sitemap.blade.php` | Mapa del sitio en XML |
| `home.blade.php` | Portada completa: navegación, encabezado con el teléfono que recorre las aperturas, franja de tipos de evento, servicios en filas numeradas, sección de plantillas con la muestra interactiva, pasos de trabajo, precios en columnas, preguntas frecuentes, contacto y pie |
| `auth/login.blade.php` | Acceso al panel, con el mismo rotador de la portada |

**Invitación pública: plantillas**

| Archivo | Qué hace |
| --- | --- |
| `invitations/templates/xv-premium.blade.php` | XV Años Elegante: telones de apertura, destellos dorados y marco editorial |
| `invitations/templates/boda-jardin.blade.php` | Boda Jardín: sobre con sello de cera, ramas y pétalos |
| `invitations/templates/bautizo-cielo.blade.php` | Bautizo Cielo: pila bautismal con jarra, nubes, palomas y burbujas |
| `invitations/templates/cumple-fiesta.blade.php` | Cumpleaños Fiesta: pastel con velas, confeti, globos y banderines |
| `invitations/templates/tarjeta-amor.blade.php` | Carta de amor: carta doblada con cinta que se abre, foto con cinta adhesiva, dedicatoria y contador |
| `invitations/partials/amor/intro.blade.php` / `hero.blade.php` | Apertura de la carta y portada de la tarjeta |
| `invitations/partials/modules/*.blade.php` | Vistas de los módulos registrados (`dedicatoria`, `juntos-desde`, `respuesta`); `shell/modules` las incluye solas |

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
| `admin/dashboard.blade.php` | Listado y estado de invitaciones y tarjetas, con filtro por tipo (`?tipo=invitation` / `card`) |
| `admin/invitations/create.blade.php` / `edit.blade.php` / `_form.blade.php` | Alta y edición de la invitación |
| `admin/invitations/editor/layout.blade.php` | Estructura del editor, con recorte de imágenes |
| `admin/invitations/editor/sidebar.blade.php` | Datos principales enlazados a Alpine |
| `admin/invitations/editor/preview.blade.php` | Vista previa embebida |
| `admin/invitations/editor/script.blade.php` | Lógica del editor: estado, perfil de la plantilla (pestañas, portada, destacados y avisos), subidas y guardado |
| `admin/invitations/panels/*.blade.php` | Un panel por módulo: general, hero, estética, countdown, agendar, ubicación, itinerario, dress code, destacados, galería, video, regalos, rsvp, playlist, encuestas, hashtag, fotomural, música y post-evento; «general» elige primero Invitación o Tarjeta |
| `admin/invitations/panels/modules/*.blade.php` | Paneles de los módulos registrados (dedicatoria, juntos desde, respuesta) |
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
| `resources/css/cards/amor.css` | Tema de la tarjeta del Día del Amor; entrada propia de Vite, se carga solo en esa plantilla |
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
| `public/images/share/*.jpg` | Tarjetas de 1200×630 para compartir la portada y cada tipo de evento (las genera `bida:imagenes-compartir`) |
| `public/favicon.svg` / `favicon.ico` | Íconos del sitio |
| `public/images/site/event-*.webp` | Fotos de los cuatro eventos de la portada |
| `public/images/site/servicio-enlace.webp` / `servicio-fotomural.webp` | Fotos de la sección de servicios |
| `tests/TestCase.php` | Base de las pruebas |
| `tests/Concerns/CreatesInvitations.php` | Utilidad para crear invitaciones de prueba |
| `tests/Feature/HomePageTest.php` | Portada: paquetes, WhatsApp, redes, plantillas y sincronía del teléfono |
| `tests/Feature/DemoInvitationTest.php` | Muestras interactivas: no guardan nada, apertura automática y acceso restringido |
| `tests/Feature/SecurityHardeningTest.php` | `noindex`, caché privada del enlace personal, cabeceras de seguridad y rechazo de SVG |
| `tests/Feature/GuestLinksAndModerationTest.php` | Regenerar el enlace de un invitado y ocultar aportes sin borrarlos |
| `tests/Feature/StructuredModulesRoundTripTest.php` | Cada módulo de las muestras y datos de prueba vuelve igual desde sus tablas, y ninguna tabla tiene columnas JSON |
| `tests/Feature/ModuleRegistryTest.php` | Contrato de módulos y perfiles: vistas, relaciones, reglas y guardado de los módulos de tarjeta |
| `tests/Feature/SeasonalCardTest.php` | Tarjeta: sin RSVP ni itinerario, vista previa al compartir, respuestas (guardado, límite, módulo apagado, panel del cliente), página de campaña, editor y filtro del panel |
| `tests/Feature/ModuleSyncAndRetentionTest.php` | Guardado todo o nada, reutilización de filas y purga de fotos viejas |
| `tests/Feature/AdminPanelPerformanceTest.php` | El panel pagina, cuenta en la base y no crece en consultas |
| `tests/Feature/AccessibleInvitationTest.php` | Lectura sin JavaScript, foco del menú, contraste de todas las paletas del catálogo y `alt` por foto |
| `tests/Feature/DesignSystemPageTest.php` | La referencia visual se ve completa y solo la abre administración |
| `tests/Feature/SharingAndCampaignsTest.php` | Vista previa al compartir, páginas por evento, `sitemap.xml` y código de origen en WhatsApp |
| `tests/Feature/InvitationPolicyMatrixTest.php` | Cada método de la policy, en la regla y en sus rutas, frente a otro cliente |
| `tests/Feature/ConcurrencyAndLimitsTest.php` | Votos y confirmaciones simultáneos, subidas prohibidas y 429 con su mensaje |
| `tests/Feature/TemplateRenderMatrixTest.php` | Todas las plantillas del catálogo, vacías y completas, enlace general y personal; lo que se muestra según el perfil |
| `tests/Feature/BackupRestoreTest.php` | El respaldo se crea, se restaura y un volcado roto falla |
| `tests/Feature/OperationsTest.php` | Revisión de salud, registro de peticiones lentas y cliente de prueba |
| `tests/Feature/AccessControlTest.php` | Separación de admin y cliente |
| `tests/Feature/ClientCredentialsTest.php` | Alta y acceso del cliente |
| `tests/Feature/ClientExportsTest.php` | Exportaciones Excel y PDF |
| `tests/Feature/InvitationEditorTest.php` | Guardado del editor |
| `tests/Feature/InvitationStructuredModulesTest.php` | Módulos normalizados en sus tablas |
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

Los módulos nacieron como un JSON por invitación (`invitation_data.json_data`). Hoy **todo vive en
tablas relacionadas** y no queda ninguna columna `json` (lo comprueba
`StructuredModulesRoundTripTest::test_no_application_table_has_a_json_column`). Cada módulo de
`app/Modules` es dueño de sus tablas.

| Área | Dónde vive | Módulo |
| --- | --- | --- |
| Plantilla, colores y tipografías | `invitations.template` + `invitation_themes` | `config` |
| Visibilidad de módulos | `invitation_features` (`is_enabled`) + `features` | `config` |
| Portada | `invitation_heroes` | `bienvenida` |
| Títulos y textos de sección | `invitation_sections` (una fila por invitación y módulo) | varios |
| Itinerario | `invitation_itinerary_items` | `itinerario` |
| Galería y fotos post evento | `invitation_gallery_images` (por `collection`) | `galeria`, `post_evento` |
| Encuestas | `invitation_polls` + `invitation_poll_options` | `encuestas` |
| Ubicación | `invitation_locations` | `ubicacion` |
| Personas destacadas | `invitation_featured_people` | `destacados` |
| Código de vestimenta | `invitation_dress_code_items` + `invitation_dress_code_examples` | `dress_code` |
| Regalos, sobres y tienda | `invitation_gift_options` (por `type`) + `invitation_bank_accounts` | `regalos` |
| Canción y video | `invitation_media` | `musica`, `video` |
| Hashtag | `invitation_hashtags` | `hashtag` |
| Textos del RSVP | `invitation_rsvp_settings`; las respuestas en `guests` | `rsvp` |
| Dedicatoria y «juntos desde» | `card_dedications`, `card_milestones` | `dedicatoria`, `juntos_desde` |
| Playlist, fotomural y respuestas de tarjeta | `guest_contributions` (por `type`, con moderación) | `playlist`, `fotomural`, `respuesta` |

**Regla:** un dato nuevo va en columnas tipadas de una tabla propia, nunca en JSON. El editor sigue
enviando cada módulo como JSON dentro del formulario, pero es solo el formato de envío.

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
| 12 | Ubicación, personas destacadas, código de vestimenta, opciones de regalo, medios y fotos post evento pasaron a tablas propias, con orden e índices; después, todo lo demás (ver 7.8) | Migraciones, modelos y `app/Modules` |
| 13 | El voto se relaciona con la encuesta por su fila: se completó la relación, se movió la clave única a `(invitation_poll_id, voter_key)` y se eliminó el `poll_id` textual | Migración `drop_textual_poll_id`, `ContributionController@votePoll`, `InvitationModuleService::pollResultsFor` |
| 14 | El guardado del editor ya era una transacción; ahora además reutiliza las filas en vez de borrarlas y recrearlas, así los ids no cambian | `Modules/Concerns/ReplacesOrderedRows` |
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

- ~~Dejar de escribir el JSON (punto 12).~~ Hecho: ver 7.8.
- **Votos sin encuesta (punto 13).** La migración eliminó los votos que apuntaban a encuestas que ya
  no existían (en esta base no había ninguno). Si al desplegar en producción hay muchos, conviene
  guardarlos antes en una tabla de archivo.
- **Datos en producción.** `invitation_data` e `invitation_settings` ya no existen y el comando
  `invitations:migrate-json` se eliminó. Si producción tiene invitaciones reales (no solo muestras),
  hay que pasar sus datos a las tablas nuevas **antes** de correr la migración `drop_json_module_storage`.
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
### 7.4 Frontend, diseño y accesibilidad — implementada

| # | Qué se hizo | Dónde |
| --- | --- | --- |
| 22 | La invitación se sirve con la clase `no-js`, que el primer script de la cabecera quita al arrancar. Sin JavaScript todo se ve plano y completo; lo que de verdad lo necesita (confirmar, votar, sugerir, subir fotos) avisa dentro de `<noscript>` | `shell/head`, bloque «Sin JavaScript» de `invitation/base.css`, avisos en `rsvp`, `polls`, `playlist` y `fotomural` |
| 22 | Un vigía devuelve la clase si Alpine no arrancó a los 6 segundos: también cubre el caso de que el bundle no llegue, no solo el de JavaScript desactivado | `shell/head` |
| 22 | Los datos bancarios viven en un `<template x-teleport>` que sin Alpine nunca se pinta: ahora hay una copia plana en `<noscript>`. El video se sirve con `controls` y el reproductor propio los quita al iniciarse | `regalos.blade.php`, `video.blade.php`, `resources/js/video-player.js` |
| 23 | Los tonos apagados no llegaban a 4.5:1 en tres de las cuatro paletas: se recalcularon las mezclas y el dorado decorativo se separó en `--inv-accent-deco` para no apagar los números grandes ni el hashtag | `invitation/base.css` y los cuatro temas |
| 23 | Enlace «Saltar al contenido», anillo de foco con contraste suficiente, y el menú devuelve el foco al botón al cerrarse y atrapa el tabulador mientras tapa la página | `templates/*`, `shell/nav`, `shell/scripts` |
| 23 | Cada foto de la galería y del post evento puede llevar descripción, que se guarda en `alt_text` y llega al `alt` de la invitación | panel de galería y post evento, `InvitationModuleRules`, `gallery-stack`, `post-event` |
| 23 | El panel de Estética mide el contraste de los tonos derivados, no solo texto sobre fondo, y avisa cuál no se lee | `panels/estetica`, `contrastChecks()` en el editor |
| 24 | Página interna `/admin/sistema-visual` con los colores del sitio, la tipografía, los componentes que ya existen y las cuatro paletas con su contraste medido | `Admin\DesignSystemController`, `admin/design-system.blade.php`, `Support\ColorContrast` |
| 25 | El editor marca con un punto los apartados incompletos y resume, encima de los botones, todo lo que falta, con un atajo a cada apartado | `editor/sidebar`, `moduleIssues()`/`pendingIssues` en el editor |
| 25 | Publicar dejó de ser un selector escondido en General: hay botón de publicar y de despublicar, con el estado en texto claro | `editor/sidebar`, `publish()`/`unpublish()` |

**Lo que se comprobó**

- Con el navegador sin JavaScript: la invitación mide 11 256 px de alto, con itinerario, ubicación y
  regalos visibles (opacidad 1), la apertura y el menú ocultos, los tres grupos de vestimenta uno
  debajo de otro y los datos del banco con su QR a la vista. Con JavaScript vuelve el comportamiento
  normal y el video pierde los controles nativos.
- Con teclado: al abrir el menú el foco cae en el primer enlace y, al pulsar Escape, vuelve al botón.
- Las cuatro paletas pasan AA en las ocho piezas que mide `ColorContrast` (la más ajustada es
  «Etiquetas y ayudas» del bautizo, 4.58:1).
- La lógica de «qué falta» se ejecutó con los datos reales de una invitación: con todo cargado avisa
  solo del cliente sin asignar, y al vaciar módulos encendidos aparece un aviso por cada uno.

**Pendiente**

- **Descripción de fotos en la portada y en la ubicación.** El `alt` por foto está en la galería y en
  el post evento; la foto de portada y la del lugar siguen con `alt` vacío (son decorativas, pero la
  del lugar podría describirse).
- **Repaso con un lector de pantalla real.** Se verificó el recorrido con teclado y el contraste
  medido, no la experiencia completa con NVDA o TalkBack.
- **La vista previa del editor en pantallas chicas.** Sigue siendo una columna aparte; en portátiles
  angostos el editor queda apretado.
### 7.5 Compartir, SEO y marketing — implementada

| # | Qué se hizo | Dónde |
| --- | --- | --- |
| 26 | Cada invitación comparte su nombre, fecha, lugar y foto de portada recortada por Cloudinary a 1200×630 en JPG. El enlace personal nombra al invitado; la muestra de la home no, porque su invitado es ficticio | `ShareMeta`, `InvitationPage::share()`, `CloudinaryImage::card()`, `shell/head` |
| 26 | Sin foto de portada, la invitación usa la tarjeta de su tipo de evento. La portada del sitio y las páginas por evento tienen su propia tarjeta, recortada de las fotos del sitio | `public/images/share`, `bida:imagenes-compartir`, `layouts/site` |
| 27 | Cuatro páginas por tipo de evento con la apertura de su plantilla en el teléfono, lo propio de ese evento, la muestra interactiva, precios, preguntas marcadas con `FAQPage` y enlaces entre ellas | `EventLandingController`, `landing.blade.php`, `config/bida.php` (`landings`) |
| 27 | Cabecera, precios, preguntas y pie salieron de la portada a parciales, para no duplicarlos. La portada enlaza cada página desde su plantilla y desde el pie | `site/partials/*`, `home.blade.php` |
| 27 | `sitemap.xml` con la portada y las páginas por evento; las invitaciones y las muestras siguen fuera | `EventLandingController@sitemap` |
| 28 | Los botones de WhatsApp agregan al final del mensaje un código con la página, la fuente y la campaña: `Ref. BODA-FB-MAYO`. El origen (`utm_*` o `?ref=` para impresos y QR) se recuerda 30 días | `LeadSource`, `CaptureLeadSource`, `config/bida.php` (`lead_sources`) |
| 28 | Un comando arma el enlace de cada campaña y muestra el código que va a llegar | `bida:enlace-campana` |

**Lo que se comprobó**

- Etiquetas de la muestra de boda: «Camila & Andrés · Nos casamos», «Sábado 12 de diciembre · 16:00 h ·
  Hacienda Los Molles» y la foto de portada por Cloudinary en `c_fill,g_auto,w_1200,h_630,f_jpg`.
- Las cinco tarjetas del sitio miden 1200×630; se revisaron los recortes (en bautizo y cumpleaños se
  ajustó el encuadre para no cortar las caras).
- Una visita con `?utm_source=facebook&utm_campaign=Mayo 2026` a la página de bodas deja la cookie y el
  mensaje termina en «Ref. BODA-FB-MAYO2026»; otra visita días después, sin parámetros, a la de XV años
  conserva la fuente. Sin campaña, la portada manda «Ref. WEB».
- Las páginas por evento se revisaron en escritorio y en 390 px.

**Pendiente**

- **Probar la tarjeta en un chat real.** WhatsApp solo lee la vista previa de una dirección pública con
  HTTPS: hay que hacerlo en producción (o con el depurador de Facebook, que usa las mismas etiquetas).
  WhatsApp guarda la tarjeta en caché: si se cambia la foto de portada, puede tardar en actualizarse.
- **Declarar el mapa del sitio.** Agregar `Sitemap: https://<dominio>/sitemap.xml` a `public/robots.txt`
  con el dominio definitivo y enviarlo en Google Search Console.
- **`APP_URL` en producción.** Los enlaces de `sitemap.xml`, las imágenes para compartir y el comando de
  campañas usan `APP_URL`; en local sale `http://localhost`.
- **Medir resultados.** El código llega en el mensaje, pero nadie lo anota: conviene llevar una hoja con
  los contactos por código para saber qué campaña vende.
### 7.6 Pruebas, calidad y operación — implementada

| # | Qué se hizo | Dónde |
| --- | --- | --- |
| 29 | Una prueba por método de la policy (`view`, `export`, `update`, `manageGuests`, `moderateContributions`), en la regla y en cada ruta, frente a otro cliente; incluye consultar y descargar un archivo ajeno | `InvitationPolicyMatrixTest` |
| 29 | Voto simultáneo: se inserta el voto rival entre la comprobación y el guardado, y la clave única lo frena. Confirmaciones seguidas: gana la última y nunca pasa de los lugares | `ConcurrencyAndLimitsTest` |
| 29 | Subidas en el fotomural y en el editor: archivo grande, PDF, SVG con script, PHP con extensión de foto, ejecutable como video e imagen como audio | `ConcurrencyAndLimitsTest` |
| 29 | Los cuatro endpoints públicos y el login responden 429 con «Demasiados intentos…» | `ConcurrencyAndLimitsTest` |
| 29 | Las cuatro plantillas se arman vacías con todos los módulos encendidos y completas, en el enlace general y el personal | `TemplateRenderMatrixTest` |
| 30 | Peticiones lentas en su propio log, con el nombre de la ruta y sin el código del invitado; trabajos fallidos al log en el momento | `LogSlowRequests`, `AppServiceProvider::reportFailedJobs()`, canales `performance` y `operations` |
| 30 | Respaldo diario de la base (PostgreSQL, MySQL o SQLite), medios locales y manifiesto de Cloudinary, con retención | `bida:respaldo`, `BackupService` |
| 30 | Prueba de restauración semanal en una base temporal que se borra al terminar | `bida:probar-respaldo` |
| 30 | Revisión cada hora con aviso por correo: trabajos fallidos, cola sin worker, exportaciones atascadas, antigüedad del respaldo y de su prueba, disco | `bida:salud`, `config/operations.php` |
| 30 | Procedimiento escrito de despliegue, vuelta atrás, cron, worker, respaldo fuera del servidor y restauración | `docs/despliegue.md`, `docs/operacion.md` |
| 31 | Flujo de GitHub Actions en cada push y pull request: `npm run build`, `pint --test` y `php artisan test` | `.github/workflows/ci.yml` |
| 31 | Pint se pasó a todo el código para que el flujo arranque en verde (solo formato: espacios, orden de imports, llaves) | 45 archivos |

**Lo que se comprobó**

- Suite completa: 126 pruebas (800 aserciones), 32 nuevas en este bloque.
- Contra la base PostgreSQL local: el respaldo pesó 32 KB y listó 75 medios de Cloudinary; la
  restauración cargó 28 tablas y 578 filas sin diferencias y la base temporal ya no existe;
  `bida:salud` salió sin fallas.

**Pendiente**

- **Copiar los respaldos fuera del servidor** (`rclone` u otro, ver `docs/operacion.md`) y activar
  Backup en Cloudinary: el manifiesto dice qué hay, no guarda los archivos.
- **Configurar el correo** (`MAIL_*`) y `OPERATIONS_ALERT_EMAIL` en producción; sin eso las alertas
  solo quedan en el log.
- **El primer push** a GitHub dirá si el flujo de CI pasa también allá (no se pudo ejecutar localmente
  el runner de Actions).
- **Disco local:** la revisión marcó 11 % libre en este equipo, apenas sobre el umbral de alerta.

### 7.7 Limpieza — hecha

| Elemento | Qué se hizo |
| --- | --- |
| `resources/views/welcome.blade.php` | Eliminado (ninguna ruta lo usaba) |
| `public/images/site/nosotros.webp` | Eliminado |
| `routes/console.php` | Reemplazado por las tareas programadas (respaldo, prueba, purga, salud) |
| `config/optimizations.php` → `blade`, `database`, `cdn` | Quitadas: ningún código las leía |
| `app/Providers/BladeServiceProvider.php` | Eliminado: no estaba registrado y apuntaba a un componente que no existe; también se quitó el registro comentado |
| `InvitationDefaults::modules()` | Eliminado: nadie lo llamaba y era lo único que hacía depender a la aplicación de los datos de prueba |
| Plantillas de ejemplo (`BodaJardinDemoSeeder`, `XvSofiaModuleData`, …) | Se mantienen, marcadas en su comentario como datos de prueba |
| Cliente de prueba | Nuevo `ClientUserSeeder`, incluido en `DatabaseSeeder` |
### 7.8 Datos sin JSON, editor por evento y tarjetas de temporada — hecha

| Qué se hizo | Dónde |
| --- | --- |
| Todo lo que quedaba en JSON (portada, textos de sección, colores, visibilidad, hashtag, RSVP, cuentas, sobres, tienda, `meta` y `examples`) pasó a tablas; se eliminaron `invitation_data`, `invitation_settings`, `InvitationStructuredDataService` y `invitations:migrate-json` | Migraciones `2026_09_17_*`, `app/Modules` |
| Cada módulo es una unidad con su contrato (tablas, carga, guardado, reglas, vista y panel) y se registra en una línea | `Modules/Module.php`, `ModuleRegistry.php`, `config/modules.php` |
| Perfiles de evento: el editor muestra solo las pestañas del evento, pide dos nombres en la boda, la edad en el cumpleaños, rotula los destacados según el evento y avisa lo que falta según el perfil | `app/EventProfiles`, `editor/script.blade.php`, paneles `hero`, `destacados`, `general` |
| Primero se elige Invitación o Tarjeta; el panel filtra por tipo | `panels/general.blade.php`, `Admin\DashboardController` |
| Tarjeta del Día del Amor con carta que se abre, dedicatoria, contador «juntos desde», galería, música y respuesta privada al cliente | `tarjeta-amor.blade.php`, `css/cards/amor.css`, `Modules/Card/*`, `POST /p/{slug}/respuesta` |
| Muestra `tarjeta-ana-luis`, página `/tarjetas-dia-del-amor` con código `AMOR` y vista previa «Para Ana, de Luis» | `showcase/tarjeta-ana-luis.php`, `config/bida.php`, `ShareMeta::forCard` |
| Receta para nuevas temporadas y módulos | `docs/temporadas.md` |

**Pendiente**

- **Precio de las tarjetas:** la página de campaña pide el precio por WhatsApp hasta definirlo.
- **Fotos propias de la muestra:** la tarjeta de muestra reusa las fotos de la boda.
- **Datos reales en producción:** ver el aviso de 7.2 antes de migrar.

---

## 8. Hoja de ruta sugerida

| Fase | Foco | Trabajos |
| --- | --- | --- |
| **1. Seguridad** | ✔ Hecho | Indexación, caché del enlace personal, proxies, votos, contraseña del cliente, subidas, cabeceras, tokens y moderación (ver 7.1). Falta configurar `TRUSTED_PROXIES` y exigir la CSP en producción |
| **2. Datos** | ✔ Hecho | Todo en tablas relacionadas, sin columnas JSON; módulos con contrato propio; voto por relación real; guardado que reutiliza filas; borrado y retención de archivos (ver 7.2 y 7.8) |
| **3. Rendimiento** | ✔ Hecho | Paginación y conteos en la base, caché con candado, exportaciones en cola, imágenes por tamaño y comando de medición. Falta encender la caché y correr un worker en producción |
| **4. Experiencia** | ✔ Hecho en parte | Lectura sin JavaScript, contraste AA, foco con teclado, `alt` por foto, sistema visual y editor con avisos y botón de publicar (ver 7.4) y limpieza de código sin uso (7.7) |
| **5. Crecimiento** | ✔ Hecho en parte | Vista previa al compartir, páginas por evento con `sitemap.xml`, código de origen en WhatsApp (ver 7.5) y tarjetas de temporada (7.8). Falta declarar el sitemap con el dominio, llevar la cuenta de contactos por campaña y definir el precio de las tarjetas |
| **6. Calidad y operación** | ✔ Hecho | Pruebas de permisos, concurrencia, subidas, límites y plantillas; respaldos con restauración probada; alertas; CI (ver 7.6). Falta copiar los respaldos fuera del servidor y configurar el correo de alertas |

---

## 9. Cómo mantener este documento

- Cuando se agregue un archivo importante, sumar su fila en la sección 5.
- Cuando cambie el flujo de datos o se migre un módulo, actualizar las secciones 3 y 6.
- Cuando una sugerencia se implemente, moverla de la sección 7 a una línea de historial o borrarla.
- Este documento describe el estado real del repositorio: si algo aquí ya no coincide con el código,
  el código manda y el documento se corrige.
