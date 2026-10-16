#!/usr/bin/env bash
#
# Arranque de los contenedores de producción (app, queue y scheduler).
#
# El código ya viene dentro de la imagen; acá solo se prepara lo que depende del servidor: las
# carpetas de storage (que viven en un volumen), la base de datos y las cachés de Laravel.
# Todo es idempotente: los tres servicios pueden arrancar a la vez.

set -euo pipefail

cd /var/www/html

log() {
    printf '\033[0;34m[bida]\033[0m %s\n' "$*"
}

# El volumen de storage nace vacío: las carpetas que Laravel espera se crean acá
mkdir -p \
    storage/app/public \
    storage/app/backups \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs

if [ -n "${DB_HOST:-}" ]; then
    log "Esperando a la base de datos en ${DB_HOST}:${DB_PORT:-5432}"

    until pg_isready --quiet --host="${DB_HOST}" --port="${DB_PORT:-5432}" --username="${DB_USERNAME:-bida}"; do
        sleep 1
    done
fi

# Solo el servicio «app» prepara la aplicación; los demás esperan a que termine.
if [ "${BIDA_RELEASE:-0}" = "1" ]; then
    if [ "${BIDA_MIGRATE:-1}" = "1" ]; then
        log 'Aplicando las migraciones'
        php artisan migrate --force
    fi

    # El enlace público/storage apunta al volumen; si ya existe, no es motivo para abortar
    php artisan storage:link --quiet || true

    log 'Armando las cachés de configuración, rutas, vistas y eventos'
    php artisan optimize

    # Las imágenes de 1200×630 para compartir viven en public/, dentro de la imagen: solo se
    # generan si faltan (por ejemplo, al montar public/ como volumen).
    php artisan bida:imagenes-compartir --quiet || true

    log 'Listo'
elif [ -n "${DB_HOST:-}" ]; then
    # Los workers esperan a que «app» termine de migrar: cada contenedor tiene su propio disco,
    # así que lo que se mira es la base, que sí es compartida.
    log 'Esperando a que la base esté migrada'

    until [ "$(PGPASSWORD="${DB_PASSWORD:-}" psql \
        --host="${DB_HOST}" --port="${DB_PORT:-5432}" \
        --username="${DB_USERNAME:-bida}" --dbname="${DB_DATABASE:-bida_events}" \
        --tuples-only --no-align \
        --command="SELECT to_regclass('public.jobs') IS NOT NULL" 2>/dev/null)" = "t" ]; do
        sleep 2
    done
fi

exec "$@"
