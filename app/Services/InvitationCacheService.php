<?php

namespace App\Services;

use App\Models\Invitation;
use Illuminate\Support\Facades\Cache;

class InvitationCacheService
{
    public static function enabled(): bool
    {
        return (bool) config('optimizations.cache.enabled', false);
    }

    public static function invitationTtl(): int
    {
        return (int) config('optimizations.cache.invitations.ttl', 3600);
    }

    public static function invalidate(Invitation $invitation, ?string $previousSlug = null): void
    {
        $slugs = array_filter([$invitation->slug, $previousSlug]);

        foreach ($slugs as $slug) {
            Cache::forget("invitation.{$slug}");
            Cache::forget("invitation.{$slug}.modules");
        }

        Cache::forget("invitation.{$invitation->id}.polls");
        Cache::forget("invitation.{$invitation->id}.playlist");
        Cache::forget("invitation.{$invitation->id}.fotomural");
    }

    /**
     * Ya no pre-cacheamos el modelo Eloquent (provocaba datos obsoletos al publicar).
     */
    public static function warmup(Invitation $invitation): void
    {
        if (! self::enabled()) {
            return;
        }

        $invitation->loadMissing('modulesData');
        $invitation->clearModulesCache();

        $moduleService = app(InvitationModuleService::class);
        $modules = $moduleService->normalizeModules($invitation->modules);

        Cache::put(
            "invitation.{$invitation->slug}.modules",
            $modules,
            self::invitationTtl()
        );
    }
}
