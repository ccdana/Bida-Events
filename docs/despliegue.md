# Despliegue en producción (Docker)

Cómo está armado el servidor y el paso a paso para publicar una versión nueva. Los servicios
externos se conectan con las guías de [`conexiones/`](conexiones/01-zoho-mail.md) (Zoho, Google, Lemon Squeezy y Libélula); los
registros, alertas y respaldos están en [`operacion.md`](operacion.md).

## 1. Cómo está armado

```
Invitado ──► Cloudflare (DNS, HTTPS «Full (strict)», caché de /build)
                 │
                 ▼
           VPS · docker compose -f compose.prod.yaml   (proyecto «bida-events-prod»)
           ├─ web        Nginx: 80 → 443, certificado de origen de Cloudflare,
           │             archivos de public/ y el resto a PHP-FPM
           ├─ app        PHP 8.4-FPM: al arrancar migra y arma las cachés (docker/entrypoint.sh)
           ├─ queue      queue:work — exportaciones y avisos de pago
           ├─ scheduler  schedule:work — respaldos, salud, verificar pagos (routes/console.php)
           ├─ pgsql      PostgreSQL 17 (volumen pgsql-data)
           └─ redis      caché (volumen redis-data)

           Volumen «storage»: lo único que escribe la aplicación (logs, respaldos, exportaciones,
           fotos si no hay Cloudinary). Sobrevive a cada despliegue.
```

Fuera de git y solo en el servidor:

| Archivo | Qué es |
| --- | --- |
| `.env` | La configuración y los secretos (plantilla: `.env.example`) |
| `docker/nginx/certs/origin.crt` y `origin.key` | Certificado de origen de Cloudflare |

El código viaja **dentro de la imagen**: un cambio en el código no se ve hasta reconstruirla.
Un cambio solo en el `.env` no necesita reconstruir: basta con `dc up -d`, que recrea los
contenedores (leen el `.env` al crearse y arman la caché de configuración al arrancar).

### El atajo `dc`

Todos los comandos de esta guía y de las demás se corren en la carpeta del proyecto en el
servidor, con este atajo (agrégalo a `~/.bashrc` para que quede siempre):

```bash
alias dc='docker compose -f compose.prod.yaml'
```

## 2. Actualizar el servidor a esta versión (una sola vez)

El último despliegue conocido es del 22 de septiembre (commit «Cambios de produccion, NO TOCAR»).
Desde entonces cambió bastante: cuentas con registro y Google, pagos en línea, «Hazlo tú», planes
nuevos y la imagen de Docker. Esta actualización:

- Aplica las migraciones pendientes desde `2026_09_22_000001` hasta
  `2026_10_15_000001_retire_unica_and_aliado_plans`. Agregan tablas y columnas; las que mueven datos
  son `switch_prices_to_dollars` (precios de Ajustes de Bs a US$), `replace_xv_templates` y
  `replace_caleidoscopio_with_zapatos` (invitaciones con plantillas que se rehicieron) y
  `retire_unica_and_aliado_plans` (cuentas con esos planes pasan al Inicial).
- Renombra `docker/entrypoint.prod.sh` → `docker/entrypoint.sh` y `docker/php.prod.ini` →
  `docker/php.ini`, y quita el Docker de desarrollo (`compose.yaml`). La imagen se construye solo
  para producción; `compose.prod.yaml` y `docker/nginx.conf` siguen iguales.
- Sube dependencias con avisos de seguridad (Laravel 12.70, Guzzle, DomPDF, Laravel Excel,
  PhpSpreadsheet, CommonMark, Flysystem) y quita axios y Laravel Sail, que no se usaban.

Cuenta con unos minutos de mantenimiento entre los pasos 2.5 y 2.7.

### 2.1 Respaldo antes de tocar nada

```bash
cd /ruta/a/bida-events
alias dc='docker compose -f compose.prod.yaml'

# Respaldo completo de la aplicación (queda en el volumen storage)
dc exec app php artisan bida:respaldo

# Y una copia de la base fuera de Docker, en la carpeta del proyecto (ignorada por git: *.sql.gz)
dc exec -T pgsql sh -c 'pg_dump -U "$POSTGRES_USER" "$POSTGRES_DB"' | gzip > antes-de-actualizar-$(date +%F).sql.gz
ls -lh antes-de-actualizar-*.sql.gz      # que no pese 0
```

Anota también el commit que está corriendo, para poder volver (sección 4):

```bash
git log -1 --oneline
```

### 2.2 Traer el código

```bash
git status            # tiene que decir «nothing to commit»; si hay cambios, avisa antes de seguir
git pull
```

`docker/nginx/certs/` y `.env` no están en git: `git pull` no los toca.

### 2.3 Poner al día el `.env`

Compara las variables del servidor con las de la plantilla:

```bash
diff <(grep -oE '^[A-Z0-9_]+' .env | sort -u) <(grep -oE '^[A-Z0-9_]+' .env.example | sort -u)
```

Las líneas con `>` son variables nuevas que faltan en el servidor; las que tienen `<`, variables
que el servidor tiene y que ya no se usan. Edita con `nano .env`:

**Agregar** (copia el bloque de `.env.example` y complétalo):

| Bloque | Variables | Valor para empezar |
| --- | --- | --- |
| Correo | `MAIL_*`, `OPERATIONS_ALERT_EMAIL` | Zoho: [`conexiones/01-zoho-mail.md`](conexiones/01-zoho-mail.md). Sin correo nadie confirma su cuenta ni recupera la contraseña |
| Cuentas | `AUTH_REGISTRATION`, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | `AUTH_REGISTRATION=true` cuando el correo funcione; Google vacío oculta el botón |
| Pagos | `PAYMENTS_*`, `LEMONSQUEEZY_*`, `LIBELULA_*` | `PAYMENTS_ENABLED=false` hasta completar [`conexiones/03-lemon-squeezy.md`](conexiones/03-lemon-squeezy.md) (los botones van a WhatsApp) |
| Marca | `BIDA_LEGAL_NAME`, `BIDA_LEGAL_NIT`, `BIDA_DIY_DRAFTS_LIMIT` | Razón social y NIT para las páginas legales; vacíos muestran la marca |
| Temporadas | `BIDA_SEASON_AMOR_ENDS_AT`, `BIDA_SEASON_HALLOWEEN_ENDS_AT` | Los de la plantilla |
| Seguridad y registros | `RATE_LIMIT_REGISTER`, `RATE_LIMIT_PASSWORD_EMAIL`, `RATE_LIMIT_DOOR`, `LOG_DAILY_DAYS` | Los de la plantilla |

**Borrar** si están (ya no las lee nada): `BIDA_SEASON_ENDS_AT` (pasó a ser
`BIDA_SEASON_AMOR_ENDS_AT`), `VITE_APP_NAME`, `MEMCACHED_HOST`, `BCRYPT_ROUNDS`,
`BROADCAST_CONNECTION`, `APP_MAINTENANCE_DRIVER`, `CLOUDINARY_UPLOAD_PRESET`,
`CLOUDINARY_NOTIFICATION_URL`, `BACKUP_MYSQLDUMP`, `BACKUP_MYSQL`, `LEMONSQUEEZY_VARIANT_UNICA`,
`LEMONSQUEEZY_VARIANT_ALIADO`, `SEED_CLIENT_*` y las del Docker de desarrollo (`APP_PORT`,
`VITE_PORT`, `DB_FORWARD_PORT`, `DOCKER_DB_*`, `UID`, `GID`).

**No cambiar** `DB_DATABASE`, `DB_USERNAME` ni `DB_PASSWORD`: la base ya existe con esos datos.

Revisa además estos valores de producción:

```dotenv
APP_URL=https://bida-events.com
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
LOG_STACK=daily
LOG_LEVEL=warning
CACHE_OPTIMIZATIONS_ENABLED=true
TRUSTED_PROXIES=
```

`TRUSTED_PROXIES` va vacío: Nginx ya toma la IP real de Cloudflare (`CF-Connecting-IP`) y le pasa
a PHP la del visitante.

### 2.4 Construir la imagen nueva

Mientras se construye, la versión anterior sigue atendiendo.

```bash
dc build --pull
```

Tarda unos minutos (instala dependencias y compila el CSS y el JS). Si falla, no se tocó nada de
lo que está corriendo: corrige y repite.

### 2.5 Mantenimiento y arranque

```bash
dc exec app php artisan down --retry=60 --secret="una-palabra-larga"
dc up -d --remove-orphans
dc logs -f app                             # espera «Listo» y sal con Ctrl+C
```

Los invitados ven «volvemos en un momento»; tú entras igual abriendo una vez
`https://bida-events.com/una-palabra-larga` (deja una cookie que salta el mantenimiento).

`app` aplica las migraciones y arma las cachés antes de quedar listo, y `web` espera a que `app`
responda. El modo mantenimiento se guarda en el volumen `storage`, así que sigue activo después de
recrear los contenedores; mientras dura, la cola y las tareas programadas no corren: nada trabaja
contra una base a medio migrar.

Si en el log aparece un error de migración, **no subas el sitio**: ve a la sección 4.

### 2.6 Comprobar

```bash
dc ps                                               # todos «Up»; app «healthy»
dc exec app php artisan about                       # Environment production, Debug OFF, URL https
dc exec app php artisan migrate:status | tail -5    # todas «Ran»
dc exec app php artisan schedule:list               # respaldo, salud, verificar pagos…
dc exec app php artisan bida:salud --sin-correo
```

Con el enlace secreto del paso 2.5, prueba a mano: entrar al panel, abrir el editor de una
invitación (que cargue la vista previa), abrir una invitación pública y la portada.

### 2.7 Abrir el sitio

```bash
dc exec app php artisan up
```

### 2.8 Limpiar

Las imágenes viejas ocupan disco:

```bash
docker image prune -f
```

Después, con calma: conectar el correo, Google y los pagos con las guías de [`conexiones/`](conexiones/01-zoho-mail.md), en orden.

## 3. Publicar una versión nueva (cada vez)

```bash
cd /ruta/a/bida-events
dc exec app php artisan bida:respaldo --sin-medios     # respaldo de la base
git log -1 --oneline                                    # anota el commit actual
git pull
diff <(grep -oE '^[A-Z0-9_]+' .env | sort -u) <(grep -oE '^[A-Z0-9_]+' .env.example | sort -u)
dc build --pull
dc exec app php artisan down --retry=60                 # solo si la versión trae migraciones
dc up -d --remove-orphans
dc logs -f app                                          # hasta «Listo»
dc ps
dc exec app php artisan up
docker image prune -f
```

Si el `diff` muestra variables nuevas (`>`), agrégalas al `.env` antes de `dc up -d`.

La CI de GitHub (`.github/workflows/ci.yml`) compila, revisa el estilo y corre las pruebas en cada
push: publica solo commits en verde.

## 4. Si algo sale mal

**El sitio no arranca o algo se rompió después de actualizar:**

```bash
dc logs --tail=200 app
dc exec app tail -n 100 storage/logs/laravel-$(date +%F).log
```

**Volver a la versión anterior:**

```bash
git checkout <commit-anotado>
dc build
dc up -d --remove-orphans
```

Si la versión nueva ya migró la base, el código anterior puede no entender las tablas nuevas.
Restaura la copia del paso 2.1 (o la de `bida:respaldo`, ver [`operacion.md`](operacion.md) ›
«Restaurar un respaldo»):

```bash
dc exec app php artisan down
dc stop app queue scheduler
gunzip -c antes-de-actualizar-AAAA-MM-DD.sql.gz | dc exec -T pgsql sh -c \
  'dropdb --force -U "$POSTGRES_USER" "$POSTGRES_DB" && createdb -U "$POSTGRES_USER" "$POSTGRES_DB" && psql --set=ON_ERROR_STOP=1 -U "$POSTGRES_USER" "$POSTGRES_DB"'
dc up -d
dc exec app php artisan up
```

Para volver a la rama después: `git checkout main` (o la rama que use el servidor).

## 5. Comandos útiles

| Para | Comando |
| --- | --- |
| Ver el estado | `dc ps` |
| Logs en vivo | `dc logs -f app queue scheduler web` |
| Consola de Laravel | `dc exec app php artisan tinker` |
| Trabajos fallidos | `dc exec app php artisan queue:failed` · `queue:retry all` |
| Aplicar un cambio del `.env` | `dc up -d` |
| Reiniciar la cola | `dc restart queue` |
| Entrar a la base | `dc exec pgsql psql -U bida bida_events` |
| Espacio de Docker | `docker system df` |
