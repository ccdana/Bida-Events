# Operación: registros, alertas y respaldos

Qué vigila la aplicación sola, dónde mirar cuando algo falla y cómo recuperar la información.
La configuración está en `config/operations.php`; el despliegue, en `docs/despliegue.md`.

En el servidor todo corre en Docker: los comandos van con el atajo de `docs/despliegue.md`
(`dc exec app php artisan …`) y los archivos de `storage/` viven en el volumen `storage`.

## Registros

| Archivo | Qué guarda | Se conserva |
| --- | --- | --- |
| `storage/logs/laravel-*.log` | Errores de la aplicación (con `LOG_STACK=daily`) | Según `LOG_DAILY_DAYS` |
| `storage/logs/performance-*.log` | Peticiones más lentas que `SLOW_REQUEST_MS` (1500 ms): ruta, método, estado y tiempo | 14 días |
| `storage/logs/operations-*.log` | Respaldos, pruebas de restauración, trabajos fallidos y alertas | 60 días |

El registro de peticiones lentas guarda el **nombre** de la ruta (`invitation.guest`), no la
dirección: los enlaces personales llevan el código del invitado y no deben quedar en un archivo.

Para ver las más lentas de un día:

```bash
grep "Petición lenta" storage/logs/performance-2026-09-16.log | sort -t'"' -k4 -n | tail
```

## Alertas: `bida:salud`

Corre cada hora y revisa:

| Revisión | Falla cuando |
| --- | --- |
| Trabajos fallidos | Hay al menos `ALERT_FAILED_JOBS` en las últimas 24 horas |
| Cola | Un trabajo espera más de `ALERT_PENDING_JOB_MINUTES` (no hay worker) |
| Exportaciones atascadas | Un Excel o PDF lleva más de 15 minutos «preparando…» |
| Avisos de pago sin aplicar | Un aviso de una pasarela lleva más de 15 minutos sin aplicarse (ver «Pagos en línea») |
| Pagos para revisar | En los últimos 7 días llegó un cobro que no se pudo aplicar: orden desconocida o monto distinto |
| Último respaldo | Tiene más de `ALERT_BACKUP_MAX_AGE_HOURS` (26 h) |
| Prueba de restauración | Nunca se probó, falló, o tiene más de 8 días |
| Disco | Queda menos de `ALERT_MIN_FREE_DISK_PERCENT` (10 %) libre |

Si algo falla, lo deja en `operations-*.log` y, con `OPERATIONS_ALERT_EMAIL` y el correo
configurado (`MAIL_*`), manda un mensaje. Además, cada trabajo que agota sus reintentos queda en
el log en el momento en que falla.

Para correrla a mano: `php artisan bida:salud` (con `--sin-correo` no manda nada).

Los trabajos fallidos se ven con `php artisan queue:failed`, se reintentan con
`php artisan queue:retry all` y se borran solos a los 30 días.

## Pagos en línea

Cada pasarela avisa lo que pasó con un cobro a `/webhooks/{pasarela}`. El aviso se valida por su
firma, se guarda una sola vez en `webhook_events` y se aplica en la cola (`ProcessWebhookEvent`).
Una orden cobrada (`payment_orders`) registra el pago en `subscription_payments` y extiende la
suscripción. Pase lo que pase, ningún pago suma meses dos veces.

- **Aviso sin aplicar** (la cola estaba caída, la base no respondía): queda con `processed_at`
  vacío y su error. Se vuelve a aplicar con `php artisan bida:reprocesar-avisos`.
- **Pago para revisar**: el cobro llegó, pero no corresponde a una orden nuestra, el monto o el
  plan no coinciden, o es la renovación de una suscripción que no conocemos. No se activa nada: el
  aviso queda procesado con la nota en `error` y una línea «Pago para revisar» en
  `operations.log`. Hay que mirarlo en la pasarela y, si corresponde, registrar el pago a mano
  desde Revendedores, o corregir la orden y correr
  `php artisan bida:reprocesar-avisos --id=<id del aviso>`.
- Los pagos con tarjeta o QR **no se anulan** desde Revendedores: se devuelven en la pasarela, y
  su aviso de devolución completa anula el pago y devuelve la fecha a la anterior. Una devolución
  parcial no cambia la suscripción (queda anotada en `operations.log`).
- **«Hazlo tú»** (órdenes con `purpose` = `invitation`): el cobro publica la invitación con su nivel
  y queda en `invitation_payments` (`App\Services\InvitationActivator`). Subir de nivel cobra la
  diferencia. Una devolución completa la vuelve al nivel que tenía (si era la primera compra, vuelve
  a borrador y se oculta). Si el cobro es de una invitación que ya se borró, o se devuelve un pago
  que no es el último de esa invitación, queda «Pago para revisar»: el nivel se corrige a mano desde
  el editor del administrador («Paquete»).

### Tarjeta (Lemon Squeezy)

- La suscripción se renueva sola cada mes. Cada renovación cobrada suma un mes; la fecha de
  renovación del sitio se alinea con la próxima fecha de cobro de Lemon Squeezy.
- **Cobro fallido**: la cuenta queda «Cobro fallido» (`past_due`). Puede seguir creando durante
  los días de gracia (`PAYMENTS_GRACE_DAYS`, 3) mientras Lemon Squeezy reintenta el cobro (unas dos
  semanas). Si un reintento cobra, vuelve a estar al día sola.
- **Cancelación**: sigue al día hasta su fecha y ahí termina (Revendedores muestra «canceló la
  renovación»). Cambiar de plan, de tarjeta o cancelar lo hace el cliente desde «Mi plan ›
  Administrar mi suscripción» (el portal de Lemon Squeezy).
- Si en la tabla aparece «Pago para revisar» por un plan distinto, revisa que cada variante de
  Lemon Squeezy sea la de su plan (`LEMONSQUEEZY_VARIANT_*`). Si un cobro sale menor que el precio
  del sitio, `operations.log` lo avisa: iguala el precio de la variante con el de Ajustes.

### Pago en Bolivia (Libélula)

- Cada compra es un cobro en bolivianos por los meses elegidos (1, 3 o 12); no se renueva sola.
  El QR vale hasta la medianoche del día siguiente; después la orden queda «vencida». Si igual se
  paga, se activa.
- `bida:verificar-pagos` corre cada 5 minutos: pregunta a Libélula por los cobros pendientes de los
  últimos 3 días (por si su aviso no llegó) y vence los que pasaron su fecha. Se puede correr a
  mano.
- El aviso de Libélula no se cree por sí solo: siempre se confirma con su consulta de pagos. Si
  Libélula no responde, queda una línea «Libélula no respondió la consulta de pagos» en
  `operations.log` y se reintenta en la próxima vuelta.
- Un pago por un monto distinto del cobro (por ejemplo, si se editó la deuda en Libélula) queda en
  «Pagos para revisar».

## Respaldos

### Qué se respalda

`php artisan bida:respaldo` (cada noche a las 02:30) deja en `storage/app/backups`:

| Archivo | Contenido |
| --- | --- |
| `base-AAAA-MM-DD_HHMMSS.sql.gz` | Volcado completo de la base (`pg_dump`) |
| `medios-AAAA-MM-DD_HHMMSS.zip` | Fotos y archivos guardados en el servidor (`storage/app/public`), si los hay |
| `medios-AAAA-MM-DD_HHMMSS.json` | Lista de cada foto, video y audio que vive en Cloudinary |
| `ultima-prueba.json` | Resultado de la última prueba de restauración |

Se conservan `BACKUP_KEEP_DAYS` días (14).

### Copia fuera del servidor

Un respaldo que vive en el mismo disco se pierde con el disco. Copiar la carpeta a otro lugar
cada noche, después de las 02:30. Desde el volumen se saca con `docker compose cp` y de ahí, por
ejemplo con `rclone`, a un almacenamiento externo (crontab del usuario que maneja Docker):

```cron
15 3 * * * cd /ruta/a/bida-events && docker compose -f compose.prod.yaml cp app:/var/www/html/storage/app/backups/. respaldos/ && rclone sync respaldos remoto:bida-respaldos
```

### Medios en Cloudinary

Las fotos de Cloudinary no se descargan en cada respaldo (pesan mucho y no cambian). El
manifiesto dice cuáles son. Para protegerlas:

- Activar **Backup** en la cuenta de Cloudinary (Settings → Upload → Backup), que guarda una copia
  de cada archivo subido.
- Si hiciera falta reconstruir la cuenta, el manifiesto más reciente tiene todas las direcciones.

## Prueba de restauración

`php artisan bida:probar-respaldo` (domingos 04:00) carga el último volcado en una base temporal
(`<base>_restauracion_prueba`), cuenta las filas de cada tabla contra la base real y la borra.
No toca la base de la aplicación. Que haya tablas con más filas en la base real es normal: hubo
actividad después del respaldo.

Para probar un archivo concreto: `dc exec app php artisan bida:probar-respaldo storage/app/backups/base-….sql.gz`.

El usuario de la base necesita permiso para crear y borrar bases (`CREATEDB`). El que crea el
contenedor `pgsql` con `DB_USERNAME` ya lo tiene.

## Restaurar un respaldo

Solo ante una pérdida real de datos: reemplaza toda la base. En el servidor, con el atajo
`dc` de `docs/despliegue.md`. El volcado se lee dentro del contenedor `app`, que tiene el
cliente de PostgreSQL 17 y las credenciales en su entorno.

```bash
dc exec app php artisan down
dc exec app php artisan bida:respaldo --sin-medios     # por si hay que volver atrás
dc stop queue scheduler

ARCHIVO=storage/app/backups/base-AAAA-MM-DD_HHMMSS.sql.gz
dc exec app sh -c 'export PGPASSWORD="$DB_PASSWORD"
  dropdb --force -h "$DB_HOST" -U "$DB_USERNAME" "$DB_DATABASE"
  createdb -h "$DB_HOST" -U "$DB_USERNAME" "$DB_DATABASE"
  gunzip -c '"$ARCHIVO"' | psql --set=ON_ERROR_STOP=1 -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE"'
```

**Medios locales**, si el respaldo trae `.zip` (sin Cloudinary):

```bash
dc exec app unzip -o storage/app/backups/medios-AAAA-MM-DD_HHMMSS.zip -d storage/app/public
```

Después:

```bash
dc up -d
dc exec app php artisan bida:salud --sin-correo
dc exec app php artisan up
```
