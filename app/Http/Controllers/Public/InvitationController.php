<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Support\InvitationDefaults;
use App\Services\InvitationModuleService;
use App\Services\InvitationCacheService;
use App\Support\YouTubeHelper;
use Illuminate\Support\Facades\Cache;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationModuleService $moduleService
    ) {}

    public function show(string $slug, ?string $token = null)
    {
        $invitation = Invitation::query()
            ->with(['eventType', 'user', 'modulesData'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $invitation->clearModulesCache();

        $modulos = $this->resolveModules($invitation);

        $guest = null;
        if ($token) {
            $guest = Guest::where('invitation_id', $invitation->id)
                ->where('qr_code_token', $token)
                ->select('id', 'invitation_id', 'name', 'qr_code_token', 'passes_allocated', 'status', 'passes_confirmed', 'confirmed_at')
                ->firstOrFail();
        }

        $rawFlags = $modulos['config']['modulos'] ?? [];
        $config = $modulos['config'] ?? [];
        $template = $invitation->template ?: ($config['template'] ?? 'invitations.templates.xv-premium');

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

        $response = response()->view($template, [
            'invitation' => $invitation,
            'modulos' => $modulos,
            'guest' => $guest,
            'pollResults' => $pollResults,
            'calendarUrl' => $calendarUrl,
            'playlistSongs' => $playlistSongs,
            'fotomuralPhotos' => $fotomuralPhotos,
        ]);

        if ($invitation->updated_at) {
            $response->setLastModified($invitation->updated_at);
            $response->setEtag(sha1($invitation->id.'-'.$invitation->updated_at->timestamp));
        }

        return $response;
    }

    protected function resolveModules(Invitation $invitation): array
    {
        if (InvitationCacheService::enabled()) {
            $cacheKey = "invitation.{$invitation->slug}.modules";
            $cached = Cache::get($cacheKey);

            if (is_array($cached)) {
                return array_replace_recursive(InvitationDefaults::emptyModules(), $cached);
            }
        }

        $modulos = $this->moduleService->loadForDisplay($invitation);

        if (InvitationCacheService::enabled()) {
            Cache::put(
                "invitation.{$invitation->slug}.modules",
                $modulos,
                InvitationCacheService::invitationTtl()
            );
        }

        return array_replace_recursive(InvitationDefaults::emptyModules(), $modulos);
    }

    private function getPollResults(Invitation $invitation, array $modulos): array
    {
        $pollResults = [];
        if (empty($modulos['encuestas']['preguntas'])) {
            return $pollResults;
        }

        if (! InvitationCacheService::enabled()) {
            return $this->buildPollResults($invitation, $modulos);
        }

        return Cache::remember(
            "invitation.{$invitation->id}.polls",
            (int) config('optimizations.cache.invitations.polls_ttl', 300),
            fn () => $this->buildPollResults($invitation, $modulos)
        );
    }

    private function buildPollResults(Invitation $invitation, array $modulos): array
    {
        $results = [];
        foreach ($modulos['encuestas']['preguntas'] as $poll) {
            $results[$poll['id']] = $this->moduleService->pollResults(
                $invitation,
                $poll['id'],
                count($poll['opciones'])
            );
        }

        return $results;
    }

    private function getPlaylistSongs(Invitation $invitation): array
    {
        if (! InvitationCacheService::enabled()) {
            return $this->buildPlaylistSongs($invitation);
        }

        return Cache::remember(
            "invitation.{$invitation->id}.playlist",
            (int) config('optimizations.cache.invitations.playlist_ttl', 120),
            fn () => $this->buildPlaylistSongs($invitation)
        );
    }

    private function buildPlaylistSongs(Invitation $invitation): array
    {
        return $invitation->contributions()
            ->where('type', 'song_request')
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
        if (! InvitationCacheService::enabled()) {
            return $this->buildFotomuralPhotos($invitation);
        }

        return Cache::remember(
            "invitation.{$invitation->id}.fotomural",
            (int) config('optimizations.cache.invitations.fotomural_ttl', 120),
            fn () => $this->buildFotomuralPhotos($invitation)
        );
    }

    private function buildFotomuralPhotos(Invitation $invitation): array
    {
        return $invitation->contributions()
            ->where('type', 'live_photo')
            ->select('id', 'invitation_id', 'guest_id', 'file_path', 'created_at')
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
    }
}
