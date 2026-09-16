<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use App\Services\InvitationCacheService;
use App\Support\YouTubeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function show(string $slug, ?string $token = null)
    {
        $invitation = $this->findPublished($slug);

        $guest = null;
        if ($token) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('qr_code_token', $token)
                ->select('id', 'invitation_id', 'name', 'qr_code_token', 'passes_allocated', 'status', 'passes_confirmed', 'confirmed_at')
                ->firstOrFail();
        }

        $response = $this->render($invitation, $guest);

        if ($invitation->updated_at) {
            $response->setLastModified($invitation->updated_at);
            $response->setEtag(sha1($invitation->id.'-'.$invitation->updated_at->timestamp));
        }

        return $response;
    }

    /**
     * Invitación de muestra para los teléfonos de la home: se ve como la de un invitado real (confirmación,
     * encuestas, playlist, fotomural), pero las respuestas se simulan en el navegador y nada se guarda.
     * Solo existe para config('bida.demo_invitations'). Con ?portada=1 la apertura se abre sola.
     */
    public function demo(Request $request, string $slug)
    {
        abort_unless(in_array($slug, config('bida.demo_invitations', []), true), 404);

        $autoplay = $request->boolean('portada');

        // La apertura del teléfono de la portada va sin invitado; la muestra interactiva, con uno ficticio
        $response = $this->render($this->findPublished($slug), $autoplay ? null : $this->demoGuest(), [
            'isDemo' => true,
            'coverAutoplay' => $autoplay,
        ]);

        $response->headers->set('Cache-Control', 'no-store, max-age=0');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    protected function findPublished(string $slug): Invitation
    {
        return Invitation::query()
            ->with(['eventType', 'user'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();
    }

    /** Invitado ficticio, sin guardar, para que la muestra enseñe el enlace personal, la confirmación y el pase QR. */
    protected function demoGuest(): Guest
    {
        return (new Guest)->forceFill([
            'name' => 'Familia Pérez',
            'passes_allocated' => 3,
            'passes_confirmed' => 0,
            'status' => 'pending',
            'qr_code_token' => 'MUESTRABIDAEVENTS',
        ]);
    }

    protected function render(Invitation $invitation, ?Guest $guest, array $extra = [])
    {
        $invitation->clearModulesCache();

        $modulos = $this->resolveModules($invitation);

        $rawFlags = $modulos['config']['modulos'] ?? [];
        $config = $modulos['config'] ?? [];
        $template = InvitationDefaults::resolveTemplate($invitation->template ?: ($config['template'] ?? null));

        $pollResults = $this->getPollResults($invitation, $modulos);

        $calendarUrl = $this->moduleService->googleCalendarUrl(
            $invitation,
            $modulos['ubicacion'] ?? []
        );

        $playlistSongs = $this->getPlaylistSongs($invitation);
        $fotomuralPhotos = $this->getFotomuralPhotos($invitation);

        if (! array_key_exists('playlist', $rawFlags) && ! empty($playlistSongs)) {
            $modulos['config']['modulos']['playlist'] = true;
        }

        if (! array_key_exists('fotomural', $rawFlags) && ! empty($fotomuralPhotos)) {
            $modulos['config']['modulos']['fotomural'] = true;
        }

        return response()->view($template, [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'guest' => $guest,
            'pollResults' => $pollResults,
            'calendarUrl' => $calendarUrl,
            'playlistSongs' => $playlistSongs,
            'fotomuralPhotos' => $fotomuralPhotos,
        ] + $extra);
    }

    protected function resolveModules(Invitation $invitation): array
    {
        $modulos = InvitationCacheService::remember(
            "invitation.{$invitation->slug}.modules",
            InvitationCacheService::invitationTtl(),
            fn () => $this->moduleService->loadForDisplay($invitation)
        );

        return array_replace_recursive(InvitationDefaults::emptyModules(), is_array($modulos) ? $modulos : []);
    }

    private function getPollResults(Invitation $invitation, array $modulos): array
    {
        $pollResults = [];
        if (empty($modulos['encuestas']['preguntas'])) {
            return $pollResults;
        }

        return InvitationCacheService::remember(
            "invitation.{$invitation->id}.polls",
            (int) config('optimizations.cache.invitations.polls_ttl', 300),
            fn () => $this->buildPollResults($invitation, $modulos)
        );
    }

    private function buildPollResults(Invitation $invitation, array $modulos): array
    {
        $optionCounts = [];
        foreach ($modulos['encuestas']['preguntas'] as $poll) {
            $optionCounts[(string) $poll['id']] = count($poll['opciones'] ?? []);
        }

        return $this->moduleService->pollResultsFor($invitation, $optionCounts);
    }

    private function getPlaylistSongs(Invitation $invitation): array
    {
        return InvitationCacheService::remember(
            "invitation.{$invitation->id}.playlist",
            (int) config('optimizations.cache.invitations.playlist_ttl', 120),
            fn () => $this->buildPlaylistSongs($invitation)
        );
    }

    private function buildPlaylistSongs(Invitation $invitation): array
    {
        return $invitation->contributions()
            ->where('type', 'song_request')
            ->visible()
            ->select('id', 'invitation_id', 'guest_id', 'type', 'content_text', 'created_at')
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(50)
            ->get()
            ->map(fn ($c) => YouTubeHelper::formatContribution($c))
            ->values()
            ->all();
    }

    private function getFotomuralPhotos(Invitation $invitation): array
    {
        return InvitationCacheService::remember(
            "invitation.{$invitation->id}.fotomural",
            (int) config('optimizations.cache.invitations.fotomural_ttl', 120),
            fn () => $this->buildFotomuralPhotos($invitation)
        );
    }

    private function buildFotomuralPhotos(Invitation $invitation): array
    {
        return $invitation->contributions()
            ->where('type', 'live_photo')
            ->visible()
            ->select('id', 'invitation_id', 'guest_id', 'file_path', 'created_at')
            ->with('guest:id,name')
            ->latest('created_at')
            ->take(60)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'url' => \App\Support\CloudinaryImage::url($c->file_path, 800),
                'srcset' => \App\Support\CloudinaryImage::srcset($c->file_path, [320, 640, 960]),
                'guest' => $c->guest?->name,
                'at' => $c->created_at?->diffForHumans(),
            ])
            ->values()
            ->all();
    }
}
