<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationItineraryItem extends Model
{
    protected $fillable = [
        'invitation_id',
        'time',
        'title',
        'icon',
        'description',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'meta' => 'array',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
