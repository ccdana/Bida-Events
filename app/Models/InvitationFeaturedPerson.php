<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationFeaturedPerson extends Model
{
    protected $table = 'invitation_featured_people';

    protected $fillable = [
        'invitation_id',
        'group',
        'name_key',
        'name',
        'initials',
        'role',
        'detail',
        'message',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
