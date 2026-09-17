<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Tareas programadas
|--------------------------------------------------------------------------
|
| Necesitan que el servidor ejecute cada minuto:
|   * * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
| Para ver qué corre y cuándo: php artisan schedule:list
| Procedimiento completo en docs/operacion.md.
|
*/

// Respaldo de la base y los medios, y limpieza de respaldos vencidos
Schedule::command('bida:respaldo')->dailyAt('02:30')->withoutOverlapping();

// Un respaldo que no se restauró no está probado
Schedule::command('bida:probar-respaldo')->weeklyOn(0, '04:00')->withoutOverlapping();

// Fotos de eventos viejos y exportaciones vencidas (retención en config/optimizations.php)
Schedule::command('invitations:purge-contributions')->dailyAt('03:30')->withoutOverlapping();

// Trabajos fallidos, cola detenida, respaldos y disco; avisa por correo si OPERATIONS_ALERT_EMAIL está definido
Schedule::command('bida:salud')->hourly()->withoutOverlapping();

// La tabla failed_jobs no crece sin límite: se conservan 30 días
Schedule::command('queue:prune-failed --hours=720')->daily();
