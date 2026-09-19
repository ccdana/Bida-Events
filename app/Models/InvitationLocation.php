<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationLocation extends Model
{
    protected $fillable = [
        'invitation_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'map_url',
        'image_url',
        'note',
        'sort_order',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
