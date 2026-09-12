<?php

namespace App\Listeners;

use App\Events\GuestContributionSubmitted;
use App\Events\InvitationUpdated;
use App\Events\PollVoteSubmitted;
use App\Services\InvitationCacheService;

/**
 * Listener síncrono: la caché debe quedar consistente antes de responder la petición.
 * Laravel lo registra automáticamente por los tipos de cada método handle*.
 */
class RefreshInvitationCache
{
    public function handleInvitationUpdated(InvitationUpdated $event): void
    {
        InvitationCacheService::invalidate($event->invitation, $event->previousSlug);
        InvitationCacheService::warmup($event->invitation);
    }

    public function handleGuestContributionSubmitted(GuestContributionSubmitted $event): void
    {
        $invitationId = $event->contribution->invitation_id;

        match ($event->contribution->type) {
            'song_request' => InvitationCacheService::forgetPlaylist($invitationId),
            'live_photo' => InvitationCacheService::forgetFotomural($invitationId),
            default => null,
        };
    }

    public function handlePollVoteSubmitted(PollVoteSubmitted $event): void
    {
        InvitationCacheService::forgetPolls($event->vote->invitation_id);
    }
}
