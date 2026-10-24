# Conectar Lemon Squeezy (cobro con tarjeta)

Con esto el sitio cobra con **tarjeta internacional, en dólares**: la suscripción de cada plan se
renueva sola, y la invitación de «Hazlo tú» se paga una vez al publicarla. Lemon Squeezy actúa como
vendedor oficial (se encarga de los impuestos de cada país).

**Tiempo:** 1 a 2 horas.
**Necesitas:** tu cuenta de Lemon Squeezy con la tienda aprobada, y acceso al `.env` del servidor.
**Requisitos del servidor** (compruébalos antes; con `dc` = `docker compose -f compose.prod.yaml`):

| Qué | Por qué | Cómo comprobarlo |
| --- | --- | --- |
| `APP_URL=https://bida-events.com` | Las direcciones de regreso y de aviso que se mandan a Lemon Squeezy | `dc exec app php artisan about` |
| HTTPS activo | Lemon Squeezy solo avisa a direcciones `https://` | El sitio abre con candado |
| Servicio `queue` en marcha | Aplica los avisos de pago (activa el plan) | `dc ps` lo muestra `Up` |
| `/webhooks/` sin desafíos de Cloudflare | Los avisos vienen de un servidor, no de un navegador: un desafío o *Bot Fight Mode* los bloquea | Cloudflare › *Security › WAF*: regla *Skip* para la ruta `/webhooks/` |
| Zoho funcionando | Para pagar hace falta una cuenta con el correo confirmado | [01-zoho-mail.md](01-zoho-mail.md) |

> Se arma **primero en modo de prueba** y luego se repite en modo real. Lemon Squeezy tiene el
> interruptor **Test mode** (abajo a la izquierda del panel): lo que creas con él encendido no existe
> con él apagado, y las claves, el webhook y los ids son distintos en cada modo.

---

## Parte A. Modo de prueba

Enciende **Test mode** y haz todo lo de esta parte con el interruptor encendido.

### Paso 1. Anotar el id de la tienda

*Settings › Stores*: anota el **número de la tienda** → será `LEMONSQUEEZY_STORE_ID`.
En *Settings › General* comprueba que la moneda sea **USD**.

### Paso 2. Crear el producto de las suscripciones

1. *Products › + New product* y llena el formulario así (lo que no aparece aquí se deja como está):

   | Campo | Valor |
   | --- | --- |
   | Name | `Únete` |
   | Description | Opcional |
   | Pricing | **Subscription** |
   | Pricing model | **Standard pricing** |
   | Price per unit | `10.00` (el plan Inicial; pasa a ser su variante) |
   | Repeat payment every | `1` **Month** |
   | Usage is metered / setup fee / free trial | Los tres apagados |
   | Tax category | La que viene por defecto |
   | Media, Files, Links | Vacíos |
   | Generate license keys | Apagado |
   | Display product on storefront | Apagado (los cobros los crea el sitio) |

   Pulsa **Publish product** (no *Save as draft*).
2. Con **Add variant** crea el resto: **una variante mensual por plan**, con el **mismo precio que el
   plan tiene hoy en el sitio** (Admin › Ajustes; si tiene descuento, el precio con descuento). Si
   pide nombre para la primera, ponle `Inicial`:

   | Variante | Precio mensual | Variable del `.env` |
   | --- | --- | --- |
   | Inicial | US$ 10 | `LEMONSQUEEZY_VARIANT_INICIAL` |
   | Emprendedor | US$ 20 | `LEMONSQUEEZY_VARIANT_EMPRENDEDOR` |
   | Agencia | US$ 30 | `LEMONSQUEEZY_VARIANT_AGENCIA` |

3. **Opcional — cobrar cada 3 meses y cada año con tarjeta.** Dentro del mismo producto (o en un
   producto aparte por ciclo, por ejemplo `Trimestral` y `Anual`: al sitio solo le importa el id de cada
   variante, no en qué producto está), agrega por
   cada plan una variante con intervalo de **3 meses** y otra de **1 año**. El precio debe ser el que
   cobra el sitio: precio mensual × meses, menos el descuento de Admin › Ajustes (con 10 % y 20 %):

   | Plan | Cada 3 meses | Cada año |
   | --- | --- | --- |
   | Inicial | US$ 27 → `…_INICIAL_TRIMESTRAL` | US$ 96 → `…_INICIAL_ANUAL` |
   | Emprendedor | US$ 54 → `…_EMPRENDEDOR_TRIMESTRAL` | US$ 192 → `…_EMPRENDEDOR_ANUAL` |
   | Agencia | US$ 81 → `…_AGENCIA_TRIMESTRAL` | US$ 288 → `…_AGENCIA_ANUAL` |

   (Todas empiezan con `LEMONSQUEEZY_VARIANT`.) Sin la variante de un ciclo, ese ciclo solo se ofrece
   con QR.

> **La variante es lo que le dice al sitio qué plan y qué ciclo se compró.** Si un id queda cruzado, el
> cobro queda en «Pagos para revisar» y no activa nada. Si después cambias un precio en Ajustes,
> cambia también el de la variante (quienes ya están suscritos conservan su precio anterior).

### Paso 3. Crear el producto de «Hazlo tú»

La invitación que arma cada persona se paga una sola vez.

1. *Products › + New product*. Name: `Invitación`. Pricing: **Single payment**, **Standard pricing**.
   Storefront y licencias apagados; publícalo con **Publish product**.
2. **Una sola variante**, con cualquier precio (por ejemplo `1.00`). El precio real lo manda el sitio
   en cada cobro, según el nivel que se publica.
3. Su id → `LEMONSQUEEZY_VARIANT_INVITACION`.

Sin esta variable, «Hazlo tú» solo ofrece el QR (si está Libélula) o WhatsApp.

### Paso 4. Obtener los ids de las variantes

El `.env` usa el id de cada **variante**, no el del producto. Es el número que aparece en la
dirección al editar la variante. Para verlos todos
juntos, después de crear la API key (paso 5), desde cualquier terminal:

```bash
curl -s https://api.lemonsqueezy.com/v1/variants -H "Authorization: Bearer TU_API_KEY" -H "Accept: application/vnd.api+json" | grep -o '"id":"[0-9]*"\|"name":"[^"]*"'
```

Cada `id` aparece seguido del `name` de la variante. Apunta cuál es cuál.

### Paso 5. Crear la API key

*Settings › API › +*. Nombre: `Bida Events` → crear → **copia la clave** (no se vuelve a mostrar) →
`LEMONSQUEEZY_API_KEY`.

### Paso 6. Crear el webhook

*Settings › Webhooks › +* (o *Add webhook*):

- **Callback URL:** `https://bida-events.com/webhooks/lemonsqueezy`
- **Signing secret:** inventa uno largo y aleatorio. Para generarlo: `openssl rand -hex 32`.
  → será `LEMONSQUEEZY_SIGNING_SECRET`.
- **Eventos:** marca exactamente estos 12:

  ```text
  order_created
  order_refunded
  subscription_created
  subscription_updated
  subscription_cancelled
  subscription_resumed
  subscription_expired
  subscription_paused
  subscription_unpaused
  subscription_payment_success
  subscription_payment_failed
  subscription_payment_refunded
  ```

### Paso 7. Activar el portal del cliente

Es el panel donde el cliente cambia de plan, actualiza la tarjeta o cancela. El sitio lo abre desde
«Mi plan › Administrar mi suscripción» con un enlace firmado que pide a Lemon Squeezy en el momento
(vale 24 horas e inicia la sesión del cliente solo), así que no hace falta que el cliente tenga cuenta
en Lemon Squeezy.

Está en *Settings › Stores* → tu tienda → pestaña **Portal**. Con Test mode encendido:

| Campo | Valor |
| --- | --- |
| Enable customer portal | **Encendido** |
| Heading | `Administra tu suscripción y tu facturación` |
| Description | `Consulta tus pagos, descarga tus facturas y administra tu suscripción y tu tarjeta.` |
| Link (texto) / Link URL | `← Volver a Bida Events` / `https://bida-events.com` |
| Cancel subscriptions | **Encendido** |
| Pause subscriptions | Apagado |
| Update subscription plans | **Encendido** → *Select products*: todos los productos de suscripción (el mensual, el trimestral y el anual; **no** `Invitación`) |
| Apply changes immediately with proration | **Encendido** (el sitio espera que el cambio rija desde ya) |
| Update subscription quantity | Apagado |
| Discounts, License keys, Files, Expired subscriptions | Apagados |
| Appearance | Sin tocar |
Guarda con el botón de abajo.

> Al pasar a modo real, los productos de suscripción son otros: vuelve a esta pantalla y selecciónalos
> de nuevo en **Update subscription plans**.

### Paso 8. Poner las variables en el servidor

En el `.env` del servidor (los ids son de ejemplo):

```dotenv
PAYMENTS_ENABLED=true
PAYMENTS_CARD_PROVIDER=lemonsqueezy
LEMONSQUEEZY_API_KEY=eyJ0eXAiOiJKV1Qi...
LEMONSQUEEZY_STORE_ID=12345
LEMONSQUEEZY_SIGNING_SECRET=el-secreto-del-paso-6
LEMONSQUEEZY_TEST_MODE=true

LEMONSQUEEZY_VARIANT_INICIAL=111111
LEMONSQUEEZY_VARIANT_EMPRENDEDOR=222222
LEMONSQUEEZY_VARIANT_AGENCIA=333333

# Solo si creaste las variantes de 3 meses y anual (repite para EMPRENDEDOR y AGENCIA)
LEMONSQUEEZY_VARIANT_INICIAL_TRIMESTRAL=111113
LEMONSQUEEZY_VARIANT_INICIAL_ANUAL=111112

LEMONSQUEEZY_VARIANT_INVITACION=444444
```

Aplica el cambio:

```bash
alias dc='docker compose -f compose.prod.yaml'
dc up -d
```

### Paso 9. Probar una compra

1. Entra al sitio con una cuenta **con el correo confirmado** → «Mi plan» (o «Únete» → «Elegir») →
   plan Inicial → «Tarjeta de crédito o débito» → «Continuar al pago».
2. Paga con la tarjeta de prueba `4242 4242 4242 4242`, cualquier fecha futura y cualquier CVC.
3. Vuelves a «Estamos confirmando tu pago» y en segundos dice «Listo: tu plan Inicial está activo».
4. Comprueba:
   - Admin › Revendedores: la cuenta aparece «Al día» y «Con tarjeta: se renueva sola».
   - «Mi plan» → «Administrar mi suscripción» abre el portal de Lemon Squeezy.
   - En Lemon Squeezy › *Settings › Webhooks* › tu webhook › *Recent deliveries*: cada aviso tiene
     respuesta **200**.
5. Cancela la suscripción desde el portal: en Revendedores pasa a «Con tarjeta: canceló la
   renovación» y sigue al día hasta su fecha.
6. Prueba también «Hazlo tú»: publica una invitación y paga con la misma tarjeta de prueba.

---

## Parte B. Pasar a cobros reales

Tu tienda ya está aprobada, así que puedes cobrar de verdad.

1. **Apaga Test mode.**
2. Repite en modo real los **pasos 2, 3, 5, 6 y 7** de la Parte A. Todo sale nuevo: otro producto con
   otras variantes (con otros ids), otra API key, otro webhook con su propio secreto. El número de
   tienda (paso 1) es el mismo.
3. En el `.env` cambia la API key, el signing secret y **todos** los ids de variantes por los reales, y
   pon `LEMONSQUEEZY_TEST_MODE=false`. Luego `dc up -d`.
4. Haz una compra real del plan más barato con tu propia tarjeta y comprueba que activa el plan.
   Después devuélvela desde Lemon Squeezy (*Orders › Refund*): el pago desaparece del historial y la
   fecha del plan vuelve a la anterior.

---

## Si falla

| Síntoma | Causa y solución |
| --- | --- |
| No aparece «Continuar al pago» | Falta `PAYMENTS_ENABLED=true` o alguna credencial, o no se corrió `dc up -d` tras editar el `.env` |
| «Para pagar en línea, agrega tu correo y confírmalo» | La cuenta no tiene el correo confirmado: «Mi cuenta» → agregar correo → abrir el enlace que llega |
| Pagó y sigue en «Estamos confirmando» | Revisa las entregas del webhook en Lemon Squeezy: **401** = `LEMONSQUEEZY_SIGNING_SECRET` vacío o distinto del webhook; **404** = URL del webhook mal escrita; **403** = desafío de Cloudflare (regla *Skip* para `/webhooks/`). Si responde 200, comprueba que el servicio `queue` esté `Up` |
| «Pagos para revisar» en `bida:salud` | El cobro llegó pero no coincide (plan o monto): suele ser un id de variante cruzado. Mira el `error` en la tabla `webhook_events` y, si corresponde, registra el pago a mano en Admin › Revendedores |
| «Avisos de pago sin aplicar» | La cola estaba caída: `dc up -d queue` y luego `dc exec app php artisan bida:reprocesar-avisos` |
| En modo real cobra pero no activa | Quedaron claves o ids de prueba mezclados con los reales: revisa el paso 3 de la Parte B |

Para ver el estado general del sitio: `dc exec app php artisan bida:salud --sin-correo`.
Operación diaria de los pagos: [`../operacion.md`](../operacion.md) › «Pagos en línea».

Siguiente (pago en Bolivia): [04-libelula.md](04-libelula.md).
