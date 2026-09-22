# Imágenes de desarrollo de Bida Events.
#
# El código NO se copia a la imagen: compose.yaml lo monta como volumen para que los cambios se
# vean al instante. Por eso tampoco hay `composer install` acá; lo hace docker/entrypoint.sh.


# --- node: compila el CSS y el JS ------------------------------------------------------------
FROM node:22-bookworm-slim AS node

ARG UID=1000
ARG GID=1000

# El contenedor usa su propio node_modules (compose.yaml lo monta como volumen con nombre): el del
# equipo tiene los binarios de Windows o macOS y rollup y esbuild no arrancan con ellos desde
# Linux. Crear la carpeta acá con el dueño correcto es lo que hace que el volumen nazca con ese
# dueño y el usuario sin privilegios pueda instalar dentro.
RUN mkdir -p /var/www/html/node_modules \
    && chown -R "${UID}:${GID}" /var/www/html

WORKDIR /var/www/html
USER ${UID}:${GID}


# --- app: sirve la aplicación y procesa la cola -----------------------------------------------
# PHP 8.4 (la versión que exige composer.json y la que usa la integración continua) con las
# extensiones que piden el proyecto y sus dependencias, el cliente de PostgreSQL 17 que necesitan
# `bida:respaldo` y `bida:probar-respaldo`, y Composer.
FROM php:8.4-cli-bookworm AS app

# Se pueden cambiar desde .env (UID/GID) para que en Linux los archivos que crea el contenedor
# queden a nombre del usuario del host y no de root. En Windows y macOS no tienen efecto.
ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_MEMORY_LIMIT=-1

# Repositorio oficial de PostgreSQL: el cliente de Debian es la versión 15 y no puede respaldar
# un servidor 17, así que se instala el cliente que corresponde al servidor de compose.yaml.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends ca-certificates curl gnupg; \
    curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        | gpg --dearmor -o /usr/share/keyrings/pgdg.gpg; \
    echo "deb [signed-by=/usr/share/keyrings/pgdg.gpg] https://apt.postgresql.org/pub/repos/apt bookworm-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        postgresql-client-17 \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libsqlite3-dev \
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
        pdo_sqlite \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apt-get purge -y --auto-remove gnupg; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-bida.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

# Usuario sin privilegios. Los `getent` evitan chocar con un UID o GID que la imagen ya use.
RUN set -eux; \
    chmod +x /usr/local/bin/entrypoint; \
    if ! getent group "${GID}" >/dev/null; then groupadd -g "${GID}" bida; fi; \
    if ! getent passwd "${UID}" >/dev/null; then \
        useradd -u "${UID}" -g "${GID}" -m -s /bin/bash bida; \
    fi; \
    mkdir -p /var/www/html/vendor; \
    chown -R "${UID}:${GID}" /var/www/html

WORKDIR /var/www/html
USER ${UID}:${GID}

ENTRYPOINT ["entrypoint"]

# Sin --no-reload, `artisan serve` levanta un solo proceso y atiende de a una petición por vez, y
# una página que pide varios archivos a la vez se vuelve lenta. A cambio, el servidor deja de
# reiniciarse solo al editar el .env: para eso, `docker compose restart app`.
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]


# ══════════════════════════════════════════════════════════════════════════════════════════════
#  PRODUCCIÓN
#  El código sí se copia a la imagen: nada se monta. Se arma en tres pasos (dependencias de PHP,
#  assets compilados e imagen final con PHP-FPM) para que la imagen que se publica no lleve ni
#  Composer ni Node. Ver compose.prod.yaml y docs/docker.md.
# ══════════════════════════════════════════════════════════════════════════════════════════════

# --- deps: dependencias de PHP sin las de desarrollo ------------------------------------------
FROM app AS deps

USER root
WORKDIR /var/www/html

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

# --- assets: el CSS y el JS compilados por Vite -----------------------------------------------
FROM node:22-bookworm-slim AS assets

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build


# --- production: PHP-FPM con el proyecto adentro ----------------------------------------------
FROM php:8.4-fpm-bookworm AS production

ARG UID=1000
ARG GID=1000

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

COPY docker/php.prod.ini /usr/local/etc/php/conf.d/zz-bida.ini
COPY docker/entrypoint.prod.sh /usr/local/bin/entrypoint

RUN set -eux; \
    chmod +x /usr/local/bin/entrypoint; \
    if ! getent group "${GID}" >/dev/null; then groupadd -g "${GID}" bida; fi; \
    if ! getent passwd "${UID}" >/dev/null; then useradd -u "${UID}" -g "${GID}" -m -s /bin/bash bida; fi

WORKDIR /var/www/html

# El código con sus dependencias, y encima los assets ya compilados
COPY --chown=${UID}:${GID} --from=deps /var/www/html ./
COPY --chown=${UID}:${GID} --from=assets /var/www/html/public/build ./public/build

# storage/ y bootstrap/cache son lo único que la aplicación escribe
RUN set -eux; \
    rm -rf public/hot storage/framework/cache/data/* storage/logs/*; \
    mkdir -p storage/app/public storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache; \
    chown -R "${UID}:${GID}" storage bootstrap/cache

USER ${UID}:${GID}

ENV BIDA_RELEASE=0

# Salud del contenedor: PHP-FPM responde en el 9000
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]


# --- web: Nginx con los archivos públicos ------------------------------------------------------
FROM nginx:1.27-alpine AS web

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget --quiet --spider http://127.0.0.1/robots.txt || exit 1
