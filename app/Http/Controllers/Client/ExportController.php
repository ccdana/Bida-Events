<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateInvitationExport;
use App\Models\Invitation;
use App\Models\InvitationExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Exportaciones del cliente. El archivo se arma en segundo plano: el panel muestra el estado y,
 * cuando está listo, el enlace de descarga. La pertenencia la revisa la policy de la invitación.
 */
class ExportController extends Controller
{
    public function store(Request $request, Invitation $invitation, string $type)
    {
        abort_unless(isset(InvitationExport::TYPES[$type]), 404);

        $export = InvitationExport::create([
            'invitation_id' => $invitation->id,
            'user_id' => $request->user()->id,
            'type' => $type,
            'status' => InvitationExport::PENDING,
        ]);

        GenerateInvitationExport::dispatch($export);

        // Con la cola en modo inmediato el archivo ya está listo al volver
        $export->refresh();

        if ($request->wantsJson()) {
            return response()->json($this->payload($export));
        }

        return back()->with('export', $this->payload($export));
    }

    public function status(InvitationExport $export)
    {
        $this->authorizeExport($export);

        return response()->json($this->payload($export));
    }

    public function download(InvitationExport $export)
    {
        $this->authorizeExport($export);
        abort_unless($export->isReady(), 404);

        return Storage::disk('local')->download($export->path, $export->filename());
    }

    private function authorizeExport(InvitationExport $export): void
    {
        abort_unless((int) $export->user_id === (int) auth()->id(), 403);
    }

    /** @return array<string, mixed> */
    private function payload(InvitationExport $export): array
    {
        return [
            'id' => $export->id,
            'status' => $export->status,
            'label' => $export->label(),
            'error' => $export->error,
            'status_url' => route('client.export.status', $export),
            'download_url' => $export->isReady() ? route('client.export.download', $export) : null,
        ];
    }
}
