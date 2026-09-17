<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /** Ejemplos de prendas o accesorios de una sugerencia, en orden. */
    public function examples(): HasMany
    {
        return $this->hasMany(InvitationDressCodeExample::class, 'dress_code_item_id')->orderBy('sort_order')->orderBy('id');
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
