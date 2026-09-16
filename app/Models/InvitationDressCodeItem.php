<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationDressCodeItem extends Model
{
    public const KIND_SUGGESTION = 'suggestion';

    public const KIND_COLOR = 'color';

    public const KIND_AVOID = 'avoid';

    protected $fillable = [
        'invitation_id',
        'kind',
        'audience',
        'title',
        'description',
        'image_url',
        'color_hex',
        'examples',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'examples' => 'array',
        'meta' => 'array',
        'sort_order' => 'integer',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
