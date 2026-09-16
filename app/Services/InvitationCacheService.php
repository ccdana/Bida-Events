<?php

namespace App\Services;

use App\Models\Invitation;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
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

    /**
     * Devuelve el valor cacheado y, si no está, lo arma una sola vez: quien llega primero toma el
     * candado y los demás esperan su resultado en lugar de rearmar todos lo mismo (cache stampede),
     * que es justo lo que pasaría el día del evento cuando todos abren el enlace a la vez.
     */
    public static function remember(string $key, int $ttl, Closure $callback): mixed
    {
        if (! self::enabled()) {
            return $callback();
        }

        $cached = Cache::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $lock = Cache::lock($key.'.lock', 10);

        try {
            $lock->block(3);
        } catch (LockTimeoutException) {
            // Si la espera se agota, se arma sin cachear: es preferible a dejar al invitado esperando
            return $callback();
        }

        try {
            return Cache::remember($key, $ttl, $callback);
        } finally {
            $lock->release();
        }
    }

    public static function invalidate(Invitation $invitation, ?string $previousSlug = null): void
    {
        $slugs = array_filter([$invitation->slug, $previousSlug]);

        foreach ($slugs as $slug) {
            Cache::forget("invitation.{$slug}");
            Cache::forget("invitation.{$slug}.modules");
        }

        self::forgetPolls($invitation->id);
        self::forgetPlaylist($invitation->id);
        self::forgetFotomural($invitation->id);
    }

    public static function forgetPolls(int $invitationId): void
    {
        Cache::forget("invitation.{$invitationId}.polls");
    }

    public static function forgetPlaylist(int $invitationId): void
    {
        Cache::forget("invitation.{$invitationId}.playlist");
    }

    public static function forgetFotomural(int $invitationId): void
    {
        Cache::forget("invitation.{$invitationId}.fotomural");
    }

    /**
     * Ya no pre-cacheamos el modelo Eloquent (provocaba datos obsoletos al publicar).
     */
    public static function warmup(Invitation $invitation): void
    {
        if (! self::enabled()) {
            return;
        }

        $invitation->clearModulesCache();

        $modules = app(InvitationModuleService::class)->resolveModules($invitation);

        Cache::put(
            "invitation.{$invitation->slug}.modules",
            $modules,
            self::invitationTtl()
        );
    }
}
