<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Momento clave de «Nuestra historia»: cuándo fue, qué pasó y su foto. */
class CardStoryMoment extends Model
{
    protected $table = 'card_story_moments';

    protected $fillable = ['invitation_id', 'when_label', 'title', 'description', 'photo', 'photo_alt', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
