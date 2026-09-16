<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use App\Services\InvitationStructuredDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class MigrateInvitationJsonModules extends Command
{
    protected $signature = 'invitations:migrate-json
                            {--dry-run : Ejecuta la migración dentro de una transacción y la revierte al final}
                            {--invitation=* : Limita la migración a IDs o slugs concretos}
                            {--force : Vuelve a copiar invitaciones que ya tienen datos normalizados}';

    protected $description = 'Copia los módulos de invitation_data.json_data a sus tablas normalizadas (config, itinerario, galería, encuestas, ubicación, destacados, vestimenta, regalos y medios)';

    public function handle(InvitationStructuredDataService $structuredData): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $filters = array_values(array_filter((array) $this->option('invitation'), fn ($value) => $value !== ''));

        $query = Invitation::query()->with(['modulesData', 'settings']);

        if ($filters !== []) {
            $ids = array_map('intval', array_filter($filters, 'ctype_digit'));
            $query->where(fn ($q) => $q->whereIn('id', $ids)->orWhereIn('slug', $filters));
        }

        $this->components->info($dryRun
            ? 'Modo simulación: los cambios se revierten al terminar cada invitación.'
            : 'Migrando módulos JSON a tablas normalizadas.');

        $totals = ['migradas' => 0, 'omitidas' => 0, 'fallidas' => 0];
        $rows = [];
        $details = [];

        foreach ($query->lazyById(50) as $invitation) {
            $label = "#{$invitation->id} {$invitation->slug}";

            if ($invitation->settings && ! $force) {
                $totals['omitidas']++;
                $rows[] = [$invitation->id, $invitation->slug, 'omitida (ya migrada)', '-', '-', '-', 0];
                continue;
            }

            $modules = $invitation->modules;

            if ($modules === []) {
                $totals['omitidas']++;
                $rows[] = [$invitation->id, $invitation->slug, 'omitida (sin JSON)', '-', '-', '-', 0];
                continue;
            }

            DB::beginTransaction();

            try {
                $report = $structuredData->sync($invitation, $modules);
                $differences = $structuredData->verify($invitation, $modules);

                if ($differences === [] && ! $dryRun) {
                    DB::commit();
                } else {
                    DB::rollBack();
                }
            } catch (Throwable $exception) {
                DB::rollBack();
                $totals['fallidas']++;
                $rows[] = [$invitation->id, $invitation->slug, 'error', '-', '-', '-', 0];
                $details[] = "{$label} error: {$exception->getMessage()}";
                continue;
            }

            if ($differences === []) {
                $totals['migradas']++;
            } else {
                $totals['fallidas']++;
            }

            $rows[] = [
                $invitation->id,
                $invitation->slug,
                match (true) {
                    $differences !== [] => 'fallida (verificación)',
                    $dryRun => 'simulada',
                    default => 'migrada',
                },
                "{$report['itinerario']['created']}/{$report['itinerario']['detected']}",
                "{$report['galeria']['created']}/{$report['galeria']['detected']}",
                "{$report['encuestas']['created']}/{$report['encuestas']['detected']} ({$report['encuestas']['options']})",
                $this->otherModulesSummary($report),
                count($report['skipped']),
            ];

            foreach ($report['skipped'] as $message) {
                $details[] = "{$label} omitido: {$message}";
            }

            foreach ($report['warnings'] as $message) {
                $details[] = "{$label} aviso: {$message}";
            }

            foreach ($differences as $message) {
                $details[] = "{$label} diferencia: {$message}";
            }
        }

        $this->table(
            ['ID', 'Slug', 'Estado', 'Itinerario', 'Galería', 'Encuestas (opciones)', 'Otros módulos', 'Omitidos'],
            $rows
        );

        if ($details !== []) {
            $this->components->bulletList($details);
        }

        $summary = sprintf(
            '%s: %d, omitidas: %d, fallidas: %d.',
            $dryRun ? 'Simuladas sin errores' : 'Migradas',
            $totals['migradas'],
            $totals['omitidas'],
            $totals['fallidas']
        );

        if ($totals['fallidas'] > 0) {
            $this->components->error($summary);

            return self::FAILURE;
        }

        $this->components->info($summary);

        return self::SUCCESS;
    }

    /** Resumen corto de los módulos que se normalizaron después: ubicación, destacados, etc. */
    protected function otherModulesSummary(array $report): string
    {
        $created = 0;
        $detected = 0;

        foreach (['ubicacion', 'destacados', 'dress_code', 'regalos', 'media', 'post_evento'] as $module) {
            $created += $report[$module]['created'] ?? 0;
            $detected += $report[$module]['detected'] ?? 0;
        }

        return "{$created}/{$detected}";
    }
}
