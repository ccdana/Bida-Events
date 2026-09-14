# Mapa del proyecto Bida-Events

Documento de arquitectura, responsabilidades, riesgos y hoja de ruta técnica de Bida-Events. Describe el estado actual del repositorio y prioriza las acciones que más reducen riesgo, consumo de datos, tiempos de respuesta y deuda estructural.

El proyecto es una aplicación Laravel 12 para crear y publicar invitaciones digitales de eventos. Tiene tres superficies principales:

- Panel administrativo para crear invitaciones, editar módulos, gestionar clientes, invitados y medios.
- Portal de cliente para consultar eventos, invitados y exportar reportes.
- Invitación pública con RSVP, playlist, fotomural, encuestas, galería, itinerario, multimedia y datos personalizados.

## Estado arquitectónico actual

La aplicación está en una transición importante: los módulos comenzaron como bloques JSON en `invitation_data.json_data`, pero ya existen tablas normalizadas para configuración, itinerario, galería y encuestas. La aplicación conserva un fallback al JSON para invitaciones todavía no migradas.

### Estado de la normalización

| Área | Estado actual | Fuente preferida | Pendiente |
| --- | --- | --- | --- |
| Configuración visual y visibilidad | Normalizada en `invitation_settings` | Tablas | Migrar todas las invitaciones y retirar el fallback |
| Itinerario | Normalizado en `invitation_itinerary_items` | Tablas | Migrar datos antiguos y eliminar lecturas JSON |
| Galería | Normalizada en `invitation_gallery_images` | Tablas | Migrar todos los tipos de galería y medios post-evento |
| Encuestas | Normalizadas en `invitation_polls` y `invitation_poll_options` | Tablas | Completar migración y usar relación de voto como fuente única |
| Ubicaciones | Sigue dentro de `invitation_data` | JSON | Crear tabla de sedes si habrá varias ubicaciones |
| Personas destacadas | Sigue dentro de `invitation_data` | JSON | Crear tabla con grupo y orden |
| Código de vestimenta | Sigue dentro de `invitation_data` | JSON | Separar recomendaciones, colores y restricciones |
| Regalos | Sigue dentro de `invitation_data` | JSON | Separar opciones públicas de datos bancarios sensibles |
| Audio, video y medios | Parcialmente en JSON/Cloudinary | JSON y servicio de medios | Crear catálogo de medios por invitación |
| Playlist y fotomural públicos | Tablas operativas | `guest_contributions` | Añadir moderación y retención |
| RSVP | Tabla `guests` | Tabla relacional | Auditar transacciones, historial y límites |

La regla de diseño es: **las tablas representan entidades repetibles, ordenables, filtrables o consultables; el JSON queda para configuración flexible, pequeña y específica de una plantilla**.

---

## Prioridad 0: base de datos y relaciones

Esta es la primera línea de trabajo. No conviene seguir agregando caché, índices aislados o funcionalidades sobre una estructura que todavía mezcla configuración y colecciones completas en JSON.

### 1. Mantener un modelo híbrido, no eliminar JSON de forma impulsiva

Debe conservarse JSON sólo en casos como colores, tipografías, toggles, textos simples del hero, mensajes RSVP o extensiones específicas de una plantilla. Deben ser tablas los datos que se repiten o cambian de manera independiente:

- Itinerario y sus elementos ordenados.
- Imágenes de galería, portada y galerías post-evento.
- Preguntas y opciones de encuestas.
- Personas destacadas, padrinos y cortejo.
- Elementos del código de vestimenta.
- Ubicaciones y sedes.
- Opciones de regalos y medios multimedia.

Esto evita leer, deserializar y reescribir un módulo completo para cambiar un solo registro. También permite índices, paginación, restricciones y actualizaciones concurrentes más seguras.

### 2. Modelo relacional objetivo

```text
users
└── invitations
    ├── invitation_settings
    ├── invitation_locations
    ├── invitation_itinerary_items
    ├── invitation_gallery_images
    ├── invitation_featured_people
    ├── invitation_dress_code_items
    ├── invitation_polls
    │   └── invitation_poll_options
    ├── invitation_gift_options
    ├── invitation_media
    ├── guests
    │   ├── guest_contributions
    │   └── poll_votes
    └── invitation_features
```

### 3. Relaciones, claves foráneas y restricciones

- Cada tabla hija debe tener `invitation_id` con foreign key y `cascadeOnDelete()` cuando el contenido no tenga valor histórico independiente.
- Para datos que deban conservarse como auditoría, usar `deleted_at`, Soft Deletes o una tabla de archivo en vez de borrar en cascada.
- `invitation_settings` debe tener una sola fila por invitación mediante `unique(invitation_id)`.
- Los listados deben incluir `sort_order` e índice `(invitation_id, sort_order, id)` para devolver orden estable sin ordenar grandes colecciones en PHP.
- Las encuestas deben usar `invitation_polls.id` como relación interna. `poll_key` debe conservarse como identificador estable visible para el editor y compatibilidad histórica.
- `invitation_poll_options` debe pertenecer a `invitation_polls` y tener orden único por encuesta.
- `poll_votes.invitation_poll_id` debe convertirse en la relación principal después de completar la migración; `poll_id` textual puede eliminarse en una migración posterior.
- Las relaciones con `guests` deben usar `nullOnDelete()` si se necesita conservar la interacción cuando se elimina un invitado.
- Validar que un `poll_vote` use una opción perteneciente a la misma encuesta. No confiar sólo en `option_index` enviado por el navegador.
- Aplicar límites de longitud, estados válidos y reglas de consistencia en Form Requests y, cuando el motor lo permita, en `CHECK` constraints.

### 4. Tablas recomendadas y responsabilidades

| Tabla | Campos relevantes | Índice o regla principal |
| --- | --- | --- |
| `invitation_settings` | `template`, `colors`, `typography`, `module_visibility`, `extra` | `unique(invitation_id)` |
| `invitation_locations` | nombre, dirección, latitud, longitud, enlaces y orden | `(invitation_id, sort_order, id)` |
| `invitation_itinerary_items` | hora, título, icono, descripción, `meta`, orden | `(invitation_id, sort_order, id)` |
| `invitation_gallery_images` | colección, URL, tipo, portada, estado, `meta`, orden | `(invitation_id, collection, sort_order, id)` |
| `invitation_featured_people` | grupo, nombre, iniciales, rol, detalle, mensaje, orden | `(invitation_id, group, sort_order, id)` |
| `invitation_dress_code_items` | categoría, título, descripción, ejemplo, color, orden | `(invitation_id, category, sort_order, id)` |
| `invitation_polls` | `poll_key`, pregunta, tipo, estado y orden | `unique(invitation_id, poll_key)` |
| `invitation_poll_options` | encuesta, etiqueta, valor y orden | `unique(poll_id, sort_order)` |
| `invitation_gift_options` | categoría, título, descripción, enlace y orden | `(invitation_id, sort_order, id)` |
| `invitation_media` | tipo, proveedor, URL, poster, metadatos y estado | `(invitation_id, type, status)` |
| `guests` | identidad, pases, RSVP, mesa y token | `(invitation_id, status)` y token único |
| `guest_contributions` | tipo, invitado, texto, archivo y estado de moderación | `(invitation_id, type, created_at)` |
| `poll_votes` | invitación, encuesta, opción, votante y fecha | unicidad por encuesta y votante |

Los nombres exactos pueden adaptarse al código existente, pero las responsabilidades no deben volver a concentrarse en un único JSON gigante.

### 5. Migración gradual y verificable

El repositorio ya incluye `MigrateInvitationJsonModules`, `InvitationStructuredDataService` y migraciones para configuración, itinerario, galería y encuestas. El procedimiento recomendado es:

1. Ejecutar `php artisan invitations:migrate-json --dry-run` y revisar diferencias, advertencias y filas omitidas.
2. Migrar por lotes o por invitación usando `--invitation`, con respaldo de la base de datos.
3. Ejecutar la migración real y repetir una verificación de conteos, orden, URLs, claves de encuestas y opciones.
4. Leer primero las tablas normalizadas y usar `invitation_data` sólo como fallback observable.
5. Migrar ubicaciones, destacados, dress code, regalos y media.
6. Registrar cualquier lectura que todavía dependa del JSON.
7. Cuando ninguna invitación dependa del fallback, crear una migración de limpieza. No editar migraciones históricas ya ejecutadas.

La sincronización del editor debe ejecutarse dentro de una transacción. Para colecciones grandes, preferir `upsert()` o cambios por registro; evitar borrar y recrear todo si no es necesario, porque eso aumenta locks, actualiza timestamps y puede romper referencias históricas.

---

## Mapa del código

### Raíz

| Ruta | Responsabilidad | Recomendación |
| --- | --- | --- |
| `artisan` | Entrada de comandos Laravel | Documentar comandos de migración, retención y diagnóstico |
| `composer.json` / `composer.lock` | Dependencias PHP | Actualizar con revisión de seguridad y mantener lock versionado |
| `package.json` / `package-lock.json` | Dependencias frontend y Vite | Ejecutar auditoría npm y mantener builds reproducibles |
| `phpunit.xml` | Configuración de pruebas | Separar bases de datos y servicios de test de producción |
| `vite.config.js` | Entrada y compilación de assets | Mantener code-splitting y revisar tamaño de chunks |
| `.env` / `.env.example` | Configuración y secretos locales | Nunca exponer `.env`; documentar todas las variables nuevas en `.env.example` |
| `PROJECT_MAPA.md` | Este mapa | Actualizarlo junto con migraciones y cambios de arquitectura |

### Aplicación

| Área | Componentes actuales | Riesgo o siguiente paso |
| --- | --- | --- |
| `app/Http/Controllers` | Home, autenticación, admin, cliente y endpoints públicos | Mantener controladores delgados; mover reglas a servicios y policies |
| `app/Models` | Usuarios, invitaciones, módulos, invitados, contribuciones y votos | Completar relaciones normalizadas y reducir accessors que cargan JSON completo |
| `app/Http/Requests` | Validación de invitaciones, clientes e invitados | Añadir reglas de autorización y validación de límites/propiedad |
| `app/Policies` | Autorización de invitaciones | Revisar que las acciones reales tengan métodos y tests; no depender sólo del middleware de rol |
| `app/Services` | Caché, módulos, migración estructurada, medios y preview | Separar servicios por dominio y hacer explícitas las transacciones |
| `app/Events` / `app/Listeners` | Invalidación de invitación, contribuciones y votos | Mantener invalidación idempotente y probar concurrencia |
| `app/ViewModels` | Datos de dashboards, reportes y editor | No cargar colecciones completas cuando sólo se necesitan métricas |
| `app/Exports` | Excel y PDF de invitados e invitación | Usar consultas por lotes y colas para reportes grandes |
| `app/Support` | Defaults, plantillas, mapas, YouTube, imágenes y PDF | Validar entradas externas y cachear sólo respuestas seguras |
| `app/Console` | Comandos de mantenimiento y migración | Añadir comandos de diagnóstico, archivado y limpieza |

### Frontend y vistas

- `resources/js/app.js` coordina Alpine, Axios y cargas dinámicas por DOM.
- `gallery-stack.js`, `itinerary-scroll.js`, `lottie-icons.js`, `video-player.js` y `site.js` son módulos de interacción y deben permanecer bajo demanda.
- `resources/css/` separa estilos de invitación, sitio y paneles.
- `resources/views/home.blade.php` y el layout de sitio atienden la portada y login.
- `resources/views/layouts/` contiene layouts de sitio, admin, editor y cliente.
- `resources/views/admin/` contiene editor y gestión administrativa.
- `resources/views/pages/` contiene vistas consumidas directamente por controladores y exportaciones.
- `resources/views/invitations/templates/` contiene las plantillas públicas XV, boda, bautizo y cumpleaños.
- `resources/views/invitations/partials/` contiene módulos, shell, temas y componentes compartidos.

Debe mantenerse una fuente canónica para cada vista. La coexistencia de `admin/` y `pages/admin/`, o de varias plantillas equivalentes, debe resolverse con una migración de vistas gradual y referencias de controlador verificadas.

---

## Seguridad

### Autenticación y sesiones

- Mantener login limitado por usuario e IP mediante `RateLimiter`.
- Usar `Hash`/password hashing para autenticación. La contraseña de acceso visible al administrador debe tratarse como dato sensible y no debería poder recuperarse en texto plano.
- Revisar si `access_password` realmente necesita ser descifrable; preferir mostrarla una sola vez al crear el cliente y guardar únicamente el hash.
- Regenerar la sesión después del login, invalidarla al cerrar sesión y usar cookies `HttpOnly`, `Secure` y `SameSite` apropiadas.
- No registrar contraseñas, tokens QR, credenciales, payloads bancarios ni URLs privadas en logs.
- Aplicar expiración o rotación a tokens de invitados si el modelo de negocio lo permite.

### Autorización y control de propiedad

- Las rutas ya usan `admin`, `client`, `scopeBindings` y policies; conservar esa defensa en profundidad.
- Completar `InvitationPolicy` para que `update` y `manageGuests` expresen explícitamente las reglas actuales en vez de quedar como métodos que siempre devuelven `false`.
- Usar policies para invitaciones, invitados, archivos, exportaciones y módulos normalizados.
- Verificar siempre que un invitado, voto o contribución pertenezca a la invitación de la URL.
- No confiar en IDs, slugs, `poll_id`, `guest_token` ni `option_index` enviados por el cliente.

### Endpoints públicos

- Mantener throttle separado para RSVP, canciones, fotos y votos.
- Añadir límites por IP, invitación, sesión y token cuando el flujo lo permita.
- Validar MIME real, extensión, tamaño, dimensiones y contenido de las imágenes.
- Nunca ejecutar ni servir archivos subidos como código.
- Moderar contribuciones antes de mostrarlas si el evento necesita control editorial.
- Escapar textos de canciones, mensajes, nombres y metadatos en Blade; no permitir HTML arbitrario.
- Usar la restricción única de votos como última defensa contra solicitudes concurrentes, no sólo una consulta previa.

### Archivos, medios y terceros

- Mantener secretos de Cloudinary, mapas, YouTube y mail fuera del repositorio.
- Usar timeouts, límites de respuesta y manejo de errores en todas las llamadas HTTP externas.
- Restringir dominios remotos permitidos cuando se descarguen posters, fuentes o imágenes para PDF.
- Eliminar o marcar el asset de Cloudinary cuando se borre su fila local.
- Añadir políticas de retención y borrado de fotos, archivos temporales, PDFs y logs.

### Datos sensibles y privacidad

- Proteger datos bancarios, documentos, teléfonos y restricciones alimentarias con control de acceso mínimo.
- No exponer datos de cliente en la invitación pública salvo que el módulo lo requiera.
- Definir qué datos se conservan después del evento y cómo se atiende una solicitud de eliminación.
- Evitar incluir datos personales en URLs, logs, nombres de archivos y mensajes de error.

---

## Rendimiento y escalabilidad

### Consultas y memoria

- El dashboard admin no debe hacer `with('guests')->get()` para todas las invitaciones. Usar `withCount`, columnas explícitas y paginación.
- Los listados de invitados admin/cliente deben usar `paginate()` o `cursorPaginate()`, especialmente cuando se ordenan por nombre.
- Mantener índices alineados con filtros y orden: `guests (invitation_id, status)`, `guests (invitation_id, name, id)` y `guest_contributions (invitation_id, type, created_at)` después de validar con `EXPLAIN`.
- Evitar índices duplicados generados por claves únicas o foreign keys.
- En encuestas, usar `COUNT` y `GROUP BY` en SQL en lugar de traer todos los votos a PHP.
- Seleccionar sólo columnas necesarias y evitar accessors que disparen relaciones no cargadas.
- Usar `lazyById`, `chunkById` o cursores para exportaciones, migraciones y purgas.

### Caché

- Mantener cacheado el payload público normalizado por versión de `updated_at`.
- Invalidar settings, playlist, fotomural y resultados de encuestas cuando cambie su fuente.
- Evitar cache stampede con locks o estrategias de regeneración controlada.
- Usar Redis en producción si hay varias instancias o alto tráfico; el driver file no es suficiente como caché distribuida.
- No cachear respuestas que incluyan datos de un invitado identificado por token sin separar correctamente la clave.

### JSON y carga pública

- No transportar módulos deshabilitados ni colecciones que la plantilla no utiliza.
- Cargar relaciones normalizadas sólo cuando el módulo esté activo.
- No enviar todas las fotos originales: usar transformaciones Cloudinary, `f_auto`, `q_auto`, tamaños máximos y `srcset`.
- Mantener lazy loading para galerías, videos, Lottie y recursos fuera de pantalla.
- Mantener `prefers-reduced-motion` y pausar animaciones fuera del viewport.

### Colas y tareas pesadas

- Mover PDF, Excel, procesamiento de imágenes, borrado remoto y migraciones grandes a jobs.
- Configurar reintentos, backoff, timeouts, `failed_jobs` y alertas.
- No hacer llamadas lentas a mapas, YouTube o Cloudinary dentro de cada render público.
- Programar limpieza de sesiones, cachés, archivos temporales, contribuciones archivadas y logs.

### Medición

Antes y después de cada optimización medir:

- Tiempo P50/P95 de home, login, dashboard, editor, invitación pública, RSVP y endpoints de contribuciones.
- Número de consultas y memoria por pantalla.
- Filas examinadas y uso de índices con `EXPLAIN`/`EXPLAIN ANALYZE`.
- Tamaño HTML, JavaScript, imágenes y respuestas JSON.
- Tasa de errores, jobs fallidos, cache hit ratio y uso de almacenamiento.

---

## Integridad y operaciones de base de datos

- No editar migraciones históricas ya ejecutadas; crear migraciones nuevas.
- Probar `migrate`, `migrate:fresh`, rollback y despliegue sobre una copia de datos antes de migraciones destructivas.
- Confirmar compatibilidad entre MySQL/MariaDB/SQLite, especialmente para `enum`, `change()`, JSON, índices y `CHECK`.
- Usar transacciones para cambios relacionados de invitación, settings, hijos y votos.
- Crear respaldos antes de eliminar `plans`, limpiar JSON o purgar contribuciones.
- Documentar migraciones destructivas, duración estimada, locks y plan de reversión.
- Evitar `enum` cuando los estados puedan evolucionar; preferir strings validados o catálogos cuando aplique.
- Usar `created_at` y `updated_at` coherentemente. Las tablas de actividad deben definir claramente si son inmutables.
- Añadir `status`, `moderation_status` o Soft Deletes a fotos y contribuciones si deben ocultarse sin perder historial.
- Mantener seeders idempotentes y separados entre catálogos, demos y datos de prueba.

---

## Pruebas

### Unitarias

- `InvitationStructuredDataService`: normalización, datos inválidos, orden, duplicados y fallback.
- `InvitationModuleService`: visibilidad, URLs de calendario y resultados agrupados.
- Parsers de mapas y YouTube.
- Generación y validación de tokens.
- Reglas de reportes y cálculos de pases.

### Integración y seguridad

- Login, rate limiting, regeneración de sesión y separación admin/cliente.
- Policies: un cliente no puede ver, editar, exportar ni gestionar la invitación de otro.
- `scopeBindings`: un invitado ajeno no puede modificarse mediante otra URL.
- RSVP transaccional y límites de pases.
- Voto duplicado bajo solicitudes concurrentes.
- Subida de archivos inválidos, grandes, no permitidos o con URL peligrosa.
- Migración JSON en modo `dry-run`, migración real, verificación y fallback.

### Rendimiento y regresión

- Fixtures con miles de invitados, contribuciones y votos.
- Pruebas de consultas máximas por pantalla.
- Pruebas de paginación y exportación por lotes.
- Render de las cuatro plantillas con módulos vacíos, completos y normalizados.
- Verificación de que una invitación migrada produce el mismo contenido visible que el JSON original.

---

## Hoja de ruta priorizada

### Fase 1: seguridad e integridad

1. Completar policies de invitación e invitado y cubrirlas con tests.
2. Revisar almacenamiento de `access_password`, tokens y datos sensibles.
3. Auditar uploads, dominios externos, logs y configuración de cookies.
4. Verificar throttling y restricciones únicas bajo concurrencia.

### Fase 2: normalización y relaciones

1. Ejecutar y verificar `invitations:migrate-json --dry-run`.
2. Migrar settings, itinerario, galería y encuestas por lotes.
3. Cambiar editor, preview, plantillas y exportaciones a las relaciones.
4. Crear ubicaciones, destacados, dress code, regalos y media como tablas.
5. Eliminar gradualmente `poll_id` textual y el fallback JSON cuando no haya lecturas.

### Fase 3: rendimiento de datos

1. Paginar invitados y dashboard.
2. Convertir resultados de encuestas a agregaciones SQL.
3. Revisar índices duplicados con `EXPLAIN`.
4. Reducir columnas y relaciones cargadas.
5. Añadir retención y archivado para contribuciones, votos, medios y logs.

### Fase 4: operación y experiencia

1. Pasar tareas pesadas a colas.
2. Usar Redis y locks de caché en producción.
3. Optimizar Cloudinary, `srcset`, lazy loading y chunks.
4. Añadir observabilidad, alertas y métricas de rendimiento.
5. Completar pruebas de carga y regresión de plantillas.

## Comandos de referencia

```bash
php artisan migrate:status
php artisan migrate
php artisan invitations:migrate-json --dry-run
php artisan invitations:migrate-json --invitation=xv-isabella
php artisan optimize:clear
php artisan queue:work
php artisan test
npm run build
```

El comando de migración debe ejecutarse primero en simulación y con respaldo. `queue:work` sólo debe ejecutarse en producción mediante un supervisor o servicio administrado, no como proceso manual permanente.
