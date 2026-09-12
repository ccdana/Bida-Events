<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvitationPoll extends Model
{
    protected $fillable = [
        'invitation_id',
        'poll_key',
        'question',
        'type',
        'is_enabled',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'meta' => 'array',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(InvitationPollOption::class, 'poll_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class, 'invitation_poll_id');
    }
}
