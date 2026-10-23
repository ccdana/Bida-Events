# Conectar Libélula (pago en Bolivia con QR)

Libélula cobra una **comisión por cada pago y no tiene mensualidad**. El comprador elige 1, 3 o 12
meses, escanea el QR con la app de su banco (o paga con Tigo Money o tarjeta boliviana en la página de
Libélula) y el plan se activa cuando Libélula confirma el pago, en bolivianos. No se renueva solo.

**Necesitas:** estar afiliado a Libélula y acceso al `.env` del servidor. Antes, el servidor debe tener
funcionando [Zoho](01-zoho-mail.md) y los servicios `queue` y **`scheduler`** (`dc ps` los muestra
`Up`). El `scheduler` es obligatorio: corre `bida:verificar-pagos` cada 5 minutos para confirmar los
pagos cuyo aviso se perdió y vencer los QR no pagados.

---

## Paso 1. Afiliarse

Escribe a `soporte@libelula.bo` (o al formulario de [libelula.bo](https://libelula.bo)) y pide, **por
escrito**:

- [ ] Que acepten **persona natural con NIT** y la lista de documentos.
- [ ] La **comisión** exacta por QR, Tigo Money y tarjeta, y si hay algún costo fijo o de retiro.
- [ ] En qué cuenta y **cada cuánto liquidan**.
- [ ] Que habiliten el canal **QR Simple** (con él, el QR se muestra en nuestro sitio; sin él, el
      comprador paga en la página de Libélula).
- [ ] El **appkey** de pruebas y el de producción, y la dirección de cada ambiente.
- [ ] La documentación **vigente** de su API. La integración sigue su guía v2.7.1; si cambió algo, se
      ajusta solo `app/Payments/Gateways/LibelulaGateway.php`.
- [ ] Si quieres que ellos emitan la **factura** de cada pago (`LIBELULA_EMITE_FACTURA=true`).

## Paso 2. Poner las variables en el servidor

Primero con los datos de **pruebas**:

```dotenv
PAYMENTS_ENABLED=true
PAYMENTS_QR_PROVIDER=libelula
LIBELULA_APPKEY=el-appkey-de-pruebas
LIBELULA_BASE_URL=la-direccion-de-pruebas-que-te-den
LIBELULA_EMITE_FACTURA=false
```

```bash
alias dc='docker compose -f compose.prod.yaml'
dc up -d
```

No hay que registrar ninguna URL en Libélula: el sitio manda con cada cobro la dirección de aviso
(`https://bida-events.com/webhooks/libelula`) y la de regreso del comprador. El descuento por pagar 3 o
12 meses se cambia en Admin › Ajustes.

El aviso de Libélula **no viene firmado**, así que el sitio no le cree por sí solo: al llegar, pregunta
a Libélula con el appkey si ese pago existe y por cuánto. Un aviso falso no activa nada y un monto
distinto queda en «Pagos para revisar».

## Paso 3. Probar

1. Cuenta con el correo confirmado → «Mi plan» → plan Inicial → «Pago en Bolivia» → **1 mes** →
   «Continuar al pago».
2. Aparece «Escanea el QR para pagar» con el monto en bolivianos (precio del plan × 6,96, redondeado).
   Paga como indique Libélula para su ambiente de pruebas.
3. En segundos pasa a «Listo: tu plan Inicial está activo». En Admin › Revendedores el pago aparece
   como «QR».
4. Si no se activa sola, corre a mano:

   ```bash
   dc exec app php artisan bida:verificar-pagos
   ```

   «Cobros confirmados: 1» significa que Libélula lo confirmó (el aviso no había llegado). Si dice 0,
   Libélula todavía no lo registra como pagado o no responde: revisa
   `dc exec app tail -n 50 storage/logs/operations-$(date +%F).log`.
5. Prueba el vencimiento: genera un QR y no lo pagues; pasada la medianoche, `bida:verificar-pagos` lo
   marca vencido y la página dice «Este cobro venció sin pagarse».

## Paso 4. Pasar a cobros reales

Cambia `LIBELULA_APPKEY` y `LIBELULA_BASE_URL` por los de producción, `dc up -d`, y haz un pago real de
1 mes del plan más barato.

---

## Si falla

| Síntoma | Causa y solución |
| --- | --- |
| No aparece «Pago en Bolivia» | Falta `PAYMENTS_ENABLED=true`, el `LIBELULA_APPKEY`, o `dc up -d` tras editar el `.env` |
| Pagó con QR y no se activa | `dc exec app php artisan bida:verificar-pagos`, y luego el log `operations-*.log` |
| «Pagos para revisar» | El monto o el plan no coinciden: míralo en Admin › Revendedores y regístralo a mano si corresponde |
