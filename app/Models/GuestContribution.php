<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestContribution extends Model
{
    // Solo permitimos created_at, deshabilitando updated_at al ser un historial
    const UPDATED_AT = null;

    protected $fillable = ['invitation_id', 'guest_id', 'type', 'content_text', 'file_path'];

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}