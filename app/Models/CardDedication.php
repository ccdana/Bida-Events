<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Dedicatoria de una tarjeta: de quién, para quién, mensaje y firma. */
class CardDedication extends Model
{
    protected $table = 'card_dedications';

    protected $fillable = ['invitation_id', 'from_name', 'to_name', 'message', 'signature'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
