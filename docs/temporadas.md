# Temporadas y módulos nuevos

Cómo sumar una tarjeta de temporada (Halloween, Día de Muertos, Navidad…) o un módulo que hoy no existe
sin tocar lo que ya funciona. La primera tarjeta, **Día del Amor** (`tarjeta-amor`), sirve de ejemplo
para todo lo que sigue.

## Las tres piezas

| Pieza | Dónde | Qué decide |
| --- | --- | --- |
| **Módulo** | `app/Modules/**`, registrado en `config/modules.php` | Cómo se guarda un bloque de contenido (sus tablas), cómo se lee, cómo se valida, qué vista pública y qué panel del editor tiene |
| **Perfil de evento** | `app/EventProfiles/*Profile.php`, registrado en `config/event_profiles.php` | Qué es (`invitation` o `card`), qué módulos ofrece, cuáles nacen encendidos, qué campos pide la portada, cómo se llaman los grupos de destacados, qué es obligatorio y el texto de ejemplo del editor |
| **Plantilla** | `resources/views/invitations/templates/*.blade.php` + su entrada en `App\Support\InvitationTemplates::all()` | Cómo se ve: paleta, orden de secciones, textos propios; apunta a un perfil con `event` |

El tipo de evento de la base (`event_types.code`, `kind`, `season`) usa el mismo código que el perfil.

**No hay columnas JSON.** Una prueba (`StructuredModulesRoundTripTest::test_no_application_table_has_a_json_column`)
falla si alguna tabla de la aplicación tiene una. Todo dato nuevo va en columnas de una tabla propia.
El editor envía cada módulo como JSON en el formulario, pero eso es solo el formato de envío.

---

## Receta 1: una temporada nueva que reutiliza módulos

Ejemplo: una tarjeta de Navidad con portada, dedicatoria, galería y música.

1. **Perfil.** Copiar `LoveCardProfile` como `ChristmasCardProfile`: `code()` = `navidad`,
   `kind()` = `Module::KIND_CARD`, `season()` = `navidad`, la lista de `modules()`,
   `enabledByDefault()`, `required()` (mensajes para el aviso «qué falta») y `sample()`.
   Registrarlo en `config/event_profiles.php`.
2. **Tipo de evento.** Sumarlo en `EventTypeSeeder` con `code`, `kind` y `season`.
3. **Plantilla.** Crear `invitations/templates/tarjeta-navidad.blade.php` sobre las partes de
   `invitations/partials/shell/*` (igual que `tarjeta-amor`). Estilos propios en
   `resources/css/cards/navidad.css`, cargados con `@vite` solo en esa plantilla y sumados a `input`
   en `vite.config.js`.
4. **Catálogo.** Entrada en `InvitationTemplates::all()` con `label`, `description`,
   `event => 'navidad'`, `palette` (tiene que pasar el contraste AA: lo revisa
   `AccessibleInvitationTest`), `order` y `copy`.
5. **Muestra.** `database/seeders/showcase/tarjeta-….php`, su slug en
   `ShowcaseInvitationsSeeder::SLUGS` y en `config('bida.seasons.{clave}.templates')` (ver receta 4).
6. **Pruebas.** `TemplateRenderMatrixTest::completeContent()` necesita su contenido de prueba (la
   plantilla entra sola a la matriz y falla hasta tenerlo).
7. **Campaña (opcional).** Página en `config('bida.landings')` con `kind => 'card'`, su código de
   WhatsApp, preguntas frecuentes e imagen en `config('bida.share_images')`. Sus muestras son las de
   la temporada (o una lista propia en `demos`); con varias, la página muestra un selector de diseños.
   Los textos hablan del evento, no de un diseño, para que sumar diseños no obligue a reescribirlos.

Con eso el editor ya ofrece la plantilla bajo «Tarjeta», muestra solo las pestañas del perfil y
avisa lo que falta según `required()`.

## Receta 1b: una invitación de temporada (ejemplo: Halloween)

Igual que la receta 1, con estas diferencias (`HalloweenProfile`, `halloween-calabazas`):

- El perfil es de **invitación** (`kind()` por defecto) pero declara `season()`; así Ajustes la lista entre las plantillas de temporada que se pueden apagar.
- La página de campaña lleva `'kind' => 'season'`: se vende al precio de su temporada, sin paquetes, mientras la temporada que la tiene como `landing` esté encendida y vigente.
- Cada temporada es una entrada propia en `config('bida.seasons')` (clave, `code`, `name`, `product` —`invitación` o `tarjeta`—, `active`, `templates`, `landing`, precios y `ends_at`). Son independientes: en Ajustes cada una se enciende, se apaga y cambia de precio y fecha sin tocar a las demás, y en la portada cada una vigente tiene su propio botón.
- En la portada la temporada se abre desde un botón flotante (`site/partials/season.blade.php`); su adorno y colores se eligen por `key` en `site.css` (`.site-season--halloween`).

## Receta 2: un módulo que hoy no existe

Ejemplo: «deseos» para Navidad, una lista de deseos con su texto.

1. **Migración** con su tabla: `invitation_id` con `cascadeOnDelete`, columnas tipadas y
   `sort_order` si es lista. Nada de `json`.
2. **Modelo** en `app/Models` y la relación en `Invitation` (`hasMany` / `hasOne`).
3. **Clase** en `app/Modules/Card/WishesModule.php` que extiende `App\Modules\Module`:
   - `code()` (clave del módulo en el editor y en las vistas), `label()`, `kinds()`;
   - `defaults()`: `(object) []` si es un bloque, `[]` si es una lista;
   - `relations()`: lo que hay que precargar (`['wishes']`);
   - `load()` devuelve el arreglo con las claves en español que usan la vista y el editor;
   - `save()` escribe las tablas. Para listas usar `ReplacesOrderedRows::replaceOrdered` (reutiliza
     filas y no cambia los ids); para textos, `ReadsValues`;
   - `hasContent()`, `rules($prefix)` y `attributes($prefix)` con campos bajo `"{$prefix}.{code}."`;
   - `partial()` y `panel()` con las vistas.
4. **Registro:** una línea en `config/modules.php` (el orden es el orden de guardado).
5. **Vista pública** `invitations/partials/modules/deseos.blade.php`: recibe `$data` y `$page`. La
   incluye sola `shell/modules.blade.php` cuando el módulo está visible y figura en el `order` de la
   plantilla. Estilos base en `resources/css/invitation/modules.css`.
6. **Panel del editor** `admin/invitations/panels/modules/deseos.blade.php` con
   `x-show="activeTab === 'deseos'"`; lo incluye `editor/sidebar.blade.php`. Sumar la pestaña en
   `allTabGroups` de `editor/script.blade.php` (grupo `tarjeta` o el que corresponda) con
   `moduleCode`.
7. **Menú de la invitación:** si su nombre en el menú no es el `label()`, agregarlo a
   `InvitationPage::NAV_LABELS`.
8. **Perfil:** sumarlo a `modules()` del perfil que lo use.
9. **Si recibe algo del invitado** (como `respuesta`): usar `guest_contributions` con un `type`
   propio (constante en la clase del módulo), una ruta con su límite en
   `AppServiceProvider::configureRateLimiting` y `config('optimizations.rate_limits')`, y comprobar
   en el controlador que el módulo esté encendido para esa invitación.

`ModuleRegistryTest` revisa solo que las vistas existan, que las relaciones estén en `Invitation`,
que las reglas no pisen campos de otro módulo y que cada perfil use módulos de su tipo.

## Receta 3: recorrido por escenas y gestos

La tarjeta del Día del Amor no se baja con scroll: se recorre como una historia. Para usarlo en otra
plantilla:

1. Incluir `invitations.partials.story.chrome` después del menú y cargar
   `resources/css/invitation/story.css` con `@vite` junto a la hoja del tema.
2. Las escenas salen solas: `#inicio`, cada `<section>` de `#contenido` y el pie. Lo que deba entrar
   por turnos lleva `data-step` y `style="--step: n"`; el tema cambia la animación con `--step-anim`
   y el fondo de cada escena con `--story-scene-bg`.
3. Gestos disponibles (`resources/js/story`), que también funcionan con «Ver todo»:
   - `holdToOpen()` para mantener presionado (el sello de la carta);
   - `scratchReveal()` para raspar, con `[data-count-to]` en los números que cuentan desde cero;
   - `window.dispatchEvent(new CustomEvent('inv-celebrate', { detail: { rect } }))` para la lluvia de
     pétalos con vibración;
   - `--tilt-x` / `--tilt-y` en `<html>` para mover algo con la inclinación del teléfono.
4. Una puerta (`data-story-gate` con `:data-gate-done`) se abre con el primer «siguiente», así nadie
   queda trabado. El contenido va siempre en el HTML debajo de la puerta, para que se lea sin
   JavaScript y con lector de pantalla.

## Qué no hacer

- No preguntar en vistas o controladores «¿es boda?» o «¿es tarjeta?» por el nombre de la plantilla:
  leer `$page->profile` (vista) o `profile` / `isCard` (editor).
- No cambiar las claves en español de un módulo existente: las leen las plantillas, el editor y
  los seeders de muestra.
- No repetir el código de un módulo que ya existe, aunque se trabaje en otra rama: antes de crear
  uno, buscarlo en `config/modules.php`. («historia» son los capítulos del libro de aventuras;
  los actos de «Bajo la misma luna» usan «relato».)
- No reutilizar un módulo cambiándole el sentido (por ejemplo, usar `hashtag` para guardar otra
  cosa): crear uno nuevo.
- Cuidar imágenes y palabras con doble sentido en el español de Bolivia antes de publicar una
  temporada.

## Comprobar

```bash
php artisan migrate
php artisan db:seed --class=EventTypeSeeder
php artisan db:seed --class=ShowcaseInvitationsSeeder
php artisan test --filter "ModuleRegistryTest|TemplateRenderMatrixTest|AccessibleInvitationTest|SeasonalCardTest"
npm run build
```

Y abrir `/muestra/{slug}` en un teléfono (390 px) y con JavaScript apagado.
