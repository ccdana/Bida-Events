# Probar pagos, registro y correo en tu computadora

Guía para probar en Laragon (`bida-events.test`) el registro con Google, el correo, la tarjeta
(Lemon Squeezy en **modo de prueba**, sin dinero real) y el QR de Libélula. Para dejarlos funcionando
en el servidor está [conexiones.md](conexiones.md).

**Ya quedó listo en tu `.env`:** todas las variables, vacías, al final del archivo (bloque «Pruebas
de pagos y registro en desarrollo»), con el modo de prueba de Lemon Squeezy encendido y
`TRUSTED_PROXIES=127.0.0.1` para que el túnel funcione con https. **Solo te falta pegar los valores**
que vas sacando en los pasos 2 a 6.

> Los paneles de estos servicios cambian de nombre o de lugar sus menús de vez en cuando. Si un
> botón no está exactamente donde dice la guía, busca el mismo nombre en el menú lateral o en la
> configuración de la cuenta.

---

## 1. Resumen: qué copiar y de dónde sale

| Variable | De dónde la sacas | Paso | Tiempo |
| --- | --- | --- | --- |
| `APP_URL` | Tu dominio gratis de ngrok (`https://xxxx.ngrok-free.app`) | 2 | 5 min |
| `MAIL_*` | Mailpit (ya viene en Laragon) o tu casilla de Zoho | 3 | 5 min |
| `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` | Google Cloud Console | 4 | 10 min |
| `LEMONSQUEEZY_STORE_ID` | Lemon Squeezy › *Settings › Stores* | 5.2 | 15 min en total |
| `LEMONSQUEEZY_VARIANT_*` | Las 4 variantes del producto | 5.3 y 5.5 | |
| `LEMONSQUEEZY_API_KEY` | Lemon Squeezy › *Settings › API* | 5.4 | |
| `LEMONSQUEEZY_SIGNING_SECRET` | Lo generas tú y lo pegas también en el webhook | 5.6 | |
| `LIBELULA_APPKEY` / `LIBELULA_BASE_URL` | Te los manda Libélula por correo | 6 | días |

Lo que dejes vacío simplemente no aparece: sin Google no se ve «Entrar con Google», sin Lemon
Squeezy no se ofrece tarjeta y sin Libélula no se ofrece QR. Puedes ir probando uno a la vez.

Después de cambiar el `.env`, siempre:

```bash
php artisan config:clear
```

**Orden recomendado:** primero ngrok (paso 2), porque su dirección se usa en Google y en Lemon
Squeezy.

---

## 2. ngrok: tu dirección pública (`APP_URL`)

Lemon Squeezy y Libélula avisan los pagos a una URL de internet, y Google no acepta `.test` como
dirección de regreso. ngrok le da a tu Laragon una dirección pública fija con https. Ya viene en
Laragon (`C:\laragon\bin\ngrok\ngrok.exe`).

### Paso a paso

1. Entra a [dashboard.ngrok.com/signup](https://dashboard.ngrok.com/signup).
2. Regístrate con tu correo (o con *Sign up with Google / GitHub*). Si usaste correo, abre el
   mensaje de verificación que te llega y confírmalo.
3. Si te pregunta para qué lo vas a usar, elige cualquier opción (por ejemplo *Development*).
4. En el menú de la izquierda, entra a **Getting Started › Your Authtoken**.
5. Pulsa **Copy** al lado del token (una cadena larga tipo `2abc...XYZ`).
6. Abre la terminal de Laragon (botón **Terminal**) y registra el token, una sola vez:

   ```bash
   C:\laragon\bin\ngrok\ngrok.exe config add-authtoken PEGA_AQUI_TU_AUTHTOKEN
   ```

   Debe responder `Authtoken saved to configuration file`.
7. En el menú de la izquierda, entra a **Domains** (puede estar dentro de *Universal Gateway*).
   La cuenta gratis trae **un dominio fijo** ya creado, tipo `algo-algo-1234.ngrok-free.app`. Si la
   lista está vacía, pulsa **+ New Domain** / **Create Domain** y acepta el que te propone.
8. Copia ese dominio. Es tu dirección de pruebas para siempre (no cambia al cerrar ngrok).
9. Pégalo en el `.env` (con `https://` y sin barra al final):

   ```dotenv
   APP_URL=https://algo-algo-1234.ngrok-free.app
   ```

10. Abre el túnel y déjalo abierto mientras pruebas:

    ```bash
    C:\laragon\bin\ngrok\ngrok.exe http 80 --host-header=bida-events.test --url=https://algo-algo-1234.ngrok-free.app
    ```

    Debe mostrar `Session Status  online` y la línea `Forwarding https://algo… -> http://localhost:80`.
11. Abre tu dominio de ngrok en el navegador. La primera vez ngrok muestra una página de aviso:
    pulsa **Visit Site** y debe verse el sitio.

**Mientras pruebes, entra al sitio por la dirección de ngrok**, no por `bida-events.test`: así el
inicio de sesión de Google, los enlaces de los correos y el aviso de Libélula apuntan al mismo
lugar. Los avisos de las pasarelas no ven la página de aviso de ngrok.

---

## 3. Correo (`MAIL_*`)

### Opción A: Mailpit (recomendada para probar)

Atrapa todos los correos en tu computadora: no llegan a nadie y los ves en el navegador. **No
necesita credenciales.**

1. Abre Laragon → clic derecho en la ventana (o el botón **Menú**) → **Mailpit**. Si no aparece,
   abre directamente `C:\laragon\bin\mailpit\mailpit.exe` y deja esa ventana abierta.
2. En el `.env`, reemplaza estas líneas del bloque `MAIL_`:

   ```dotenv
   MAIL_MAILER=smtp
   MAIL_SCHEME=null
   MAIL_HOST=127.0.0.1
   MAIL_PORT=1025
   MAIL_USERNAME=null
   MAIL_PASSWORD=null
   ```

3. `php artisan config:clear`.
4. Abre <http://localhost:8025>: ahí aparece cada correo que mande el sitio.

### Opción B: tu Zoho real

Para ver cómo llegan de verdad a Gmail u Outlook.

#### Paso a paso: saber tu servidor

1. Entra a tu correo de Zoho en el navegador y mira la dirección:
   - `mail.zoho.com` → servidor `.com`
   - `mail.zoho.eu` → servidor `.eu`
   - `mail.zoho.in` → servidor `.in`
2. Mira tu plan (*Configuración › Suscripción*, o pregúntate si pagas Zoho):
   - Plan **pagado** con tu propio dominio (`hola@tudominio.com`) → `smtppro.zoho.com`
   - Plan **gratis** (*Forever Free*) → `smtp.zoho.com`
   - Si tu servidor es `.eu`, cambia el final: `smtppro.zoho.eu` o `smtp.zoho.eu`.

#### Paso a paso: contraseña de aplicación

El sitio no debe usar tu contraseña normal de Zoho, sino una propia que puedes borrar cuando
quieras.

1. Entra a [accounts.zoho.com](https://accounts.zoho.com) (o `accounts.zoho.eu`, según tu servidor)
   con la casilla que va a enviar los correos.
2. Menú de la izquierda → **Seguridad** (*Security*).
3. Si no tienes activada la **verificación en dos pasos** (*Multi-Factor Authentication*),
   actívala: Zoho solo ofrece contraseñas de aplicación con ella encendida.
4. En la misma sección, busca **Contraseñas específicas de la aplicación** (*App Passwords*) →
   **Generar nueva contraseña** (*Generate New Password*).
5. Nombre: `Bida Events local` → **Generar**.
6. Copia la contraseña que aparece (Zoho la muestra **una sola vez**; si la pierdes, bórrala y crea
   otra).
7. Si eres administrador de la organización: en [mailadmin.zoho.com](https://mailadmin.zoho.com) ›
   *Usuarios* › tu usuario › *Acceso al correo*, confirma que **SMTP** esté permitido.

#### Cómo queda el `.env`

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtppro.zoho.com
MAIL_PORT=587
MAIL_USERNAME=hola@tudominio.com
MAIL_PASSWORD=la-contraseña-de-aplicación
# La misma casilla de MAIL_USERNAME (o un alias suyo), si no Zoho lo rechaza
MAIL_FROM_ADDRESS="hola@tudominio.com"
```

Para probar el envío (después de `php artisan config:clear`):

```bash
php artisan tinker --execute="Mail::raw('Prueba', fn (\$m) => \$m->to('tu@correo.com')->subject('Prueba'));"
```

Si no aparece ningún error, revisa tu bandeja (y spam). Los DNS para que no caiga en spam están en
[conexiones.md › 1.5](conexiones.md#15-dns-para-que-no-lleguen-a-spam).

> Con `MAIL_MAILER=log` (como está ahora) los correos no se envían: quedan escritos en
> `storage/logs/laravel.log`, y de ahí puedes copiar el enlace para confirmar el correo.

---

## 4. Google: entrar con Google (`GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET`)

Necesitas tener el dominio de ngrok del paso 2.

### Paso a paso: crear el proyecto

1. Entra a [console.cloud.google.com](https://console.cloud.google.com) con tu cuenta de Google.
   La primera vez acepta los términos.
2. Arriba a la izquierda, al lado del logo, pulsa el **selector de proyecto** → **Proyecto nuevo**.
3. Nombre: `Bida Events` → **Crear**. Espera unos segundos y elige ese proyecto en el selector.

### Paso a paso: pantalla de consentimiento

Es lo que ve la persona cuando Google le pregunta si deja entrar a Bida Events.

1. Menú ☰ → **APIs y servicios** → **Pantalla de consentimiento de OAuth** (en algunas cuentas se
   llama **Google Auth Platform**). Pulsa **Comenzar** (*Get started*).
2. **Información de la aplicación:** nombre `Bida Events`, correo de asistencia: el tuyo →
   **Siguiente**.
3. **Público** (*Audience*): **Externo** → **Siguiente**.
4. **Información de contacto:** tu correo → **Siguiente**.
5. Acepta la política de datos de usuario → **Continuar** → **Crear**.
6. Entra a **Público** (*Audience*) → sección **Usuarios de prueba** → **+ Agregar usuarios** →
   escribe los Gmail con los que vas a probar → **Guardar**. Mientras la app esté «En prueba», solo
   esas cuentas pueden entrar (y no hace falta que Google la revise).

### Paso a paso: credenciales

1. Menú ☰ → **APIs y servicios** → **Credenciales** (o en Google Auth Platform: **Clientes**).
2. **+ Crear credenciales** → **ID de cliente de OAuth** (o **+ Crear cliente**).
3. **Tipo de aplicación:** **Aplicación web**.
4. **Nombre:** `Bida Events`.
5. **Orígenes autorizados de JavaScript:** déjalo vacío.
6. **URI de redireccionamiento autorizados** → **+ Agregar URI** → escribe exactamente:

   ```text
   https://algo-algo-1234.ngrok-free.app/auth/google/callback
   ```

   (Tu dominio de ngrok.) Cuando subas al servidor, vuelve aquí y agrega también
   `https://bida-events.com/auth/google/callback` en este mismo cliente.
7. **Crear**.
8. Aparece una ventana con el **ID de cliente** y el **Secreto del cliente**. Cópialos ya (o pulsa
   **Descargar JSON**): Google puede no volver a mostrarte el secreto completo. Si lo pierdes, en
   el cliente pulsa **Agregar secreto** y crea otro.
9. Pégalos en el `.env`:

   ```dotenv
   GOOGLE_CLIENT_ID=123456789-xxxxxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=GOCSPX-xxxxxxxxxxxxxxxx
   ```

10. `php artisan config:clear`. Los cambios en Google pueden tardar unos minutos en aplicarse.

---

## 5. Lemon Squeezy en modo de prueba (tarjeta)

En modo de prueba no hace falta activar la tienda, verificar identidad ni poner datos bancarios.

### 5.1 Crear la cuenta

1. Entra a [app.lemonsqueezy.com/register](https://app.lemonsqueezy.com/register).
2. Regístrate con tu correo y confirma el mensaje que te llega.
3. Te pide crear tu **tienda** (*store*): nombre `Bida Events`, y la dirección
   (`bida-events.lemonsqueezy.com`). Moneda: **USD**.
4. Abajo a la izquierda del panel, enciende el interruptor **Test mode**. Todo lo que sigue se
   hace con ese interruptor encendido: lo que crees en modo de prueba no existe en modo real, y al
   revés.

### 5.2 `LEMONSQUEEZY_STORE_ID`

1. Abajo a la izquierda → **Settings** → **Stores**.
2. Al lado del nombre de tu tienda aparece un número, por ejemplo `#12345`.
3. Copia solo el número:

   ```dotenv
   LEMONSQUEEZY_STORE_ID=12345
   ```

### 5.3 Crear el producto con los 4 planes

1. Menú de la izquierda → **Store** → **Products** → **+ New product**.
2. **Name:** `Hazlo tú`. Descripción: la que quieras (la ve el comprador al pagar).
3. En **Pricing**, elige **Subscription**.
4. Baja a **Variants** → **Add variant** y crea una por plan. En cada variante: el nombre, el
   precio, **Subscription** y repetir cada **1 month** (*Monthly*):

   | Variante | Precio | Repite |
   | --- | --- | --- |
   | Inicial | 9 | cada 1 mes |
   | Aliado | 14 | cada 1 mes |
   | Emprendedor | 29 | cada 1 mes |
   | Agencia | 59 | cada 1 mes |

   Los precios tienen que ser **los mismos del sitio** (Admin › Ajustes, el precio con descuento si
   tiene). Sin periodo de prueba gratis (*free trial*).
5. **Publish** (arriba a la derecha).

### 5.4 `LEMONSQUEEZY_API_KEY`

1. **Settings** → **API**.
2. Pulsa **+** (*Create API key*).
3. Nombre: `Bida local` → **Create API key**.
4. Copia la clave completa (empieza con `eyJ...` y es muy larga). Se muestra **una sola vez**; si
   la pierdes, borra esa y crea otra.

   ```dotenv
   LEMONSQUEEZY_API_KEY=eyJ0eXAiOiJKV1QiLCJhbGciOi...
   ```

### 5.5 `LEMONSQUEEZY_VARIANT_*`: los ids de los planes

1. Abre **Git Bash** (o la terminal de Laragon) y corre, cambiando `TU_API_KEY` por la clave del
   paso 5.4:

   ```bash
   curl -s https://api.lemonsqueezy.com/v1/variants -H "Authorization: Bearer TU_API_KEY" -H "Accept: application/vnd.api+json" | grep -o '"id":"[0-9]*"\|"name":"[^"]*"'
   ```

2. Sale una lista donde cada `"id"` va seguido del `"name"` de su variante:

   ```text
   "id":"111111"
   "name":"Inicial"
   "id":"222222"
   "name":"Aliado"
   ...
   ```

   Ignora la variante «Default» si aparece.
3. Copia cada número en su plan:

   ```dotenv
   LEMONSQUEEZY_VARIANT_INICIAL=111111
   LEMONSQUEEZY_VARIANT_ALIADO=222222
   LEMONSQUEEZY_VARIANT_EMPRENDEDOR=333333
   LEMONSQUEEZY_VARIANT_AGENCIA=444444
   ```

Si cruzas un id, el cobro queda «para revisar» y no activa el plan.

### 5.6 `LEMONSQUEEZY_SIGNING_SECRET` y el webhook

El *signing secret* lo inventas tú: es una contraseña que Lemon Squeezy usa para firmar cada aviso
y que el sitio comprueba.

1. Genéralo en la terminal:

   ```bash
   php -r "echo bin2hex(random_bytes(32));"
   ```

   Copia el resultado (64 letras y números).
2. **Settings** → **Webhooks** → **+**.
3. **Callback URL:** `https://algo-algo-1234.ngrok-free.app/webhooks/lemonsqueezy` (tu dominio
   de ngrok).
4. **Signing secret:** pega el valor del punto 1.
5. Marca estos eventos: `order_created`, `order_refunded`, `subscription_created`,
   `subscription_updated`, `subscription_cancelled`, `subscription_resumed`,
   `subscription_expired`, `subscription_paused`, `subscription_unpaused`,
   `subscription_payment_success`, `subscription_payment_failed`,
   `subscription_payment_refunded`.
6. **Save webhook**.
7. Pega el mismo secreto en el `.env`:

   ```dotenv
   LEMONSQUEEZY_SIGNING_SECRET=el-mismo-valor-del-punto-1
   ```

### 5.7 Portal del cliente

1. **Settings** → **Customer portal**.
2. Activa que el cliente pueda **cambiar de plan** (*change plans*, entre las variantes de «Hazlo
   tú»), **actualizar el método de pago** y **cancelar**. **Save**.

### 5.8 Revisa el `.env`

```dotenv
LEMONSQUEEZY_TEST_MODE=true
LEMONSQUEEZY_API_KEY=eyJ0eXAiOiJKV1Qi...
LEMONSQUEEZY_STORE_ID=12345
LEMONSQUEEZY_SIGNING_SECRET=...
LEMONSQUEEZY_VARIANT_INICIAL=111111
LEMONSQUEEZY_VARIANT_ALIADO=222222
LEMONSQUEEZY_VARIANT_EMPRENDEDOR=333333
LEMONSQUEEZY_VARIANT_AGENCIA=444444
```

Tarjeta de prueba: `4242 4242 4242 4242`, cualquier fecha futura, cualquier CVC y cualquier nombre.
Para probar un rechazo: `4000 0000 0000 0002`.

---

## 6. Libélula (`LIBELULA_APPKEY` y `LIBELULA_BASE_URL`)

Libélula **no tiene registro en línea**: el appkey te lo dan ellos cuando te afilias, y puede
tardar unos días. Mientras tanto puedes probar todo lo demás.

### Paso a paso

1. Escribe a `soporte@libelula.bo` (o usa el formulario de contacto de
   [libelula.bo](https://libelula.bo)). Puedes copiar este mensaje:

   > Hola, soy [tu nombre], persona natural con NIT [tu NIT], y quiero cobrar con Libélula las
   > suscripciones mensuales de mi plataforma web de invitaciones digitales (Bida Events), con QR
   > y Tigo Money, integrando su API REST (registrar deuda y consultar pagos).
   >
   > Quisiera saber:
   > 1. Si aceptan persona natural con NIT y qué documentos necesitan.
   > 2. La comisión por QR, Tigo Money y tarjeta, y si hay algún costo fijo, mensual o de retiro.
   > 3. A qué cuenta y cada cuánto liquidan los cobros.
   > 4. Que me habiliten el canal QR Simple.
   > 5. El appkey y la dirección del ambiente de pruebas, y después los de producción.
   > 6. La documentación vigente de la API.
   > 7. Qué se necesita para que Libélula emita la factura de cada pago.
   >
   > Gracias.

2. Cuando te respondan con el **appkey de pruebas** (un código tipo
   `588e5e10-xxxx-xxxx-xxxx-xxxxxxxxxxxx`) y la **dirección de pruebas** (en su guía de 2020 era
   `http://www.todotix.com:10888`; usa la que te den), pégalos:

   ```dotenv
   LIBELULA_APPKEY=588e5e10-xxxx-xxxx-xxxx-xxxxxxxxxxxx
   LIBELULA_BASE_URL=http://la-direccion-de-pruebas-que-te-den
   LIBELULA_EMITE_FACTURA=false
   ```

3. Pregúntales también cómo se simula un pago en su ambiente de pruebas.
4. Si cambiaron algo de la API respecto a la guía v2.7.1 (`/rest/deuda/registrar`,
   `/rest/deuda/consultar_pagos`, el aviso GET con `?transaction_id=`), avísame: se ajusta solo
   `app/Payments/Gateways/LibelulaGateway.php`.

En Libélula no se registra ninguna URL: el sitio manda la de aviso con cada cobro, armada con la
dirección por la que entraste (por eso hay que entrar por ngrok). Aunque el aviso no llegue, el pago
se confirma igual con `bida:verificar-pagos` (paso 7).

Si no te dan ambiente de pruebas, déjalo vacío y pruébalo ya en el servidor con un pago real de 1 mes
del plan más barato.

---

## 7. Arrancar todo

Abre **cuatro terminales** (la de Laragon ya tiene `php` disponible), en `C:\laragon\www\Bida-Events`:

| Terminal | Comando | Para qué |
| --- | --- | --- |
| 1 | `C:\laragon\bin\ngrok\ngrok.exe http 80 --host-header=bida-events.test --url=https://algo-algo-1234.ngrok-free.app` | El túnel |
| 2 | `php artisan queue:work` | Aplica los avisos de pago y envía los correos |
| 3 | `php artisan schedule:work` | Cada 5 min confirma los QR pagados y vence los viejos |
| 4 | `php artisan config:clear` | Una vez, después de pegar las credenciales |

Sin la terminal 2 los pagos quedan «confirmando» para siempre: el aviso llega, pero nadie lo aplica.

**Para trabajar solo en Laragon (sin túnel):** Apache, PostgreSQL y Redis ya los arranca Laragon; lo
que falta es la cola y el programador. Deja Mailpit abierto (`C:\laragon\bin\mailpit\1.22.3\mailpit.exe`,
los correos se ven en <http://localhost:8025>), con `MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1` y
`MAIL_PORT=1025` en el `.env`, y corre en una terminal:

```bash
composer laragon
```

Eso levanta la cola y el programador juntos. Una sola vez, `php artisan storage:link` para que exista
`public/storage`.

---

## 8. Qué probar

Abre `https://algo-algo-1234.ngrok-free.app`:

1. **Registro con correo:** `/registro` → crea una cuenta → llega el correo de confirmación (Mailpit
   o Zoho) → pulsa el enlace → la cuenta queda confirmada.
2. **Google:** cierra sesión → «Entrar con Google» con uno de tus usuarios de prueba.
3. **Recuperar contraseña:** «¿Olvidaste tu contraseña?» → llega el correo → cambia la contraseña.
4. **Tarjeta:** con la cuenta confirmada → «Mi plan» (`/client/suscripcion`) → plan Inicial →
   «Tarjeta de crédito o débito» → paga con `4242 4242 4242 4242` → vuelves y en segundos dice que el
   plan está activo. Revisa:
   - En <http://127.0.0.1:4040> (panel de ngrok) el `POST /webhooks/lemonsqueezy` respondió **200**.
   - Admin › Revendedores: la cuenta «Al día», «Con tarjeta: se renueva sola».
   - «Mi plan › Administrar mi suscripción» abre el portal; cancela ahí y mira que el sitio lo
     refleje.
5. **QR (si tienes appkey):** «Mi plan» → «Pago en Bolivia» → 1 mes → aparece el QR en bolivianos →
   paga en su ambiente de pruebas → se activa. Si no, corre `php artisan bida:verificar-pagos` y
   debería decir «Cobros confirmados: 1».

El panel de ngrok (<http://127.0.0.1:4040>) muestra cada aviso que llegó y lo que respondió el
sitio, y permite reenviarlo (*Replay*).

---

## 9. Si algo falla

| Síntoma | Causa probable |
| --- | --- |
| No aparece «Tarjeta» o «Pago en Bolivia» | Falta una credencial o no corriste `php artisan config:clear` |
| El webhook responde **401** | `LEMONSQUEEZY_SIGNING_SECRET` distinto del pegado en Lemon Squeezy |
| El webhook responde **404** o ngrok dice *ERR_NGROK_3200* | La URL del webhook está mal escrita o el túnel está cerrado |
| Pagué y queda «confirmando» | No está corriendo `php artisan queue:work` |
| El cobro aparece en «Pagos para revisar» y no activa el plan | Un id de variante cruzado o un precio distinto al del sitio |
| El `curl` de variantes responde `Unauthenticated` | La API key está incompleta o es de modo real |
| `redirect_uri_mismatch` en Google | Entraste por `bida-events.test` o la URI registrada no coincide |
| `Error 403: access_denied` en Google | Ese Gmail no está en *Usuarios de prueba* |
| Error `535` al enviar correo | Contraseña de aplicación de Zoho incorrecta o servidor equivocado (`smtp` / `smtppro`) |
| Error `553` al enviar correo | `MAIL_FROM_ADDRESS` no es tu casilla de Zoho ni un alias suyo |
| Los enlaces de los correos van a `bida-events.test` | `APP_URL` no es tu dominio de ngrok (y corre `config:clear`) |

---

## 10. Al terminar

Vuelve a `APP_URL=http://bida-events.test` y corre `php artisan config:clear` para seguir trabajando
como siempre. Las credenciales de prueba pueden quedarse en el `.env`: sin el túnel abierto
simplemente no llegan avisos.
