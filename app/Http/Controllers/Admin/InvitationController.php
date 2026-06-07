<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function edit(Invitation $invitation)
    {
        $invitation->load('modulesData', 'eventType');
        $modulos = $invitation->modules;
        $templates = [
            'invitations.templates.xv-premium' => 'XV Años Premium',
        ];

        return view('admin.invitations.edit', compact('invitation', 'modulos', 'templates'));
    }

    public function update(Request $request, Invitation $invitation)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:invitations,slug,'.$invitation->id],
            'template' => ['required', 'string'],
            'event_date' => ['required', 'date'],
            'status' => ['required', 'in:draft,active,suspended,expired'],
            'expires_at' => ['required', 'date'],
        ]);

        $invitation->update($validated);

        $this->syncModulesFromRequest($request, $invitation);

        return redirect()
            ->route('admin.invitations.edit', $invitation)
            ->with('success', 'Invitación actualizada correctamente.');
    }

    protected function syncModulesFromRequest(Request $request, Invitation $invitation): void
    {
        $moduleCodes = [
            'config', 'bienvenida', 'ubicacion', 'itinerario', 'dress_code',
            'destacados', 'galeria', 'musica', 'video', 'playlist', 'hashtag',
            'encuestas', 'regalos', 'post_evento', 'rsvp',
        ];

        foreach ($moduleCodes as $code) {
            $raw = $request->input("modulos.{$code}");
            if ($raw === null) {
                continue;
            }

            $data = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($data)) {
                $this->moduleService->syncModule($invitation, $code, $data);
            }
        }
    }
}
