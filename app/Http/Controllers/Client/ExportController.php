<?php

namespace App\Http\Controllers\Client;

use App\Exports\GuestsExport;
use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function guestsExcel(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        return Excel::download(
            new GuestsExport($invitation),
            "confirmados-{$invitation->slug}.xlsx"
        );
    }

    public function guestsPdf(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $guests = $invitation->guests()->orderBy('name')->get();

        $pdf = Pdf::loadView('client.exports.guests-pdf', compact('invitation', 'guests'));

        return $pdf->download("confirmados-{$invitation->slug}.pdf");
    }

    public function invitationPdf(Invitation $invitation)
    {
        abort_unless($invitation->user_id === auth()->id(), 403);

        $modulos = $invitation->modulesData->pluck('json_data', 'feature_code')->toArray();

        $pdf = Pdf::loadView('client.exports.invitation-pdf', compact('invitation', 'modulos'));

        return $pdf->download("invitacion-{$invitation->slug}.pdf");
    }
}
