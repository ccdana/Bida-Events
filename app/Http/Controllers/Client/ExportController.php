<?php

namespace App\Http\Controllers\Client;

use App\Exports\GuestReportExport;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use App\Support\Pdf\PdfAssets;
use App\ViewModels\Client\GuestReportData;
use App\ViewModels\Client\InvitationPrintData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    private const GUEST_COLUMNS = [
        'id', 'invitation_id', 'name', 'phone', 'passes_allocated', 'passes_confirmed',
        'status', 'dietary_restrictions', 'table_number', 'confirmed_at', 'qr_code_token',
    ];

    /** Excel con resumen, listado completo, invitados por contactar y alimentación. */
    public function guestsExcel(Invitation $invitation, GuestReportData $report)
    {
        $this->authorizeOwner($invitation);

        return Excel::download(
            new GuestReportExport($report->make($invitation, $this->guests($invitation))),
            "invitados-{$invitation->slug}.xlsx",
        );
    }

    /** PDF para planificar: cifras clave, qué hacer ahora y listas por estado. */
    public function guestsPdf(Invitation $invitation, GuestReportData $report, PdfAssets $assets)
    {
        $this->authorizeOwner($invitation);

        $data = $report->make($invitation, $this->guests($invitation)) + ['logo' => $assets->logo()];

        return Pdf::loadView('client.exports.guests-pdf', $data)
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->download("reporte-invitados-{$invitation->slug}.pdf");
    }

    /** Invitación lista para imprimir, con el diseño y los datos de la plantilla del cliente. */
    public function invitationPdf(Invitation $invitation, InvitationPrintData $print, InvitationModuleService $moduleService)
    {
        $this->authorizeOwner($invitation);

        $data = $print->make($invitation, $moduleService->storedModules($invitation));

        return Pdf::loadView('client.exports.invitation-pdf', $data)
            ->setPaper('a4')
            ->setOption('isFontSubsettingEnabled', true)
            ->download("invitacion-{$invitation->slug}.pdf");
    }

    private function authorizeOwner(Invitation $invitation): void
    {
        abort_unless($invitation->user_id === auth()->id(), 403);
    }

    private function guests(Invitation $invitation): Collection
    {
        return $invitation->guests()->select(self::GUEST_COLUMNS)->orderBy('name')->get();
    }
}
