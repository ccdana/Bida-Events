<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function store(Request $request)
    {
        $payload = $this->extractPayload($request);
        session(['admin_invitation_preview' => $payload]);

        return response()->json(['success' => true]);
    }

    public function frame()
    {
        $payload = session('admin_invitation_preview');

        if (! $payload) {
            return response('<html><body style="font-family:sans-serif;padding:2rem;color:#888">Configura la invitación para ver la vista previa</body></html>');
        }

        $modulos = $payload['modulos'] ?? [];
        $config = $modulos['config'] ?? [];
        $template = $payload['template'] ?? ($config['template'] ?? 'invitations.templates.xv-premium');

        $invitation = new Invitation([
            'title' => $payload['title'] ?? 'Vista previa',
            'slug' => $payload['slug'] ?? 'preview',
            'template' => $template,
            'event_date' => Carbon::parse($payload['event_date'] ?? now()->addMonths(3)),
            'status' => 'active',
            'expires_at' => Carbon::parse($payload['expires_at'] ?? now()->addYear()),
        ]);

        $pollResults = [];
        foreach ($modulos['encuestas']['preguntas'] ?? [] as $poll) {
            $pollResults[$poll['id']] = array_fill(0, count($poll['opciones'] ?? []), 0);
        }

        $calendarUrl = $this->moduleService->googleCalendarUrl($invitation, $modulos['ubicacion'] ?? []);

        return view($template, [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'guest' => null,
            'pollResults' => $pollResults,
            'calendarUrl' => $calendarUrl,
            'playlistSongs' => [],
            'fotomuralPhotos' => [],
            'isPreview' => true,
        ]);
    }

    protected function extractPayload(Request $request): array
    {
        $moduleCodes = [
            'config', 'bienvenida', 'ubicacion', 'itinerario', 'dress_code',
            'destacados', 'galeria', 'musica', 'video', 'playlist', 'hashtag',
            'encuestas', 'regalos', 'post_evento', 'rsvp',
        ];

        $modulos = [];
        foreach ($moduleCodes as $code) {
            $raw = $request->input("modulos.{$code}");
            if ($raw === null) {
                continue;
            }
            $modulos[$code] = is_string($raw) ? json_decode($raw, true) : $raw;
        }

        return [
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'template' => $request->input('template'),
            'event_date' => $request->input('event_date'),
            'expires_at' => $request->input('expires_at'),
            'modulos' => $modulos,
        ];
    }
}
