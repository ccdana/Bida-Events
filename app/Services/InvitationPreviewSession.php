<?php

namespace App\Services;

use App\Models\Invitation;

class InvitationPreviewSession
{
    public static function keyFor(?Invitation $invitation): string
    {
        return $invitation ? "invitation.{$invitation->id}" : 'draft';
    }

    public static function sessionKey(string $key): string
    {
        return "admin_invitation_preview.{$key}";
    }

    public static function seed(?Invitation $invitation, array $payload): void
    {
        session()->forget('admin_invitation_preview');

        $key = self::keyFor($invitation);
        session([
            self::sessionKey($key) => array_merge($payload, [
                'revision' => $invitation?->updated_at?->timestamp ?? now()->timestamp,
            ]),
        ]);
    }

    public static function forget(?Invitation $invitation): void
    {
        session()->forget(self::sessionKey(self::keyFor($invitation)));
    }

    public static function forgetDraft(): void
    {
        session()->forget(self::sessionKey('draft'));
    }

    public static function get(string $key): ?array
    {
        $payload = session(self::sessionKey($key));

        return is_array($payload) ? $payload : null;
    }

    public static function store(string $key, array $payload): void
    {
        session([
            self::sessionKey($key) => array_merge($payload, [
                'revision' => now()->timestamp,
            ]),
        ]);
    }

    public static function payloadFromInvitation(Invitation $invitation, array $modulos): array
    {
        return [
            'title' => $invitation->title,
            'slug' => $invitation->slug,
            'template' => $invitation->template,
            'event_date' => $invitation->event_date?->format('Y-m-d\TH:i'),
            'expires_at' => $invitation->expires_at?->format('Y-m-d'),
            'modulos' => $modulos,
        ];
    }

}
