<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'invitation_id',
        'poll_id',
        'option_index',
        'guest_id',
        'voter_key',
        'created_at',
    ];

    protected $casts = [
        'option_index' => 'integer',
        'created_at' => 'datetime',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
