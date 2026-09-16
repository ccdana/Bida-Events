# Medición de rendimiento

Generado con `php artisan bida:medir --guardar` (6 repeticiones por pantalla).
Fecha: 2026-09-15 23:08. Entorno: local.

| Pantalla | Ruta | Tiempo P50 | Tiempo P95 | Consultas | Memoria | HTML |
| --- | --- | --- | --- | --- | --- | --- |
| Portada | / | 22.9 ms | 114.9 ms | 4 | 113.4 KB | 95.8 KB |
| Invitación | /p/xv-isabella | 17.4 ms | 120.2 ms | 8 | 226.7 KB | 144.7 KB |
| Muestra | /muestra/xv-isabella | 21.1 ms | 35.9 ms | 8 | 236.5 KB | 157.4 KB |

Para comparar: corre el comando antes y después del cambio, con los mismos datos.
La caché de invitaciones estaba encendida.
