# The Story We Write Together
## Plantilla de Invitación Interactiva para El Día del Amor

**Versión**: 1.1 (implementada; secciones 2, 3 y 7 ajustadas a la arquitectura real)  
**Creada**: 2026-09-19  
**Tipo**: Plantilla de Invitación Digital (weStoryTogether)  
**Propósito**: Capturar la historia de amor de una pareja a través de scroll narrativo, imágenes y música.

---

## 1. Concepto Visual & Narrativo

### La Metáfora del Viaje
La invitación sigue el viaje de ver el amor **desde lejos (reflejo en el agua) hasta verlo de frente (luz de la Luna)**, culminando en la magia de *La Noche Estrellada* de Van Gogh.

Cada acto corresponde a una **fase visual y emocional** del viaje:

### **Acto I: El Reflejo**
*"Vemos desde la distancia"*

- **Visual**: Pantalla de agua tranquila, la Luna apenas visible como reflejo
- **Narrativa**: Los momentos pequeños que pasaron desapercibidos pero que eran el inicio
- **Contenido**: 
  - Portada poética
  - Fecha de conocimiento
  - Primeras impresiones
  - Fotos iniciales (filtradas/suavizadas, como si fueran vistas a través del agua)
- **Transición**: Las ondas del agua comienzan a perturbarse levemente

---

### **Acto II: La Oleada (Ascensión)**
*"La emoción desbordante que crece"*

- **Visual**: Transición animada—nos elevamos desde la superficie del agua hacia arriba
- **Narrativa**: El sentimiento inevitable, la creciente intensidad, la imposibilidad de resistirse
- **Contenido**:
  - Timeline interactivo: momentos clave (primer beso, primer viaje, decisiones importantes)
  - Animación de ondas/flujos que se intensifican
  - Fotos que se revelan con mayor claridad (aún con algo de luminiscencia acuática)
  - Definiciones/poesía sobre el amor de autores: Cortázar, Bukowski, Neruda, etc.
- **Interactividad**: Al scrollear, las olas se agitan más, la Luna se acerca

---

### **Acto III: El Encuentro (De Frente)**
*"Ya no vemos el reflejo; vemos la verdad"*

- **Visual**: Llegamos a la superficie; la Luna está ahora al frente, clara, sin filtros
- **Narrativa**: La persistencia del sentimiento. Los ojos que no mienten. El deseo de lo mejor para el otro.
- **Contenido**:
  - **LA ANÉCDOTA PERSONALIZADA**: Sección destacada donde la pareja cuenta el momento pequeño que lo significó todo
  - Fotos de pareja actuales (sin filtros, en alta claridad)
  - Declaraciones/citas personalizadas
  - La canción que les significa (reproductor interactivo)
- **Transición**: La Luna se ilumina completamente

---

### **Acto IV: La Constelación (Epítome)**
*"Como en La Noche Estrellada de Van Gogh"*

- **Visual**: 
  - Fondo inspirado en La Noche Estrellada: cielo oscuro, estrellas en movimiento, vórtices suaves
  - Luna prominente e iluminada en el centro
  - Líneas/swirls que representan la eternidad del sentimiento
- **Narrativa**: La transformación mutua. Cómo su vida se maximizó. Cómo ven la vida diferente ahora.
- **Contenido**:
  - Reflexión final poética
  - Promesa o dedicación mutua
  - Envío de mensaje (interactivo)
  - Fecha de celebración
  - Línea de "A quién va dirigida esta invitación" (si aplica, para permitir que otros invitados la vean)

---

## 2. Estructura de Datos & Módulos

### **De dónde sale cada dato (implementado)**

Se reutilizan los módulos existentes y solo se agregó uno nuevo (`historia`). **Nada es obligatorio para publicar**: si un campo queda vacío, el acto muestra un texto de respaldo propio (claves `*_fallback` del catálogo), así la cadena de sentido nunca se rompe. `required()` del perfil solo genera avisos de "qué falta".

| Acto | Dato | Módulo (pestaña del editor) | Si falta |
|------|------|-----------------------------|----------|
| I | Nombres de la pareja | `bienvenida` (Banner principal) | Título de la invitación |
| I | Frase de apertura | `bienvenida.subtitulo` | "Toda historia empieza lejos…" |
| I | Primera foto (vista a través del agua) | `bienvenida.imagen_hero` | Solo el reflejo de la luna |
| I | Día en que se conocieron | `juntos_desde.fecha` | "No recordamos el día exacto…" |
| I | Primeras impresiones | **`historia.primeras_impresiones`** | Texto de respaldo del acto |
| II | Momentos clave (cuándo, qué, texto, foto; máx. 6) | **`historia.momentos[]`** | Párrafo sobre los primeros momentos |
| II | Cita elegida | **`historia.cita`** (lista cerrada) | Cortázar |
| III | La anécdota (título, texto, foto) | **`historia.anecdota*`** | "Ese momento…"; sin foto se usa la del Acto I, ahora nítida |
| III | Canción | `musica` (archivo de audio en Cloudinary) | Se oculta la línea y el reproductor |
| III | Dedicatoria (de, para, mensaje, firma) | `dedicatoria` | Mensaje de respaldo |
| III | Fotos de la pareja hoy (máx. 3) | `galeria` | Se oculta el bloque |
| IV | Reflexión y promesa | **`historia.reflexion` / `historia.promesa`** | Textos de respaldo |
| IV | Tiempo juntos | Calculado desde `juntos_desde.fecha` | Se oculta |
| IV | Mensaje de quien la recibe | `respuesta` (guest_contributions) | Se oculta si está apagado |
| — | Fecha de celebración | `event_date` (General) | — |
| — | Luz de la luna | `config.colores.primary` | Dorado `#E8C872` |

**Música:** se usa el `AudioModule` existente (archivo subido a Cloudinary), no Spotify: un embed de Spotify exige sesión para escuchar completo, no se puede controlar desde la página y no arranca con el toque de apertura.

**Colores:** la noche es el producto, así que texto/fondo/acentos van fijos en el tema; del cliente solo se toma `primary` (la luz de la luna). La paleta pasa el contraste AA (`AccessibleInvitationTest`).

---

## 3. Arquitectura Técnica (implementada)

Producto propio: perfil `historia` (tarjeta, temporada `amor`), aparte de la "Carta de amor".

```
app/EventProfiles/StoryCardProfile.php          # código historia, módulos, avisos
config/event_profiles.php                       # registro del perfil
database/seeders/EventTypeSeeder.php            # tipo de evento nuestra-historia

app/Modules/Card/StoryModule.php                # único módulo nuevo (código «historia») + citas verificadas
config/modules.php                              # registro del módulo
app/Models/CardStory.php, CardStoryMoment.php   # + relaciones story()/storyMoments() en Invitation
database/migrations/2026_09_19_000001_create_card_story_tables.php

app/Support/InvitationTemplates.php             # WE_STORY_TOGETHER: paleta, orden del menú, textos y *_fallback

resources/views/invitations/templates/we-story-together.blade.php
resources/views/invitations/partials/historia/
  ├─ cover.blade.php     estanque que se toca para empezar
  ├─ stage.blade.php     escenario fijo: cielos, Van Gogh, luna, agua
  ├─ act-1 … act-4.blade.php
  └─ mark.blade.php      marca «Acto I…IV»
resources/views/admin/invitations/panels/modules/historia.blade.php   # un panel, secciones por acto

resources/css/cards/historia.css (+ historia/base, cover, stage)
resources/js/cards/historia/ (index, cover, stage)   # cargado por app.js solo con [data-card="historia"]

database/seeders/showcase/historia-ana-luis.php      # muestra en /muestra/historia-ana-luis
tests/Feature/StoryTemplateTest.php
```

Se reutilizan sin cambios: `shell/head`, `shell/nav`, `shell/footer`, `shell/scripts`, `shell/cover-component`, el reproductor, y el formulario de `modules/respuesta`.

---

## 4. Componentes Visuales Clave

### **Acto I: El Reflejo**
```
┌─────────────────────────────────────┐
│    [Portada]                        │
│                                     │
│    "The Story We Write Together"    │
│                                     │
│    [Agua tranquila con reflejo]     │
│    [Luna diminuta, visible]         │
│                                     │
│    [Nombres: María & Juan]          │
│    "Conocidos el 15 de mayo, 2019"  │
│                                     │
│    [Foto inicial - suave, luminosa] │
│                                     │
│    → SCROLL AQUÍ                    │
└─────────────────────────────────────┘
```

### **Acto II: La Oleada**
- Animación de olas que se agitan
- Moon icon que sube gradualmente
- Timeline interactivo:
  - Click/hover en cada punto revela foto + texto
- Incluye quotes sobre amor intercalados
- Reproductor de música (integrado, no invasivo)

### **Acto III: El Encuentro**
```
┌─────────────────────────────────────┐
│    [La Luna clara, de frente]       │
│                                     │
│    ✨ LA ANÉCDOTA ✨                 │
│                                     │
│    [Titulo del momento]             │
│                                     │
│    [Texto de la anécdota]           │
│    [Foto del momento - prominente]  │
│                                     │
│    [Fotos actuales de pareja]       │
│                                     │
│    "Dedicatoria personal"           │
│                                     │
│    → SCROLL AQUÍ                    │
└─────────────────────────────────────┘
```

### **Acto IV: La Constelación**
- SVG inspirado en Van Gogh:
  - Estrellas animadas (parpadean, giran levemente)
  - Vórtices suaves
  - Luna iluminada en el centro
  - Líneas cósmicas conectando puntos
- Reflexión final (poético, tipografía elegante)
- Promesa/Dedicatoria
- Call-to-action final (compartir, agradecer)

---

## 5. Experiencia de Scroll & Interactividad

### **Scroll-Trigger Events**

```javascript
Acto I:  0-25% → Agua tranquila, Luna crece lentamente
Acto II: 25-50% → Olas se agitan, Moon sube, Timeline activo
Acto III: 50-75% → Llegada a la superficie, Luna de frente, Anécdota destacada
Acto IV: 75-100% → Cielo constelación, animaciones finales
```

### **Interactividad**
- **Timeline (Acto II)**: Hover/Click en cada punto → revelar foto + texto
- **Reproductor de Música**: Play/Pause integrado, visualización de lyrics (opcional)
- **Galería de fotos**: Swipe/click entre fotos de cada acto
- **Mensaje final**: Formulario para enviar un mensaje antes de terminar

---

## 6. Paleta de Colores & Tipografía

### **Colores**
- **Primario**: Azul profundo de noche (`#0a1e3a`)
- **Secundario**: Dorado/Oro de Luna (`#d4af37`)
- **Acentos**: Azul agua translúcido (`#1e5a78`)
- **Fondo**: Negro suave con degradado sutil (`#0f0f1e` → `#1a0a2e`)
- **Texto**: Blanco cremoso (`#f5f1de`)

### **Tipografía**
- **Títulos**: `Playfair Display` (elegante, serif)
- **Narrativa**: `Lora` (readable, romántica)
- **Citas/Poesía**: `Cormorant Garamond` (sofisticada)
- **Detalles**: `Inter` (clara, moderna)

---

## 7. Elementos Poéticos: Citas sobre Amor

A integrar en el Acto II (entre eventos):

Solo citas con obra verificable (`StoryModule::QUOTES`); la pareja elige una en el editor:

- **Julio Cortázar**, *Rayuela*: "Andábamos sin buscarnos, pero sabiendo que andábamos para encontrarnos." (por defecto)
- **Pablo Neruda**, *Soneto XVII*: "Te amo sin saber cómo, ni cuándo, ni de dónde, te amo directamente sin problemas ni orgullo."
- **Octavio Paz**, *Piedra de sol*: "El mundo cambia si dos se miran y se reconocen."
- **Rainer Maria Rilke**, *Cartas a un joven poeta*: "El amor consiste en esto: dos soledades que se protegen, se tocan y se saludan."
- **Mario Benedetti**, *Te quiero*: "Y en la calle, codo a codo, somos mucho más que dos."

Se descartaron las de la propuesta original que no tienen fuente: "Find what you love and let it kill you" suele atribuirse a Bukowski pero no es suya; "Amar es arriesgar todo" (Cortázar) y "El amor es experiencia de vida" (Paz) no aparecen en sus obras.

---

## 8. Implementación: Checklist

### **Fase 1: Estructura Base**
- [ ] Crear `LoveStoryProfile.php`
- [ ] Crear módulos en `app/Modules/`
- [ ] Registrar plantilla en `InvitationTemplates.php`
- [ ] Crear template principal: `the-story-we-write-together.blade.php`

### **Fase 2: Vistas Públicas**
- [ ] Crear 4 parciales de actos
- [ ] Integrar shell (head, nav, footer)
- [ ] Crear CSS tema nocturno

### **Fase 3: Admin Panels**
- [ ] Crear paneles de input para cada acto
- [ ] Validaciones de campos
- [ ] Preview en tiempo real

### **Fase 4: Interactividad & Animaciones**
- [ ] JavaScript para scroll-trigger
- [ ] Animaciones de olas/Luna (Canvas o SVG)
- [ ] Reproductor de música
- [ ] Timeline interactivo

### **Fase 5: Pulido & Testing**
- [ ] Responsive design
- [ ] Performance (lazy loading de imágenes)
- [ ] Accesibilidad
- [ ] Testing cross-browser

---

## 9. Diferenciadores & Valor

Esta plantilla destaca porque:

1. **Narrativa Guiada**: No es un viaje aleatorio; es la metáfora del agua → Luna → constelación
2. **Anécdota Central**: El módulo más importante es el momento pequeño que significó todo
3. **Multimedial**: Texto + fotos + música + movimiento
4. **Emotivamente Resonante**: Basada en definiciones reales de amor, no clichés
5. **Reutilizable**: Cada pareja puede contar su propia historia con la misma estructura
6. **Artística**: La referencia a Van Gogh eleva la experiencia más allá de una invitación

---

## 10. Próximos Pasos

1. ✅ Validar concepto visual (esta propuesta)
2. ⏳ Crear mockup/prototipo visual (Figma o HTML)
3. ⏳ Desarrollar módulos en Bida Events
4. ⏳ Crear paneles administrativos
5. ⏳ Implementar animaciones
6. ⏳ Testing y refinamiento

---

**Autor**: Claude Haiku 4.5  
**Última actualización**: 2026-09-19