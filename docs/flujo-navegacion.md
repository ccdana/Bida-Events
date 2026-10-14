# Recorridos del sitio: a dónde lleva cada botón

Mapa de navegación de Bida Events con el modelo principal primero: **cada persona arma su propia
invitación con un plan mensual («Únete»)**. Lo adicional es que el equipo se la haga («Te la
hacemos», paquetes de pago único por WhatsApp).

Los diagramas se leen de arriba hacia abajo (o de izquierda a derecha en el último). Los nombres entre
llaves son datos que cambian: `{plan}`, `{paquete}`, `{evento}`, `{slug}`, `X` es la clave del plan.

## El recorrido principal

De la portada a la primera invitación publicada, pasando por elegir el plan, crear la cuenta, confirmar el correo y pagar.

```mermaid
flowchart TD
  A["Portada /"] -->|"Crear mi invitación · Planes · Elegir mi plan"| P["Planes de la portada #planes"]
  OT["Guía, páginas por evento, legales"] -->|"Crear mi invitación (cabecera)"| B["/unete#planes<br/>planes con «Elegir»"]
  P -->|"Elegir {plan}"| R["/registro?plan=X"]
  B -->|"Elegir"| R
  R -->|"Crear mi cuenta"| V["/correo/confirmar"]
  V -->|"enlace del correo"| S["/client/suscripcion?plan=X"]
  S -->|"Pagar"| O["/client/suscripcion/orden/ID"]
  O -->|"plan activo"| C["/client/invitaciones/nueva"]
  C -->|"guardar"| E["/client/invitations/ID/editar"]
  E -->|"publicar y compartir"| L["/p/slug (enlace público)"]
```

## Hazlo tú: armarla sin pagar antes

Cualquier cuenta del sitio (que no sea revendedor) arma su propia invitación con todo desbloqueado. Al
publicarla, el nivel sale de las partes encendidas (`App\Support\DiyInvitations`): **Gratis** (portada,
cuenta regresiva y ubicación, con la marca de Bida Events a la vista, una por cuenta) o **Básico,
Estándar y Premium** (los mismos módulos que los paquetes del equipo). Mientras no hay pago en línea por
nivel, el nivel pagado se pide por WhatsApp y lo activa el equipo desde el editor del administrador.

```mermaid
flowchart TD
  D["/client (panel)"] -->|"Crear mi invitación · Nueva invitación"| N["/client/mi-invitacion/nueva<br/>asistente de 3 pasos"]
  N -->|"Crear invitación (borrador)"| E["/client/mi-invitacion/ID/editar<br/>todo desbloqueado, «Nivel …» en la barra"]
  E -->|"Publicar"| P["/client/mi-invitacion/ID/publicar"]
  P -->|"Publicar gratis (apaga lo que no trae)"| L["/p/slug con la franja de Bida Events"]
  P -->|"Pedir {nivel} por WhatsApp"| W["wa.me (Ref. HAZLO)"]
  W -->|"el equipo pone el nivel en el editor"| L2["/p/slug con todo lo que armó"]
  E -->|"Subir a {nivel} (publicada con menos)"| P
```

- Hasta 3 borradores sin publicar a la vez (`bida.diy.drafts_limit`); un borrador se puede borrar desde el panel.
- Publicar exige el correo confirmado. La gratuita queda en línea hasta 7 días después del evento; la pagada, 30.
- Ya publicada, lo que se encienda por encima de su nivel no se ve hasta subirla de nivel.

## Qué hace «Elegir {plan}»

El mismo botón lleva a tres lugares distintos según haya pago en línea, sesión iniciada y registro abierto.

```mermaid
flowchart TD
  Q["Botón «Elegir {plan}»<br/>portada y /unete"] --> M{"¿Hay pago en línea<br/>para ese plan?"}
  M -->|"No"| W["WhatsApp con el plan<br/>y el código Ref."]
  M -->|"Sí"| U{"¿Ya inició sesión?"}
  U -->|"Sí"| S["/client/suscripcion?plan=X"]
  U -->|"No"| G{"¿Registro abierto?"}
  G -->|"Sí"| R["/registro?plan=X<br/>guarda el plan elegido"]
  G -->|"No"| L["/login y vuelve<br/>a /client/suscripcion?plan=X"]
```

## Entrar, registrarse y volver

Quien crea la cuenta sigue al pago del plan que eligió; quien ingresa vuelve a la página que quería abrir o a su panel.

```mermaid
flowchart TD
  R["/registro?plan=X"] -->|"Crear mi cuenta o Google"| N["Cuenta nueva sin plan<br/>sesión iniciada"]
  N --> V["/correo/confirmar"]
  V -->|"abre el enlace del correo"| I["Correo confirmado"]
  I --> T{"¿Quedó un destino<br/>pendiente?"}
  T -->|"Sí: el plan elegido"| S["/client/suscripcion?plan=X"]
  T -->|"No"| D["/client (Mis eventos)"]
  LG["/login"] -->|"Ingresar o Google"| Z{"¿Destino pendiente distinto<br/>de / /login /registro /dashboard?"}
  Z -->|"Sí"| S2["Ese destino"]
  Z -->|"No · es admin"| A["/admin"]
  Z -->|"No · es cliente"| D
```

## El pago y la activación del plan

El plan se activa cuando se confirma el cobro, por el aviso de la pasarela o por la verificación programada, no por volver a la página.

```mermaid
flowchart TD
  S["/client/suscripcion"] --> E{"¿Correo confirmado?"}
  E -->|"No"| AC["/client/cuenta<br/>agregar o confirmar correo"]
  E -->|"Sí"| F{"Forma de pago"}
  F -->|"Tarjeta · Lemon Squeezy"| LS["Página de pago de la pasarela<br/>(fuera del sitio)"]
  F -->|"QR Bolivia · Libélula"| O["/client/suscripcion/orden/ID<br/>muestra el QR"]
  LS -->|"vuelve al sitio"| O
  O -->|"consulta cada 3 s"| P{"¿Cobro confirmado?"}
  WH["Aviso de la pasarela<br/>/webhooks/proveedor"] -->|"cola ProcessWebhookEvent"| P
  VP["Programador<br/>bida:verificar-pagos cada 5 min"] --> P
  P -->|"Sí"| OK["Plan activo<br/>Crear mi primera invitación"]
  P -->|"No"| W["Sigue esperando"]
```

## Lo adicional: te la hacemos

Los paquetes no pasan por cuenta ni pago en línea: salen todos por WhatsApp y el equipo deja la invitación en el panel del cliente.

```mermaid
flowchart LR
  H["Portada · Te la hacemos<br/>#precios"] -->|"Elegir {paquete}"| WA["WhatsApp con el paquete<br/>y Ref. de origen"]
  HZ["/unete · Pedir {paquete}"] --> WA
  LD["Páginas por evento<br/>Elegir {paquete}"] --> WA
  WA --> EQ["El equipo arma la invitación<br/>con el cliente"]
  EQ --> CP["/client · Ver invitados<br/>Abrir mi invitación"]
```

## Botón por botón

### Portada (`/`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Logo | / | Vuelve a la portada |
| Planes · Plantillas · Qué incluye · Te la hacemos · Preguntas | #planes · #plantillas · #incluye · #precios · #preguntas | Anclas dentro de la misma página |
| Crear mi invitación (cabecera) | #planes | Baja a los planes de la propia portada; en las demás páginas va a /unete#planes |
| Ícono de WhatsApp (cabecera) | wa.me con el mensaje y Ref. HOME | Acción secundaria |
| Ingresar / Mi panel | /login · /dashboard | Según haya sesión |
| Únete · Elegir mi plan | #planes | Primer bloque de servicios |
| Te la hacemos · Ver los paquetes | #precios | Paquetes, pago único |
| Diseños de temporada | #temporada o la página de la temporada | Solo mientras la temporada se vende |
| Pestañas de evento (Pruébala como invitado) | /muestra/{slug} | Muestra interactiva, nada se guarda |
| Ver todos los diseños de {evento} | /invitaciones-de-{evento} | Página por evento |
| Así funciona · Elegir mi plan | #planes |  |
| Elegir {plan} (cada columna) | Ver «Qué hace Elegir {plan}» | Registro con el plan, pago o WhatsApp |
| Ver el detalle de cada plan | /unete#planes | Los mismos planes con lo que trae cada uno |
| Elegir {paquete} | wa.me con el paquete y su precio | Lo adicional |
| Botón flotante de temporadas | Panel de la temporada → WhatsApp o su página |  |

### Únete (`/unete`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Planes (menú) | #planes | Mismas columnas que los paquetes, con el detalle de cada plan |
| Plan {nombre} (en «Para quién») | #planes |  |
| Elegir {plan} | Ver «Qué hace Elegir {plan}» | Registro con el plan, pago o WhatsApp |
| Escríbenos (¿Necesitas algo distinto?) | wa.me con el mensaje de Únete (Ref. HAZLO) | Última celda de la cuadrícula |
| Pedir {paquete} | wa.me con el paquete | Lo adicional |
| Ver todo lo que incluye cada paquete | /#precios |  |
| Elegir mi plan (cierre) | #planes |  |
| Tengo una duda | wa.me con el mensaje de Únete (Ref. HAZLO) |  |

### Registro y correo (`/registro · /correo/confirmar`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Crear mi cuenta | /correo/confirmar | Crea el usuario, envía el correo y deja la sesión iniciada |
| Entrar con Google | /auth/google → /auth/google/callback → destino pendiente o /client | Solo si hay credenciales de Google |
| Cambiar (plan elegido) | /unete#planes |  |
| Ingresa | /login |  |
| Términos · política de privacidad | /legal/terminos · /legal/privacidad |  |
| Enlace del correo | /client/suscripcion?plan=X o /client | Al plan elegido si lo había |
| Enviarme otro enlace | Misma página, con aviso | POST /correo/confirmar |
| Cámbialo en tu cuenta | /client/cuenta |  |
| Ir a mi panel | /dashboard → /client o /admin |  |

### Mi plan (pago) (`/client/suscripcion`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Confirmar mi correo / Agregar mi correo | /client/cuenta | Hace falta para pagar en línea |
| Pagar (tarjeta) | Página de pago de Lemon Squeezy → /client/suscripcion/orden/ID | Se renueva sola cada mes |
| Pagar (QR Bolivia) | /client/suscripcion/orden/ID | Muestra el QR en bolivianos |
| Administrar mi suscripción | /client/suscripcion/portal → portal de la pasarela | Solo con tarjeta |
| Crear mi primera invitación (plan activo) | /client/invitaciones/nueva | En la página de la orden |
| Ir a mi panel | /client |  |

### Panel del cliente (`/client`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Nueva invitación (revendedor) | /client/invitaciones/nueva | Solo con plan activo y cupo; si no, queda desactivado con el motivo |
| Crear mi invitación · Nueva invitación (Hazlo tú) | /client/mi-invitacion/nueva | Cualquier cuenta que no sea revendedor; hasta 3 borradores |
| Mira los planes mensuales (panel vacío) | /client/suscripcion o /unete#planes | La primera si hay pago en línea |
| Prefiero que me la hagan | /#precios |  |
| Borrar borrador | /client/mi-invitacion/{id} (DELETE) | Solo un borrador de Hazlo tú que nunca se publicó |
| Pagar ahora / Renovar | /client/suscripcion | Aviso de vencimiento |
| Ver invitados / Ver respuestas | /client/invitations/{id} |  |
| Editar | /client/invitations/{id}/editar · /client/mi-invitacion/{id}/editar | Revendedor al día · dueño de una de Hazlo tú |
| Abrir mi invitación · Copiar enlace | /p/{slug} | Solo si está publicada |
| Mi plan · Mi cuenta · Ver el sitio (menú) | /client/suscripcion o /unete#planes · /client/cuenta · / |  |

### Páginas por evento (`/invitaciones-de-boda, /invitaciones-xv-anos…`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Crear mi invitación (cabecera) | /unete#planes |  |
| Ver los planes (aviso «¿Prefieres armarla tú?») | /unete#planes |  |
| Diseños (capturas) | /muestra/{slug} |  |
| Escríbenos · Elegir {paquete} | wa.me con el mensaje del evento | Código propio de cada página |
| La quiero por {precio} (temporada) | wa.me con el precio de temporada |  |

### Pie del sitio (`En todas las páginas`)

| Botón o enlace | Lleva a | Notas |
| --- | --- | --- |
| Invitaciones: Boda, XV años, Bautizo… | /invitaciones-de-{evento} | Las de temporada (Halloween, Día del Amor) no van fijas en el pie |
| Únete · Guía de invitaciones | /unete · /guia-invitaciones-digitales |  |
| Precios | /#precios |  |
| Mi panel / Ingresar | /dashboard · /login |  |
| Privacidad · Cookies · Términos | /legal/{página} |  |
| Escríbenos por WhatsApp · correo | wa.me · mailto |  |
| Instagram · Facebook · TikTok | Redes (sitio externo) |  |

## De dónde sale cada destino

- Lógica de los planes y de «Elegir»: `app/Support/DiyPlans.php`.
- Portada: `app/Http/Controllers/HomeController.php` y `resources/views/home.blade.php`.
- Únete: `app/Http/Controllers/PublicPagesController.php` y `resources/views/unete.blade.php`.
- Cuenta y redirecciones: `RegisterController`, `VerifyEmailController`, `LoginController`
  (trait `RedirectsAfterLogin`) y la ruta `/dashboard` de `routes/web.php`.
- Pago: `app/Http/Controllers/Client/SubscriptionController.php` y `app/Payments`.
