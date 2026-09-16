<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationMedia extends Model
{
    public const TYPE_AUDIO = 'audio';

    public const TYPE_VIDEO = 'video';

    protected $table = 'invitation_media';

    protected $fillable = [
        'invitation_id',
        'type',
        'title',
        'url',
        'poster_url',
        'autoplay',
        'status',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'autoplay' => 'boolean',
        'meta' => 'array',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
