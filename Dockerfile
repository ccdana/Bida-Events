# Imagen de producción de Bida Events (la usa compose.prod.yaml; ver docs/despliegue.md).
#
# El código viaja dentro de la imagen: nada se monta salvo storage/. Se arma en etapas para que la
# imagen final no lleve ni Composer ni Node:
#
#   base        PHP 8.4-FPM con las extensiones del proyecto y el cliente de PostgreSQL 17
#   deps        dependencias de PHP sin las de desarrollo y el autoload optimizado
#   assets      el CSS y el JS compilados por Vite
#   production  PHP-FPM con el proyecto adentro (servicios app, queue y scheduler)
#   web         Nginx con los archivos públicos


# --- base -------------------------------------------------------------------------------------
# Repositorio oficial de PostgreSQL: el cliente de Debian es la versión 15 y no puede respaldar un
# servidor 17. Lo usan `bida:respaldo`, `bida:probar-respaldo` y el arranque (pg_isready).
FROM php:8.4-fpm-bookworm AS base

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends ca-certificates curl gnupg; \
    curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        | gpg --dearmor -o /usr/share/keyrings/pgdg.gpg; \
    echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] https://apt.postgresql.org/pub/repos/apt bookworm-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        postgresql-client-17 \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apt-get purge -y --auto-remove gnupg; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html


# --- deps -------------------------------------------------------------------------------------
FROM base AS deps

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./
# Sin scripts todavía: el código aún no está copiado y artisan no existe
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

COPY . .

RUN set -eux; \
    mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs; \
    composer dump-autoload --no-dev --optimize --classmap-authoritative


# --- assets -----------------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build


# --- production -------------------------------------------------------------------------------
FROM base AS production

ARG UID=1000
ARG GID=1000

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-bida.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN set -eux; \
    chmod +x /usr/local/bin/entrypoint; \
    if ! getent group "${GID}" >/dev/null; then groupadd -g "${GID}" bida; fi; \
    if ! getent passwd "${UID}" >/dev/null; then useradd -u "${UID}" -g "${GID}" -m -s /bin/bash bida; fi

# El código con sus dependencias, y encima los assets ya compilados
COPY --chown=${UID}:${GID} --from=deps /var/www/html ./
COPY --chown=${UID}:${GID} --from=assets /var/www/html/public/build ./public/build

# storage/ y bootstrap/cache son lo único que la aplicación escribe
RUN set -eux; \
    rm -rf public/hot storage/framework/cache/data/* storage/logs/*; \
    mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; \
    chown -R "${UID}:${GID}" storage bootstrap/cache

USER ${UID}:${GID}

ENV BIDA_RELEASE=0

# Salud del contenedor: PHP-FPM responde en el 9000
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]


# --- web --------------------------------------------------------------------------------------
FROM nginx:1.27-alpine AS web

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget --quiet --spider http://127.0.0.1/health || exit 1
