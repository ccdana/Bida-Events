# Mapa del proyecto Bida Events

> Revisión estática del repositorio al 7 de octubre de 2026. Describe lo que está implementado en el código y en los archivos versionados; no confirma qué valores tiene el servidor real en su archivo `.env`, cómo está configurado Cloudflare ni qué versión está desplegada. No se ejecutaron pruebas en esta revisión.

## 1. Qué es el sistema

Bida Events es una aplicación web Laravel para crear, editar y publicar invitaciones digitales de eventos y tarjetas interactivas. Los clientes administran invitados y respuestas; el equipo administra cuentas e invitaciones; los revendedores pueden crear y gestionar invitaciones según su suscripción.

El sitio se renderiza en el servidor con Blade. Alpine.js añade interacción en el navegador. Las invitaciones y tarjetas comparten módulos de contenido, perfiles de evento y plantillas visuales. Los RSVP, votos y aportes se registran en la base de datos; los archivos se guardan en Cloudinary si está configurado o en almacenamiento local.

**Stack detectado:** PHP 8.4, Laravel 12, PostgreSQL en los archivos de Docker, SQLite en pruebas, Blade, Alpine.js 3, Tailwind CSS 4, Vite 7, Cloudinary opcional para medios, DomPDF y Laravel Excel para exportaciones.

La aplicación tiene cinco áreas principales:

| Área | Quién la usa | Rutas principales |
| --- | --- | --- |
| Sitio público y páginas SEO | Cualquier visitante | `/`, páginas por tipo de evento, `/guia-invitaciones-digitales`, `/hazlo-tu`, páginas legales |
| Invitaciones y tarjetas públicas | Invitados con enlace | `/p/{slug}`, `/p/{slug}/i/{token}`, `/muestra/{slug}` |
| Control de entrada | Personal de puerta | `/puerta/{doorToken}`, `/entrada/{slug}/{token}` |
| Administración | Usuarios con rol administrador | `/admin/**` |
| Portal del cliente y revendedor | Clientes y revendedores autenticados | `/client/**` |

Los perfiles registrados incluyen bodas, XV años, bautizos, cumpleaños, graduaciones, baby shower, lienzo en blanco, Halloween y tarjetas de temporada o historia. La lista está en `config/event_profiles.php`; las plantillas y nombres, en `app/Support/InvitationTemplates.php`.

## 2. Cómo se ejecuta y qué despliega cada configuración

Hay configuraciones diferentes para desarrollo y producción:

| Configuración | Servicios que declara | Observación |
| --- | --- | --- |
| `compose.yaml` | Laravel con servidor de desarrollo, PostgreSQL 17, Vite y worker de cola | Entorno local montado desde el código del equipo. No representa el servidor productivo. |
| `compose.prod.yaml` | PHP-FPM, Nginx, PostgreSQL 17, Redis, worker y scheduler | Imagen de producción; Nginx entrega assets y envía PHP a PHP-FPM. Redis es el cache store; la cola sigue configurada con la conexión `database`. |
| `Dockerfile` | Etapas de desarrollo, dependencias, assets y producción | La imagen productiva instala dependencias sin desarrollo, compila Vite y usa OPcache. |
| `docs/despliegue.md` | Despliegue manual en VPS | Es una guía operativa aparte de Compose; verificar sus instrucciones contra la infraestructura elegida. |

El archivo de producción monta `storage` como volumen y deja PostgreSQL y Redis dentro de la red de Compose. Nginx publica los puertos 80 y 443. **Hay una discrepancia que conviene resolver antes del despliegue:** el comentario inicial de `compose.prod.yaml` dice que escucha en 8080, pero el servicio publica `80:80` y `443:443`. Si el origen debe aceptar tráfico únicamente desde Cloudflare, esa restricción también debe estar aplicada en el firewall del servidor.

Para levantar el entorno local y publicar cambios, seguir `docs/docker.md` y `docs/despliegue.md`. Los valores de `.env.example` son de referencia, no evidencia de la configuración real. No leer ni copiar secretos de `.env` a documentación.

## 3. Recorrido de los datos

1. **Creación:** el panel de administración o el editor de revendedor crea una invitación y la asocia con su cliente, tipo de evento y plantilla.
2. **Validación:** las operaciones principales del editor usan Form Requests; otras acciones validan en el controlador y los módulos registrados aportan sus reglas de contenido.
3. **Guardado:** `InvitationModuleService` y `ModuleRegistry` coordinan el guardado de los módulos en una transacción. Cada módulo maneja sus datos y relaciones.
4. **Autorización:** middleware distingue administrador, cliente y revendedor; `InvitationPolicy` decide qué invitaciones, invitados, edición y exportaciones puede usar cada usuario.
5. **Publicación:** `Public\InvitationController` resuelve la invitación, perfil, datos de módulos y plantilla Blade. Las rutas de muestra cargan contenido de demostración sin guardar respuestas.
6. **Interacción:** RSVP, votos, fotos, canciones, respuestas de tarjetas y control de entrada usan rutas públicas con validación y límites de frecuencia.
7. **Caché:** `InvitationCacheService` guarda datos de invitación y módulos; eventos de actualización invalidan o precalientan esas claves. `CachePublicInvitations` controla los encabezados HTTP de las páginas públicas. Son mecanismos distintos y sus flags dependen de configuración.
8. **Exportaciones:** Excel y PDF se registran como exportaciones, se procesan con `GenerateInvitationExport` en la cola y se consultan o descargan desde el portal con autorización.

## 4. Datos y organización del código

El almacenamiento de módulos se normalizó: las tablas actuales guardan los campos y listas por relación; el formulario puede enviar estructuras JSON, pero eso no significa que el contenido completo se persista en una columna JSON genérica. La migración histórica de esa transición está en `database/migrations/2026_09_17_000008_drop_json_module_storage.php`.

| Grupo de datos | Entidades principales |
| --- | --- |
| Cuentas y roles | `users`, clientes, administradores, revendedores y pagos de suscripción |
| Invitaciones | `invitations`, tipo, estado, paquete, propietario y revendedor |
| Contenido de invitación | Tema, portada, textos, secciones, ubicaciones, itinerario, galería, vestimenta, regalos, medios, encuestas y opciones |
| Invitados e interacción | `guests`, confirmaciones, contribuciones, votos y marcas de entrada |
| Tarjetas | Dedicatorias, hitos, recuerdos, relatos, momentos, collage, marcos y aventuras |
| Operación | Exportaciones, settings, trabajos y caché |

Puntos de entrada para orientarse:

| Ruta del código | Responsabilidad |
| --- | --- |
| `routes/web.php` | Rutas públicas, acceso, paneles, editor, aportes y exportaciones |
| `app/Http/Controllers/` | Controladores separados por área: Admin, Client, Public y Auth |
| `app/Http/Requests/` | Validación de formularios de administración, clientes y acciones públicas |
| `app/Policies/InvitationPolicy.php` y `app/Http/Middleware/` | Autorización por rol, propietario y suscripción |
| `app/Modules/`, `config/modules.php` | Contratos, guardado y carga de módulos de invitación y tarjeta |
| `app/EventProfiles/`, `config/event_profiles.php` | Campos y vocabulario según el tipo de evento |
| `app/Models/` y `database/migrations/` | Esquema y relaciones Eloquent |
| `app/Services/`, `app/Support/` | Caché, subida de medios, reglas y lógica compartida |
| `app/ViewModels/` | Datos preparados para paneles, invitados y PDF |
| `app/Jobs/`, `app/Console/Commands/`, `routes/console.php` | Exportaciones, respaldos, retención, salud y scheduler |
| `resources/views/` | Pantallas Blade, paneles, layout y plantillas |
| `resources/js/`, `resources/css/`, `vite.config.js` | Interacción, estilos y compilación de assets |
| `config/` | Ajustes de aplicación, seguridad, caché, límites, medios y operación |
| `tests/Feature/`, `tests/Unit/` | Pruebas automatizadas de permisos, flujos, plantillas y operación |

Para extender el sistema, usar `docs/temporadas.md` y `docs/guiaGeneraciondePlantillas.md`. SEO/GEO está descrito en `docs/geo-estrategia.md`; rendimiento en `docs/rendimiento.md`; respaldos y alertas en `docs/operacion.md`.

## 5. Estado de rendimiento observado en el repositorio

Hay varias optimizaciones implementadas:

- Las plantillas usan CSS por diseño y `resources/js/app.js` carga componentes dinámicos según el DOM.
- Video.js, Lottie, Motion y otros componentes aparecen en chunks separados; no forman parte de cada descarga inicial.
- `MediaUploadService` valida tipos y tamaño, rechaza SVG y solicita transformaciones de imagen a Cloudinary cuando está configurado.
- El Nginx de producción tiene gzip y sirve `/build/` con caché de un año e `immutable`; los nombres de Vite llevan hash.
- Las exportaciones se procesan en segundo plano.
- Hay un comando `php artisan bida:medir` para comparar tiempo de servidor, consultas, memoria y tamaño del HTML.

El reporte guardado en `docs/rendimiento.md` es una **referencia local del 17 de septiembre de 2026**, no una medición del servidor actual ni del navegador móvil. En esa captura, la invitación registró P50 de 27 ms, P95 de 65.3 ms, 30 consultas, 564 KB de memoria y 148.9 KB de HTML. Sirve como punto histórico; conviene renovarlo con datos comparables antes de priorizar cambios.

Los artefactos actualmente presentes en `public/build/assets` incluyen chunks de aproximadamente 681 KB para Video.js, 300 KB para Lottie y 223 KB para un icono animado de video; el CSS base compilado ocupa unos 404 KB. Son tamaños sin compresión en disco, no el total descargado por toda página ni el tamaño transferido con gzip. La división en chunks evita que cada página los descargue, pero las páginas que activan esos componentes todavía pueden beneficiarse de carga bajo demanda más estricta.

La caché de datos y los encabezados de caché HTML se controlan por separado en `config/optimizations.php`. El valor de reserva de `CACHE_OPTIMIZATIONS_ENABLED` es falso; `.env.example` propone activarla. `HTTP_CACHE_ENABLED` se propone desactivada. Compose de producción selecciona Redis como almacén de caché. El estado efectivo depende del entorno desplegado.

## 6. Recomendaciones priorizadas

### Seguridad

| Prioridad | Recomendación | Motivo y ubicación |
| --- | --- | --- |
| Alta | Corregir y documentar la exposición del origen. Confirmar si el tráfico debe pasar solo por Cloudflare y limitar 80/443 en el firewall a los rangos de confianza. Alinear el comentario de puerto 8080 con los puertos publicados. | `compose.prod.yaml`, `docker/nginx.conf`. Publicar puertos en Docker no restringe por sí mismo quién llega al origen. Mantener al día las redes de Cloudflare usadas por Nginx. |
| Alta | Pasar la CSP de observación a aplicación tras inventariar y eliminar excepciones innecesarias; preferir nonces o hashes y reducir `unsafe-inline`/`unsafe-eval`. Evaluar compatibilidad de Alpine antes de cambiar su estrategia. | `SecurityHeaders` ya envía CSP, pero `CSP_ENFORCE` es falso por defecto y la política permite scripts inline y eval. Hacer la transición gradualmente para no romper plantillas. |
| Alta | Comprobar que las IP reales y los límites de frecuencia sean correctos en cada topología. Configurar proxies de confianza con los saltos concretos del despliegue y comprobar que las claves del rate limiter usen una IP consistente. | `TRUSTED_PROXIES` está vacío en `.env.example`; los límites de login, RSVP, aportes y puerta dependen de la IP. Nginx ya reescribe IP de Cloudflare en la configuración de Docker. |
| Alta | Agregar segundo factor para cuentas administradoras y un procedimiento de revocación de sesiones y enlaces de puerta. | Login tiene límite de intentos y regenera la sesión al autenticar; no se encontró flujo de MFA en rutas/modelos revisados. |
| Alta | No devolver errores internos al navegador. Reemplazar los mensajes con una respuesta genérica y conservar el detalle solo en logs protegidos. | `MediaUploadController` concatena el mensaje de excepción en la respuesta; las exportaciones también guardan el mensaje de fallo en su payload de estado. |
| Media | Revisar el manejo de tokens personales y de puerta: alta entropía, regeneración/revocación, no registrarlos en logs ni referer y no cachear páginas personalizadas en proxies compartidos. Aplicar `Referrer-Policy: no-referrer` en vistas con tokens si la experiencia lo permite. | Los tokens viajan en URL. El middleware de invitaciones distingue respuesta general y personal; preservar esa separación al tocar caché. |
| Media | Mantener allowlists y topes de subida; medir consumo y añadir inspección adicional si se abre el sistema a formatos nuevos. Definir alertas de cuota para Cloudinary y almacenamiento local. | Ya se rechaza SVG; imagen admite JPG/PNG/GIF/WebP hasta 10 MB, audio hasta 20 MB y video hasta 100 MB. El controlador de subida no debe exponer errores de proveedor. |
| Media | Automatizar una copia cifrada fuera del servidor y practicar restauración; definir retención y eliminación de datos personales según necesidad del negocio. | Existen comandos y prueba programada de respaldos. La copia externa depende de configurar el destino en la infraestructura; fotos Cloudinary requieren protección del proveedor o una política de copia propia. |
| Media | Incluir auditoría de dependencias en CI y actualizar imágenes base y paquetes con una rutina de revisión. | Hay `composer.lock`, `package-lock.json`, compilación, Pint y suite en CI; no aparece una auditoría de vulnerabilidades en `.github/workflows/ci.yml`. |

### Velocidad de carga

| Prioridad | Recomendación | Motivo y punto de partida |
| --- | --- | --- |
| Alta | Medir primero con Lighthouse/WebPageTest en móvil y registrar LCP, INP y CLS por portada, plantilla pesada y tarjeta. Renovar también `docs/rendimiento.md` con el comando existente. | El benchmark actual mide render del kernel en local, no transferencia, CPU del teléfono ni terceros. El HTML inicial de invitación ya rondaba 149 KB y 30 consultas en la medición histórica. |
| Alta | Afinar imágenes de portada y galerías: servir dimensiones según el espacio real, añadir `srcset/sizes`, declarar `width/height`, priorizar solo la imagen LCP y diferir las que están bajo el primer pliegue. | Cloudinary ya transforma imágenes al subir, pero las páginas pueden tener múltiples imágenes grandes. Validar el resultado en móvil con red lenta y caché fría. |
| Alta | Cargar video y Lottie al acercarse al viewport o al interactuar; ofrecer poster estático para video y alternativa estática a animaciones decorativas. | El code splitting está activo, pero los chunks de Video.js y Lottie son grandes para las páginas que los solicitan. Revisar también movimiento reducido y consumo en móviles modestos. |
| Media | Revisar cada plantilla con el panel Network y eliminar assets duplicados o módulos JS/CSS no usados; mantener el CSS específico de diseño y evitar precargar todas las fuentes o animaciones. | Vite separa estilos de plantillas y componentes; el tamaño total del CSS base compilado y los iconos animados justifican medir solicitudes, no asumir que todo carga en todas las páginas. |
| Media | Activar y ajustar la caché de contenido público con pruebas de invalidación después de editar invitación, RSVP, voto, canción o foto. Mantener páginas con token personal fuera de cachés compartidas. | Existen caché de datos y middleware HTTP con flags independientes; aumentar TTL sin validar invalidación puede mostrar información vieja. |
| Media | Trazar consultas lentas y conteo por plantilla; corregir consultas puntuales con eager loading, agregaciones e índices confirmados por `EXPLAIN`. | Hay índices y consultas agrupadas; 30 consultas en la captura de una sola invitación es una señal para perfilar, no prueba por sí sola de un problema. |
| Media | Verificar compresión y cabeceras de caché en el dominio real, no solo en Nginx local. Mantener assets versionados inmutables y HTML dinámico con invalidación segura. | Nginx comprime y cachea `/build/`; CDN, TLS, reglas de caché y compresión final dependen del despliegue. |

### SEO y GEO

Ya existen títulos y descripciones por página, metadatos Open Graph/Twitter, datos JSON-LD para Organization, WebSite, Service/Offer y, cuando corresponde, FAQ, Article y BreadcrumbList. `sitemap.xml` lista páginas públicas; `robots.txt` y las muestras excluyen invitaciones y áreas privadas. `/llms.txt` resume la oferta con precios desde configuración. El detalle está en `docs/geo-estrategia.md`.

| Prioridad | Recomendación | Motivo y punto de partida |
| --- | --- | --- |
| Alta | Añadir `rel="canonical"` para cada página pública indexable y verificar que apunte al host y protocolo canónicos. Mantener parámetros de campaña fuera de esa URL. | No aparece una etiqueta canonical en los layouts revisados. La página ya genera URL limpia para Open Graph; reaprovechar esa lógica evita variantes duplicadas por UTM o parámetros. |
| Alta | Dar a cada URL del sitemap una fecha `lastmod` que cambie solo al editar realmente esa página. | `SeoController::sitemap()` usa la fecha de hoy para inicio, Hazlo tú y todas las landings; esto declara cambios diarios aunque el contenido no haya cambiado. La guía y las páginas legales ya tienen fechas explícitas. |
| Alta | Configurar Google Search Console y Bing Webmaster Tools para el dominio productivo; enviar el sitemap y revisar indexación, consultas, errores y páginas excluidas. | El repositorio contiene sitemap, robots y guía de validación; la conexión de cuentas y el envío del sitemap son tareas del despliegue, no se confirman desde el código. |
| Media | Comprobar que títulos, descripciones, preguntas visibles, JSON-LD, ofertas y precios coincidan en cada landing; validar URLs y resultados estructurados tras cambios de plantilla. | Hay datos estructurados compartidos y ofertas dinámicas. Mantener una sola fuente de verdad evita que el marcado prometa algo que la página no muestra. |
| Media | Revisar la cobertura geográfica y los datos de entidad antes de publicarlos: nombre, WhatsApp/teléfono, ciudad, redes y áreas realmente atendidas. | El marcado declara Bolivia y Latinoamérica; la guía y `config/bida.php` son fuentes de esos datos. No añadir direcciones, reseñas o presencia local que el negocio no pueda verificar. |
| Media | Fortalecer la guía con experiencia propia publicable: estadísticas agregadas y anónimas, casos autorizados, fecha de revisión y respuestas actualizadas sobre precios y funciones. | `docs/geo-estrategia.md` identifica datos propios agregados como oportunidad. `llms.txt` ayuda a descubrir información, pero debe acompañar contenido útil y comprobable en páginas HTML. |
| Media | Medir impresiones, clics orgánicos y contactos por landing, aprovechando el registro de origen de campaña; revisar qué consultas tienen intención local o de compra. | `CaptureLeadSource` y `CampaignLink` ya guardan/arman atribución de campañas; añadir una lectura periódica de resultados, con analítica respetuosa de privacidad, permite priorizar contenido que trae consultas reales. |
| Baja | Mantener la política de no indexar invitaciones, enlaces personales, muestras y paneles; comprobar que un cambio de dominio actualice el `Sitemap:` de `robots.txt` y que staging no se indexe. | Es una decisión de privacidad coherente con los datos de invitados. `robots.txt` es estático y hoy declara `https://bida-events.com/sitemap.xml`. |

### UI, UX y accesibilidad

Las invitaciones se renderizan con contenido esencial en HTML y tienen ruta de teclado para saltar al contenido. Las pruebas existentes cubren lectura sin JavaScript, navegación/foco de menú, contraste de paletas de plantillas y texto alternativo para fotos. Hay estilos para movimiento reducido. Esa cobertura es valiosa, pero no representa una auditoría completa de la portada, los paneles y el editor.

| Prioridad | Recomendación | Motivo y punto de partida |
| --- | --- | --- |
| Alta | Extender revisión de accesibilidad a portada, landings, login, panel cliente/admin y editor: teclado, nombres accesibles, orden y retorno de foco, mensajes de error, zoom y pantallas estrechas. Complementar revisión manual con axe u otra herramienta automatizada. | `AccessibleInvitationTest` verifica flujos de invitación y contraste de sus temas; no demuestra por sí sola que todos los controles y vistas cumplan las mismas condiciones. |
| Alta | Probar flujos reales en móvil: abrir una muestra, elegir CTA, enviar datos por WhatsApp, completar RSVP, subir una foto y usar el pase QR; confirmar estados de carga, éxito y error en cada paso. | La mayor parte de la experiencia pública y de invitados ocurre desde el celular. Las capturas estáticas no prueban legibilidad, controles táctiles ni recuperación ante una red lenta. |
| Media | Mantener una jerarquía visual consistente para navegación, CTA principal, acciones secundarias y avisos; reutilizar tokens y componentes entre home, landings y paneles. | Hay CSS y layouts distintos para sitio, invitación y administración. Un vocabulario visual compartido reduce variaciones y hace más fácil corregir contraste, foco y espaciado en un solo lugar. |
| Media | Asegurar estados vacíos y de espera claros en el editor, subidas y exportaciones; explicar qué se guardó, qué falta y cómo reintentar una operación fallida. | Las exportaciones ya tienen estado consultable y las vistas previas son interactivas; mantener feedback visible evita dobles envíos y solicitudes de soporte. |
| Media | Revisar cada animación nueva con `prefers-reduced-motion`, teclado y un teléfono de gama baja; ofrecer una forma directa de llegar al contenido cuando una apertura tarda o falla. | El sistema tiene muchas aperturas y efectos, además de pruebas de movimiento reducido. La experiencia debe seguir siendo utilizable sin animación o si un chunk no llega. |
| Baja | Hacer pruebas breves de usabilidad con organizadores y personal de puerta; observar si encuentran rápido respuestas, lista, exportación y acceso QR sin instrucciones externas. | Los flujos resuelven tareas distintas antes y durante el evento. La observación permite simplificar etiquetas, navegación y prioridades con evidencia de uso. |

### Escalabilidad y operación

| Prioridad | Recomendación | Motivo |
| --- | --- | --- |
| Media | Aumentar workers según longitud de cola y aislar exportaciones pesadas si compiten con otros trabajos. Considerar Redis o un servicio de cola administrado cuando la cola en PostgreSQL afecte consultas de negocio. | Ya existen worker y jobs de Excel/PDF, pero Compose define conexión `database`. Escalar gradualmente con métricas de espera, fallos y duración. |
| Media | Para varias réplicas web, usar almacenamiento de objetos compartido para exportaciones y subidas locales; no depender del disco de una sola instancia. Mantener sesión, caché y rate limiter en almacenes compartidos. | Cloudinary es opcional y el fallback local depende de `storage`; el volumen de Compose no sustituye un almacenamiento distribuido entre hosts. |
| Media | Añadir métricas y alertas de TTFB, errores HTTP, consultas lentas, caché, cola, disco y consumo de Cloudinary. Correlacionar fallos con un identificador de petición sin registrar tokens o datos personales. | Hay logs de peticiones lentas, fallos de jobs, salud y disco. Son una buena base, pero el log lento por sí solo no identifica la consulta o el asset responsable. |
| Baja | Mantener operaciones idempotentes y desplegar con migraciones compatibles hacia atrás cuando haya varias réplicas. | Producción ejecuta migraciones al iniciar el servicio app; evitar que cambios de esquema dependan de que todas las instancias se actualicen simultáneamente. |

## 7. Orden sugerido de trabajo

1. **Antes de la siguiente publicación:** alinear puertos y firewall del origen; comprobar `APP_DEBUG=false`, HTTPS, IP real y rate limits; quitar errores internos de las respuestas.
2. **Seguridad de cuentas:** instrumentar CSP en modo reporte, corregir violaciones y planificar MFA para administradores; revisar ciclo de vida de enlaces con token.
3. **Mejoras de carga:** obtener una medición móvil reproducible; optimizar imagen LCP y carga diferida de Video.js/Lottie; perfilar la consulta de plantillas con más carga.
4. **SEO/GEO:** añadir canonical, corregir fechas reales de sitemap y revisar indexación y consultas en Search Console/Bing.
5. **UI y accesibilidad:** ampliar pruebas fuera de las invitaciones y observar los flujos clave con usuarios en móvil y teclado.
6. **Preparar crecimiento:** probar invalidación de caché, configurar alertas de cola/recursos y validar almacenamiento compartido y respaldos fuera del host.
7. **Mantener la calidad:** ampliar CI con auditoría de dependencias y actualizar este mapa al cambiar rutas, módulos, tablas, jobs o topología de despliegue.

## 8. Cómo mantener este mapa

Este documento explica arquitectura y decisiones pendientes; no es un changelog de cada plantilla. Al agregar una ruta, perfil, módulo, tabla, job o servicio de infraestructura, actualizar la sección correspondiente y los documentos especializados enlazados arriba. Mantener fechas y mediciones de rendimiento en `docs/rendimiento.md`, no copiarlas aquí como si fueran datos vigentes de producción.
