# Bida Events

Aplicación web para crear, editar y publicar **invitaciones digitales de eventos**: bodas, XV años,
bautizos y cumpleaños.

El equipo arma la invitación en un editor, entrega un enlace general y, según el paquete, un enlace
personal por invitado. Desde el celular, los invitados confirman asistencia, votan encuestas,
sugieren canciones y suben fotos a un fotomural.

## Arrancar

Con Docker instalado, nada más:

```bash
docker compose up -d
docker compose logs -f app
```

La primera vez tarda unos minutos mientras construye la imagen, instala las dependencias y carga
los datos de ejemplo. Cuando el registro diga `Listo: http://localhost:8000`, ya está.

Entrar en http://localhost:8000 con el usuario `admin` y la clave `password`.

El detalle completo (comandos del día a día, servicios, qué hacer cuando algo falla) está en
[`docs/docker.md`](docs/docker.md).

## Comandos habituales

```bash
docker compose exec app php artisan test     # las pruebas
docker compose exec app vendor/bin/pint      # el estilo del código, lo exige la CI
docker compose exec vite npm run build       # compilar el frontend
```

## Tecnologías

PHP 8.4 y Laravel 12 con Blade del lado del servidor; Alpine 3, Tailwind 4 y Vite 7 del lado del
navegador. PostgreSQL como base de datos, Cloudinary para las fotos, DomPDF y Maatwebsite Excel
para los reportes.

## Documentación

| Archivo | Qué explica |
| --- | --- |
| [`PROJECT_MAPA.md`](docs/PROJECT_MAPA.md) | Qué hace cada archivo, cómo fluye la información, qué conviene mejorar |
| [`docs/docker.md`](docs/docker.md) | Docker: el entorno de desarrollo en detalle y la puesta en producción (`compose.prod.yaml`) |
| [`docs/despliegue.md`](docs/despliegue.md) | Publicar una versión nueva en el servidor |
| [`docs/operacion.md`](docs/operacion.md) | Registros, alertas y respaldos |
| [`docs/rendimiento.md`](docs/rendimiento.md) | Mediciones y optimizaciones |
