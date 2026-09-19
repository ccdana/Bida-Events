#!/usr/bin/env bash
#
# Prepara el contenedor antes de ejecutar el comando que pide compose.yaml.
#
# El servicio `app` es el único que hace la preparación completa (BIDA_BOOTSTRAP=1): crea el .env,
# lo apunta a los servicios de compose, instala las dependencias de PHP, genera la APP_KEY, migra
# y, si la base está vacía, carga los datos de ejemplo. Los demás servicios solo esperan a que eso
# termine.
#
# Todo es idempotente: se puede levantar y bajar el entorno las veces que haga falta.

set -euo pipefail

cd /var/www/html

log() {
    printf '\033[0;34m[bida]\033[0m %s\n' "$*"
}

# Deja `clave=valor` en el .env, ya sea cambiando la línea que existe o agregándola al final.
escribir_env() {
    local clave="$1" valor="$2"

    if grep -qE "^${clave}=" .env; then
        sed -i "s|^${clave}=.*|${clave}=${valor}|" .env
    else
        printf '%s=%s\n' "$clave" "$valor" >> .env
    fi
}

bootstrap="${BIDA_BOOTSTRAP:-0}"

if [ "$bootstrap" = "1" ]; then
    if [ ! -f .env ]; then
        # .env.example viene escrito para producción: acá se pasa a desarrollo. Con APP_DEBUG en
        # false no se verían los errores, y con la cookie de sesión «segura» (solo HTTPS) no se
        # podría ni iniciar sesión en http://localhost.
        log 'No hay .env: se copia de .env.example y se ajusta para desarrollo'
        cp .env.example .env

        for ajuste in \
            'APP_ENV=local' \
            'APP_DEBUG=true' \
            'LOG_LEVEL=debug' \
            'LOG_STACK=single' \
            'SESSION_ENCRYPT=false' \
            'SESSION_SECURE_COOKIE=false' \
            'MAIL_MAILER=log' \
            'CACHE_STORE=database' \
            'CACHE_OPTIMIZATIONS_ENABLED=false' \
            'HTTP_CACHE_ENABLED=false'; do
            sed -i "s|^${ajuste%%=*}=.*|${ajuste}|" .env
        done
    fi

    # Estos valores los manda compose.yaml y no son negociables dentro de Docker: la base es un
    # servicio, no 127.0.0.1. Se escriben en el .env en lugar de pasarse como variables de entorno
    # para que Laravel y PHPUnit se comporten igual que en una instalación normal.
    log 'Apuntando el .env a los servicios de Docker'
    escribir_env DB_CONNECTION pgsql
    escribir_env DB_HOST "$BIDA_DB_HOST"
    escribir_env DB_PORT "$BIDA_DB_PORT"
    escribir_env DB_DATABASE "$BIDA_DB_DATABASE"
    escribir_env DB_USERNAME "$BIDA_DB_USERNAME"
    escribir_env DB_PASSWORD "$BIDA_DB_PASSWORD"
    escribir_env APP_URL "http://localhost:${BIDA_APP_PORT}"

    if [ ! -f vendor/autoload.php ]; then
        log 'Instalando las dependencias de PHP (la primera vez tarda unos minutos)'
        composer install --no-interaction --prefer-dist --no-progress
    fi

    if ! grep -qE '^APP_KEY=base64:' .env; then
        log 'Generando la APP_KEY'
        php artisan key:generate --force
    fi
else
    # Los demás servicios arrancan en paralelo con `app`: esperan a que deje el .env apuntando a
    # la base y las dependencias instaladas.
    if [ ! -f vendor/autoload.php ] || ! grep -qE "^DB_HOST=${BIDA_DB_HOST}$" .env 2>/dev/null; then
        log 'Esperando a que el servicio app termine de preparar el proyecto'
        while [ ! -f vendor/autoload.php ] || ! grep -qE "^DB_HOST=${BIDA_DB_HOST}$" .env 2>/dev/null; do
            sleep 2
        done
    fi
fi

log "Esperando a PostgreSQL en ${BIDA_DB_HOST}:${BIDA_DB_PORT}"
until pg_isready --quiet --host="$BIDA_DB_HOST" --port="$BIDA_DB_PORT" --username="$BIDA_DB_USERNAME"; do
    sleep 1
done

if [ "$bootstrap" = "1" ]; then
    # 't' si la tabla `migrations` todavía no existe, es decir, si la base está recién creada.
    base_vacia="$(
        PGPASSWORD="$BIDA_DB_PASSWORD" psql \
            --host="$BIDA_DB_HOST" --port="$BIDA_DB_PORT" \
            --username="$BIDA_DB_USERNAME" --dbname="$BIDA_DB_DATABASE" \
            --tuples-only --no-align \
            --command="SELECT to_regclass('public.migrations') IS NULL"
    )"

    log 'Aplicando las migraciones'
    php artisan migrate --force

    if [ "$base_vacia" = "t" ]; then
        log 'Base nueva: cargando los datos de ejemplo (admin, tipos de evento e invitaciones de muestra)'
        php artisan db:seed --force
    fi

    # El enlace a storage falla si ya existe; no es motivo para abortar el arranque.
    php artisan storage:link --quiet || true

    php artisan optimize:clear --quiet

    log "Listo: http://localhost:${BIDA_APP_PORT}"
fi

exec "$@"
