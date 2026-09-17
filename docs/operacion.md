# Operación: registros, alertas y respaldos

Qué vigila la aplicación sola, dónde mirar cuando algo falla y cómo recuperar la información.
La configuración está en `config/operations.php`; el despliegue, en `docs/despliegue.md`.

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
| Último respaldo | Tiene más de `ALERT_BACKUP_MAX_AGE_HOURS` (26 h) |
| Prueba de restauración | Nunca se probó, falló, o tiene más de 8 días |
| Disco | Queda menos de `ALERT_MIN_FREE_DISK_PERCENT` (10 %) libre |

Si algo falla, lo deja en `operations-*.log` y, con `OPERATIONS_ALERT_EMAIL` y el correo
configurado (`MAIL_*`), manda un mensaje. Además, cada trabajo que agota sus reintentos queda en
el log en el momento en que falla.

Para correrla a mano: `php artisan bida:salud` (con `--sin-correo` no manda nada).

Los trabajos fallidos se ven con `php artisan queue:failed`, se reintentan con
`php artisan queue:retry all` y se borran solos a los 30 días.

## Respaldos

### Qué se respalda

`php artisan bida:respaldo` (cada noche a las 02:30) deja en `storage/app/backups`:

| Archivo | Contenido |
| --- | --- |
| `base-AAAA-MM-DD_HHMMSS.sql.gz` | Volcado completo de la base (`pg_dump`, `mysqldump` o copia de SQLite) |
| `medios-AAAA-MM-DD_HHMMSS.zip` | Fotos y archivos guardados en el servidor (`storage/app/public`), si los hay |
| `medios-AAAA-MM-DD_HHMMSS.json` | Lista de cada foto, video y audio que vive en Cloudinary |
| `ultima-prueba.json` | Resultado de la última prueba de restauración |

Se conservan `BACKUP_KEEP_DAYS` días (14).

### Copia fuera del servidor

Un respaldo que vive en el mismo disco se pierde con el disco. Copiar la carpeta a otro lugar
cada noche, después de las 02:30, por ejemplo con `rclone` a un almacenamiento externo:

```cron
15 3 * * * rclone sync /ruta/a/bida-events/storage/app/backups remoto:bida-respaldos
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

Para probar un archivo concreto: `php artisan bida:probar-respaldo storage/app/backups/base-….sql.gz`.

El usuario de la base necesita permiso para crear y borrar bases (`CREATEDB` en PostgreSQL).

## Restaurar un respaldo

Solo ante una pérdida real de datos. Reemplaza toda la base.

```bash
php artisan down
php artisan bida:respaldo --sin-medios        # por si hay que volver atrás

gunzip -k storage/app/backups/base-AAAA-MM-DD_HHMMSS.sql.gz
```

**PostgreSQL**

```bash
dropdb bida_events && createdb bida_events
psql --set=ON_ERROR_STOP=1 --dbname=bida_events --file=storage/app/backups/base-AAAA-MM-DD_HHMMSS.sql
```

**MySQL**

```bash
mysql -e "DROP DATABASE bida_events; CREATE DATABASE bida_events"
mysql bida_events < storage/app/backups/base-AAAA-MM-DD_HHMMSS.sql
```

**Medios locales**, si el respaldo trae `.zip`:

```bash
unzip -o storage/app/backups/medios-AAAA-MM-DD_HHMMSS.zip -d storage/app/public
```

Después:

```bash
php artisan optimize:clear
php artisan bida:salud
php artisan up
```
