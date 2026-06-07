<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function show(string $slug, ?string $token = null)
    {
        $invitation = Invitation::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $guest = null;
        if ($token) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('qr_code_token', $token)
                ->firstOrFail();
        }

        $modulos = $this->moduleService->loadForDisplay($invitation);
        $config = $modulos['config'] ?? [];
        $template = $invitation->template ?: ($config['template'] ?? 'invitations.templates.xv-premium');

        $pollResults = [];
        if (! empty($modulos['encuestas']['preguntas'])) {
            foreach ($modulos['encuestas']['preguntas'] as $poll) {
                $pollResults[$poll['id']] = $this->moduleService->pollResults(
                    $invitation,
                    $poll['id'],
                    count($poll['opciones'])
                );
            }
        }

        $calendarUrl = $this->moduleService->googleCalendarUrl(
            $invitation,
            $modulos['ubicacion'] ?? []
        );

        return view($template, compact(
            'invitation',
            'modulos',
            'guest',
            'pollResults',
            'calendarUrl'
        ));
    }
}
