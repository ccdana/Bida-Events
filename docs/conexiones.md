# Conectar el correo y los pagos: Zoho Mail, Lemon Squeezy y Libélula

Guía para dejar funcionando, en el servidor de producción:

1. **Zoho Mail**: los correos que manda el sitio (confirmar el correo, crear una contraseña nueva,
   alertas de `bida:salud`).
2. **Lemon Squeezy**: el cobro con tarjeta internacional, en dólares, que se renueva solo cada mes.
3. **Libélula**: el pago en Bolivia (QR de cualquier banco, Tigo Money y tarjeta boliviana), en
   bolivianos, por 1, 3 o 12 meses.

Se hace en este orden: sin correo, nadie puede confirmar su cuenta ni recuperar la contraseña, y
para pagar en línea el sitio exige un correo confirmado.

> Todo lo que va en `.env` son secretos: nunca los subas a GitHub ni los mandes por chat. Después
> de cada cambio en `.env`, en el servidor:
>
> ```bash
> php artisan config:cache
> sudo supervisorctl restart bida-worker
> ```

---

## 0. Antes de empezar (una sola vez)

Revisa que el servidor tenga lo que necesitan los pagos (detalle en `docs/despliegue.md`, paso 5.7):

| Qué | Para qué | Cómo comprobarlo |
| --- | --- | --- |
| `APP_URL=https://bida-events.com` | Los enlaces de los correos y las direcciones que se mandan a las pasarelas | `php artisan about` muestra la URL |
| Cron con `schedule:run` cada minuto | `bida:verificar-pagos` cada 5 minutos (confirma pagos de Libélula y vence los QR no pagados) | `php artisan schedule:list` lo lista |
| Worker de la cola (`queue:work`) | Aplica los avisos de las pasarelas (activa los planes) | `sudo supervisorctl status bida-worker` dice `RUNNING` |
| HTTPS | Las pasarelas solo avisan a direcciones `https://` | El sitio abre con candado |

---

## 1. Zoho Mail (correo saliente)

El sitio manda los correos por SMTP con tu cuenta de Zoho Mail. Fuente: [configuración SMTP de
Zoho](https://www.zoho.com/mail/help/zoho-smtp.html).

### 1.1 Elegir la cuenta que envía

Usa una casilla de tu dominio, por ejemplo `hola@bida-events.com`. Zoho solo deja enviar **desde
la misma casilla con la que se inicia sesión** (o un alias suyo): el remitente (`MAIL_FROM_ADDRESS`)
tiene que ser esa casilla o un alias de ella. Si no, Zoho rechaza el envío.

### 1.2 Contraseña de aplicación

Si la cuenta tiene verificación en dos pasos (recomendado), la contraseña normal no sirve para SMTP:

1. Entra a [accounts.zoho.com](https://accounts.zoho.com) con esa casilla.
2. *Seguridad › Contraseñas de aplicación* (*Security › App Passwords*) → *Generar nueva*.
3. Nombre: `Bida Events servidor`. Copia la contraseña que muestra (no se vuelve a ver).

Sin verificación en dos pasos se usa la contraseña de la cuenta, pero es mejor activarla y usar
una contraseña de aplicación: si se filtra, se revoca sin cambiar la de la cuenta.

### 1.3 Servidor según tu plan

| Tu cuenta | `MAIL_HOST` |
| --- | --- |
| Plan pagado de Zoho Mail con tu dominio | `smtppro.zoho.com` |
| Plan gratuito con tu dominio | `smtp.zoho.com` |
| Cuenta alojada en Europa / India | `smtppro.zoho.eu` · `smtppro.zoho.in` (o `smtp.` en plan gratuito) |

Para saber la región: entra a Zoho Mail y mira la dirección del navegador (`mail.zoho.com`,
`mail.zoho.eu`, `mail.zoho.in`…). El plan gratuito tiene un límite de envíos por hora; para los
volúmenes del sitio alcanza, pero si crece conviene un plan pagado.

### 1.4 Variables

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtppro.zoho.com
MAIL_PORT=587
MAIL_USERNAME=hola@bida-events.com
MAIL_PASSWORD=la-contrasena-de-aplicacion
MAIL_FROM_ADDRESS="hola@bida-events.com"
MAIL_FROM_NAME="Bida Events"
OPERATIONS_ALERT_EMAIL=tu-correo-personal@gmail.com
```

- Puerto **587** con `MAIL_SCHEME=null`: la conexión se cifra sola (STARTTLS). Si el servidor
  bloquea el 587, usa `MAIL_PORT=465` con `MAIL_SCHEME=smtps`.
- `MAIL_USERNAME` y `MAIL_FROM_ADDRESS` son la **misma casilla** (o la segunda un alias de la
  primera).

### 1.5 DNS para que no lleguen a spam

En Cloudflare (*DNS › Records*) del dominio:

1. **MX**: los que te indica Zoho al agregar el dominio (`mx.zoho.com`, `mx2.zoho.com`,
   `mx3.zoho.com`). Si antes usabas *Email Routing* de Cloudflare para recibir, desactívalo: un
   dominio recibe en un solo lugar.
2. **SPF** (TXT, nombre `@`): `v=spf1 include:zoho.com ~all`. Debe haber **un solo** registro SPF;
   si ya existía uno (por ejemplo con Resend), júntalos en uno: `v=spf1 include:zoho.com include:… ~all`.
3. **DKIM**: en la consola de administración de Zoho Mail › *Dominios* › tu dominio ›
   *Configuración de correo › DKIM* → *Agregar* (selector `zmail`) → copia el TXT a Cloudflare →
   vuelve a Zoho y *Verificar*.
4. **DMARC** (TXT, nombre `_dmarc`): `v=DMARC1; p=quarantine; rua=mailto:hola@bida-events.com`.

En Cloudflare, los registros de correo van **sin proxy** (nube gris).

### 1.6 Probar

```bash
php artisan config:cache
php artisan tinker --execute="Mail::raw('Prueba de correo de Bida Events', fn (\$m) => \$m->to('tu-correo-personal@gmail.com')->subject('Prueba SMTP'));"
```

Debe llegar en segundos a tu bandeja de entrada, no a spam. Después prueba el flujo real: en
`/contrasena/olvide` pide el enlace con el correo de una cuenta → llega «Crea una contraseña nueva».

**Si falla:**

| Error | Causa |
| --- | --- |
| `535 Authentication Failed` | Contraseña: con dos pasos activado hace falta la de aplicación; o el `MAIL_HOST` no es el de tu plan/región |
| `553 Sender is not allowed to relay emails` | `MAIL_FROM_ADDRESS` no es la casilla de `MAIL_USERNAME` ni un alias suyo |
| `Connection timed out` | El proveedor del servidor bloquea el puerto: prueba 465 con `MAIL_SCHEME=smtps` |
| Llega a spam | Falta SPF o DKIM (paso 1.5), o el SPF está duplicado |

---

## 2. Lemon Squeezy (tarjeta internacional)

Lemon Squeezy cobra como vendedor oficial (se encarga de los impuestos de cada país) y renueva sola
la suscripción cada mes. Confirma antes con su soporte que te pagan en tu cuenta de Bolivia.

> Está pasando sus comercios a *Stripe Managed Payments*, que todavía no confirma Bolivia. Hoy
> funciona normal; si un día pide migrar, se cambia la pasarela sin tocar el resto del sitio.

Todo se arma primero en **modo de prueba** (interruptor *Test mode* abajo a la izquierda del panel).
La API key, el webhook y los ids de prueba son **distintos** de los reales.

### 2.1 Tienda

1. Cuenta en [lemonsqueezy.com](https://www.lemonsqueezy.com) → crea la tienda.
2. *Settings › Stores*: anota el **número de la tienda** → `LEMONSQUEEZY_STORE_ID`.
3. *Settings › General*: moneda **USD**.

### 2.2 Producto con un plan por variante

1. *Products › + New product*: nombre «Hazlo tú», tipo **Subscription**.
2. Crea **cinco variantes**, una por plan, cada una **mensual** y con el **mismo precio que el
   plan tiene hoy en el sitio** (Admin › Ajustes, el precio con descuento si tiene):

   | Variante | Precio (hoy) | Variable |
   | --- | --- | --- |
   | Única | US$ 5 | `LEMONSQUEEZY_VARIANT_UNICA` |
   | Inicial | US$ 9 | `LEMONSQUEEZY_VARIANT_INICIAL` |
   | Aliado | US$ 14 | `LEMONSQUEEZY_VARIANT_ALIADO` |
   | Emprendedor | US$ 29 | `LEMONSQUEEZY_VARIANT_EMPRENDEDOR` |
   | Agencia | US$ 59 | `LEMONSQUEEZY_VARIANT_AGENCIA` |

3. El **id de cada variante** se ve en la API o al editar la variante (el número en la dirección).
   Para listarlos de una vez, con la API key del paso 2.3:

   ```bash
   curl -s https://api.lemonsqueezy.com/v1/variants -H "Authorization: Bearer TU_API_KEY" \
     -H "Accept: application/vnd.api+json" | grep -o '"id":"[0-9]*"\|"name":"[^"]*"'
   ```

> La variante es lo que dice qué plan se compró: si un id queda cruzado, el cobro queda «para
> revisar» y no se activa. Si cambias un precio en Ajustes, cambia también la variante (los que ya
> están suscritos siguen con su precio anterior).

### 2.3 API key

*Settings › API › +*: nombre `Bida Events` → copia la clave → `LEMONSQUEEZY_API_KEY`.

### 2.4 Webhook

*Settings › Webhooks › +*:

- **Callback URL**: `https://bida-events.com/webhooks/lemonsqueezy`
- **Signing secret**: inventa uno largo (por ejemplo, el resultado de `openssl rand -hex 32`) →
  `LEMONSQUEEZY_SIGNING_SECRET`.
- **Eventos** (marca estos): `order_created`, `order_refunded`, `subscription_created`,
  `subscription_updated`, `subscription_cancelled`, `subscription_resumed`, `subscription_expired`,
  `subscription_paused`, `subscription_unpaused`, `subscription_payment_success`,
  `subscription_payment_failed`, `subscription_payment_refunded`.

### 2.5 Portal del cliente

*Settings › Customer portal*: activa que el cliente pueda **cambiar de plan** (entre las variantes
de «Hazlo tú»), **actualizar la tarjeta** y **cancelar**. El sitio lo abre desde «Mi plan ›
Administrar mi suscripción».

### 2.6 Variables

```dotenv
PAYMENTS_ENABLED=true
PAYMENTS_CARD_PROVIDER=lemonsqueezy
LEMONSQUEEZY_API_KEY=eyJ0eXAiOiJKV1Qi...
LEMONSQUEEZY_STORE_ID=12345
LEMONSQUEEZY_SIGNING_SECRET=el-secreto-del-paso-2.4
LEMONSQUEEZY_TEST_MODE=true
LEMONSQUEEZY_VARIANT_UNICA=555555
LEMONSQUEEZY_VARIANT_INICIAL=111111
LEMONSQUEEZY_VARIANT_ALIADO=222222
LEMONSQUEEZY_VARIANT_EMPRENDEDOR=333333
LEMONSQUEEZY_VARIANT_AGENCIA=444444
```

### 2.7 Probar (modo de prueba)

1. Entra con una cuenta de cliente **con el correo confirmado** → «Mi plan» (o «Hazlo tú» →
   «Elegir») → plan Inicial → «Tarjeta de crédito o débito» → «Continuar al pago».
2. Paga con la tarjeta de prueba `4242 4242 4242 4242`, cualquier fecha futura y cualquier CVC.
3. Vuelves a «Estamos confirmando tu pago» y en segundos dice «Listo: tu plan Inicial está activo».
4. Comprueba:
   - Admin › Revendedores: la cuenta aparece «Al día» y «Con tarjeta: se renueva sola».
   - «Mi plan» → «Administrar mi suscripción» abre el portal de Lemon Squeezy.
   - En Lemon Squeezy › *Settings › Webhooks* › tu webhook, cada aviso tiene respuesta **200**.
5. Cancela la suscripción desde el portal: en Revendedores pasa a «Con tarjeta: canceló la
   renovación» y sigue al día hasta su fecha.

### 2.8 Pasar a cobros reales

1. Activa la tienda (*Activate store*: datos del negocio y verificación de identidad; tarda unos
   días).
2. Apaga *Test mode* y repite los pasos **2.2 a 2.5 en modo real** (otro producto con otras
   variantes, otra API key, otro webhook con su secreto).
3. Cambia esas variables y `LEMONSQUEEZY_TEST_MODE=false`.
4. Haz una compra real del plan más barato con tu tarjeta y devuélvela desde Lemon Squeezy: el
   pago desaparece del historial y la fecha vuelve a la anterior.

---

## 3. Libélula (pago en Bolivia)

Libélula cobra una **comisión por cada pago y no tiene mensualidad**. El comprador elige 1, 3 o 12
meses, escanea el QR con la app de su banco (o paga con Tigo Money o tarjeta boliviana en la página
de Libélula) y el plan se activa cuando Libélula confirma el pago. No se renueva solo.

### 3.1 Afiliarse

Escribe a `soporte@libelula.bo` (o al formulario de [libelula.bo](https://libelula.bo)) y pide, **por
escrito**:

- [ ] Que acepten **persona natural con NIT** y la lista de documentos.
- [ ] La **comisión** exacta por QR, Tigo Money y tarjeta, y si hay algún costo fijo o de retiro.
- [ ] En qué cuenta y **cada cuánto liquidan**.
- [ ] Que habiliten el canal **QR Simple** (con él, el QR se muestra en nuestro sitio; sin él, el
      comprador paga en la página de Libélula).
- [ ] El **appkey** de pruebas y el de producción, y la dirección de cada ambiente.
- [ ] La documentación **vigente** de su API. La integración sigue su guía v2.7.1:
      `POST /rest/deuda/registrar`, el aviso `GET` a la `callback_url` con `?transaction_id=…`, y
      `POST /rest/deuda/consultar_pagos`. Si cambió algo, se ajusta solo
      `app/Payments/Gateways/LibelulaGateway.php`.
- [ ] Si quieres que ellos emitan la **factura** de cada pago (`LIBELULA_EMITE_FACTURA=true`).

### 3.2 Variables

```dotenv
PAYMENTS_ENABLED=true
PAYMENTS_QR_PROVIDER=libelula
LIBELULA_APPKEY=588e5e10-xxxx-xxxx-xxxx-xxxxxxxxxxxx
# Pruebas: la dirección que te den (en su guía: http://www.todotix.com:10888)
LIBELULA_BASE_URL=https://api.todotix.com
LIBELULA_EMITE_FACTURA=false
# Descuento en % por pagar varios meses de una vez (0 = sin descuento)
PAYMENTS_PREPAY_3_DISCOUNT=0
PAYMENTS_PREPAY_12_DISCOUNT=0
```

No hay que registrar ninguna URL en Libélula: el sitio manda con cada cobro la dirección de aviso
(`https://bida-events.com/webhooks/libelula?orden=…`) y la de regreso del comprador.

### 3.3 Cómo se protege

El aviso de Libélula **no viene firmado**, así que el sitio no le cree por sí solo: al llegar,
pregunta a Libélula con el appkey si ese pago existe y por cuánto. Un aviso falso no activa nada y
un monto distinto queda en «Pagos para revisar». Además, `bida:verificar-pagos` (cada 5 minutos)
confirma los pagos cuyo aviso se perdió. Por eso **el cron del paso 0 es obligatorio**.

### 3.4 Probar

1. Con el appkey de pruebas: cuenta con correo confirmado → «Mi plan» → plan Inicial → «Pago en
   Bolivia» → **1 mes** → «Continuar al pago».
2. Aparece «Escanea el QR para pagar» con el monto en bolivianos (precio del plan × 6,96,
   redondeado). Paga como indique Libélula para su ambiente de pruebas.
3. En segundos la página pasa a «Listo: tu plan Inicial está activo». En Revendedores, el pago
   aparece como «QR».
4. Si no se activa sola, corre a mano y mira el resultado:

   ```bash
   php artisan bida:verificar-pagos
   ```

   «Cobros confirmados: 1» significa que Libélula lo confirmó (el aviso no había llegado). Si dice
   0, Libélula todavía no lo registra como pagado, o no responde: revisa
   `storage/logs/operations-*.log` («Libélula no respondió la consulta de pagos»).
5. Prueba el vencimiento: genera un QR y no lo pagues; al día siguiente pasada la medianoche,
   `bida:verificar-pagos` lo marca vencido y la página dice «Este cobro venció sin pagarse».

### 3.5 Pasar a cobros reales

Cambia `LIBELULA_APPKEY` y `LIBELULA_BASE_URL` por los de producción, `php artisan config:cache`, y
haz un pago real de 1 mes del plan más barato.

---

## 4. Encender todo

Con el correo y al menos una pasarela probados:

```dotenv
PAYMENTS_ENABLED=true
# Abre el registro de cuentas nuevas (con Google, si configuraste GOOGLE_CLIENT_ID; ver despliegue.md 6.4)
AUTH_REGISTRATION=true
```

```bash
php artisan config:cache
sudo supervisorctl restart bida-worker
php artisan bida:salud --sin-correo
```

Desde ese momento, en «Hazlo tú» el botón «Elegir» de cada plan lleva a pagar en línea (antes iba
a WhatsApp). Solo se ofrecen las formas de pago que tienen sus credenciales: si falta Libélula,
solo aparece la tarjeta, y al revés.

### Prueba final de punta a punta

1. Ventana privada → `bida-events.com/registro` → crea una cuenta con un correo tuyo.
2. Llega «Confirma tu correo» (Zoho) → ábrelo → la cuenta queda confirmada.
3. «Hazlo tú» → «Elegir» un plan → paga con tarjeta o QR.
4. El plan queda activo, aparece «Nueva invitación» y en Admin › Revendedores la cuenta figura
   «Al día» con su pago.
5. `php artisan bida:salud --sin-correo`: «Avisos de pago sin aplicar» y «Pagos para revisar» en OK.

---

## 5. Si algo no funciona

| Síntoma | Qué revisar |
| --- | --- |
| No aparece «Continuar al pago» | Falta `PAYMENTS_ENABLED=true` o las credenciales de la pasarela; corrió `config:cache` |
| «Para pagar en línea, agrega tu correo y confírmalo» | La cuenta no tiene correo confirmado: «Mi cuenta» → agregar correo → abrir el enlace |
| Pagó con tarjeta y sigue «Estamos confirmando» | Lemon Squeezy › Webhooks: ¿el aviso tiene respuesta 200? Un 401 es el `LEMONSQUEEZY_SIGNING_SECRET` vacío o distinto del webhook; un 404, la URL del webhook mal escrita. ¿Está corriendo el worker? |
| Pagó con QR y no se activa | `php artisan bida:verificar-pagos`; luego `operations-*.log` |
| «Pagos para revisar» en `bida:salud` | El cobro llegó pero no coincide (plan o monto). Mira el `error` en la tabla `webhook_events` y, si corresponde, registra el pago a mano en Revendedores |
| «Avisos de pago sin aplicar» | El worker estaba caído: `sudo supervisorctl restart bida-worker` y `php artisan bida:reprocesar-avisos` |
| No llegan los correos | Paso 1.6 y su tabla de errores |

Más detalle de la operación diaria en `docs/operacion.md` › «Pagos en línea».
