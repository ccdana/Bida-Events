<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Fecha importante de una tarjeta, con su contador («juntos desde»). */
class CardMilestone extends Model
{
    protected $table = 'card_milestones';

    protected $fillable = ['invitation_id', 'label', 'started_on', 'sort_order'];

    protected $casts = ['started_on' => 'date', 'sort_order' => 'integer'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }
}
