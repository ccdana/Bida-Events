<?php

namespace App\Jobs;

use App\Exports\GuestReportExport;
use App\Models\InvitationExport;
use App\Services\InvitationModuleService;
use App\Support\Pdf\PdfAssets;
use App\ViewModels\Client\GuestReportData;
use App\ViewModels\Client\InvitationPrintData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * Arma el Excel o el PDF que pidió el cliente. Va en cola porque una lista de invitados grande
 * puede tardar más de lo que conviene tener a alguien esperando en el navegador.
 */
class GenerateInvitationExport implements ShouldQueue
{
    use Queueable;

    private const GUEST_COLUMNS = [
        'id', 'invitation_id', 'name', 'phone', 'passes_allocated', 'passes_confirmed',
        'status', 'dietary_restrictions', 'table_number', 'confirmed_at', 'qr_code_token',
    ];

    public int $tries = 3;

    public array $backoff = [10, 60];

    public int $timeout = 180;

    public function __construct(public InvitationExport $export) {}

    public function handle(
        GuestReportData $report,
        InvitationPrintData $print,
        InvitationModuleService $modules,
        PdfAssets $assets
    ): void {
        $invitation = $this->export->invitation;
        $path = "exports/{$this->export->id}/".$this->export->filename();

        match ($this->export->type) {
            'guests-excel' => Excel::store(
                new GuestReportExport($report->make($invitation, $this->guests())),
                $path,
                'local'
            ),
            'guests-pdf' => Storage::disk('local')->put($path, $this->pdf(
                'client.exports.guests-pdf',
                $report->make($invitation, $this->guests()) + ['logo' => $assets->logo()]
            )),
            'invitation-pdf' => Storage::disk('local')->put($path, $this->pdf(
                'client.exports.invitation-pdf',
                $print->make($invitation, $modules->storedModules($invitation))
            )),
            default => throw new \InvalidArgumentException("Tipo de exportación desconocido: {$this->export->type}"),
        };

        $this->export->update([
            'status' => InvitationExport::READY,
            'path' => $path,
            'error' => null,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        $this->export->update([
            'status' => InvitationExport::FAILED,
            'error' => Str::limit((string) $exception?->getMessage(), 500),
        ]);
    }

    private function guests()
    {
        return $this->export->invitation->guests()
            ->select(self::GUEST_COLUMNS)
            ->orderBy('name')
            ->get();
    }

    private function pdf(string $view, array $data): string
    {
        return Pdf::loadView($view, $data)
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->output();
    }
}
