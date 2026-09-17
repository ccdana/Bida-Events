<?php

/*
|--------------------------------------------------------------------------
| Operación: tiempos, alertas y respaldos
|--------------------------------------------------------------------------
|
| Lo usan el middleware LogSlowRequests, los comandos bida:respaldo,
| bida:probar-respaldo y bida:salud, y el programador de tareas
| (routes/console.php). El procedimiento completo está en docs/operacion.md.
|
*/

return [

    // Peticiones más lentas que esto quedan en storage/logs/performance-*.log
    'slow_request_ms' => (int) env('SLOW_REQUEST_MS', 1500),

    'alerts' => [
        // Si se define, bida:salud manda un correo cuando algo falla (además de dejarlo en el log)
        'email' => env('OPERATIONS_ALERT_EMAIL'),

        // Trabajos fallidos en las últimas 24 horas a partir de los cuales se avisa
        'failed_jobs' => (int) env('ALERT_FAILED_JOBS', 1),

        // Un trabajo pendiente más viejo que esto indica que no hay worker corriendo
        'pending_job_minutes' => (int) env('ALERT_PENDING_JOB_MINUTES', 30),

        // Si el último respaldo es más viejo, se avisa (el respaldo corre cada noche)
        'backup_max_age_hours' => (int) env('ALERT_BACKUP_MAX_AGE_HOURS', 26),

        // Espacio libre mínimo en el disco de la aplicación
        'min_free_disk_percent' => (int) env('ALERT_MIN_FREE_DISK_PERCENT', 10),
    ],

    'backups' => [
        // Carpeta dentro de storage/app
        'path' => env('BACKUP_PATH', 'backups'),

        'keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),

        // Ejecutables del motor. En Laragon: C:\laragon\bin\postgresql\pgsql\bin\pg_dump.exe
        'binaries' => [
            'pg_dump' => env('BACKUP_PG_DUMP', 'pg_dump'),
            'psql' => env('BACKUP_PSQL', 'psql'),
            'mysqldump' => env('BACKUP_MYSQLDUMP', 'mysqldump'),
            'mysql' => env('BACKUP_MYSQL', 'mysql'),
        ],

        // Segundos máximos para volcar o restaurar la base
        'timeout' => (int) env('BACKUP_TIMEOUT', 900),
    ],

];
