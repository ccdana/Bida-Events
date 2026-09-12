<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationPollOption extends Model
{
    // Las opciones se reemplazan completas al guardar la encuesta
    public $timestamps = false;

    protected $fillable = ['poll_id', 'label', 'value', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(InvitationPoll::class, 'poll_id');
    }
}
