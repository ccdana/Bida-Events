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
| Público objetivo | 1) Familias y parejas que organizan un evento (compra única). 2) Quien arma varias invitaciones o las vende: familias con varios eventos, fotógrafos, salones y organizadores (plan mensual «Hazlo tú»). |

Si alguno de estos datos es distinto, se cambia aquí y en `resources/views/guia.blade.php`.

## Lo que quedó implementado

| Pieza | Dónde | Para qué |
| --- | --- | --- |
| Guía pública | `/guia-invitaciones-digitales` (`resources/views/guia.blade.php`) | La página de referencia: responde las preguntas conversacionales con la respuesta primero, listas y tablas. |
| Datos estructurados | `site/partials/structured-data.blade.php` | Organization (entidad única con `@id`), WebSite, Service con Offers en USD, FAQPage, BreadcrumbList y Article (en la guía). |
| `llms.txt` | `/llms.txt` (`SeoController::llms`, vista `seo/llms`) | Resumen en Markdown para los modelos: qué es la marca, qué vende, precios de hoy y dónde está cada cosa. |
| `robots.txt` | `public/robots.txt` | Bloquea invitaciones, muestras, paneles y la puerta (`/puerta/`, `/entrada/`) y declara el sitemap. Es estático porque nginx y el healthcheck de Docker lo usan; una prueba verifica que siga a `SeoController::PRIVATE_PATHS`. |
| `sitemap.xml` | `/sitemap.xml` (`SeoController::sitemap`) | Portada, guía, Hazlo tú, páginas por evento y legales, con `lastmod` y prioridad. |
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
  y a Hazlo tú, y desde el pie del sitio a la guía.
- **Intención conversacional**: H2 formulados como la pregunta que se hace («¿Qué es…?», «Cuánto
  cuesta…», «Cuándo enviar…»).

## Mantenimiento

- Al cambiar la guía, actualizar `PublicPagesController::GUIDE_UPDATED_AT` (se muestra en la página,
  en `Article.dateModified` y en el sitemap).
- Los precios no se tocan en la guía: salen de Ajustes.
- Tras publicar en producción: enviar el sitemap en Google Search Console y Bing Webmaster Tools, y
  validar la guía con la prueba de resultados enriquecidos de Google.
