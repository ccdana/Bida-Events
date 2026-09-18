# Entorno de desarrollo con Docker

Todo el entorno (PHP, PostgreSQL, la cola y Vite) corre en contenedores. No hace falta instalar
PHP, Composer, Node ni PostgreSQL en el equipo: alcanza con Docker.

## Arrancar por primera vez

```bash
git clone https://github.com/ccdana/Bida-Events.git bida-events
cd bida-events
docker compose up -d
```

Eso es todo. La primera vez tarda varios minutos: construye la imagen de PHP, instala las
dependencias de Composer y de npm, crea el `.env`, genera la `APP_KEY`, aplica las migraciones y
carga los datos de ejemplo. Para mirar el avance:

```bash
docker compose logs -f app
```

Cuando el registro diga `Listo: http://localhost:8000`, la aplicación está arriba.

| Dirección | Qué es |
| --- | --- |
| http://localhost:8000 | La portada pública |
| http://localhost:8000/login | Entrada al panel |
| http://localhost:8000/muestra/xv-isabella | Una invitación de muestra |
| localhost:5432 | PostgreSQL, para conectar DBeaver o TablePlus (usuario `bida`, clave `secret`) |

Usuarios que deja cargados el seeder (solo para desarrollo):

| Usuario | Clave | Rol |
| --- | --- | --- |
| `admin` | `password` | Administrador |
| `cliente.prueba` | se genera y se imprime una sola vez en el registro de arranque | Cliente |

La clave del cliente aparece en `docker compose logs app` la primera vez. Si se perdió, se saca
otra con `docker compose exec app php artisan db:seed --class=ClientUserSeeder` después de borrar
ese usuario, o se cambia desde el panel de administración.

## El día a día

```bash
docker compose up -d        # levantar
docker compose down         # apagar (la base se conserva)
docker compose logs -f app  # ver los registros
docker compose ps           # ver qué está corriendo
```

Los comandos del proyecto se ejecutan dentro del contenedor `app`:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan test
docker compose exec app php artisan tinker
docker compose exec app vendor/bin/pint
docker compose exec app composer install
```

Y los de npm, en el contenedor `vite`:

```bash
docker compose exec vite npm install
docker compose exec vite npm run build
```

> En Windows, si usas Git Bash y un comando falla diciendo que la ruta no es absoluta, pon
> `MSYS_NO_PATHCONV=1` delante. En PowerShell no pasa.

Para no escribir tanto, conviene un alias:

```bash
# Linux y macOS (~/.bashrc o ~/.zshrc)
alias bida='docker compose exec app'

# Windows, Git Bash (~/.bashrc)
alias bida='MSYS_NO_PATHCONV=1 docker compose exec app'
```

Con eso, `bida php artisan test`.

## Los servicios

| Servicio | Imagen | Para qué |
| --- | --- | --- |
| `app` | La del `Dockerfile` (PHP 8.4) | Sirve la aplicación en el puerto 8000 y prepara el proyecto al arrancar |
| `queue` | La misma | `queue:work`: genera los Excel y PDF que piden los clientes |
| `vite` | `node:22-bookworm-slim` | Compila el CSS y el JS, y recarga el navegador al guardar |
| `pgsql` | `postgres:17-alpine` | La base de datos. Vive en el volumen `pgsql-data` |

### Por qué `vendor/` y `node_modules/` van en volúmenes

Los dos viven en volúmenes propios del contenedor y no en la carpeta del proyecto. No es por gusto:

- **`node_modules/`**: rollup y esbuild traen binarios distintos según el sistema. El
  `node_modules` instalado en Windows o macOS hace que Vite no arranque dentro de Linux.
- **`vendor/`**: Docker Desktop en Windows pierde entradas al recorrer un directorio con miles de
  archivos a través de la carpeta compartida. Los iconos de Phosphor son 9072 archivos y se
  perdían 670, así que la portada fallaba con un error 500 por iconos que sí están en el disco.
  En un volumen del contenedor el problema desaparece, y de paso las páginas cargan mucho más
  rápido.

Si tienes `vendor/` o `node_modules/` en el equipo, no molestan: el contenedor no los usa y le
sirven al editor para el autocompletado. Para regenerar el del equipo sin instalar PHP:

```bash
docker compose cp app:/var/www/html/vendor ./vendor
```

### Qué escribe el contenedor en el `.env`

Al arrancar, el servicio `app` apunta el `.env` a los servicios de Docker: `DB_CONNECTION`,
`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` y `APP_URL`. Lo hace escribiendo
el archivo en vez de pasar variables de entorno, y la diferencia importa: una variable de entorno
real tiene prioridad sobre el `.env` **y sobre el `phpunit.xml`**, así que `php artisan test`
correría contra la base de desarrollo y `RefreshDatabase` la borraría entera. Escribiendo el
`.env`, las pruebas usan SQLite en memoria como corresponde.

El resto del `.env` es tuyo: el contenedor no lo toca. Después de editarlo,
`docker compose restart app` (el servidor no se reinicia solo porque corre con `--no-reload`, que
es lo que le permite atender varias peticiones a la vez).

## Cambiar la configuración

Las opciones del entorno se ajustan en el `.env` del proyecto (docker compose también lo lee):

| Variable | Por defecto | Para qué |
| --- | --- | --- |
| `APP_PORT` | `8000` | Puerto de la aplicación, si el 8000 está ocupado |
| `VITE_PORT` | `5173` | Puerto de Vite |
| `DB_FORWARD_PORT` | `5432` | Puerto de PostgreSQL en el equipo, si ya hay uno instalado |
| `DOCKER_DB_NAME`, `DOCKER_DB_USER`, `DOCKER_DB_PASSWORD` | `bida_events`, `bida`, `secret` | Credenciales de la base. Cambiarlas exige borrar el volumen |
| `UID`, `GID` | `1000` | Solo en Linux, si tu usuario no es el 1000 (`id -u` y `id -g`). Después, `docker compose build` |

Las variables de la base de datos que Laravel usa (`DB_HOST`, `DB_USERNAME`, …) las define
`compose.yaml` y llegan al contenedor como variables de entorno reales, que tienen prioridad sobre
el `.env`. Por eso el `.env` puede conservar los valores de una instalación sin Docker sin que se
pisen entre sí.

## Cuando algo se rompe

**Empezar de cero con la base de datos.** Borra los datos y vuelve a cargar los de ejemplo:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

Si quieres borrar además las dependencias y volver a construirlo todo, `docker compose down -v`
elimina los cuatro volúmenes y el siguiente `docker compose up -d` reinstala desde cero.

**Reconstruir la imagen** después de tocar el `Dockerfile`:

```bash
docker compose build --no-cache
docker compose up -d
```

**Reinstalar las dependencias.** Viven en volúmenes, así que se borran esos:

```bash
docker compose down
docker volume rm bida-events_vendor bida-events_node-modules
docker compose up -d
```

**Instalar un paquete nuevo**:

```bash
docker compose exec app composer require proveedor/paquete
docker compose exec vite npm install paquete
```

**El navegador no toma los cambios de CSS o JS.** Revisa que el servicio `vite` esté arriba con
`docker compose logs -f vite`. Si el archivo `public/hot` quedó de una corrida anterior, bórralo.

**El puerto 8000 o el 5432 ya están ocupados.** Pon `APP_PORT=8080` o `DB_FORWARD_PORT=5433` en
el `.env` y vuelve a levantar.

## Sin Docker

Sigue funcionando la instalación directa con PHP 8.4, Composer, Node 22 y PostgreSQL en el equipo;
está en [`PROJECT_MAPA.md`](../PROJECT_MAPA.md), sección 1. Para el servidor de producción,
[`despliegue.md`](despliegue.md).

El proyecto todavía trae `laravel/sail` como dependencia de desarrollo, pero no se usa: el script
`vendor/bin/sail` no corre en Windows fuera de WSL2 y su `compose.yaml` necesitaba que `vendor/`
existiera de antemano, que es justo lo que un equipo nuevo no tiene.
