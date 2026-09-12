<?php

namespace Tests\Concerns;

use App\Models\EventType;
use App\Models\Invitation;
use Illuminate\Support\Str;

trait CreatesInvitations
{
    protected function createInvitation(array $attributes = []): Invitation
    {
        $eventType = EventType::firstOrCreate(['slug' => 'xv-anos'], ['name' => 'XV Años']);

        return Invitation::create(array_merge([
            'event_type_id' => $eventType->id,
            'slug' => 'xv-'.Str::lower(Str::random(8)),
            'template' => 'invitations.templates.xv-premium',
            'title' => 'XV Años de prueba',
            'event_date' => now()->addMonths(3)->setTime(18, 0),
            'status' => 'active',
            'expires_at' => now()->addMonths(9),
        ], $attributes));
    }
}
