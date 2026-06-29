<?php

namespace App\Http\Controllers\Client;

use App\Exports\GuestsExport;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\ViewModels\Client\InvitationExportViewData;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function guestsExcel(Invitation $invitation, InvitationExportViewData $viewData)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'phone', 'passes_allocated', 'passes_confirmed', 'status', 'dietary_restrictions', 'table_number', 'confirmed_at')
            ->orderBy('name')
            ->get();

        $stats = $viewData->buildGuestStats($guests);
        $modulos = $invitation->modulesData->pluck('json_data', 'feature_code')->toArray();

        return Excel::download(
            new GuestsExport($invitation, $stats, $modulos, $guests, $viewData->buildGuestRows($guests)),
            "confirmados-{$invitation->slug}.xlsx"
        );
    }

    public function guestsPdf(Invitation $invitation, InvitationExportViewData $viewData)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $guests = $invitation->guests()
            ->select('id', 'invitation_id', 'name', 'phone', 'passes_allocated', 'passes_confirmed', 'status', 'dietary_restrictions', 'table_number', 'confirmed_at')
            ->orderBy('name')
            ->get();

        $stats = $viewData->buildGuestStats($guests);
        $summary = $viewData->buildSummaryInsights($guests, $stats);

        $pdf = Pdf::loadView('pages.client.exports.guests-pdf', [
            'invitation' => $invitation,
            'guests' => $guests,
            'stats' => $stats,
            'summary' => $summary,
            'guestRows' => $viewData->buildGuestRows($guests),
        ]);

        return $pdf->download("confirmados-{$invitation->slug}.pdf");
    }

    public function invitationPdf(Invitation $invitation, InvitationExportViewData $viewData)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $invitation->loadMissing(['modulesData']);
        $modulos = $invitation->modulesData->pluck('json_data', 'feature_code')->toArray();
        $guests = $invitation->guests()->select('id', 'invitation_id', 'status', 'passes_allocated', 'passes_confirmed')->get();
        $stats = $viewData->buildGuestStats($guests);
        $pdf = Pdf::loadView('pages.client.exports.invitation-pdf', array_merge(
            $viewData->buildInvitationPdfViewData($invitation, $modulos, $stats),
            [
                'guestRows' => $viewData->buildGuestRows($guests),
            ]
        ));

        return $pdf->download("invitacion-{$invitation->slug}.pdf");
    }
}
