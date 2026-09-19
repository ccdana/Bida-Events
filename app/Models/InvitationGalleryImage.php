<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationGalleryImage extends Model
{
    public const COLLECTION_GALLERY = 'gallery';

    public const COLLECTION_POST_EVENT = 'post_event';

    // Tarjeta «Libro de aventuras»
    public const COLLECTION_COLLAGE = 'collage';

    public const COLLECTION_FRAMES = 'marcos';

    public const COLLECTION_MEMORY_GAME = 'memoria';

    protected $fillable = [
        'invitation_id',
        'collection',
        'url',
        'media_type',
        'alt_text',
        'is_cover',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
