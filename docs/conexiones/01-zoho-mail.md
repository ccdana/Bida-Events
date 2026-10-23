# Conectar Zoho Mail (correos del sitio)

Con esto el sitio manda, desde `hola@bida-events.com`, los correos de **confirmar el correo**, **crear
una contraseña nueva** y las alertas de `bida:salud`. Sin correo nadie confirma su cuenta, y sin cuenta
confirmada no se puede pagar en línea: por eso es la primera conexión.

**Tiempo:** unos 30 minutos (más la espera de propagación de DNS).
**Necesitas:** tu cuenta de Zoho, acceso a Cloudflare (DNS de `bida-events.com`) y al `.env` del servidor.

---

## Paso 1. Tener el dominio y la casilla en Zoho

1. Entra a [mailadmin.zoho.com](https://mailadmin.zoho.com) (consola de administración).
2. *Domains* → verifica que `bida-events.com` aparezca como **verificado**. Si no está, *Add Domain* y
   sigue el asistente (te pide un registro TXT o CNAME en Cloudflare para comprobar que el dominio es tuyo).
3. *Users* → comprueba que existe la casilla **`hola@bida-events.com`**. Si no, créala.

> El sitio envía **desde esa casilla**. Zoho solo permite enviar con la casilla con la que se inicia
> sesión o con un alias suyo; cualquier otro remitente lo rechaza.

## Paso 2. Crear la contraseña de aplicación

La contraseña normal no sirve para SMTP cuando la cuenta tiene verificación en dos pasos. Se crea una
exclusiva para el servidor:

1. Entra a [accounts.zoho.com](https://accounts.zoho.com) **con `hola@bida-events.com`**.
2. *Security* → *App Passwords* → *Generate New Password*.
3. Nombre: `Bida Events servidor` → *Generate*.
4. **Copia la contraseña ahora**: no se vuelve a mostrar.

## Paso 3. Saber qué servidor SMTP te toca

Mira la dirección del navegador al entrar a tu correo de Zoho:

| Entras por… | Plan de pago | Plan gratuito |
| --- | --- | --- |
| `mail.zoho.com` | `smtppro.zoho.com` | `smtp.zoho.com` |
| `mail.zoho.eu` | `smtppro.zoho.eu` | `smtp.zoho.eu` |
| `mail.zoho.in` | `smtppro.zoho.in` | `smtp.zoho.in` |

Apunta el que te corresponda: es tu `MAIL_HOST`.

## Paso 4. Poner las variables en el servidor

En el `.env` del servidor:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtppro.zoho.com
MAIL_PORT=587
MAIL_USERNAME=hola@bida-events.com
MAIL_PASSWORD=la-contraseña-de-aplicación-del-paso-2
MAIL_FROM_ADDRESS="hola@bida-events.com"
MAIL_FROM_NAME="Bida Events"
OPERATIONS_ALERT_EMAIL=tu-correo-personal@gmail.com
```

- `MAIL_USERNAME` y `MAIL_FROM_ADDRESS` son la **misma casilla** (o el segundo, un alias del primero).
- `OPERATIONS_ALERT_EMAIL` es donde llegan las alertas del sitio: pon un correo que sí revises.
- Si el servidor bloquea el puerto 587, usa `MAIL_PORT=465` y `MAIL_SCHEME=smtps`.

Aplica el cambio (los contenedores leen el `.env` al crearse):

```bash
alias dc='docker compose -f compose.prod.yaml'
dc up -d
```

## Paso 5. DNS para que no caiga en spam

En Cloudflare → *DNS › Records* de `bida-events.com`. **Todos estos registros van sin proxy (nube gris).**

| Tipo | Nombre | Valor |
| --- | --- | --- |
| MX | `@` | `mx.zoho.com` (prioridad 10), `mx2.zoho.com` (20), `mx3.zoho.com` (50) |
| TXT (SPF) | `@` | `v=spf1 include:zoho.com ~all` |
| TXT (DKIM) | `zmail._domainkey` | el valor que te da Zoho (ver abajo) |
| TXT (DMARC) | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:hola@bida-events.com` |

(Si usas `.eu` o `.in`, los MX y el SPF llevan esa terminación: `mx.zoho.eu`, `include:zoho.eu`.)

**DKIM:** en la consola de Zoho → *Domains* → `bida-events.com` → *Email Configuration* → *DKIM* →
*Add selector* (nombre `zmail`) → copia el valor TXT a Cloudflare → vuelve a Zoho y pulsa *Verify*.

Reglas que evitan problemas:

- Debe haber **un solo** registro SPF. Si ya existía otro (por ejemplo de otro servicio), júntalos:
  `v=spf1 include:zoho.com include:otro.com ~all`.
- Si usabas *Email Routing* de Cloudflare para recibir, desactívalo: un dominio recibe en un solo lugar.

## Paso 6. Probar

```bash
dc exec app php artisan tinker --execute="Mail::raw('Prueba de Bida Events', fn (\$m) => \$m->to('tu-correo-personal@gmail.com')->subject('Prueba SMTP'));"
```

Debe llegar en segundos y **en la bandeja de entrada, no en spam**. Luego prueba el flujo real: en
`https://bida-events.com/contrasena/olvide` pide el enlace con el correo de una cuenta existente →
llega «Crea una contraseña nueva».

---

## Si falla

| Error | Causa y solución |
| --- | --- |
| `535 Authentication Failed` | Falta usar la contraseña **de aplicación** (paso 2), o `MAIL_HOST` no es el de tu plan/región (paso 3) |
| `553 Sender is not allowed to relay emails` | `MAIL_FROM_ADDRESS` no es la casilla de `MAIL_USERNAME` ni un alias suyo |
| `Connection timed out` | El proveedor del servidor bloquea el puerto: usa 465 con `MAIL_SCHEME=smtps` |
| Llega a spam | Falta SPF o DKIM, o hay dos registros SPF (paso 5). Los cambios de DNS pueden tardar hasta unas horas |
| No pasa nada y no hay error | No se corrió `dc up -d` después de editar el `.env` |

Siguiente: [02-google-oauth.md](02-google-oauth.md).
