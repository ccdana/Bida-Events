# Mapa del proyecto Bida Events

> Revisión estática del repositorio al 9 de octubre de 2026, incluyendo los cambios presentes en el árbol de trabajo. Describe el código y la configuración de ejemplo; no confirma qué valores tiene el servidor real en `.env`, qué conexiones externas están activas ni qué versión está desplegada. No se ejecutaron pruebas en esta revisión.

## 1. Qué es el sistema

Bida Events es una aplicación web Laravel para crear, editar y publicar invitaciones digitales de eventos y tarjetas interactivas. Los clientes administran invitados y respuestas; el equipo administra cuentas e invitaciones; los revendedores pueden crear y gestionar invitaciones según su suscripción. El sitio ya incluye registro de cuentas, acceso con Google configurable, verificación de correo y recuperación de contraseña, además de cobro en línea opcional para los planes de revendedor.

El sitio se renderiza en el servidor con Blade. Alpine.js añade interacción en el navegador. Las invitaciones y tarjetas comparten módulos de contenido, perfiles de evento y plantillas visuales. Los RSVP, votos y aportes se registran en la base de datos; los archivos se guardan en Cloudinary si está configurado o en almacenamiento local.

**Stack detectado:** PHP 8.4, Laravel 12, PostgreSQL en los archivos de Docker, SQLite en pruebas, Blade, Alpine.js 3, Tailwind CSS 4, Vite 7, Socialite para OAuth, Cloudinary opcional para medios, SMTP para correo, adaptadores propios para Lemon Squeezy y Libélula, DomPDF y Laravel Excel para exportaciones.

La aplicación tiene cinco áreas principales:

| Área | Quién la usa | Rutas principales |
| --- | --- | --- |
| Sitio público y páginas SEO | Cualquier visitante | `/`, páginas por tipo de evento, `/guia-invitaciones-digitales`, `/unete`, páginas legales |
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

### Identidad, correo y suscripciones

El login acepta usuario o correo y contraseña. Las cuentas que crea el equipo pueden seguir usando usuario y no necesitan correo; las cuentas abiertas desde el sitio usan correo. `AUTH_REGISTRATION` controla el registro público (el ejemplo `.env.example` lo deja cerrado). Google OAuth aparece solo si están configuradas sus credenciales; su callback vincula por correo verificado. Las cuentas nuevas reciben un enlace para confirmar el correo. También hay recuperación de contraseña por correo, con token de un solo uso y vencimiento; al cambiar la contraseña se cierran las sesiones anteriores.

Los servicios de correo se usan para confirmar correo, restablecer contraseña y enviar alertas operativas. La guía de conexión propone Zoho Mail por SMTP; tener variables de correo en el ejemplo no prueba que el SMTP de producción ya esté configurado.

El checkout de suscripción está en `/client/suscripcion` y requiere correo confirmado. Los adaptadores disponibles son:

| Medio | Pasarela | Moneda y ciclo | Requisitos |
| --- | --- | --- | --- |
| Tarjeta internacional | Lemon Squeezy | USD, cobro mensual recurrente | API key, tienda, secreto de webhook y variante configurados para cada plan. Incluye portal para gestionar/cancelar la suscripción. |
| Pago en Bolivia | Libélula | BOB, pago por adelantado de 1, 3 o 12 meses | Appkey y base URL. Puede mostrar QR Simple o llevar a la página de pago de Libélula. No se renueva automáticamente. |

Los dos medios se ocultan si el cobro global está apagado o si a su pasarela le faltan credenciales. `PAYMENTS_ENABLED=false` en `.env.example`; el registro manual de pagos desde Administración sigue disponible de forma independiente.

Al iniciar checkout se crea una orden con el precio y moneda de ese momento. Las pasarelas notifican en `/webhooks/{provider}`; la aplicación valida la firma de Lemon Squeezy o consulta el estado del pago a Libélula, registra el evento una vez y procesa la activación en la cola. `bida:verificar-pagos` corre cada cinco minutos como respaldo para cobros pendientes y órdenes vencidas. Por tanto, los cobros dependen de que estén operativos el worker y el scheduler.

## 3. Recorrido de los datos

1. **Creación:** el panel de administración, el editor de revendedor o «Hazlo tú» (cualquier cuenta arma la suya, `Client\DiyInvitationController`) crea una invitación y la asocia con su cliente, tipo de evento y plantilla. Las de «Hazlo tú» nacen borrador con todo desbloqueado y toman su nivel al publicarse (`App\Support\DiyInvitations`, `Packages::TIERS`).
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
| Cuentas y roles | `users`, clientes, administradores, revendedores, correo verificado, identidad Google y estado de suscripción |
| Invitaciones | `invitations`, tipo, estado, paquete o nivel (`gratis` solo en «Hazlo tú»), propietario, revendedor e `is_diy` |
| Contenido de invitación | Tema, portada, textos, secciones, ubicaciones, itinerario, galería, vestimenta, regalos, medios, encuestas y opciones |
| Invitados e interacción | `guests`, confirmaciones, contribuciones, votos y marcas de entrada |
| Tarjetas | Dedicatorias, hitos, recuerdos, relatos, momentos, collage, marcos y aventuras |
| Pagos y operación | `payment_orders`, `webhook_events`, historial de pagos, exportaciones, settings, jobs y caché |

Puntos de entrada para orientarse:

| Ruta del código | Responsabilidad |
| --- | --- |
| `routes/web.php` | Rutas públicas, acceso, paneles, editor, aportes y exportaciones |
| `app/Http/Controllers/` | Controladores separados por área: Admin, Client, Public y Auth |
| `app/Http/Controllers/Auth/`, `app/Support/AuthOptions.php` | Login por usuario/correo, alta, Google OAuth, verificación de correo y recuperación de contraseña |
| `app/Http/Controllers/Client/SubscriptionController.php` | Selección de plan, checkout, consulta de orden y portal de suscripción |
| `app/Payments/`, `config/payments.php` | Contrato de pasarela, selección de método, adaptadores Lemon Squeezy y Libélula, precios y eventos canónicos |
| `app/Http/Controllers/Webhooks/`, `app/Jobs/ProcessWebhookEvent.php` | Validación, registro idempotente y procesamiento asíncrono de avisos de pago |
| `app/Models/PaymentOrder.php`, `app/Models/WebhookEvent.php` | Órdenes de cobro y registro de eventos recibidos/procesados |
| `app/Http/Requests/` | Validación de formularios de administración, clientes y acciones públicas |
| `app/Policies/InvitationPolicy.php` y `app/Http/Middleware/` | Autorización por rol, propietario y suscripción |
| `app/Modules/`, `config/modules.php` | Contratos, guardado y carga de módulos de invitación y tarjeta |
| `app/EventProfiles/`, `config/event_profiles.php` | Campos y vocabulario según el tipo de evento |
| `app/Models/` y `database/migrations/` | Esquema y relaciones Eloquent |
| `app/Services/`, `app/Support/` | Caché, subida de medios, reglas y lógica compartida |
| `app/ViewModels/` | Datos preparados para paneles, invitados y PDF |
| `app/Jobs/`, `app/Console/Commands/`, `routes/console.php` | Exportaciones, aplicación/reproceso de webhooks, conciliación de pagos, respaldos, retención, salud y scheduler |
| `resources/views/` | Pantallas Blade, paneles, layout y plantillas |
| `resources/js/`, `resources/css/`, `vite.config.js` | Interacción, estilos y compilación de assets |
| `config/` | Ajustes de aplicación, seguridad, caché, límites, medios y operación |
| `tests/Feature/`, `tests/Unit/` | Pruebas de permisos, flujos, plantillas, registro/login, restablecimiento, checkout y adaptadores de pago |

Para extender el sistema, usar `docs/temporadas.md` y `docs/guiaGeneraciondePlantillas.md`. SEO/GEO está descrito en `docs/geo-estrategia.md`; correo, OAuth y pasarelas en `docs/conexiones.md`; despliegue en `docs/despliegue.md`; rendimiento en `docs/rendimiento.md`; respaldos, alertas y operación de pagos en `docs/operacion.md`.

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
| Alta | Agregar segundo factor para cuentas administradoras y un procedimiento de revocación de sesiones y enlaces de puerta. | Login tiene límite de intentos y regenera la sesión al autenticar; Google OAuth es opcional y no sustituye MFA obligatorio para administración. |
| Alta | Antes de activar cobros reales, confirmar con Libélula la versión vigente de su API y el contrato de consultas; el material de integración referencia la guía 2.7.1 de 2020. Operar primero en modo de prueba y conciliar órdenes/eventos no aplicados. | La notificación de Libélula no va firmada: el adaptador verifica el pago consultando al proveedor antes de registrarlo. Lemon Squeezy usa firma HMAC; ambos flujos dependen del worker y tienen comandos/alertas de recuperación. La guía del proveedor necesita confirmación comercial/técnica antes del lanzamiento. |
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
| Alta | Dar a cada URL del sitemap una fecha `lastmod` que cambie solo al editar realmente esa página. | `SeoController::sitemap()` usa la fecha de hoy para inicio, Únete y todas las landings; esto declara cambios diarios aunque el contenido no haya cambiado. La guía y las páginas legales ya tienen fechas explícitas. |
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
| Media | Aumentar workers según longitud de cola y aislar exportaciones pesadas si compiten con otros trabajos. Considerar Redis o un servicio de cola administrado cuando la cola en PostgreSQL afecte consultas de negocio. | La cola procesa Excel/PDF y webhooks de pagos; Compose define conexión `database`. `bida:verificar-pagos` también consulta Libélula por cada orden pendiente, por lo que a mayor volumen conviene agrupar la conciliación para reducir llamadas repetidas al proveedor. |
| Media | Para varias réplicas web, usar almacenamiento de objetos compartido para exportaciones y subidas locales; no depender del disco de una sola instancia. Mantener sesión, caché y rate limiter en almacenes compartidos. | Cloudinary es opcional y el fallback local depende de `storage`; el volumen de Compose no sustituye un almacenamiento distribuido entre hosts. |
| Media | Añadir métricas y alertas de TTFB, errores HTTP, consultas lentas, caché, cola, disco y consumo de Cloudinary. Correlacionar fallos con un identificador de petición sin registrar tokens o datos personales. | Hay logs de peticiones lentas, fallos de jobs, salud y disco. Son una buena base, pero el log lento por sí solo no identifica la consulta o el asset responsable. |
| Baja | Mantener operaciones idempotentes y desplegar con migraciones compatibles hacia atrás cuando haya varias réplicas. | Producción ejecuta migraciones al iniciar el servicio app; evitar que cambios de esquema dependan de que todas las instancias se actualicen simultáneamente. |

## 7. Orden sugerido de trabajo

1. **Antes de la siguiente publicación:** alinear puertos y firewall del origen; comprobar `APP_DEBUG=false`, HTTPS, IP real y rate limits; quitar errores internos de las respuestas.
2. **Seguridad de cuentas:** instrumentar CSP en modo reporte, corregir violaciones y planificar MFA para administradores; revisar ciclo de vida de enlaces con token.
3. **Correo, OAuth y pagos:** configurar y verificar SMTP y los callbacks de Google por ambiente; validar el checkout de Lemon Squeezy en modo de prueba; confirmar con Libélula que el contrato API usado sigue vigente; comprobar webhooks, worker, scheduler y conciliación antes de habilitar cobros reales.
4. **Mejoras de carga:** obtener una medición móvil reproducible; optimizar imagen LCP y carga diferida de Video.js/Lottie; perfilar la consulta de plantillas con más carga.
5. **SEO/GEO:** añadir canonical, corregir fechas reales de sitemap y revisar indexación y consultas en Search Console/Bing.
6. **UI y accesibilidad:** ampliar pruebas fuera de las invitaciones y observar los flujos clave con usuarios en móvil y teclado.
7. **Preparar crecimiento:** probar invalidación de caché, configurar alertas de cola/recursos y validar almacenamiento compartido y respaldos fuera del host.
8. **Mantener la calidad:** ampliar CI con auditoría de dependencias y actualizar este mapa al cambiar rutas, módulos, tablas, jobs o topología de despliegue.

## 8. Cómo mantener este mapa

Este documento explica arquitectura y decisiones pendientes; no es un changelog de cada plantilla. Al agregar una ruta, perfil, módulo, tabla, job o servicio de infraestructura, actualizar la sección correspondiente y los documentos especializados enlazados arriba. Mantener fechas y mediciones de rendimiento en `docs/rendimiento.md`, no copiarlas aquí como si fueran datos vigentes de producción.




# Estrategia SEO + GEO (Generative Engine Optimization)

Cómo está preparado el sitio para Google y para los motores que responden con IA (Google AI Overviews,
ChatGPT, Gemini, Perplexity, Copilot), y el contenido base que se escribió con ese criterio.

## Datos del brief (supuestos)

El brief dejó en blanco la temática, la palabra clave y el público. Se tomaron del propio sitio:

| Campo | Valor usado |
| --- | --- |
| Temática o nicho | Invitaciones digitales (página web por evento) para bodas, XV años, bautizos, cumpleaños y graduaciones, con confirmación de asistencia, pase QR y control de entrada. Bolivia y Latinoamérica, en español. |
| Palabra clave principal | «invitaciones digitales» |
| Secundarias | «invitación digital para boda / XV años / bautizo», «invitación digital con confirmación de asistencia», «invitación con código QR», «cuánto cuesta una invitación digital», «control de entrada con QR para eventos» |
| Público objetivo | 1) Familias y parejas que organizan un evento (compra única). 2) Quien arma varias invitaciones o las vende: familias con varios eventos, fotógrafos, salones y organizadores (plan mensual «Únete»). |

Si alguno de estos datos es distinto, se cambia aquí y en `resources/views/guia.blade.php`.

## Lo que quedó implementado

| Pieza | Dónde | Para qué |
| --- | --- | --- |
| Guía pública | `/guia-invitaciones-digitales` (`resources/views/guia.blade.php`) | La página de referencia: responde las preguntas conversacionales con la respuesta primero, listas y tablas. |
| Datos estructurados | `site/partials/structured-data.blade.php` | Organization (entidad única con `@id`), WebSite, Service con Offers en USD, FAQPage, BreadcrumbList y Article (en la guía). |
| `llms.txt` | `/llms.txt` (`SeoController::llms`, vista `seo/llms`) | Resumen en Markdown para los modelos: qué es la marca, qué vende, precios de hoy y dónde está cada cosa. |
| `robots.txt` | `public/robots.txt` | Bloquea invitaciones, muestras, paneles y la puerta (`/puerta/`, `/entrada/`) y declara el sitemap. Es estático porque nginx y el healthcheck de Docker lo usan; una prueba verifica que siga a `SeoController::PRIVATE_PATHS`. |
| `sitemap.xml` | `/sitemap.xml` (`SeoController::sitemap`) | Portada, guía, Únete, páginas por evento y legales, con `lastmod` y prioridad. |
| Sin sesión | rutas de `sitemap` y `llms` | Responden sin cookies, con `Cache-Control: public, max-age=3600`: rápidas para los rastreadores y cacheables. |

---

## 1. Análisis de intención conversacional

Las tres preguntas exactas que alguien le haría a una IA para llegar a este contenido:

1. «¿Qué es una invitación digital y qué debería incluir para mi boda (o XV años)?»
2. «¿Cuánto cuesta una invitación digital con confirmación de asistencia en Bolivia?»
3. «¿Cómo controlo quién entra a mi fiesta con un código QR en la invitación?»

La tercera es la de mayor ganancia: casi nadie la responde bien y es justo lo que el producto hace
distinto (pase QR por invitado + enlace de puerta que no deja usar un pase dos veces).

## 2. Estructura recomendada (la que tiene la guía)

```
H1  Invitaciones digitales: qué son, cuánto cuestan y cómo elegir la tuya
    [Caja «En pocas palabras»: definición de 50–60 palabras, citable sola]
    [Párrafo de entrada: el criterio real para elegir]
H2  ¿Qué es una invitación digital?
    [2 párrafos + cita del equipo]
H2  Qué debe incluir una invitación digital
    H3  Lo que el invitado necesita saber        → LISTA
    H3  Lo que hace que se sienta del evento     → LISTA
    H3  Lo que le sirve a quien organiza          → LISTA
H2  Invitación digital, impresa o imagen por WhatsApp
    → TABLA comparativa (4 columnas × 6 criterios)
H2  Cuánto cuesta una invitación digital
    → TABLA de paquetes con el precio de hoy (sale de la configuración)
H2  Confirmación de asistencia y control de entrada con QR
    H3  Antes del evento                          → LISTA
    H3  El día del evento, en la puerta           → LISTA
    [Cita del equipo]
H2  Cuándo enviar la invitación digital
    → TABLA por tipo de evento (cuándo y por qué)
H2  Errores que conviene evitar                   → LISTA
H2  Invitaciones por tipo de evento               → LISTA de enlaces internos
H2  Preguntas frecuentes                          → FAQ (también en FAQPage)
```

Reglas que sigue: una sola H1; cada H2 abre con la respuesta directa en una o dos frases; las listas
son para pasos o componentes y las tablas para comparar; nada de numerar secciones.

## 3. Área de «Information Gain»

Lo que este contenido aporta y la competencia no suele tener:

- **El control de entrada explicado de punta a punta**: qué ve el portero, qué pasa con un pase ya
  usado, cómo se deshace un error y cómo se cambia el enlace de puerta. Es experiencia propia del
  producto, no teoría.
- **Tabla de anticipación por tipo de evento con el porqué**, no solo «envíala con tiempo».
- **Comparación práctica digital / impresa / imagen por WhatsApp** centrada en lo que pasa después
  de enviar (confirmaciones, cambios de último momento, puerta), no en el diseño.
- **Precios reales y actuales**: la tabla se arma con `Offers::packages()`, así nunca queda un precio
  viejo en la guía ni en `llms.txt`.

Para subir todavía más la ganancia de información (pendiente, necesita datos reales):

- Publicar cifras propias agregadas y anónimas, por ejemplo «porcentaje de invitados que confirma en
  las primeras 48 horas» o «cuántos días antes llegan la mayoría de las confirmaciones». Se pueden
  sacar de la tabla `guests` (`status` y la fecha de cada respuesta) sin exponer a nadie.
- Sumar citas de personas reales con nombre y rol (una organizadora de eventos, un fotógrafo aliado)
  con su permiso. Hoy las citas de la guía están firmadas por «Equipo de Bida Events»: no se
  inventaron expertos externos.

## 4. Texto optimizado

La introducción y la primera sección clave están publicadas en la guía. Van aquí como referencia.

**Introducción**

> **En pocas palabras.** Una invitación digital es una página web del evento que se envía con un
> enlace por WhatsApp. Reúne fecha, lugar con mapa, itinerario, música y fotos, y permite que cada
> invitado confirme su asistencia. Las más completas agregan un pase con código QR para controlar la
> entrada.
>
> Elegir una invitación digital ya no es solo elegir un diseño bonito. La diferencia real está en lo
> que pasa después de enviarla: si los invitados encuentran el lugar sin preguntar, si las
> confirmaciones llegan ordenadas y si el día del evento la puerta sabe quién puede entrar.

**Primera sección clave: Confirmación de asistencia y control de entrada con QR**

> La confirmación por invitado y el pase QR convierten la invitación en la lista de invitados del
> evento.
>
> *Antes del evento:* cada invitado recibe su propio enlace con la cantidad de personas que incluye su
> invitación; al confirmar elige cuántas van (nunca más de las asignadas) y recibe su pase con un
> código QR y un código corto. Quien organiza ve en su panel quién confirmó y cuántas personas van.
>
> *En la puerta:* quien recibe abre el enlace de puerta en su teléfono y escanea el pase; si no se
> lee, escribe el código corto. Ve el nombre, cuántas personas pueden pasar y cuántas ya entraron. Un
> pase usado no vuelve a servir, y un ingreso marcado por error se deshace en el momento.
>
> «El control de entrada no es para desconfiar de los invitados: es para que el salón no reciba a más
> personas de las que contrataste y la fiesta empiece a tiempo.» — Equipo de Bida Events

## Pilares aplicados

- **Optimización para LLMs**: respuesta primero, frases que se sostienen solas, listas y tablas,
  `llms.txt`, FAQ con las preguntas tal como se hacen.
- **E-E-A-T**: la marca como entidad única en todas las páginas (`Organization` con `@id`, contacto,
  área de servicio, `knowsAbout`), autoría y fecha de revisión visibles en la guía, datos
  verificables (precios de la configuración) y privacidad explicada.
- **Entidades y semántica**: invitación digital, confirmación de asistencia (RSVP), código QR,
  control de entrada, WhatsApp, tipos de evento; enlazado interno de la guía a cada página por evento
  y a Únete, y desde el pie del sitio a la guía.
- **Intención conversacional**: H2 formulados como la pregunta que se hace («¿Qué es…?», «Cuánto
  cuesta…», «Cuándo enviar…»).

## Mantenimiento

- Al cambiar la guía, actualizar `PublicPagesController::GUIDE_UPDATED_AT` (se muestra en la página,
  en `Article.dateModified` y en el sitemap).
- Los precios no se tocan en la guía: salen de Ajustes.
- Tras publicar en producción: enviar el sitemap en Google Search Console y Bing Webmaster Tools, y
  validar la guía con la prueba de resultados enriquecidos de Google.
