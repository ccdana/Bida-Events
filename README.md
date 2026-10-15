# Bida Events

Aplicación web para crear, editar y publicar **invitaciones digitales de eventos** (bodas, XV años,
bautizos, cumpleaños, graduaciones, baby shower) y tarjetas de temporada.

Cada persona arma su invitación en el editor («Hazlo tú», gratis o con pago único al publicar), los
revendedores la arman para sus clientes con un plan mensual, y el equipo la hace por encargo. Desde
el celular, los invitados confirman asistencia, votan encuestas, sugieren canciones y suben fotos a
un fotomural; el día del evento, la puerta controla la entrada con el pase QR.

## Tecnologías

PHP 8.4 y Laravel 12 con Blade; Alpine 3, Tailwind 4 y Vite 7 en el navegador. PostgreSQL 17,
Redis (caché en producción), Cloudinary para los medios, DomPDF y Laravel Excel para los reportes,
Socialite para entrar con Google, Lemon Squeezy (tarjeta) y Libélula (QR de Bolivia) para cobrar.

## Desarrollo (Laragon)

Requisitos: Laragon con PHP 8.4, PostgreSQL y Node 22.

1. `cp .env.example .env` y cambia lo marcado con «Local:» (`APP_ENV=local`, `APP_DEBUG=true`,
   `APP_URL=http://bida-events.test`, `SESSION_SECURE_COOKIE=false`, `CACHE_STORE=database`,
   `MAIL_MAILER=log`); completa `DB_USERNAME` y `DB_PASSWORD`.
2. Instala y prepara todo:

   ```bash
   composer setup          # dependencias, APP_KEY, migraciones y build
   php artisan db:seed     # datos de ejemplo: usuario «admin» o «cliente», contraseña «password»
   ```

Laragon sirve el sitio en http://bida-events.test. Para trabajar:

```bash
npm run dev             # Vite con recarga (o npm run build para servir lo compilado)
composer laragon        # cola y tareas programadas (exportaciones, pagos, alertas)
```

Probar pagos, Google y correo en local: [`docs/pruebas-locales.md`](docs/pruebas-locales.md).

## Calidad

```bash
php artisan test        # pruebas (SQLite en memoria)
vendor/bin/pint         # estilo del código
npm run build           # compilación del frontend
```

La CI de GitHub corre las tres en cada push (`.github/workflows/ci.yml`).

## Producción

Docker con `compose.prod.yaml` (Nginx, PHP-FPM, cola, programador, PostgreSQL y Redis) detrás de
Cloudflare. El paso a paso está en [`docs/despliegue.md`](docs/despliegue.md).

## Documentación

| Archivo | Qué explica |
| --- | --- |
| [`docs/despliegue.md`](docs/despliegue.md) | Cómo está armado el servidor y cómo publicar una versión nueva |
| [`docs/conexiones.md`](docs/conexiones.md) | Conectar el correo (Zoho), Lemon Squeezy y Libélula en producción |
| [`docs/operacion.md`](docs/operacion.md) | Registros, alertas, pagos en línea, respaldos y restauración |
| [`docs/flujo-navegacion.md`](docs/flujo-navegacion.md) | A dónde lleva cada botón del sitio |
| [`docs/idiomas.md`](docs/idiomas.md) | Español e inglés: cómo agregar textos y páginas, y la comprobación de traducciones |
| [`docs/pruebas-locales.md`](docs/pruebas-locales.md) | Probar registro, correo y pagos en Laragon |
