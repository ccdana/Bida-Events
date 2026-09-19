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
