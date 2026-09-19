<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Relato de la tarjeta «Nuestra historia»: lo que cuenta cada acto además de los momentos. */
class CardStory extends Model
{
    protected $table = 'card_stories';

    protected $fillable = [
        'invitation_id',
        'first_impression',
        'quote_key',
        'anecdote_title',
        'anecdote',
        'anecdote_photo',
        'anecdote_photo_alt',
        'reflection',
        'promise',
    ];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
