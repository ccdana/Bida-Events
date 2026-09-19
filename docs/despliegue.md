# Despliegue en producción

Guía completa para publicar Bida Events en internet desde cero: comprar el dominio
**bida-events.com**, contratar el servidor, instalarlo, asegurarlo y dejar la aplicación
funcionando con HTTPS, correo, respaldos y alertas. Al final están los pasos para publicar
versiones nuevas.

> Los precios son aproximados (septiembre de 2026) y en dólares. Confírmalos al contratar.

---

## 0. Lo que vas a armar

```
Invitado (celular) ──► Cloudflare (DNS, HTTPS, caché de /build, protección)
                            │
                            ▼
                  Servidor VPS Ubuntu 24.04
                  ├─ Nginx  (web, HTTPS con certificado de origen)
                  ├─ PHP 8.4-FPM + OPcache  (Laravel)
                  ├─ PostgreSQL 16  (datos)
                  ├─ Redis  (caché)
                  ├─ Supervisor → queue:work  (Excel y PDF)
                  └─ cron → schedule:run  (respaldos, limpieza, alertas)

Fotos, música y video ──► Cloudinary
Respaldos (copia fuera del servidor) ──► Cloudflare R2
Correo: recibir ──► Cloudflare Email Routing → tu Gmail
        enviar (alertas) ──► Resend (SMTP)
```

### Costo mensual estimado

| Servicio | Plan recomendado | Costo |
| --- | --- | --- |
| Dominio `bida-events.com` | Cloudflare Registrar | ~10 USD **al año** |
| Servidor | Vultr, 2 vCPU / 4 GB, Santiago de Chile | ~20–24 USD/mes |
| Cloudflare (DNS, HTTPS, protección, correo entrante) | Free | 0 |
| Cloudinary (medios) | Free (25 créditos/mes) | 0 al empezar |
| Cloudflare R2 (respaldos) | 10 GB gratis | 0 |
| Resend (correo saliente) | Free (3.000/mes) | 0 |
| **Total** | | **~21–25 USD/mes + dominio** |

**¿Por qué Vultr en Santiago?** Es el centro de datos más cercano a Bolivia de los proveedores
simples de VPS: menos latencia para tus invitados (~40–60 ms desde La Paz o Santa Cruz, frente a
~150 ms desde EE. UU.). Alternativas: Vultr São Paulo (parecido), DigitalOcean o Hetzner (más
baratos, pero en EE. UU. o Europa). Con 4 GB hay margen para PostgreSQL, Redis, el worker y la
compilación de los assets; 2 GB alcanza al inicio si quieres ahorrar (agrega swap, paso 3.4).

---

## 1. Dominio y Cloudflare

### 1.1 Crear la cuenta de Cloudflare

1. Entra a [dash.cloudflare.com](https://dash.cloudflare.com) y crea una cuenta con un correo que
   revises siempre (será el dueño del dominio). **Activa la verificación en dos pasos** de
   inmediato (*My Profile → Authentication*).

### 1.2 Comprar `bida-events.com`

1. En el panel: *Domain Registration → Register Domains*, busca `bida-events.com`.
2. Si está disponible, cómpralo por 1 año (o más) con **renovación automática** activada.
   Cloudflare cobra el precio de costo, sin recargos al renovar, e incluye privacidad WHOIS.
3. Si no está disponible, variantes: `bidaevents.com`, `bida-events.bo` (se compra en
   [nic.bo](https://nic.bo)), `bidaevents.app`. Si eliges otro dominio, cámbialo en todos los
   pasos de esta guía.

> Si prefieres otro registrador (Namecheap, Porkbun), compra ahí y luego en Cloudflare usa
> *Add a site* y cambia los *nameservers* en el registrador por los dos que te indique Cloudflare.

### 1.3 Ajustes de Cloudflare (se hacen una vez)

En el sitio `bida-events.com`:

| Sección | Ajuste | Valor |
| --- | --- | --- |
| SSL/TLS → Overview | Modo | **Full (strict)** |
| SSL/TLS → Edge Certificates | Always Use HTTPS | On |
| | Minimum TLS Version | 1.2 |
| | Automatic HTTPS Rewrites | On |
| | HSTS | Activar **después** de comprobar que todo anda por HTTPS (max-age 6 meses, incluir subdominios) |
| Speed → Optimization | Brotli | On |
| | Rocket Loader | **Off** (rompe Alpine y las animaciones) |
| | Auto Minify / Mirage | Off |
| Caching → Configuration | Browser Cache TTL | Respect Existing Headers |
| Security → Settings | Security Level | Medium |
| | Bot Fight Mode | On |
| Network | WebSockets | On (no se usa hoy, no molesta) |

**Regla de caché** para los archivos compilados (*Caching → Cache Rules → Create rule*):

- Nombre: `assets de Vite`
- Si: *URI Path* empieza con `/build/`
- Entonces: *Eligible for cache*, *Edge TTL* = 1 mes, *Browser TTL* = 1 año.

No caches las páginas HTML: las invitaciones cambian cuando el cliente las edita.

---

## 2. El servidor

### 2.1 Crear el VPS

1. Crea una cuenta en [vultr.com](https://www.vultr.com) (con verificación en dos pasos).
2. *Deploy → Cloud Compute (Shared CPU)*:
   - Ubicación: **Santiago** (o São Paulo).
   - Sistema: **Ubuntu 24.04 LTS x64**.
   - Plan: 2 vCPU / 4 GB (o 1 vCPU / 2 GB para empezar).
   - Activa **Auto Backups** (+20 %): una foto semanal del servidor entero, aparte de los
     respaldos propios de la aplicación.
   - *SSH Keys*: agrega tu llave pública (siguiente punto).
   - Hostname: `bida-prod`.
3. Anota la **IP pública** del servidor.

**Tu llave SSH** (en tu PC con Windows, en PowerShell):

```powershell
ssh-keygen -t ed25519 -C "brandon@bida-events"
Get-Content $env:USERPROFILE\.ssh\id_ed25519.pub
```

Copia esa línea (`ssh-ed25519 AAAA...`) en Vultr. Nunca compartas el archivo sin `.pub`.

### 2.2 Apuntar el dominio al servidor

En Cloudflare → *DNS → Records*:

| Tipo | Nombre | Contenido | Proxy |
| --- | --- | --- | --- |
| A | `@` | IP del servidor | Nube naranja (proxied) |
| A | `www` | IP del servidor | Nube naranja (proxied) |

`www` se redirige a `bida-events.com` desde Nginx (paso 4.3).

---

## 3. Preparar el servidor

Conéctate como root la primera vez:

```bash
ssh root@IP_DEL_SERVIDOR
```

### 3.1 Actualizar y crear el usuario de trabajo

```bash
apt update && apt -y full-upgrade
timedatectl set-timezone America/La_Paz

adduser bida                      # te pide una contraseña: guárdala en tu gestor de contraseñas
usermod -aG sudo bida
rsync --archive --chown=bida:bida ~/.ssh /home/bida
```

Prueba en **otra** ventana que entras como `bida` antes de seguir:

```bash
ssh bida@IP_DEL_SERVIDOR
```

### 3.2 Cerrar el acceso por contraseña y como root

```bash
sudo nano /etc/ssh/sshd_config.d/10-bida.conf
```

```
PermitRootLogin no
PasswordAuthentication no
KbdInteractiveAuthentication no
```

```bash
sudo systemctl restart ssh
```

### 3.3 Firewall, fail2ban y actualizaciones automáticas

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'   # se crea al instalar Nginx; si falla ahora, repítelo en el paso 4
sudo ufw enable

sudo apt -y install fail2ban unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades   # responde «Yes»
```

### 3.4 Swap (sobre todo con 2 GB de RAM)

Evita que la compilación con Node o una exportación grande tumben el servidor:

```bash
sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile
sudo mkswap /swapfile && sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
echo 'vm.swappiness=10' | sudo tee /etc/sysctl.d/99-swap.conf && sudo sysctl --system
```

---

## 4. Instalar el software

### 4.1 PHP 8.4 con las extensiones que usa la aplicación

```bash
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt -y install php8.4-fpm php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml \
  php8.4-curl php8.4-zip php8.4-gd php8.4-intl php8.4-bcmath php8.4-redis php8.4-opcache unzip git
```

`gd` y `zip` los exige `composer.json` (PDF con dompdf, QR y Excel).

Ajustes de PHP para subir videos (hasta 100 MB) y rendimiento:

```bash
sudo nano /etc/php/8.4/fpm/conf.d/99-bida.ini
```

```ini
upload_max_filesize = 110M
post_max_size = 120M
memory_limit = 256M
max_execution_time = 120
expose_php = Off

opcache.enable = 1
opcache.memory_consumption = 192
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0   ; el código no cambia sin desplegar: se recarga en el paso 7
opcache.jit = tracing
opcache.jit_buffer_size = 64M
```

```bash
sudo systemctl restart php8.4-fpm
```

Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 4.2 Node 22 (solo para compilar los assets)

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
sudo apt -y install nodejs
```

### 4.3 Nginx

```bash
sudo apt -y install nginx
sudo ufw allow 'Nginx Full'
```

**Certificado de origen de Cloudflare** (válido 15 años, sin renovaciones): en Cloudflare →
*SSL/TLS → Origin Server → Create Certificate*, deja `bida-events.com` y `*.bida-events.com`,
formato PEM. Guarda las dos piezas en el servidor:

```bash
sudo mkdir -p /etc/ssl/bida
sudo nano /etc/ssl/bida/origin.pem   # pega el «Origin Certificate»
sudo nano /etc/ssl/bida/origin.key   # pega la «Private key» (se ve una sola vez)
sudo chmod 600 /etc/ssl/bida/origin.key
```

Sitio de Nginx:

```bash
sudo nano /etc/nginx/sites-available/bida-events
```

```nginx
# IP real del visitante detrás de Cloudflare (los límites de intentos van por IP)
# Lista vigente: https://www.cloudflare.com/ips/
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
set_real_ip_from 2400:cb00::/32;
set_real_ip_from 2606:4700::/32;
set_real_ip_from 2803:f800::/32;
set_real_ip_from 2405:b500::/32;
set_real_ip_from 2405:8100::/32;
set_real_ip_from 2a06:98c0::/29;
set_real_ip_from 2c0f:f248::/32;
real_ip_header CF-Connecting-IP;

server {
    listen 80;
    listen [::]:80;
    server_name bida-events.com www.bida-events.com;
    return 301 https://bida-events.com$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on;
    server_name www.bida-events.com;
    ssl_certificate     /etc/ssl/bida/origin.pem;
    ssl_certificate_key /etc/ssl/bida/origin.key;
    return 301 https://bida-events.com$request_uri;
}

server {
    listen 443 ssl;
    listen [::]:443 ssl;
    http2 on;
    server_name bida-events.com;

    ssl_certificate     /etc/ssl/bida/origin.pem;
    ssl_certificate_key /etc/ssl/bida/origin.key;

    root /var/www/bida-events/public;
    index index.php;
    charset utf-8;

    # Videos de hasta 100 MB desde el editor
    client_max_body_size 120M;

    gzip on;
    gzip_types text/css application/javascript application/json image/svg+xml;

    # Archivos compilados por Vite: llevan hash en el nombre, se guardan un año
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files $uri =404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 120;
    }

    # Nada de archivos ocultos (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/bida-events /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

> Con el plan gratis, Cloudflare no deja pasar peticiones de más de **100 MB**. Los videos del
> editor tienen ese mismo límite, así que un video de justo 100 MB puede fallar: pide a los
> clientes videos de menos de 90 MB (o comprímelos antes de subirlos).

### 4.4 PostgreSQL 16

```bash
sudo apt -y install postgresql
sudo -u postgres psql
```

```sql
CREATE USER bida WITH PASSWORD 'UNA_CONTRASEÑA_LARGA_Y_ALEATORIA';
CREATE DATABASE bida_events OWNER bida ENCODING 'UTF8' TEMPLATE template0;
\q
```

Genera la contraseña con `openssl rand -base64 32` y guárdala en tu gestor de contraseñas.
PostgreSQL escucha solo en `127.0.0.1`: no abras el puerto 5432 en el firewall.

### 4.5 Redis (caché)

```bash
sudo apt -y install redis-server
sudo systemctl enable --now redis-server
```

Solo escucha en `127.0.0.1` por defecto. No hace falta contraseña mientras no abras el puerto.

### 4.6 Supervisor (para el worker de la cola)

```bash
sudo apt -y install supervisor
```

---

## 5. Instalar la aplicación

### 5.1 Carpeta y permisos

```bash
sudo mkdir -p /var/www/bida-events
sudo chown bida:www-data /var/www/bida-events
```

### 5.2 Traer el código desde GitHub (llave de despliegue)

En el servidor, como `bida`:

```bash
ssh-keygen -t ed25519 -C "deploy@bida-prod" -f ~/.ssh/github_deploy -N ""
cat ~/.ssh/github_deploy.pub
```

En GitHub → repositorio `ccdana/Bida-Events` → *Settings → Deploy keys → Add deploy key*: pega
la llave, **solo lectura**. Luego:

```bash
cat >> ~/.ssh/config <<'EOF'
Host github.com
  IdentityFile ~/.ssh/github_deploy
  IdentitiesOnly yes
EOF

git clone git@github.com:ccdana/Bida-Events.git /var/www/bida-events
cd /var/www/bida-events
git checkout main
```

### 5.3 Dependencias y assets

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 5.4 Archivo `.env`

```bash
cp .env.example .env
php artisan key:generate
nano .env
```

`.env.example` ya viene preparado para producción. Completa o revisa:

| Variable | Valor |
| --- | --- |
| `APP_URL` | `https://bida-events.com` |
| `APP_ENV` / `APP_DEBUG` | `production` / `false` (nunca `true` en producción: muestra secretos al fallar) |
| `LOG_STACK` / `LOG_LEVEL` | `daily` / `warning` |
| `DB_USERNAME` / `DB_PASSWORD` | `bida` / la contraseña del paso 4.4 |
| `SESSION_SECURE_COOKIE` / `SESSION_ENCRYPT` | `true` / `true` |
| `CACHE_STORE` | `redis` |
| `QUEUE_CONNECTION` | `database` |
| `CLOUDINARY_URL` | `cloudinary://API_KEY:API_SECRET@CLOUD_NAME` (paso 6.1) |
| `MAIL_*` | Paso 6.2 |
| `OPERATIONS_ALERT_EMAIL` | Tu correo personal, para las alertas |
| `BIDA_WHATSAPP` | Número del negocio con código de país, sin `+` (ej. `59171234567`) |
| `BIDA_EMAIL` | `hola@bida-events.com` |
| `BIDA_CITY`, `BIDA_INSTAGRAM`, `BIDA_TIKTOK`, `BIDA_FACEBOOK` | Datos reales |
| `BIDA_LAUNCH_PROMO` | `true` mientras dure la promoción de inauguración |
| `BIDA_SEASON_ENDS_AT` | Fecha y hora de cierre de la temporada vigente |
| `TRUSTED_PROXIES` | **Vacío**: Nginx ya entrega la IP real (paso 4.3) y la conexión llega por HTTPS |
| `CSP_ENFORCE` | `false` la primera semana; `true` cuando confirmes que nada se bloquea (paso 9) |
| `CACHE_OPTIMIZATIONS_ENABLED` / `HTTP_CACHE_ENABLED` | `true` / `false` |

Protege el archivo:

```bash
chmod 640 .env
```

### 5.5 Base de datos, datos iniciales y permisos

```bash
php artisan migrate --force
php artisan db:seed --class=EventTypeSeeder --force
php artisan db:seed --class=ShowcaseInvitationsSeeder --force   # invitaciones y tarjetas de muestra
php artisan storage:link
php artisan bida:imagenes-compartir                              # imágenes para WhatsApp/Facebook

sudo chown -R bida:www-data storage bootstrap/cache
sudo chmod -R ug+rwX storage bootstrap/cache

php artisan optimize
```

**No corras `DatabaseSeeder` ni `ClientUserSeeder` en producción**: crean usuarios de prueba con
contraseñas conocidas.

### 5.6 Tu usuario administrador

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Brandon',
    'username' => 'brandon',
    'email' => 'tu-correo@gmail.com',
    'password' => Illuminate\Support\Facades\Hash::make(readline('Contraseña: ')),
    'is_admin' => true,
]);
```

Usa una contraseña larga y única (de tu gestor de contraseñas). Sal con `exit`.

### 5.7 Procesos permanentes

**Programador de tareas (cron)** — respaldos, limpieza de fotos viejas y revisión de salud:

```bash
crontab -e
```

```cron
* * * * * cd /var/www/bida-events && php artisan schedule:run >> /dev/null 2>&1
```

Lo que corre y cuándo está en `routes/console.php` (`php artisan schedule:list`).

**Worker de la cola** — genera los Excel y PDF que piden los clientes.
`/etc/supervisor/conf.d/bida-worker.conf`:

```ini
[program:bida-worker]
command=php /var/www/bida-events/artisan queue:work --sleep=3 --tries=3 --max-time=3600
user=bida
autostart=true
autorestart=true
stopwaitsecs=180
redirect_stderr=true
stdout_logfile=/var/www/bida-events/storage/logs/worker.log
```

```bash
sudo supervisorctl reread && sudo supervisorctl update && sudo supervisorctl start bida-worker
```

Sin worker, las exportaciones se quedan en «preparando…» y `bida:salud` lo avisa.

**PHP-FPM con el mismo usuario** (para que los archivos que crea la web y los que crean cron y el
worker tengan el mismo dueño): en `/etc/php/8.4/fpm/pool.d/www.conf` cambia `user = bida` y
deja `group = www-data`; luego `sudo systemctl restart php8.4-fpm`.

---

## 6. Servicios externos

### 6.1 Cloudinary (fotos, música y video)

1. Cuenta en [cloudinary.com](https://cloudinary.com) (plan Free, con verificación en dos pasos).
2. *Dashboard → API Keys*: copia la **API environment variable** (`cloudinary://…`) a
   `CLOUDINARY_URL`.
3. Usa una cuenta o una llave **nueva** para producción, distinta de la que usas en local.
4. *Settings → Security*: activa *Strict transformations* solo si luego ves que nadie abusa de
   las URLs; al empezar no hace falta.

El plan Free alcanza para las primeras decenas de invitaciones. Revisa el consumo en el panel
cada mes; los videos son lo que más gasta.

### 6.2 Correo

**Recibir** en `hola@bida-events.com` (gratis): Cloudflare → *Email → Email Routing* → activa,
agrega la regla `hola@` → tu Gmail. Cloudflare crea solo los registros MX.

**Enviar** (alertas de `bida:salud`): cuenta en [resend.com](https://resend.com), *Domains →
Add domain* `bida-events.com`, y agrega en Cloudflare los registros DNS que te indique (SPF y
DKIM). Luego, en `.env`:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
MAIL_USERNAME=resend
MAIL_PASSWORD=re_xxxxxxxxxxxxxxxx     # API key de Resend
MAIL_FROM_ADDRESS="hola@bida-events.com"
MAIL_FROM_NAME="Bida Events"
OPERATIONS_ALERT_EMAIL=tu-correo@gmail.com
```

Agrega también un registro DMARC en Cloudflare (TXT, nombre `_dmarc`):
`v=DMARC1; p=quarantine; rua=mailto:hola@bida-events.com`.

Prueba: `php artisan bida:salud` (sin `--sin-correo`) debe mandar un correo si algo falla; para
forzar uno de prueba: `php artisan tinker` → `Mail::raw('Prueba', fn ($m) => $m->to('tu-correo@gmail.com')->subject('Prueba'));`

### 6.3 Respaldos fuera del servidor (Cloudflare R2)

`bida:respaldo` guarda cada noche la base y los medios en `storage/app/backups`, **dentro** del
mismo servidor. Si el servidor se pierde, se pierden también. Copia la carpeta a R2:

1. Cloudflare → *R2* → crea el bucket `bida-respaldos` (privado).
2. *Manage API Tokens* → token con permiso *Object Read & Write* solo para ese bucket. Anota el
   *Access Key ID*, el *Secret* y el *endpoint* (`https://<cuenta>.r2.cloudflarestorage.com`).
3. En el servidor:

```bash
sudo apt -y install rclone
rclone config
# n (nuevo) → nombre: r2 → tipo: s3 → provider: Cloudflare → pega Access Key y Secret
# → endpoint: el de tu cuenta → deja el resto por defecto
```

4. Copia diaria, después del respaldo de las 02:30 (`crontab -e`):

```cron
15 3 * * * rclone sync /var/www/bida-events/storage/app/backups r2:bida-respaldos --max-age 30d >> /var/www/bida-events/storage/logs/rclone.log 2>&1
```

Una vez al mes, descarga un respaldo a tu PC y prueba restaurarlo en local (ver
`docs/operacion.md`, «Restaurar un respaldo»). Además, `bida:probar-respaldo` lo prueba solo
cada domingo en el servidor.

---

## 7. Comprobar que todo funciona

```bash
php artisan about                    # entorno production, debug OFF, cachés activas
php artisan schedule:list            # tareas programadas
sudo supervisorctl status            # bida-worker RUNNING
php artisan bida:respaldo            # primer respaldo a mano
php artisan bida:salud --sin-correo  # sin fallas
```

En el navegador (y desde tu celular con datos móviles):

- [ ] `https://bida-events.com` carga con candado; `http://` y `www.` redirigen.
- [ ] La portada: el teléfono recorre las aperturas sin quedar en blanco; la temporada muestra
      la cuenta regresiva (si no pasó `BIDA_SEASON_ENDS_AT`).
- [ ] Una página por evento (`/invitaciones-de-boda`) y la de tarjetas (`/tarjetas-dia-del-amor`).
- [ ] Las muestras: `/muestra/xv-isabella`, `/muestra/tarjeta-ana-luis`,
      `/muestra/tarjeta-libro-aventuras`.
- [ ] En la «Carta que florece», el botón del micrófono aparece (solo funciona con HTTPS).
- [ ] Ingresa con tu administrador, crea una invitación de prueba, sube una foto (debe quedar en
      Cloudinary), guarda y abre su enlace público.
- [ ] Desde el panel de un cliente, pide un Excel y un PDF: deben llegar en menos de un minuto.
- [ ] Botones de WhatsApp: abren el chat con el número correcto y el código de referencia.
- [ ] Comparte el enlace de la portada en WhatsApp: se ve la imagen de vista previa.
- [ ] `https://bida-events.com/.env` debe dar 403 o 404.

---

## 8. Publicar una versión nueva

Desde tu PC: prueba en local, haz commit y `git push` a `main`. La integración continua
(`.github/workflows/ci.yml`) debe estar en verde. Luego, en el servidor:

```bash
cd /var/www/bida-events

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

# 6. PHP carga el código nuevo (OPcache no revisa los archivos) y los workers también
sudo systemctl reload php8.4-fpm
php artisan queue:restart

# 7. De vuelta
php artisan up
```

Conviene guardar esos pasos en `~/deploy.sh` y correr `bash ~/deploy.sh` cada vez.

Si agregaste una invitación o tarjeta de muestra nueva, siémbrala con
`php artisan db:seed --class=ShowcaseInvitationsSeeder --force` (rehace las muestras y borra sus
invitados y respuestas de prueba; no toca las invitaciones de clientes).

Después de publicar:

- Abrir la portada, una página por evento y una invitación de muestra.
- `php artisan bida:salud` debe terminar sin fallas.
- Revisar `storage/logs/laravel-*.log` por errores nuevos.

### Si algo sale mal

1. `php artisan down`.
2. Volver al commit anterior: `git checkout <commit-anterior>` y repetir los pasos 3, 5 y 6.
3. Si la migración dañó datos, restaurar el respaldo del paso 1 (ver `docs/operacion.md`,
   «Restaurar un respaldo»).
4. `php artisan up`.

---

## 9. Después del lanzamiento

**Primera semana**

- Revisa a diario `storage/logs/laravel-*.log` y los correos de `bida:salud`.
- Política de contenido: con `CSP_ENFORCE=false` el navegador solo informa. Abre la consola del
  navegador (F12) en la portada, el editor y cada plantilla: si no aparecen avisos de «Content
  Security Policy», pasa a `CSP_ENFORCE=true`, corre `php artisan optimize` y vuelve a probar.
- Activa **HSTS** en Cloudflare (paso 1.3) cuando confirmes que todo va por HTTPS.

**Buscadores y redes**

- [Google Search Console](https://search.google.com/search-console): agrega el dominio (la
  verificación por DNS se hace con un registro TXT en Cloudflare) y envía
  `https://bida-events.com/sitemap.xml`.
- [Google Business Profile](https://business.google.com): perfil del negocio con el enlace a la
  web y el WhatsApp.
- Revisa cómo se ve el enlace al compartir con el
  [depurador de Facebook](https://developers.facebook.com/tools/debug/).

**Cada temporada**

- Cambia `BIDA_SEASON_ENDS_AT` (y `BIDA_LAUNCH_PROMO` cuando termine la inauguración) en `.env`
  y corre `php artisan optimize` para que tome el cambio. Con la configuración en caché, editar
  `.env` sin ese comando no tiene efecto.

**Mantenimiento mensual**

- `sudo apt update && sudo apt -y upgrade` (las actualizaciones de seguridad ya se instalan
  solas) y reinicia si lo pide (`sudo reboot`).
- Mira el consumo de Cloudinary y el espacio en disco (`df -h`).
- Prueba restaurar un respaldo de R2 en local.
- Cuando cambie la lista de IP de Cloudflare, actualiza `set_real_ip_from` en Nginx.

## Seguridad: lo que nunca debe pasar

- `APP_DEBUG=true` en producción.
- El `.env` en el repositorio o compartido por chat. Si una llave se filtra (Cloudinary, Resend,
  R2, base de datos), **cámbiala** en su servicio y en el `.env`, y corre `php artisan optimize`.
- Usuarios con contraseñas de prueba (`DatabaseSeeder`, `ClientUserSeeder`).
- El puerto de PostgreSQL o de Redis abierto en el firewall.
- Entrar al servidor como root o con contraseña.
