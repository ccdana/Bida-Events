# Despliegue

Procedimiento para publicar una versión nueva de Bida Events en el servidor. Está pensado para un
servidor Linux con PHP 8.4, Composer, Node 22, PostgreSQL (o MySQL) y Nginx; los comandos son los
mismos en cualquier otro.

> Antes de empezar: la integración continua (`.github/workflows/ci.yml`) debe estar en verde para
> el commit que se publica.

## 1. Primera instalación

```bash
git clone https://github.com/ccdana/Bida-Events.git bida-events
cd bida-events
cp .env.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
npm ci
npm run build
php artisan storage:link
php artisan migrate --force
php artisan db:seed --class=EventTypeSeeder --force
php artisan db:seed --class=ShowcaseInvitationsSeeder --force
php artisan bida:imagenes-compartir
php artisan optimize
```

No correr `DatabaseSeeder` completo en producción: crea un administrador con contraseña conocida.
El administrador real se crea a mano (`php artisan tinker`) con una contraseña propia.

### Variables que hay que revisar en `.env`

| Variable | Valor en producción |
| --- | --- |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` |
| `APP_URL` | La dirección pública con `https://`. La usan el mapa del sitio, las tarjetas para compartir y los enlaces de campaña |
| `DB_*` | Credenciales de la base |
| `CLOUDINARY_URL` | Cuenta de Cloudinary (sin ella, las fotos se guardan en el disco del servidor) |
| `QUEUE_CONNECTION` | `database` (o `redis`) y un worker corriendo (sección 3) |
| `CACHE_STORE` / `CACHE_OPTIMIZATIONS_ENABLED` | `redis` si hay más de una instancia / `true` |
| `TRUSTED_PROXIES` | IP del balanceador o de Cloudflare, si lo hay |
| `CSP_ENFORCE` | `true` cuando el reporte de la política de contenido esté limpio |
| `LOG_STACK` | `daily`, para que el log rote |
| `OPERATIONS_ALERT_EMAIL` y `MAIL_*` | Correo que recibe las alertas de `bida:salud` |
| `BACKUP_PG_DUMP` / `BACKUP_PSQL` | Ruta de los ejecutables si no están en el `PATH` |

## 2. Publicar una versión nueva

```bash
cd /ruta/a/bida-events

# 1. Respaldo antes de tocar nada
php artisan bida:respaldo

# 2. Modo mantenimiento (los invitados ven la página de mantenimiento unos segundos)
php artisan down --retry=60

# 3. Código y dependencias
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# 4. Base de datos
php artisan migrate --force

# 5. Cachés: se limpian las viejas y se arman las nuevas
php artisan optimize:clear
php artisan optimize

# 6. Los workers cargan el código nuevo
php artisan queue:restart

# 7. De vuelta
php artisan up
```

Después de publicar:

- Abrir la portada, una página por evento y una invitación de muestra (`/muestra/xv-isabella`).
- `php artisan bida:salud` debe terminar sin fallas.
- Revisar `storage/logs/laravel-*.log` por errores nuevos.

### Si algo sale mal

1. `php artisan down`.
2. Volver al commit anterior: `git checkout <commit-anterior>` y repetir los pasos 3, 5 y 6.
3. Si la migración dañó datos, restaurar el respaldo del paso 1 (ver `docs/operacion.md`,
   «Restaurar un respaldo»).
4. `php artisan up`.

## 3. Procesos que deben quedar corriendo

### Programador de tareas (cron)

Una sola línea en el crontab del usuario que ejecuta la aplicación:

```cron
* * * * * cd /ruta/a/bida-events && php artisan schedule:run >> /dev/null 2>&1
```

Corre los respaldos, la prueba de restauración semanal, la limpieza de fotos viejas y la revisión
de salud cada hora. La lista está en `routes/console.php` y se ve con `php artisan schedule:list`.

### Worker de la cola (Supervisor)

Genera los Excel y PDF que piden los clientes. `/etc/supervisor/conf.d/bida-worker.conf`:

```ini
[program:bida-worker]
command=php /ruta/a/bida-events/artisan queue:work --sleep=3 --tries=3 --max-time=3600
user=www-data
autostart=true
autorestart=true
stopwaitsecs=180
redirect_stderr=true
stdout_logfile=/ruta/a/bida-events/storage/logs/worker.log
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start bida-worker
```

Sin worker, las exportaciones se quedan en «preparando…» y `bida:salud` lo avisa.
