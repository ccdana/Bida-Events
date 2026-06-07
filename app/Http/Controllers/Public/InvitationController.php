<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Services\InvitationModuleService;
use App\Support\YouTubeHelper;
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

        $playlistSongs = $invitation->contributions()
            ->where('type', 'song_request')
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(50)
            ->get()
            ->map(fn ($c) => YouTubeHelper::formatContribution($c))
            ->values()
            ->all();

        $fotomuralPhotos = $invitation->contributions()
            ->where('type', 'live_photo')
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(60)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'url' => $c->file_path,
                'guest' => $c->guest?->name,
                'at' => $c->created_at?->diffForHumans(),
            ])
            ->values()
            ->all();

        return view($template, compact(
            'invitation',
            'modulos',
            'guest',
            'pollResults',
            'calendarUrl',
            'playlistSongs',
            'fotomuralPhotos'
        ));
    }
}
