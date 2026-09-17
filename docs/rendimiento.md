# Medición de rendimiento

Generado con `php artisan bida:medir --guardar` (5 repeticiones por pantalla).
Fecha: 2026-09-17 08:49. Entorno: local.

| Pantalla | Ruta | Tiempo P50 | Tiempo P95 | Consultas | Memoria | HTML |
| --- | --- | --- | --- | --- | --- | --- |
| Portada | / | 20.9 ms | 102.6 ms | 4 | 125.4 KB | 100.6 KB |
| Invitación | /p/xv-isabella | 27 ms | 65.3 ms | 30 | 564 KB | 148.9 KB |
| Muestra | /muestra/xv-isabella | 31.8 ms | 46.1 ms | 30 | 569.1 KB | 161.8 KB |

Para comparar: corre el comando antes y después del cambio, con los mismos datos.
La caché de invitaciones estaba apagada.
