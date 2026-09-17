<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Respaldo y prueba de restauración de la base de datos y los medios.
 *
 * - Base: pg_dump / mysqldump (o copia del archivo en SQLite), comprimido en .sql.gz.
 * - Medios locales: storage/app/public en un .zip (solo existe si Cloudinary no está configurado).
 * - Medios en Cloudinary: un manifiesto .json con cada URL, para poder verificarlos o descargarlos.
 *
 * Las contraseñas viajan a los ejecutables por variables de entorno (PGPASSWORD, MYSQL_PWD),
 * nunca en la línea de comandos ni en el log.
 */
class BackupService
{
    /** Carpeta de respaldos: relativa a storage/app, o absoluta (por ejemplo un disco montado aparte). */
    public function directory(): string
    {
        $path = (string) config('operations.backups.path', 'backups');

        return preg_match('#^([a-zA-Z]:[\\\\/]|/)#', $path)
            ? rtrim($path, '/\\')
            : storage_path('app/'.trim($path, '/\\'));
    }

    /**
     * @return array{database: string, media: ?string, manifest: string, size: int}
     */
    public function run(bool $withMedia = true): array
    {
        File::ensureDirectoryExists($this->directory());
        $stamp = now()->format('Y-m-d_His');

        $database = $this->dumpDatabase("{$this->directory()}/base-{$stamp}.sql.gz");
        $media = $withMedia ? $this->zipLocalMedia("{$this->directory()}/medios-{$stamp}.zip") : null;
        $manifest = $this->writeManifest("{$this->directory()}/medios-{$stamp}.json");

        return [
            'database' => $database,
            'media' => $media,
            'manifest' => $manifest,
            'size' => filesize($database) + ($media ? filesize($media) : 0),
        ];
    }

    /** Borra los respaldos más viejos que keep_days. Devuelve cuántos archivos quitó. */
    public function prune(): int
    {
        $limit = now()->subDays((int) config('operations.backups.keep_days', 14))->getTimestamp();
        $deleted = 0;

        foreach (glob($this->directory().'/{base,medios}-*', GLOB_BRACE) ?: [] as $file) {
            if (filemtime($file) < $limit && @unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function latestDump(): ?string
    {
        $files = glob($this->directory().'/base-*.sql.gz') ?: [];
        usort($files, fn (string $a, string $b) => filemtime($b) <=> filemtime($a));

        return $files[0] ?? null;
    }

    public function latestDumpAge(): ?Carbon
    {
        $latest = $this->latestDump();

        return $latest ? Carbon::createFromTimestamp(filemtime($latest), config('app.timezone')) : null;
    }

    /**
     * Restaura el volcado en una base temporal, cuenta las filas de cada tabla contra la base
     * real y borra la temporal. No toca la base de la aplicación.
     *
     * @return array{tables: int, rows: int, differences: array<string, array{live: int, restored: int}>}
     */
    public function testRestore(string $dump): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $sql = $this->decompress($dump);

        try {
            return match ($config['driver']) {
                'pgsql' => $this->testRestorePostgres($config, $sql),
                'mysql', 'mariadb' => $this->testRestoreMysql($config, $sql),
                'sqlite' => $this->testRestoreSqlite($sql),
                default => throw new RuntimeException("No hay prueba de restauración para {$config['driver']}."),
            };
        } finally {
            @unlink($sql);
        }
    }

    // ── Volcado ──────────────────────────────────────────────────────────────

    private function dumpDatabase(string $target): string
    {
        $config = config('database.connections.'.config('database.default'));
        $plain = Str::beforeLast($target, '.gz');
        $binaries = config('operations.backups.binaries');

        match ($config['driver']) {
            'pgsql' => $this->runProcess([
                $binaries['pg_dump'], '--no-owner', '--no-privileges', '--format=plain',
                '--host='.$config['host'], '--port='.$config['port'], '--username='.$config['username'],
                '--file='.$plain, $config['database'],
            ], ['PGPASSWORD' => (string) $config['password']]),

            'mysql', 'mariadb' => $this->runProcess([
                $binaries['mysqldump'], '--single-transaction', '--routines', '--no-tablespaces',
                '--host='.$config['host'], '--port='.$config['port'], '--user='.$config['username'],
                '--result-file='.$plain, $config['database'],
            ], ['MYSQL_PWD' => (string) $config['password']]),

            'sqlite' => $this->dumpSqlite($config['database'], $plain),

            default => throw new RuntimeException("No hay respaldo para el motor {$config['driver']}."),
        };

        $this->compress($plain, $target);

        return $target;
    }

    /** En SQLite se copia el archivo tal cual (VACUUM INTO da una copia coherente aunque haya escrituras). */
    private function dumpSqlite(string $database, string $target): void
    {
        if ($database === ':memory:') {
            throw new RuntimeException('Una base SQLite en memoria no se puede respaldar.');
        }

        DB::statement('VACUUM INTO ?', [$target]);
    }

    // ── Medios ───────────────────────────────────────────────────────────────

    private function zipLocalMedia(string $target): ?string
    {
        $source = storage_path('app/public');
        $files = is_dir($source) ? File::allFiles($source) : [];

        if ($files === []) {
            return null;
        }

        $zip = new ZipArchive;

        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("No se pudo crear {$target}.");
        }

        foreach ($files as $file) {
            $zip->addFile($file->getPathname(), str_replace('\\', '/', $file->getRelativePathname()));
        }

        $zip->close();

        return $target;
    }

    /**
     * Lista de las fotos, videos y audios que viven fuera del servidor (Cloudinary): si hubiera
     * que reconstruir la cuenta, dice qué recuperar y dónde se usaba cada archivo.
     */
    private function writeManifest(string $target): string
    {
        $sources = [
            ['invitation_gallery_images', ['url']],
            ['invitation_media', ['url', 'poster_url']],
            ['invitation_locations', ['image_url']],
            ['invitation_dress_code_items', ['image_url']],
            ['invitation_gift_options', ['image_url']],
            ['guest_contributions', ['file_path']],
            ['invitation_heroes', ['image_url']],
            ['invitation_bank_accounts', ['qr_image_url']],
        ];

        $urls = collect();

        foreach ($sources as [$table, $columns]) {
            foreach ($columns as $column) {
                $urls = $urls->merge(rescue(
                    fn () => DB::table($table)->whereNotNull($column)->pluck($column)->all(),
                    [],
                    report: false,
                ));
            }
        }

        $remote = $urls
            ->filter(fn ($url) => is_string($url) && str_starts_with($url, 'http'))
            ->unique()
            ->sort()
            ->values();

        File::put($target, json_encode([
            'generated_at' => now()->toIso8601String(),
            'count' => $remote->count(),
            'urls' => $remote->all(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $target;
    }

    // ── Prueba de restauración ───────────────────────────────────────────────

    private function testRestorePostgres(array $config, string $sql): array
    {
        $temporary = $config['database'].'_restauracion_prueba';
        $admin = DB::connection();

        $admin->statement("DROP DATABASE IF EXISTS \"{$temporary}\"");
        $admin->statement("CREATE DATABASE \"{$temporary}\"");

        try {
            $this->runProcess([
                config('operations.backups.binaries.psql'), '--quiet', '--set=ON_ERROR_STOP=1',
                '--host='.$config['host'], '--port='.$config['port'], '--username='.$config['username'],
                '--dbname='.$temporary, '--file='.$sql,
            ], ['PGPASSWORD' => (string) $config['password']]);

            config(['database.connections.restauracion_prueba' => array_merge($config, ['database' => $temporary])]);

            return $this->compareTables('restauracion_prueba', "SELECT tablename AS name FROM pg_tables WHERE schemaname = 'public'");
        } finally {
            DB::purge('restauracion_prueba');
            $admin->statement("DROP DATABASE IF EXISTS \"{$temporary}\"");
        }
    }

    private function testRestoreMysql(array $config, string $sql): array
    {
        $temporary = $config['database'].'_restauracion_prueba';
        $admin = DB::connection();

        $admin->statement("DROP DATABASE IF EXISTS `{$temporary}`");
        $admin->statement("CREATE DATABASE `{$temporary}`");

        try {
            $this->runProcess([
                config('operations.backups.binaries.mysql'),
                '--host='.$config['host'], '--port='.$config['port'], '--user='.$config['username'],
                $temporary, '--execute=source '.str_replace('\\', '/', $sql),
            ], ['MYSQL_PWD' => (string) $config['password']]);

            config(['database.connections.restauracion_prueba' => array_merge($config, ['database' => $temporary])]);

            return $this->compareTables('restauracion_prueba', 'SELECT table_name AS name FROM information_schema.tables WHERE table_schema = DATABASE()');
        } finally {
            DB::purge('restauracion_prueba');
            $admin->statement("DROP DATABASE IF EXISTS `{$temporary}`");
        }
    }

    private function testRestoreSqlite(string $copy): array
    {
        config(['database.connections.restauracion_prueba' => ['driver' => 'sqlite', 'database' => $copy, 'prefix' => '']]);

        try {
            return $this->compareTables('restauracion_prueba', "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'");
        } finally {
            DB::purge('restauracion_prueba');
        }
    }

    /**
     * Cuenta filas tabla por tabla. Las diferencias son normales si hubo actividad después del
     * respaldo; lo que importa es que las tablas existan y tengan datos legibles.
     */
    private function compareTables(string $restoredConnection, string $listTablesSql): array
    {
        $restored = DB::connection($restoredConnection);
        $tables = collect($restored->select($listTablesSql))->pluck('name')->sort()->values();

        if ($tables->isEmpty()) {
            throw new RuntimeException('La base restaurada no tiene tablas.');
        }

        $rows = 0;
        $differences = [];

        foreach ($tables as $table) {
            $restoredCount = (int) $restored->table($table)->count();
            $liveCount = (int) rescue(fn () => DB::table($table)->count(), -1, report: false);
            $rows += $restoredCount;

            if ($restoredCount !== $liveCount) {
                $differences[$table] = ['live' => $liveCount, 'restored' => $restoredCount];
            }
        }

        return ['tables' => $tables->count(), 'rows' => $rows, 'differences' => $differences];
    }

    // ── Utilidades ───────────────────────────────────────────────────────────

    private function runProcess(array $command, array $environment): void
    {
        $process = new Process($command, base_path(), $environment, null, (int) config('operations.backups.timeout', 900));
        $process->run();

        if (! $process->isSuccessful()) {
            $error = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            throw new RuntimeException(basename((string) $command[0]).' falló: '.Str::limit($error, 500));
        }
    }

    private function compress(string $plain, string $target): void
    {
        $input = fopen($plain, 'rb');
        $output = gzopen($target, 'wb6');

        while (! feof($input)) {
            gzwrite($output, (string) fread($input, 1024 * 512));
        }

        fclose($input);
        gzclose($output);
        @unlink($plain);
    }

    private function decompress(string $dump): string
    {
        $target = sys_get_temp_dir().'/bida-restauracion-'.Str::random(8).'.sql';
        $input = gzopen($dump, 'rb');
        $output = fopen($target, 'wb');

        while (! gzeof($input)) {
            fwrite($output, (string) gzread($input, 1024 * 512));
        }

        gzclose($input);
        fclose($output);

        return $target;
    }
}
