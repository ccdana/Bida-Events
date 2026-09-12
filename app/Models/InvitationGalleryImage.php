<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationGalleryImage extends Model
{
    public const COLLECTION_GALLERY = 'gallery';

    protected $fillable = [
        'invitation_id',
        'collection',
        'url',
        'media_type',
        'alt_text',
        'is_cover',
        'status',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'meta' => 'array',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
